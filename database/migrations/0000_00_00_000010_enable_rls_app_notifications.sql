ALTER TABLE app_notifications ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "app_notifications_own_only" ON app_notifications;
-- ⚠️ শুধু institution না, নিজের notification ছাড়া অন্য কারো দেখা যাবে না
CREATE POLICY "app_notifications_own_only" ON app_notifications
    FOR ALL
    USING (
        institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid
        AND user_id = NULLIF(current_setting('app.current_user_id', true), '')::uuid
    )
    WITH CHECK (
        institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid
    );
