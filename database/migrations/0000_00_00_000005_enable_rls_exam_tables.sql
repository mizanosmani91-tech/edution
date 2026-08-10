ALTER TABLE exams ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "exams_tenant_isolation" ON exams;
CREATE POLICY "exams_tenant_isolation" ON exams
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);

ALTER TABLE subjects ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "subjects_tenant_isolation" ON subjects;
CREATE POLICY "subjects_tenant_isolation" ON subjects
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);

ALTER TABLE exam_subjects ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "exam_subjects_tenant_isolation" ON exam_subjects;
CREATE POLICY "exam_subjects_tenant_isolation" ON exam_subjects
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);

ALTER TABLE exam_marks ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "exam_marks_tenant_isolation" ON exam_marks;
CREATE POLICY "exam_marks_tenant_isolation" ON exam_marks
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);
