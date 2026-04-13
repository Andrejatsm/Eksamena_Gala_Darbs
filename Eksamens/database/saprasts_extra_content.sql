-- Saprasts papildu satura seed fails
-- Palaid šo pēc saprasts_rebuild.sql un (vēlams) saprasts_test_data.sql
-- Mērķis: iedot vairāk rakstu/testu demonstrācijai un daļu atstāt gaidīšanas statusā.

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- PAPILDU TESTI
-- ============================================

-- Publicēti testi
INSERT INTO tests (title, description, status, created_by_account_id)
SELECT
  'Stresa pārvaldības pašnovērtējums',
  'Tests palīdz novērtēt ikdienas stresa līmeni un stresa pārvaldības paradumus.',
  'published',
  (SELECT id FROM accounts WHERE username = 'dr_janis' LIMIT 1)
WHERE NOT EXISTS (
  SELECT 1 FROM tests WHERE title = 'Stresa pārvaldības pašnovērtējums'
);

INSERT INTO tests (title, description, status, created_by_account_id)
SELECT
  'Izdegšanas riska skrīnings',
  'Īss skrīnings profesionālās izdegšanas pazīmju novērtēšanai.',
  'published',
  (SELECT id FROM accounts WHERE username = 'dr_maris' LIMIT 1)
WHERE NOT EXISTS (
  SELECT 1 FROM tests WHERE title = 'Izdegšanas riska skrīnings'
);

-- Gaidot admin apstiprinājumu
INSERT INTO tests (title, description, status, created_by_account_id)
SELECT
  'Sociālās trauksmes indikators',
  'Pašnovērtējuma tests sociālās trauksmes pazīmju sākotnējai izvērtēšanai.',
  'pending_review',
  (SELECT id FROM accounts WHERE username = 'dr_dina' LIMIT 1)
WHERE NOT EXISTS (
  SELECT 1 FROM tests WHERE title = 'Sociālās trauksmes indikators'
);

INSERT INTO tests (title, description, status, created_by_account_id)
SELECT
  'Miega kvalitātes pašnovērtējums',
  'Palīdz novērtēt miega kvalitāti un iespējamos miega higiēnas riskus.',
  'pending_review',
  (SELECT id FROM accounts WHERE username = 'dr_anna' LIMIT 1)
WHERE NOT EXISTS (
  SELECT 1 FROM tests WHERE title = 'Miega kvalitātes pašnovērtējums'
);

-- Jautājumi testiem
INSERT INTO test_questions (test_id, question_text, sort_order)
SELECT t.id, q.question_text, q.sort_order
FROM tests t
JOIN (
  SELECT 1 AS sort_order, 'Cik bieži dienas laikā jūties emocionāli noslogots?' AS question_text
  UNION ALL SELECT 2, 'Cik bieži izjūti grūtības atslābināties pēc darba vai mācībām?'
  UNION ALL SELECT 3, 'Vai tev ir skaidrs personīgs plāns stresa mazināšanai?'
  UNION ALL SELECT 4, 'Cik bieži izmanto pauzes vai elpošanas tehnikas?'
  UNION ALL SELECT 5, 'Cik bieži stress ietekmē tavu miegu?'
) q
WHERE t.title = 'Stresa pārvaldības pašnovērtējums'
  AND NOT EXISTS (
    SELECT 1 FROM test_questions tq WHERE tq.test_id = t.id
  );

INSERT INTO test_questions (test_id, question_text, sort_order)
SELECT t.id, q.question_text, q.sort_order
FROM tests t
JOIN (
  SELECT 1 AS sort_order, 'Darba dienas beigās jūtos pilnībā iztukšots.' AS question_text
  UNION ALL SELECT 2, 'Man kļūst arvien grūtāk saglabāt motivāciju ikdienas pienākumiem.'
  UNION ALL SELECT 3, 'Bieži jūtu cinismu vai vienaldzību pret darbu/mācībām.'
  UNION ALL SELECT 4, 'Atpūtai neatliek laika vai enerģijas.'
  UNION ALL SELECT 5, 'Jūtu, ka manu ieguldījumu nepamana vai nenovērtē.'
) q
WHERE t.title = 'Izdegšanas riska skrīnings'
  AND NOT EXISTS (
    SELECT 1 FROM test_questions tq WHERE tq.test_id = t.id
  );

INSERT INTO test_questions (test_id, question_text, sort_order)
SELECT t.id, q.question_text, q.sort_order
FROM tests t
JOIN (
  SELECT 1 AS sort_order, 'Publiskās situācijās baidos, ka citi mani vērtēs negatīvi.' AS question_text
  UNION ALL SELECT 2, 'Pirms sociāliem notikumiem izjūtu izteiktu satraukumu.'
  UNION ALL SELECT 3, 'Sociālu situāciju dēļ dažkārt izvairos no tikšanās reizēm.'
  UNION ALL SELECT 4, 'Uztraukums traucē man runāt vai izteikties grupā.'
  UNION ALL SELECT 5, 'Pēc sociālām situācijām ilgi analizēju savu uzvedību.'
) q
WHERE t.title = 'Sociālās trauksmes indikators'
  AND NOT EXISTS (
    SELECT 1 FROM test_questions tq WHERE tq.test_id = t.id
  );

