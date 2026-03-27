-- Saprasts Test Data Insert Script
-- This file contains sample data for testing the application
-- Run this AFTER running saprasts_rebuild.sql

-- Disable foreign key checks during import
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TEST DATA: ACCOUNTS
-- ============================================

-- Delete existing test data (optional - comment out if you want to keep existing data)
-- DELETE FROM accounts WHERE id > 1;

-- Users (regular customers) - IDs 2, 3, 4, 5
INSERT INTO accounts (id, username, email, phone, password_hash, role, status)
VALUES 
(2, 'alice_j', 'alice@example.lv', '+37125555001', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'user', 'active'),
(3, 'bob_k', 'bob@example.lv', '+37125555002', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'user', 'active'),
(4, 'emma_l', 'emma@example.lv', '+37125555003', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'user', 'active'),
(5, 'john_m', 'john@example.lv', '+37125555004', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'user', 'active');

-- Psychologists - IDs 6, 7, 8, 9
INSERT INTO accounts (id, username, email, phone, password_hash, role, status)
VALUES 
(6, 'dr_janis', 'janis.psilogs@example.lv', '+37125555010', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
(7, 'dr_dina', 'dina.psilogs@example.lv', '+37125555011', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
(8, 'dr_maris', 'maris.psilogs@example.lv', '+37125555012', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
(9, 'dr_anna', 'anna.psilogs@example.lv', '+37125555013', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active');

-- ============================================
-- TEST DATA: USER PROFILES
-- ============================================

INSERT INTO user_profiles (account_id, first_name, last_name)
VALUES 
(2, 'Alīse', 'Jansone'),
(3, 'Bobs', 'Kalns'),
(4, 'Emma', 'Liepa'),
(5, 'Jānis', 'Meža');

-- ============================================
-- TEST DATA: PSYCHOLOGIST PROFILES
-- ============================================

INSERT INTO psychologist_profiles (account_id, full_name, specialization, experience_years, description, image_path, hourly_rate, approved_at)
VALUES 
(6, 'Dr. Jānis Sīmons', 'Depresija un anxiety', 8, 'Specializējos depresijas un trauksmes traucējumu ārstēšanā. Pieredze ar kognitīvi-uzvedības terapiju.', 'Images/psih1.png', 50.00, NOW()),
(7, 'Dr. Dina Rāvens', 'Attiecību problēmas', 12, 'Specializācija pāru terapijā un ģimenes problēmu risināšanā. Draudzīga un empātiska pieeja.', 'Images/psih2.png', 50.00, NOW()),
(8, 'Dr. Māris Ozoliņš', 'Traumas un PTSD', 15, 'Pieredzējis speciālists PTSD un traumu terapijā. EMDR un trauma-informētas terapijas metodes.', 'Images/psih3.png', 50.00, NOW()),
(9, 'Dr. Anna Brinina', 'Bērnu psihologija', 6, 'Specialiste bērnu un pusaudžu psiholoģiskajās problēmās. Lojāla un mierīga pieeja.', 'Images/psih4.png', 50.00, NULL);

-- ============================================
-- TEST DATA: AVAILABILITY SLOTS
-- ============================================

-- Jānis (2026-03-17 to 2026-03-23)
INSERT INTO availability_slots (psychologist_account_id, starts_at, ends_at, note)
VALUES 
(6, '2026-03-17 09:00:00', '2026-03-17 10:00:00', 'Pirmais pieņemšanas laiks'),
(6, '2026-03-17 11:00:00', '2026-03-17 12:00:00', NULL),
(6, '2026-03-18 14:00:00', '2026-03-18 15:00:00', 'Pēcpusdienas konsultācija'),
(6, '2026-03-19 10:00:00', '2026-03-19 11:00:00', NULL),
(6, '2026-03-20 15:00:00', '2026-03-20 16:00:00', 'Videokonsultācija');

-- Dina (pieejamības sloti)
INSERT INTO availability_slots (psychologist_account_id, starts_at, ends_at, note)
VALUES 
(7, '2026-03-17 13:00:00', '2026-03-17 14:00:00', NULL),
(7, '2026-03-18 10:00:00', '2026-03-18 11:00:00', NULL),
(7, '2026-03-19 15:00:00', '2026-03-19 16:00:00', 'Pāru terapija'),
(7, '2026-03-20 09:00:00', '2026-03-20 11:00:00', 'Ilgāka sesija');

-- Māris (pieejamības sloti)
INSERT INTO availability_slots (psychologist_account_id, starts_at, ends_at, note)
VALUES 
(8, '2026-03-17 16:00:00', '2026-03-17 17:00:00', NULL),
(8, '2026-03-19 09:00:00', '2026-03-19 10:00:00', NULL),
(8, '2026-03-21 14:00:00', '2026-03-21 15:30:00', 'EMDR sesija');

-- ============================================
-- TEST DATA: APPOINTMENTS
-- ============================================

-- Pending appointments
INSERT INTO appointments (user_account_id, psychologist_account_id, scheduled_at, consultation_type, status, user_name_snapshot, user_email_snapshot)
VALUES 
(2, 6, '2026-03-17 09:00:00', 'online', 'pending', 'Alīse Jansone', 'alice@example.lv'),
(3, 7, '2026-03-18 10:00:00', 'in_person', 'pending', 'Bobs Kalns', 'bob@example.lv'),
(4, 8, '2026-03-21 14:00:00', 'online', 'pending', 'Emma Liepa', 'emma@example.lv');

-- Approved appointment
INSERT INTO appointments (user_account_id, psychologist_account_id, scheduled_at, consultation_type, status, user_name_snapshot, user_email_snapshot)
VALUES 
(2, 7, '2026-03-19 15:00:00', 'in_person', 'approved', 'Alīse Jansone', 'alice@example.lv');

-- Cancelled appointment
INSERT INTO appointments (user_account_id, psychologist_account_id, scheduled_at, consultation_type, status, user_name_snapshot, user_email_snapshot)
VALUES 
(3, 6, '2026-03-20 15:00:00', 'online', 'cancelled', 'Bobs Kalns', 'bob@example.lv');

-- ============================================
-- TEST DATA: APPOINTMENT EVENTS (optional log)
-- ============================================

INSERT INTO appointment_events (appointment_id, actor_account_id, event_type, note)
VALUES 
(2, 6, 'approved', 'Pieņemta pieraksta iesniegums'),
(3, 6, 'cancelled', 'Atcelts pēc lietotāja pieprasījuma');

-- ============================================
-- TEST DATA: CONTACT MESSAGES
-- ============================================

INSERT INTO contact_messages (name, email, subject, message)
VALUES 
('Pēteris Liepa', 'peteris@example.lv', 'Jautājums par pakalpojumiem', 'Labdien! Gribētu uzzināt vairāk par jūsu pakalpojumiem un cenām.'),
('Laima Bērziņa', 'laima@example.lv', 'Tehniski problēmi', 'Nevar pieteikties savā kontā. Lūdzu palīdziet!'),
('Artūrs Viļķis', 'arturs@example.lv', 'Komentārs par platformu', 'Jūsu platforma ir fantastiska! Paldies par labu servisu.');

-- ============================================
-- TEST DATA: TESTS
-- ============================================

INSERT INTO tests (title, description, status, created_by_account_id)
VALUES 
('Depresijas skrīnings (PHQ-9)', 'Pazīstams depresijas novērtēšanas tests. Palīdz noteikt depresijas simptomātiskuma pakāpi.', 'published', 1),
('Trauksmes traucējuma skrīnings (GAD-7)', 'Standarts instrumenti trauksmes traucējumu novērtēšanai. Ľoti informatīvs sākotnējā konsultācijā.', 'published', 1),
('Pašcieņas pašnovērtējums', 'Vienkāršs tests pašcieņas līmeņa noteikšanai. Derīgs patstāvīgai analīzei.', 'pending_review', 6),
('Attiecību apmierinātības skrīnings', 'Pāru attiecību kvalitātes likten klausīšanas tests. Labi parāda problēmas jomas.', 'published', 7);

-- ============================================
-- TEST DATA: TEST QUESTIONS
-- ============================================

-- PHQ-9 Depression Test (Questions 1-9)
INSERT INTO test_questions (test_id, question_text, sort_order)
VALUES 
(1, 'Cik bieži pēdējās divas nedēļas jūs jūtāt depresijas vai skumjuma apziņu?', 1),
(1, 'Cik bieži jūs zaudējāt interesi par ikdienas aktivitātēm?', 2),
(1, 'Cik bieži jūs jūtāt nespēku un nogurumu?', 3),
(1, 'Cik bieži jums bija grūtības ar miegu vai pārmierīgu miegu?', 4),
(1, 'Cik bieži jūs jūtāties apspiesti vai nemierīgi?', 5),
(1, 'Cik bieži jūs jūtāt vērtības jausmu zaudēšanu?', 6),
(1, 'Cik bieži jums bija grūtības koncentrēties?', 7),
(1, 'Cik bieži kaut kas izšķir jūsu pašnāvnieciskos domas?', 8),
(1, 'Cik bieži jūs jūtāt naidīgus jūtas pret sevi vai citiem?', 9);

-- GAD-7 Anxiety Test (Questions 10-16)
INSERT INTO test_questions (test_id, question_text, sort_order)
VALUES 
(2, 'Cik bieži jūs jūtāt trauksmi vai nervu saspringumu?', 1),
(2, 'Cik bieži jūs nespējat apstādinājt vai kontrolēt raizes?', 2),
(2, 'Cik bieži jūs jūtāt bēdu vai uztraukumu par dažādiem jautājumiem?', 3),
(2, 'Cik bieži jūs nespējat atslābt?', 4),
(2, 'Cik bieži jūs esat tik nemierīgi, ka ir grūti sēdēt miera stāvoklī?', 5),
(2, 'Cik bieži jūs jūtāt irritāciju vai dusmību salīdzinoši viegli?', 6),
(2, 'Cik bieži jūs jūtāt bailes, it kā kaut kas bīstams varētu notikt?', 7);

-- Self-Esteem Test (Questions 17-24)
INSERT INTO test_questions (test_id, question_text, sort_order)
VALUES 
(3, 'Es esmu personīgums, kas viens pats var pabeigt svarīgus uzdevumus.', 1),
(3, 'Es esmu apmierināts ar sevi tādu, kāds esmu.', 2),
(3, 'Cik bieži jūs jūtāt, ka jums ir nozīme?', 3),
(3, 'Es vēlos, lai es būtu laimīgāks with my life (skaņa nevajag butu pārtulkota).', 4),
(3, 'Es jūtos nespēcīgs daudzu situāciju risināšanā.', 5),
(3, 'Es bieži jūtos nāvīgs attiecībā uz saviem spēkiem.', 6),
(3, 'Es vēlos sevi respektētu vairāk.', 7),
(3, 'Kopumā, cik lielā mērā jūs sevis novērtējat?', 8);

-- Relationship Satisfaction Test (Questions 25-29)
INSERT INTO test_questions (test_id, question_text, sort_order)
VALUES 
(4, 'Kopumā, cik apmierināties jūs ar jūsu attiecībām?', 1),
(4, 'Cik bieži jūs un jūsu partnera pārvaldāt konfliktus konstruktīvi?', 2),
(4, 'Cik bieži jūs jūtāt emocionālus tuvinājumu ar savu partneri?', 3),
(4, 'Cik bieži jums ir fiziska tāla un intimitate?', 4),
(4, 'Cik bieži jūs justies saprastu no sava partnera?', 5);

-- ============================================
-- TEST DATA: TEST ATTEMPTS & ANSWERS
-- ============================================
-- Note: Test attempts/answers removed due to auto-increment ID conflicts
-- Questions are available for users to take tests in the UI
-- These can be added back once question IDs are known from the database

-- ============================================
-- TEST DATA: ARTICLES
-- ============================================

INSERT INTO articles (psychologist_account_id, title, content, category, is_published)
VALUES 
(6, 'Depresijas simptomi un to atpazīšana', 'Depresija ir vairāk nekā vienkārša skumja vai stresu. Tas ir psiholoģisks traucējums, kas ietekmē mūsu domus, jūtas, enerģiju un pašnovērtējumu. Galvenie simptomi ietver pastāvīgu skumju jausmu, intereses zaudēšanu agrāk baudītajās aktivitātēs, miega traucējumus, nogurumu, grūtības koncentrēties. Ja jūs jūtaties šādi ilgāk par divām nedēļām, ir svarīgi meklēt profesionālu palīdzību.', 'Depresija', 1),
(6, 'Kognitīvi-uzvedības terapija: praktiski paņēmieni mājas apstākļos', 'KBT ir viena no efektīvākajām psihoterapijas metodēm depresijas un trauksmes ārstēšanā. Iemācieties pamatnodarbības, kas jūs varat praktizēt katru dienu: automātisko doma žurnālošana, uzvedības aktivizācija, relaksācijas paņēmieni. Šie vienkāršie uzdevumi var ievērojami uzlabot jūsu psiholoģisko labsajūtu.', 'Pašpalīdzība', 1),
(7, 'Attiecības: kā parunāties ar partneri par svarīgiem jautājumiem', 'Daudz pāru konfliktiem ir pamatā nepareiza komunikācija. Šajā rakstā es dalīšos efektīviem panēmieniem jūsu partnera sarunāšanai bez apvainošanas un klemšanas. Iemācities "es" izteikumus, aktīvo klausīšanos un neitru emociju runāšanas valodu. Labas attiecības = labā saziņa.', 'Attiecības', 1),
(8, 'PTSD un trauma: atveseļošanās maršruts', 'Traumas atveseļošana var būt grūts process, bet iespējams. Šajā rakstā pārskatu galvenos atveseļošanas soļus: stabilizācija, traumas apstrāde, reintegrācija. Es arī iepazīstinu ar EMDR un citu moderno traumu terapijas metozi, kas var būt ļoti efektīvas.', 'Traumas', 1);

-- ============================================
-- NOTES
-- ============================================
-- Password for all test accounts: "password123" (will be Admin123! from rebuild.sql bcrypt hash)
-- To login as:
--   User: alice_j / password123
--   Psychologist: dr_janis / password123
--   Admin: admin / Admin123!
--
-- Test dates are set to March 17-21, 2026 (near the current date of March 16)
-- Adjust availability and appointment dates as needed for your testing

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
