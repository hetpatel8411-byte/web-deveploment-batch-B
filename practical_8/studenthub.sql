CREATE DATABASE IF NOT EXISTS studenthub
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE studenthub;

CREATE TABLE IF NOT EXISTS programs (
  program_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  program_code VARCHAR(10) NOT NULL,
  program_name VARCHAR(100) NOT NULL,
  PRIMARY KEY (program_id),
  UNIQUE KEY uq_programs_code (program_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS students (
  student_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(254) NOT NULL,
  mobile CHAR(10) NOT NULL,
  address VARCHAR(1000) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  program_id TINYINT UNSIGNED NOT NULL,
  study_year TINYINT UNSIGNED NOT NULL,
  gender ENUM('female', 'male', 'other') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (student_id),
  UNIQUE KEY uq_students_email (email),
  KEY idx_students_program (program_id),
  CONSTRAINT chk_students_study_year CHECK (study_year BETWEEN 1 AND 4),
  CONSTRAINT fk_students_program
    FOREIGN KEY (program_id) REFERENCES programs (program_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS events (
  event_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(150) NOT NULL,
  event_date DATE NOT NULL,
  category VARCHAR(50) NOT NULL,
  location VARCHAR(150) NOT NULL,
  description VARCHAR(1000) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (event_id),
  UNIQUE KEY uq_events_title_date (title, event_date),
  KEY idx_events_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registrations (
  registration_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id INT UNSIGNED NOT NULL,
  event_id INT UNSIGNED NOT NULL,
  status ENUM('registered', 'attended', 'cancelled') NOT NULL DEFAULT 'registered',
  registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (registration_id),
  UNIQUE KEY uq_registrations_student_event (student_id, event_id),
  KEY idx_registrations_event_status (event_id, status),
  CONSTRAINT fk_registrations_student
    FOREIGN KEY (student_id) REFERENCES students (student_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_registrations_event
    FOREIGN KEY (event_id) REFERENCES events (event_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO programs (program_id, program_code, program_name) VALUES
  (1, 'BCA', 'Bachelor of Computer Applications'),
  (2, 'BSc', 'Bachelor of Science'),
  (3, 'BCom', 'Bachelor of Commerce'),
  (4, 'BA', 'Bachelor of Arts')
ON DUPLICATE KEY UPDATE program_name = VALUES(program_name);

-- The sample password hashes are deliberately not usable demo credentials.
INSERT INTO students
  (student_id, name, email, mobile, address, password_hash, program_id, study_year, gender)
VALUES
  (1, 'Aarav Mehta', 'aarav.mehta@example.test', '9876500001', 'Sample address 1', '$2y$10$8XuT.O/tKtE6I4A3w5qpJeV2N/jQ0VJVUVKACoVJqe7Dmb6oqGcn6', 1, 2, 'male'),
  (2, 'Diya Sharma', 'diya.sharma@example.test', '9876500002', 'Sample address 2', '$2y$10$8XuT.O/tKtE6I4A3w5qpJeV2N/jQ0VJVUVKACoVJqe7Dmb6oqGcn6', 2, 1, 'female'),
  (3, 'Kabir Iyer', 'kabir.iyer@example.test', '9876500003', 'Sample address 3', '$2y$10$8XuT.O/tKtE6I4A3w5qpJeV2N/jQ0VJVUVKACoVJqe7Dmb6oqGcn6', 3, 3, 'other')
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  mobile = VALUES(mobile),
  address = VALUES(address),
  program_id = VALUES(program_id),
  study_year = VALUES(study_year),
  gender = VALUES(gender);

INSERT INTO events (event_id, title, event_date, category, location, description) VALUES
  (1, 'Welcome Week', '2026-10-05', 'Campus', 'Main Hall', 'Meet students and explore campus activities.'),
  (2, 'Web Design Workshop', '2026-10-12', 'Workshop', 'Computer Lab', 'Build a simple responsive web page.'),
  (3, 'Career Planning Talk', '2026-10-18', 'Talk', 'Auditorium', 'Learn about internships and career preparation.'),
  (4, 'Science Exhibition', '2026-10-24', 'Campus', 'Science Block', 'Explore student science projects and demonstrations.'),
  (5, 'Programming Contest', '2026-11-02', 'Competition', 'Computer Lab', 'Solve programming challenges with your team.'),
  (6, 'Alumni Meetup', '2026-11-10', 'Talk', 'Main Hall', 'Connect with graduates and hear their stories.'),
  (7, 'Photography Walk', '2026-11-17', 'Campus', 'East Garden', 'Practice photography around the college grounds.')
ON DUPLICATE KEY UPDATE
  category = VALUES(category),
  location = VALUES(location),
  description = VALUES(description);

INSERT IGNORE INTO registrations (student_id, event_id, status) VALUES
  (1, 2, 'registered'),
  (1, 5, 'registered'),
  (2, 3, 'registered'),
  (3, 4, 'attended');
