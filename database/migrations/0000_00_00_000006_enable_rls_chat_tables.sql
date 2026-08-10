ALTER TABLE conversations ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "conversations_tenant_isolation" ON conversations;
CREATE POLICY "conversations_tenant_isolation" ON conversations
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);

ALTER TABLE conversation_participants ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "conversation_participants_tenant_isolation" ON conversation_participants;
CREATE POLICY "conversation_participants_tenant_isolation" ON conversation_participants
    FOR ALL
    USING (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid)
    WITH CHECK (institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid);

-- messages: institution isolation তো লাগবেই, কিন্তু এটাই যথেষ্ট না —
-- একই institution এর দুইজন ইউজার একে অপরের ব্যক্তিগত চ্যাট পড়তে পারা উচিত
-- না যদি তারা সেই conversation এর participant না হয়। তাই এখানে দ্বিতীয়
-- শর্ত: conversation_participants এ current_user_id থাকতে হবে।
ALTER TABLE messages ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "messages_participant_only" ON messages;
CREATE POLICY "messages_participant_only" ON messages
    FOR ALL
    USING (
        institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid
        AND EXISTS (
            SELECT 1 FROM conversation_participants cp
            WHERE cp.conversation_id = messages.conversation_id
              AND cp.user_id = NULLIF(current_setting('app.current_user_id', true), '')::uuid
        )
    )
    WITH CHECK (
        institution_id = NULLIF(current_setting('app.current_institution_id', true), '')::uuid
        AND EXISTS (
            SELECT 1 FROM conversation_participants cp
            WHERE cp.conversation_id = messages.conversation_id
              AND cp.user_id = NULLIF(current_setting('app.current_user_id', true), '')::uuid
        )
    );
