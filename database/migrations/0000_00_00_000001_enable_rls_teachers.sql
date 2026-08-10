-- teachers টেবিলের জন্য RLS (students এর একই প্যাটার্ন — enable_rls_template.sql দেখুন বিস্তারিত ব্যাখ্যার জন্য)

ALTER TABLE teachers ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "teachers_tenant_isolation" ON teachers;

CREATE POLICY "teachers_tenant_isolation" ON teachers
    FOR ALL
    USING (
        institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid
    )
    WITH CHECK (
        institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid
    );
