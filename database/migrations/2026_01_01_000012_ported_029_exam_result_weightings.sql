-- ==========================================================
-- ফ্লেক্সিবল গ্রেডিং/ওয়েটিং সিস্টেম — Laravel পোর্ট
-- (মূল উৎস: Edution Next.js প্রজেক্টের 029_exam_result_weightings.sql)
-- ==========================================================
--
-- যা বদলেছে (শুধু auth wiring, business logic অক্ষত):
--   auth.uid()              → current_setting('app.current_user_id', true)::uuid
--   profiles টেবিল          → users টেবিল (institution_id, role কলাম একই আছে)
--   institution_id RLS চেক  → current_setting('app.current_institution_id', true)::uuid
--                              (বাকি সব টেবিলের সাথে consistent প্যাটার্ন)
--
-- যা অক্ষত আছে (এক লাইনও বদলানো হয়নি):
--   get_own_exam_subject_marks() — students/exam_subjects/exam_marks join লজিক
--   compute_exam_subject_result() — রিকার্সিভ weighting resolution (scale + percentage)
--   get_effective_exam_marks()    — batch wrapper
--   exam_result_weightings টেবিলের সব constraint (chk_scale_fields, chk_percentage_fields,
--     chk_no_self_reference, unique constraint)
--
-- ⚠️ এই ফাইলটা চালানোর আগে নিশ্চিত করুন exams, subjects, classes, students,
-- exam_subjects, exam_marks, users টেবিল আগেই তৈরি হয়ে গেছে (migration
-- 2026_01_01_000001 থেকে 000011 পর্যন্ত)।
-- ==========================================================

create table if not exists exam_result_weightings (
  id uuid primary key default gen_random_uuid(),
  institution_id uuid not null references institutions(id) on delete cascade,
  target_exam_id uuid not null references exams(id) on delete cascade,
  source_exam_id uuid not null references exams(id) on delete cascade,
  class_id uuid references classes(id) on delete cascade,
  subject_id uuid references subjects(id) on delete cascade,
  contribution_type text not null check (contribution_type in ('scale', 'percentage')),
  group_key text,
  converted_max_marks numeric,
  weight_percentage numeric,
  require_source_pass boolean not null default false,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint chk_scale_fields check (
    (contribution_type = 'scale' and converted_max_marks is not null)
    or contribution_type = 'percentage'
  ),
  constraint chk_percentage_fields check (
    (contribution_type = 'percentage' and weight_percentage is not null)
    or contribution_type = 'scale'
  ),
  constraint chk_no_self_reference check (target_exam_id != source_exam_id),
  unique (target_exam_id, source_exam_id, class_id, subject_id, contribution_type)
);

create index if not exists idx_erw_target on exam_result_weightings(target_exam_id, subject_id);
create index if not exists idx_erw_source on exam_result_weightings(source_exam_id);
create index if not exists idx_erw_institution on exam_result_weightings(institution_id);

alter table exam_result_weightings enable row level security;

-- ⚠️ পরিবর্তিত: auth.uid() + profiles → session variable + users
create or replace function is_user_institution_admin(p_institution_id uuid)
returns boolean
language sql
security definer
stable
as $$
  select exists (
    select 1 from users
    where id = nullif(current_setting('app.current_user_id', true), '')::uuid
      and institution_id = p_institution_id
      and role = 'admin'
  );
$$;

drop policy if exists "institution_admin_manage_weightings" on exam_result_weightings;
create policy "institution_admin_manage_weightings"
  on exam_result_weightings
  for all
  using (is_user_institution_admin(institution_id))
  with check (is_user_institution_admin(institution_id));

-- ⚠️ পরিবর্তিত: profiles/auth.uid() লুকআপের বদলে সরাসরি session variable —
-- SetTenantContext middleware ইতিমধ্যে app.current_institution_id সেট করে
-- দেয় বলে এখানে আর subquery লাগে না, বাকি সব টেবিলের RLS প্যাটার্নের সাথে
-- consistent থাকল
drop policy if exists "institution_members_read_weightings" on exam_result_weightings;
create policy "institution_members_read_weightings"
  on exam_result_weightings
  for select
  using (institution_id = nullif(current_setting('app.current_institution_id', true), '')::uuid);

-- ==========================================================
-- নিচের তিনটা ফাংশন হুবহু অপরিবর্তিত (auth.uid() ব্যবহার করত না বলে
-- পোর্ট করার দরকারই হয়নি — pure business logic)
-- ==========================================================

