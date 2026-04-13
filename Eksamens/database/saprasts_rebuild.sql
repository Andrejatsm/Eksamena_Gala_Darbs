-- Saprasts datubāzes pārbūves skripts (vienota ielogošanās: lietotājs/psihologs/administrators)
-- Palaid šo phpMyAdmin vai MySQL klientā.
-- Tas dzēsīs esošās tabulas datubāzē `saprasts`.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS test_answers;
DROP TABLE IF EXISTS test_attempts;
DROP TABLE IF EXISTS test_questions;
DROP TABLE IF EXISTS tests;

DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS contact_messages;

DROP TABLE IF EXISTS appointment_events;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS availability_slots;

DROP TABLE IF EXISTS psychologist_profiles;
DROP TABLE IF EXISTS user_profiles;
DROP TABLE IF EXISTS accounts;

SET FOREIGN_KEY_CHECKS = 1;

-- Pamatkonti (kopīga ielogošanās)
CREATE TABLE accounts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(64) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(32) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','psychologist','admin') NOT NULL DEFAULT 'user',
  status ENUM('active','pending','rejected','disabled') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_accounts_username (username),
  UNIQUE KEY uq_accounts_email (email),
  KEY idx_accounts_role (role),
  KEY idx_accounts_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_profiles (
  account_id INT UNSIGNED NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  PRIMARY KEY (account_id),
  CONSTRAINT fk_user_profiles_account
    FOREIGN KEY (account_id) REFERENCES accounts(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE psychologist_profiles (
  account_id INT UNSIGNED NOT NULL,
  full_name VARCHAR(180) NOT NULL,
  specialization VARCHAR(120) NOT NULL,
  experience_years INT UNSIGNED NOT NULL DEFAULT 0,
  description TEXT NULL,
  certificate_path VARCHAR(255) NULL,
  image_path VARCHAR(255) NULL,
  hourly_rate DECIMAL(10,2) NOT NULL DEFAULT 50.00,
  approved_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (account_id),
  KEY idx_psy_specialization (specialization),
  KEY idx_psy_approved (approved_at),
  CONSTRAINT fk_psychologist_profiles_account
    FOREIGN KEY (account_id) REFERENCES accounts(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pieejamības sloti (psihologs tos pārvalda pats)
CREATE TABLE availability_slots (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  psychologist_account_id INT UNSIGNED NOT NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  consultation_type ENUM('in_person','online') NOT NULL DEFAULT 'online',
  note VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_availability_psy (psychologist_account_id),
  KEY idx_availability_starts (starts_at),
  CONSTRAINT fk_availability_psy
    FOREIGN KEY (psychologist_account_id) REFERENCES accounts(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pieraksti (lietotājs -> psihologs)
CREATE TABLE appointments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_account_id INT UNSIGNED NOT NULL,
  psychologist_account_id INT UNSIGNED NOT NULL,
  scheduled_at DATETIME NULL,
  consultation_type ENUM('in_person','online') NOT NULL DEFAULT 'online',
  status ENUM('pending','approved','rejected','cancelled','rescheduled') NOT NULL DEFAULT 'pending',
  user_name_snapshot VARCHAR(180) NULL,
  user_email_snapshot VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_appt_user (user_account_id),
  KEY idx_appt_psy (psychologist_account_id),
  KEY idx_appt_status (status),
  KEY idx_appt_created (created_at),
  CONSTRAINT fk_appointments_user
    FOREIGN KEY (user_account_id) REFERENCES accounts(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_appointments_psy
    FOREIGN KEY (psychologist_account_id) REFERENCES accounts(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Neobligāts notikumu žurnāls pierakstu izmaiņām (apstiprināt/noraidīt/atcelt/pārcelt)
CREATE TABLE appointment_events (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  appointment_id INT UNSIGNED NOT NULL,
  actor_account_id INT UNSIGNED NOT NULL,
  event_type ENUM('created','approved','rejected','cancelled','rescheduled') NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_appt_events_appt (appointment_id),
  KEY idx_appt_events_actor (actor_account_id),
  CONSTRAINT fk_appt_events_appt
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_appt_events_actor
    FOREIGN KEY (actor_account_id) REFERENCES accounts(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kontaktformas iesniegumi
CREATE TABLE contact_messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(160) NOT NULL,
  email VARCHAR(255) NOT NULL,
  subject VARCHAR(255) NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Psihologu raksti un resursi
CREATE TABLE articles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  psychologist_account_id INT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  content MEDIUMTEXT NOT NULL,
  category VARCHAR(120) NULL,
  image_path VARCHAR(255) NULL,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_articles_psy (psychologist_account_id),
  KEY idx_articles_published (is_published),
  KEY idx_articles_created (created_at),
  CONSTRAINT fk_articles_psy
    FOREIGN KEY (psychologist_account_id) REFERENCES accounts(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pašnovērtējuma testi
CREATE TABLE tests (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  status ENUM('draft','pending_review','published','archived') NOT NULL DEFAULT 'draft',
  created_by_account_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_tests_status (status),
  CONSTRAINT fk_tests_creator
    FOREIGN KEY (created_by_account_id) REFERENCES accounts(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE test_questions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  test_id INT UNSIGNED NOT NULL,
  question_text TEXT NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_test_questions_test (test_id),
  CONSTRAINT fk_test_questions_test
    FOREIGN KEY (test_id) REFERENCES tests(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE test_attempts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  test_id INT UNSIGNED NOT NULL,
  user_account_id INT UNSIGNED NOT NULL,
  total_score INT NOT NULL DEFAULT 0,
  result_text TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_test_attempts_user (user_account_id),
  KEY idx_test_attempts_test (test_id),
  CONSTRAINT fk_test_attempts_test
    FOREIGN KEY (test_id) REFERENCES tests(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_test_attempts_user
    FOREIGN KEY (user_account_id) REFERENCES accounts(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE test_answers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  attempt_id INT UNSIGNED NOT NULL,
  question_id INT UNSIGNED NOT NULL,
  answer_value INT NOT NULL,
  PRIMARY KEY (id),
  KEY idx_test_answers_attempt (attempt_id),
  KEY idx_test_answers_question (question_id),
  CONSTRAINT fk_test_answers_attempt
    FOREIGN KEY (attempt_id) REFERENCES test_attempts(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_test_answers_question
    FOREIGN KEY (question_id) REFERENCES test_questions(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sākotnējais administratora konts (pēc importa nomaini paroli)
-- Parole: Admin123! (bcrypt hash zemāk)
INSERT INTO accounts (username, email, phone, password_hash, role, status)
VALUES (
  'admin',
  'admin@saprasts.local',
  NULL,
  '$2y$10$zpvz6mF3Jv3f1eQ5aQzF4u8Tox3j3uZQqP4fB1Y8fQxg2YbU8pF6S',
  'admin',
  'active'
);

