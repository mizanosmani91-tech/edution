-- ============================================================================
-- RLS TEMPLATE — Supabase auth.uid() থেকে Laravel session-variable-এ পোর্ট করা
-- ============================================================================
-- প্রতিটা tenant টেবিলের জন্য এই প্যাটার্ন কপি করুন (নিচে `students` উদাহরণ)।
--
-- আগে (Supabase):
--   USING (institution_id = (SELECT institution_id FROM profiles WHERE id = auth.uid()))
--
-- এখন (Laravel, session variable দিয়ে):
--   USING (institution_id = current_setting('app.current_institution_id')::uuid)
--
-- current_setting() সেই ভ্যালু পড়ে যেটা SetTenantContext middleware প্রতিটা
-- request-এ সেট করে দেয় (SELECT set_config('app.current_institution_id', ...)).
-- ============================================================================

-- ধাপ ১: RLS enable করুন টেবিলে (যদি আগে থেকে না থাকে)
ALTER TABLE students ENABLE ROW LEVEL SECURITY;

-- ধাপ ২: পুরনো Supabase-specific policy থাকলে ড্রপ করুন
DROP POLICY IF EXISTS "students_tenant_isolation" ON students;

-- ধাপ ৩: নতুন policy — Laravel session variable ব্যবহার করে
CREATE POLICY "students_tenant_isolation" ON students
    FOR ALL
    USING (
        institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid
    )
    WITH CHECK (
        institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid
    );

-- NULLIF(...,'')::uuid ব্যবহার করা হয়েছে যাতে session variable সেট না থাকলে
-- (খালি স্ট্রিং) casting error না দিয়ে policy fail-closed (কোনো row match না করে)
-- আচরণ করে — অর্থাৎ context না থাকলে কিছুই দেখা যাবে না, ডিফল্টে সব খোলা থাকবে না।

-- ============================================================================
-- ⚠️ কানেকশন role নোট:
-- এই policy তখনই কাজ করবে যদি Laravel যে Postgres role দিয়ে কানেক্ট করছে,
-- সেটা `SUPERUSER` বা `BYPASSRLS` attribute-বিহীন হয়। .env-এ যে DB user
-- ব্যবহার করছেন সেটা `postgres` superuser না হয়ে, একটা সাধারণ role হওয়া
-- বাধ্যতামূলক — নাহলে RLS silently বাইপাস হয়ে যাবে এবং কোনো error-ও দেখাবে না।
--
-- চেক করার কমান্ড:
--   SELECT rolname, rolsuper, rolbypassrls FROM pg_roles WHERE rolname = 'your_app_user';
-- rolsuper এবং rolbypassrls দুইটাই `false` হতে হবে।
-- ============================================================================