create or replace function get_own_exam_subject_marks(
  p_student_id uuid,
  p_exam_id uuid,
  p_subject_id uuid,
  out marks_obtained numeric,
  out is_absent boolean,
  out full_marks numeric,
  out pass_marks numeric,
  out exam_subject_id uuid
)
language sql
security definer
stable
as $$
  select em.marks_obtained, coalesce(em.is_absent, false), es.full_marks, es.pass_marks, es.id
  from students st
  join exam_subjects es
    on es.exam_id = p_exam_id
   and es.subject_id = p_subject_id
   and es.class_id = st.class_id
  left join exam_marks em
    on em.exam_subject_id = es.id
   and em.student_id = p_student_id
  where st.id = p_student_id;
$$;

create or replace function compute_exam_subject_result(
  p_student_id uuid,
  p_exam_id uuid,
  p_subject_id uuid,
  p_visited uuid[] default '{}'::uuid[],
  out final_marks numeric,
  out final_max_marks numeric,
  out is_absent boolean,
  out is_pass boolean
)
language plpgsql
security definer
stable
as $$
declare
  v_own record;
  v_scale_total numeric := 0;
  v_pct_contribution numeric := 0;
  v_pct_weight_sum numeric := 0;
  v_final_max numeric;
  rec record;
  child_result record;
  source_id uuid;
  v_group_avg numeric;
  v_group_count int;
  v_all_required_passed boolean := true;
begin
  if p_exam_id = any(p_visited) then
    final_marks := null; final_max_marks := 0; is_absent := true; is_pass := false;
    return;
  end if;

  select * into v_own from get_own_exam_subject_marks(p_student_id, p_exam_id, p_subject_id);

  if v_own.is_absent then
    final_marks := null; final_max_marks := coalesce(v_own.full_marks, 0); is_absent := true; is_pass := false;
    return;
  end if;

  v_scale_total := coalesce(v_own.marks_obtained, 0);
  v_final_max := coalesce(v_own.full_marks, 0);

  for rec in
    select group_key, converted_max_marks, require_source_pass,
           array_agg(distinct source_exam_id) as source_exams
    from exam_result_weightings
    where target_exam_id = p_exam_id
      and (subject_id = p_subject_id or subject_id is null)
      and contribution_type = 'scale'
    group by group_key, converted_max_marks, require_source_pass
  loop
    v_group_avg := 0;
    v_group_count := 0;

    foreach source_id in array rec.source_exams
    loop
      select * into child_result from compute_exam_subject_result(
        p_student_id, source_id, p_subject_id, p_visited || p_exam_id
      );

      if rec.require_source_pass and not child_result.is_pass then
        v_all_required_passed := false;
      end if;

      if not child_result.is_absent and coalesce(child_result.final_max_marks, 0) > 0 then
        v_group_avg := v_group_avg + (child_result.final_marks / child_result.final_max_marks);
        v_group_count := v_group_count + 1;
      end if;
    end loop;

    if v_group_count > 0 then
      v_scale_total := v_scale_total + (v_group_avg / v_group_count) * rec.converted_max_marks;
      v_final_max := v_final_max + rec.converted_max_marks;
    end if;
  end loop;

  for rec in
    select source_exam_id, weight_percentage, require_source_pass
    from exam_result_weightings
    where target_exam_id = p_exam_id
      and (subject_id = p_subject_id or subject_id is null)
      and contribution_type = 'percentage'
  loop
    select * into child_result from compute_exam_subject_result(
      p_student_id, rec.source_exam_id, p_subject_id, p_visited || p_exam_id
    );

    if rec.require_source_pass and not child_result.is_pass then
      v_all_required_passed := false;
    end if;

    v_pct_contribution := v_pct_contribution +
      coalesce(child_result.final_marks, 0) * (rec.weight_percentage / 100.0);
    v_pct_weight_sum := v_pct_weight_sum + (rec.weight_percentage / 100.0);
  end loop;

  final_marks := v_scale_total * (1 - v_pct_weight_sum) + v_pct_contribution;
  final_max_marks := v_final_max;
  is_absent := false;
  is_pass := v_all_required_passed and
    (final_marks >= coalesce(v_own.pass_marks, (v_final_max * 0.33)));
end;
$$;

create or replace function get_effective_exam_marks(p_exam_id uuid, p_class_id uuid)
returns table(
  exam_subject_id uuid,
  student_id uuid,
  marks_obtained numeric,
  full_marks numeric,
  is_absent boolean
)
language plpgsql
security definer
stable
as $$
declare
  rec record;
  result record;
begin
  for rec in
    select es.id as exam_subject_id, es.subject_id, st.id as student_id
    from exam_subjects es
    join students st on st.class_id = es.class_id and st.status = 'active'
    where es.exam_id = p_exam_id and es.class_id = p_class_id
  loop
    select * into result from compute_exam_subject_result(rec.student_id, p_exam_id, rec.subject_id);
    exam_subject_id := rec.exam_subject_id;
    student_id := rec.student_id;
    marks_obtained := result.final_marks;
    full_marks := result.final_max_marks;
    is_absent := result.is_absent;
    return next;
  end loop;
end;
$$;
