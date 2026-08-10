ALTER TABLE guardian_student ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "guardian_student_tenant_isolation" ON guardian_student;
CREATE POLICY "guardian_student_tenant_isolation" ON guardian_student
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);
