ALTER TABLE fee_collections ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "fee_collections_tenant_isolation" ON fee_collections;
CREATE POLICY "fee_collections_tenant_isolation" ON fee_collections
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);
