ALTER TABLE class_diary_entries ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "class_diary_entries_tenant_isolation" ON class_diary_entries;
CREATE POLICY "class_diary_entries_tenant_isolation" ON class_diary_entries
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);
