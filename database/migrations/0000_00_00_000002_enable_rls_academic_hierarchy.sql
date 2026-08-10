-- departments, classes, sections — একই RLS প্যাটার্ন (enable_rls_template.sql দ্রষ্টব্য)

ALTER TABLE departments ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "departments_tenant_isolation" ON departments;
CREATE POLICY "departments_tenant_isolation" ON departments
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);

ALTER TABLE classes ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "classes_tenant_isolation" ON classes;
CREATE POLICY "classes_tenant_isolation" ON classes
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);

ALTER TABLE sections ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "sections_tenant_isolation" ON sections;
CREATE POLICY "sections_tenant_isolation" ON sections
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);

-- institution_settings — এটা tenant টেবিল হলেও primary key নিজেই institution_id,
-- তাই policy টা একটু ভিন্ন (institution_id কলামের বদলে id/primary key চেক)
ALTER TABLE institution_settings ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "institution_settings_isolation" ON institution_settings;
CREATE POLICY "institution_settings_isolation" ON institution_settings
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);
