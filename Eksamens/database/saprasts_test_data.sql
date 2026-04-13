-- Saprasts testa datu ievietošanas skripts
-- Šis fails satur parauga datus lietotnes testēšanai
-- Palaid šo pēc saprasts_rebuild.sql importēšanas

-- Importa laikā atslēdzam ārējo atslēgu pārbaudes
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TESTA DATI: KONTI
-- ============================================

-- Izdzēst esošos testa datus (neobligāti - atstāj aizkomentētas rindas, ja vēlies tos paturēt)
-- DELETE FROM accounts WHERE id > 1;

-- Lietotāji - ID 2, 3, 4, 5
INSERT INTO accounts (id, username, email, phone, password_hash, role, status)
VALUES 
(2, 'alice_j', 'alice@example.lv', '+37125555001', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'user', 'active'),
(3, 'bob_k', 'bob@example.lv', '+37125555002', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'user', 'active'),
(4, 'emma_l', 'emma@example.lv', '+37125555003', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'user', 'active'),
(5, 'john_m', 'john@example.lv', '+37125555004', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'user', 'active');

-- Psihologi - ID 6, 7, 8, 9
INSERT INTO accounts (id, username, email, phone, password_hash, role, status)
VALUES 
(6, 'dr_janis', 'janis.psilogs@example.lv', '+37125555010', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
(7, 'dr_dina', 'dina.psilogs@example.lv', '+37125555011', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
(8, 'dr_maris', 'maris.psilogs@example.lv', '+37125555012', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active'),
(9, 'dr_anna', 'anna.psilogs@example.lv', '+37125555013', '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S', 'psychologist', 'active');

-- ============================================
-- TESTA DATI: LIETOTĀJU PROFILI
-- ============================================

INSERT INTO user_profiles (account_id, first_name, last_name)
VALUES 
(2, 'Alīse', 'Jansone'),
(3, 'Bobs', 'Kalns'),
(4, 'Emma', 'Liepa'),
(5, 'Jānis', 'Meža');

-- ============================================
-- TESTA DATI: PSIHOLOGU PROFILI
-- ============================================

INSERT INTO psychologist_profiles (account_id, full_name, specialization, experience_years, description, image_path, hourly_rate, approved_at)
VALUES 
(6, 'Dr. Jānis Sīmons', 'Depresija un trauksme', 8, 'Specializējos depresijas un trauksmes traucējumu ārstēšanā. Pieredze ar kognitīvi biheiviorālo terapiju.', 'assets/Images/psih1.png', 50.00, NOW()),
(7, 'Dr. Dina Rāvens', 'Attiecību problēmas', 12, 'Specializācija pāru terapijā un ģimenes problēmu risināšanā. Draudzīga un empātiska pieeja.', 'assets/Images/psih2.png', 50.00, NOW()),
(8, 'Dr. Māris Ozoliņš', 'Traumas un PTSS', 15, 'Pieredzējis speciālists PTSS un traumu terapijā. EMDR un trauma-informētas terapijas metodes.', 'assets/Images/psih3.png', 50.00, NOW()),
(9, 'Dr. Anna Brinina', 'Bērnu psiholoģija', 6, 'Specialiste bērnu un pusaudžu psiholoģiskajās grūtībās. Lojāla un mierīga pieeja.', 'assets/Images/psih4.png', 50.00, NULL);

-- ============================================
-- TESTA DATI: PIEEJAMĪBAS SLOTI
-- ============================================

-- Jānis (2026-03-17 to 2026-03-23)
INSERT INTO availability_slots (psychologist_account_id, starts_at, ends_at, consultation_type, note)
VALUES 
(6, '2026-03-17 09:00:00', '2026-03-17 10:00:00', 'in_person', 'Pirmais pieņemšanas laiks'),
(6, '2026-03-17 11:00:00', '2026-03-17 12:00:00', 'online', NULL),
(6, '2026-03-18 14:00:00', '2026-03-18 15:00:00', 'in_person', 'Pēcpusdienas konsultācija'),
(6, '2026-03-19 10:00:00', '2026-03-19 11:00:00', 'online', NULL),
(6, '2026-03-20 15:00:00', '2026-03-20 16:00:00', 'online', 'Videokonsultācija');

-- Dina (pieejamības sloti)
INSERT INTO availability_slots (psychologist_account_id, starts_at, ends_at, consultation_type, note)
VALUES 
(7, '2026-03-17 13:00:00', '2026-03-17 14:00:00', 'online', NULL),
(7, '2026-03-18 10:00:00', '2026-03-18 11:00:00', 'in_person', NULL),
(7, '2026-03-19 15:00:00', '2026-03-19 16:00:00', 'in_person', 'Pāru terapija'),
(7, '2026-03-20 09:00:00', '2026-03-20 11:00:00', 'online', 'Ilgāka sesija');

-- Māris (pieejamības sloti)
INSERT INTO availability_slots (psychologist_account_id, starts_at, ends_at, consultation_type, note)
VALUES 
(8, '2026-03-17 16:00:00', '2026-03-17 17:00:00', 'online', NULL),
(8, '2026-03-19 09:00:00', '2026-03-19 10:00:00', 'in_person', NULL),
(8, '2026-03-21 14:00:00', '2026-03-21 15:30:00', 'online', 'EMDR sesija');

-- ============================================
-- TESTA DATI: PIERAKSTI
-- ============================================

-- Pieraksti, kas gaida apstiprinājumu
INSERT INTO appointments (user_account_id, psychologist_account_id, scheduled_at, consultation_type, status, user_name_snapshot, user_email_snapshot)
VALUES 
(2, 6, '2026-03-17 09:00:00', 'online', 'pending', 'Alīse Jansone', 'alice@example.lv'),
(3, 7, '2026-03-18 10:00:00', 'in_person', 'pending', 'Bobs Kalns', 'bob@example.lv'),
(4, 8, '2026-03-21 14:00:00', 'online', 'pending', 'Emma Liepa', 'emma@example.lv');

-- Apstiprināts pieraksts
INSERT INTO appointments (user_account_id, psychologist_account_id, scheduled_at, consultation_type, status, user_name_snapshot, user_email_snapshot)
VALUES 
(2, 7, '2026-03-19 15:00:00', 'in_person', 'approved', 'Alīse Jansone', 'alice@example.lv');

-- Atcelts pieraksts
INSERT INTO appointments (user_account_id, psychologist_account_id, scheduled_at, consultation_type, status, user_name_snapshot, user_email_snapshot)
VALUES 
(3, 6, '2026-03-20 15:00:00', 'online', 'cancelled', 'Bobs Kalns', 'bob@example.lv');

-- ============================================
-- TESTA DATI: PIERAKSTU NOTIKUMI (neobligāts žurnāls)
-- ============================================

INSERT INTO appointment_events (appointment_id, actor_account_id, event_type, note)
VALUES 
(2, 6, 'approved', 'Pieņemta pieraksta iesniegums'),
(3, 6, 'cancelled', 'Atcelts pēc lietotāja pieprasījuma');

-- ============================================
-- TESTA DATI: KONTAKTA ZIŅOJUMI
-- ============================================

INSERT INTO contact_messages (name, email, subject, message)
VALUES 
('Pēteris Liepa', 'peteris@example.lv', 'Jautājums par pakalpojumiem', 'Labdien! Gribētu uzzināt vairāk par jūsu pakalpojumiem un cenām.'),
('Laima Bērziņa', 'laima@example.lv', 'Tehniskas problēmas', 'Nevar pieteikties savā kontā. Lūdzu, palīdziet!'),
('Artūrs Viļķis', 'arturs@example.lv', 'Komentārs par platformu', 'Jūsu platforma ir fantastiska! Paldies par labu servisu.');

-- ============================================
-- TESTA DATI: TESTI
-- ============================================

INSERT INTO tests (title, description, status, created_by_account_id)
VALUES 
('Depresijas skrīnings (PHQ-9)', 'Pazīstams depresijas novērtēšanas tests. Palīdz noteikt depresijas simptomātiskuma pakāpi.', 'published', 1),
('Trauksmes traucējuma skrīnings (GAD-7)', 'Standarta instruments trauksmes traucējumu novērtēšanai. Ļoti informatīvs sākotnējā konsultācijā.', 'published', 1),
('Pašcieņas pašnovērtējums', 'Vienkāršs tests pašcieņas līmeņa noteikšanai. Derīgs patstāvīgai analīzei.', 'pending_review', 6),
('Attiecību apmierinātības skrīnings', 'Pāru attiecību kvalitātes pašnovērtējuma tests. Labi parāda problēmu jomas.', 'published', 7);

-- ============================================
-- TESTA DATI: TESTU JAUTĀJUMI
-- ============================================

-- PHQ-9 depresijas tests (jautājumi 1-9)
INSERT INTO test_questions (test_id, question_text, sort_order)
VALUES 
(1, 'Cik bieži pēdējās divās nedēļās jūs jutāties nomākts vai skumjš?', 1),
(1, 'Cik bieži jūs zaudējāt interesi par ikdienas aktivitātēm?', 2),
(1, 'Cik bieži jūs jūtāt nespēku un nogurumu?', 3),
(1, 'Cik bieži jums bija grūtības ar miegu vai pārmierīgu miegu?', 4),
(1, 'Cik bieži jūs jūtāties apspiesti vai nemierīgi?', 5),
(1, 'Cik bieži jūs jutāt pašvērtējuma samazināšanos?', 6),
(1, 'Cik bieži jums bija grūtības koncentrēties?', 7),
(1, 'Cik bieži jums bija pašnāvnieciskas domas?', 8),
(1, 'Cik bieži jūs jutāt dusmas pret sevi vai citiem?', 9);

-- GAD-7 trauksmes tests (jautājumi 10-16)
INSERT INTO test_questions (test_id, question_text, sort_order)
VALUES 
(2, 'Cik bieži jūs jūtāt trauksmi vai nervu saspringumu?', 1),
(2, 'Cik bieži jūs nespējat apstādināt vai kontrolēt raizes?', 2),
(2, 'Cik bieži jūs jūtāt bēdu vai uztraukumu par dažādiem jautājumiem?', 3),
(2, 'Cik bieži jūs nespējat atslābt?', 4),
(2, 'Cik bieži jūs esat tik nemierīgi, ka ir grūti sēdēt miera stāvoklī?', 5),
(2, 'Cik bieži jūs jutāt aizkaitināmību vai dusmas salīdzinoši viegli?', 6),
(2, 'Cik bieži jūs jūtāt bailes, it kā kaut kas bīstams varētu notikt?', 7);

-- Pašcieņas tests (jautājumi 17-24)
INSERT INTO test_questions (test_id, question_text, sort_order)
VALUES 
(3, 'Es esmu cilvēks, kurš pats spēj paveikt svarīgus uzdevumus.', 1),
(3, 'Es esmu apmierināts ar sevi tādu, kāds esmu.', 2),
(3, 'Cik bieži jūs jūtāt, ka jums ir nozīme?', 3),
(3, 'Es vēlētos būt vairāk apmierināts ar savu dzīvi.', 4),
(3, 'Es jūtos nespēcīgs daudzu situāciju risināšanā.', 5),
(3, 'Es bieži jūtos bezspēcīgs attiecībā uz savām spējām.', 6),
(3, 'Es vēlos sevi respektētu vairāk.', 7),
(3, 'Kopumā, cik augstu jūs sevi vērtējat?', 8);

-- Attiecību apmierinātības tests (jautājumi 25-29)
INSERT INTO test_questions (test_id, question_text, sort_order)
VALUES 
(4, 'Kopumā, cik apmierināts jūs esat ar savām attiecībām?', 1),
(4, 'Cik bieži jūs un jūsu partneris konfliktus risināt konstruktīvi?', 2),
(4, 'Cik bieži jūs jūtat emocionālu tuvību ar savu partneri?', 3),
(4, 'Cik bieži jūsu attiecībās ir fiziska tuvība un intimitāte?', 4),
(4, 'Cik bieži jūs jūtaties partnera saprasts?', 5);

-- ============================================
-- TESTA DATI: TESTU MĒĢINĀJUMI UN ATBILDES
-- ============================================
-- Piezīme: testa mēģinājumi un atbildes ir izņemtas auto-increment ID konfliktu dēļ
-- Jautājumi ir pieejami, lai lietotāji varētu pildīt testus lietotnē
-- Tos var pievienot atpakaļ, kad ir zināmi question ID no datubāzes

-- ============================================
-- TESTA DATI: RAKSTI
-- ============================================

INSERT INTO articles (psychologist_account_id, title, content, category, is_published)
VALUES 
(6, 'Depresijas simptomi un to atpazīšana', 'Depresija ir vairāk nekā vienkāršas skumjas vai stress. Tas ir psiholoģisks traucējums, kas ietekmē domas, jūtas, enerģiju un pašvērtējumu. Galvenie simptomi ietver pastāvīgu nomāktību, intereses zudumu par ierastajām aktivitātēm, miega traucējumus, nogurumu un grūtības koncentrēties. Ja šie simptomi saglabājas ilgāk par divām nedēļām, ir svarīgi meklēt profesionālu palīdzību.', 'Depresija', 1),
(6, 'Kognitīvi-uzvedības terapija: praktiski paņēmieni mājas apstākļos', 'KBT ir viena no efektīvākajām psihoterapijas metodēm depresijas un trauksmes ārstēšanā. Šajā rakstā atradīsiet praktiskus paņēmienus ikdienai: automātisko domu pierakstīšanu, uzvedības aktivizāciju un relaksācijas tehnikas. Šie vienkāršie soļi var palīdzēt uzlabot psiholoģisko pašsajūtu.', 'Pašpalīdzība', 1),
(7, 'Attiecības: kā parunāties ar partneri par svarīgiem jautājumiem', 'Daudzu pāru konfliktu pamatā ir neefektīva komunikācija. Šajā rakstā dalos ar paņēmieniem, kā runāt ar partneri mierīgi un cieņpilni, izvairoties no pārmetumiem. Izmēģiniet "es" izteikumus, aktīvo klausīšanos un skaidru emociju nosaukšanu. Labas attiecības sākas ar labu sarunu.', 'Attiecības', 1),
(8, 'PTSS un trauma: atveseļošanās ceļš', 'Traumas atveseļošana var būt grūts process, bet tā ir iespējama. Šajā rakstā pārskatu galvenos atveseļošanās soļus: stabilizāciju, traumas apstrādi un reintegrāciju. Es arī iepazīstinu ar EMDR un citām modernām traumu terapijas metodēm, kas var būt ļoti efektīvas.', 'Traumas', 1);

-- ============================================
-- PIEZĪMES
-- ============================================
-- Parole visiem testa kontiem: "password123" (adminam no rebuild.sql būs Admin123!)
-- Ielogošanās dati:
--   Lietotājs: alice_j / password123
--   Psihologs: dr_janis / password123
--   Administrators: admin / Admin123!
--
-- Testa datumi uzlikti no 2026-03-17 līdz 2026-03-21
-- Vajadzības gadījumā pielāgo pieejamības un pierakstu datumus savai testēšanai

-- Atkal ieslēdzam ārējo atslēgu pārbaudes
SET FOREIGN_KEY_CHECKS = 1;
