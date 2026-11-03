-- অডিট লগ append-only: শুধু SELECT ও INSERT পলিসি আছে, UPDATE/DELETE-এর নেই,
-- তাই RLS-এর অধীনে কেউ লগ বদলাতে বা মুছতে পারে না।
ALTER TABLE audit_logs ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "audit_logs_tenant_select" ON audit_logs;
DROP POLICY IF EXISTS "audit_logs_tenant_insert" ON audit_logs;
CREATE POLICY "audit_logs_tenant_select" ON audit_logs
    FOR SELECT
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);
CREATE POLICY "audit_logs_tenant_insert" ON audit_logs
    FOR INSERT
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);
