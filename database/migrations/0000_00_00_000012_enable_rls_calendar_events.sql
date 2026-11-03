ALTER TABLE calendar_events ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "calendar_events_tenant_isolation" ON calendar_events;
CREATE POLICY "calendar_events_tenant_isolation" ON calendar_events
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);
