-- Papildu psihologi dashboard lapošanas testēšanai
-- Palaid šo pēc saprasts_rebuild.sql un saprasts_test_data.sql importēšanas

START TRANSACTION;

INSERT INTO accounts (username, email, phone, password_hash, role, status)
VALUES
('test_psih_01', 'test.psih.01@example.lv', '+37120000001', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
('test_psih_02', 'test.psih.02@example.lv', '+37120000002', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
('test_psih_03', 'test.psih.03@example.lv', '+37120000003', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
('test_psih_04', 'test.psih.04@example.lv', '+37120000004', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
('test_psih_05', 'test.psih.05@example.lv', '+37120000005', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
('test_psih_06', 'test.psih.06@example.lv', '+37120000006', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
('test_psih_07', 'test.psih.07@example.lv', '+37120000007', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
('test_psih_08', 'test.psih.08@example.lv', '+37120000008', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active')
ON DUPLICATE KEY UPDATE
    phone = VALUES(phone),
    status = VALUES(status);

INSERT INTO psychologist_profiles (account_id, full_name, specialization, experience_years, description, image_path, hourly_rate, approved_at)
SELECT
    a.id,
    src.full_name,
    src.specialization,
    src.experience_years,
    src.description,
    NULL,
    src.hourly_rate,
    NOW()
FROM (
    SELECT 'test_psih_01' AS username, 'Dr. Laura Ozola' AS full_name, 'Trauksme' AS specialization, 7 AS experience_years, 'Strādā ar trauksmes, stresa un pašvērtējuma jautājumiem.' AS description, 45.00 AS hourly_rate
    UNION ALL SELECT 'test_psih_02', 'Dr. Mārtiņš Liepa', 'Depresija un trauksme', 10, 'Palīdz pie depresijas, trauksmes un izdegšanas simptomiem.', 50.00
    UNION ALL SELECT 'test_psih_03', 'Dr. Elīna Kalniņa', 'Attiecību terapija', 6, 'Specializējas pāru komunikācijas un konfliktu risināšanā.', 55.00
    UNION ALL SELECT 'test_psih_04', 'Dr. Rihards Bērziņš', 'Ģimenes terapija', 9, 'Strādā ar ģimenes attiecībām un emocionālo līdzsvaru mājās.', 50.00
    UNION ALL SELECT 'test_psih_05', 'Dr. Ieva Zariņa', 'Bērnu un pusaudžu psiholoģija', 8, 'Palīdz bērniem un pusaudžiem adaptācijas un emociju regulācijas jautājumos.', 45.00
    UNION ALL SELECT 'test_psih_06', 'Dr. Artūrs Vītols', 'Trauma un PTSS', 11, 'Pieredze darbā ar traumām, PTSS un drošas stabilizācijas metodēm.', 60.00
    UNION ALL SELECT 'test_psih_07', 'Dr. Līga Siliņa', 'Atkarību terapija', 12, 'Atbalsts atkarību mazināšanā un ilgtermiņa atveseļošanās plānošanā.', 55.00
    UNION ALL SELECT 'test_psih_08', 'Dr. Sandra Krūmiņa', 'Stresa vadība un izdegšana', 5, 'Palīdz sakārtot ikdienas slodzi, robežas un atjaunot enerģiju.', 40.00
) AS src
JOIN accounts a ON a.username = src.username
LEFT JOIN psychologist_profiles p ON p.account_id = a.id
WHERE p.account_id IS NULL;

COMMIT;