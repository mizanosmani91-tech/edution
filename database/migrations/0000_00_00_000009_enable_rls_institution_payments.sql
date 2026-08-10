-- institution_payments — সাধারণ tenant policy + superadmin bypass, দুইটাই।
-- Institution এর নিজের admin শুধু তার institution এর payment দেখবে/জমা দেবে।
-- Superadmin (app.is_superadmin='true') সব institution এর payment দেখতে/
-- approve করতে পারবে — কারণ তাদের app.current_institution_id খালি থাকে।
ALTER TABLE institution_payments ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "institution_payments_tenant_or_superadmin" ON institution_payments;
CREATE POLICY "institution_payments_tenant_or_superadmin" ON institution_payments
    FOR ALL
    USING (
        institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid
        OR current_setting('app.is_superadmin', true) = 'true'
    )
    WITH CHECK (
        institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid
        OR current_setting('app.is_superadmin', true) = 'true'
    );