INSERT INTO test_questions (test_id, question_text, sort_order)
SELECT t.id, q.question_text, q.sort_order
FROM tests t
JOIN (
  SELECT 1 AS sort_order, 'Cik bieži ej gulēt vienā un tajā pašā laikā?' AS question_text
  UNION ALL SELECT 2, 'Vai nakts laikā bieži pamosties un grūti aizmigt no jauna?'
  UNION ALL SELECT 3, 'Vai no rīta jūties atpūties?' 
  UNION ALL SELECT 4, 'Vai lieto telefonu vai datoru tieši pirms miega?'
  UNION ALL SELECT 5, 'Cik bieži miegainība traucē koncentrēties dienas laikā?'
) q
WHERE t.title = 'Miega kvalitātes pašnovērtējums'
  AND NOT EXISTS (
    SELECT 1 FROM test_questions tq WHERE tq.test_id = t.id
  );

-- ============================================
-- PAPILDU RAKSTI
-- ============================================

-- Publicēti raksti
INSERT INTO articles (psychologist_account_id, title, content, category, is_published)
SELECT
  (SELECT id FROM accounts WHERE username = 'dr_janis' LIMIT 1),
  'Kā atpazīt hronisku stresu ikdienā',
  'Hronisks stress var izpausties kā miega problēmas, aizkaitināmība, grūtības koncentrēties un pastāvīgs nogurums. Svarīgi ir agrīni pamanīt pazīmes un ieviest atjaunojošus ieradumus: regulāru miega režīmu, fiziskas aktivitātes un strukturētas pauzes.',
  'Stress',
  1
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE title = 'Kā atpazīt hronisku stresu ikdienā'
);

INSERT INTO articles (psychologist_account_id, title, content, category, is_published)
SELECT
  (SELECT id FROM accounts WHERE username = 'dr_dina' LIMIT 1),
  'Konfliktu risināšana pārī bez savstarpējiem pārmetumiem',
  'Efektīva konfliktu risināšana balstās uz aktīvu klausīšanos, konkrētu vajadzību formulēšanu un vienošanos par praktiskiem soļiem. Sarunā ieteicams lietot "es" teikumus, nevis vispārinošus pārmetumus, kas parasti tikai saasina konfliktu.',
  'Attiecības',
  1
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE title = 'Konfliktu risināšana pārī bez savstarpējiem pārmetumiem'
);

INSERT INTO articles (psychologist_account_id, title, content, category, is_published)
SELECT
  (SELECT id FROM accounts WHERE username = 'dr_maris' LIMIT 1),
  'Kas palīdz pēc traumatiskas pieredzes',
  'Atveseļošanās pēc traumas bieži notiek pakāpeniski. Drošības sajūtas atjaunošana, stabils dienas ritms un profesionāls atbalsts var būt izšķiroši faktori. Noderīgas var būt grounding tehnikas un pakāpeniska atgriešanās ikdienas aktivitātēs.',
  'Traumas',
  1
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE title = 'Kas palīdz pēc traumatiskas pieredzes'
);

INSERT INTO articles (psychologist_account_id, title, content, category, is_published)
SELECT
  (SELECT id FROM accounts WHERE username = 'dr_anna' LIMIT 1),
  'Kā vecākiem pamanīt pusaudža emocionālās grūtības',
  'Pusaudža uzvedības izmaiņas var signalizēt par emocionālām grūtībām. Vērtīgi ir pievērst uzmanību miega, ēšanas, sociālās aktivitātes un sekmju izmaiņām. Atbalstoša, nenosodoša saruna bieži ir pirmais solis uz problēmas risinājumu.',
  'Bērnu psiholoģija',
  1
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE title = 'Kā vecākiem pamanīt pusaudža emocionālās grūtības'
);

-- Gaidot admin apstiprinājumu (is_published = 0)
INSERT INTO articles (psychologist_account_id, title, content, category, is_published)
SELECT
  (SELECT id FROM accounts WHERE username = 'dr_dina' LIMIT 1),
  'Kad attiecībās vajadzīga pāru terapija',
  'Dažkārt pāra sarunas atkārto vienu un to pašu konfliktu loku. Pāru terapija palīdz ieraudzīt komunikācijas modeļus, atjaunot drošu dialogu un vienoties par pārmaiņām, kas strādā abiem partneriem.',
  'Attiecības',
  0
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE title = 'Kad attiecībās vajadzīga pāru terapija'
);

INSERT INTO articles (psychologist_account_id, title, content, category, is_published)
SELECT
  (SELECT id FROM accounts WHERE username = 'dr_maris' LIMIT 1),
  'Trauksme un ķermenis: kāpēc rodas fiziski simptomi',
  'Trauksme bieži izpaužas ne tikai domās, bet arī ķermenī: paātrināta sirdsdarbība, saspringums, svīšana un elpas trūkums. Izpratne par ķermeņa reakcijām palīdz mazināt bailes un efektīvāk izmantot nomierināšanās tehnikas.',
  'Trauksme',
  0
WHERE NOT EXISTS (
  SELECT 1 FROM articles WHERE title = 'Trauksme un ķermenis: kāpēc rodas fiziski simptomi'
);

SET FOREIGN_KEY_CHECKS = 1;
