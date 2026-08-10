ALTER TABLE routine_periods ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "routine_periods_tenant_isolation" ON routine_periods;
CREATE POLICY "routine_periods_tenant_isolation" ON routine_periods
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);
