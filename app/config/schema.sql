CREATE DATABASE IF NOT EXISTS eventflow_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE eventflow_db;

-- 1. Users
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(100) NOT NULL,
    lname VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    student_id VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Organizer', 'Participant')
        NOT NULL DEFAULT 'Participant',
    status ENUM('Active', 'Inactive')
        NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Events
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    venue VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected', 'Cancelled')
        NOT NULL DEFAULT 'Pending',
    qr_token VARCHAR(255) UNIQUE,
    registration_start DATETIME,
    registration_end DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_events_organizer
        FOREIGN KEY (organizer_id)
        REFERENCES users(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 3. Registrations
CREATE TABLE registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    participant_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Registered', 'Cancelled')
        NOT NULL DEFAULT 'Registered',

    CONSTRAINT uq_event_participant
        UNIQUE (event_id, participant_id),

    CONSTRAINT fk_registrations_event
        FOREIGN KEY (event_id)
        REFERENCES events(event_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_registrations_participant
        FOREIGN KEY (participant_id)
        REFERENCES users(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 4. Attendance
CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL UNIQUE,
    attendance_date DATE NOT NULL,
    attendance_time TIME NOT NULL,
    status ENUM('Present', 'Absent')
        NOT NULL DEFAULT 'Present',
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_attendance_registration
        FOREIGN KEY (registration_id)
        REFERENCES registrations(registration_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 5. Certificates
CREATE TABLE certificates (
    certificate_id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT NOT NULL,
    event_id INT NOT NULL,
    certificate_number VARCHAR(100) NOT NULL UNIQUE,
    issue_date DATE NOT NULL,
    eligibility_status ENUM('Eligible', 'Not Eligible')
        NOT NULL DEFAULT 'Not Eligible',
    certificate_path VARCHAR(255),

    CONSTRAINT uq_certificate_participant_event
        UNIQUE (participant_id, event_id),

    CONSTRAINT fk_certificates_participant
        FOREIGN KEY (participant_id)
        REFERENCES users(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_certificates_event
        FOREIGN KEY (event_id)
        REFERENCES events(event_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;