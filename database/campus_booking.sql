-- =============================================
-- VNUIS Campus Booking System Database v1.0
-- Compatible with MySQL 5.7+ / MariaDB 10.3+
-- =============================================

CREATE DATABASE IF NOT EXISTS campus_booking
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE campus_booking;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- =============================================
-- DROP TABLES (clean install)
-- =============================================
DROP TABLE IF EXISTS facility_images;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS approval_logs;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS facilities;
DROP TABLE IF EXISTS users;

-- =============================================
-- TABLE: users
-- =============================================
CREATE TABLE users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(100)  NOT NULL,
    email        VARCHAR(150)  NOT NULL UNIQUE,
    password     VARCHAR(255)  NOT NULL,
    role         ENUM('student','lecturer','admin') NOT NULL DEFAULT 'student',
    student_code VARCHAR(20)   NULL,
    phone        VARCHAR(20)   NULL,
    avatar       VARCHAR(255)  NULL,
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: facilities
-- =============================================
CREATE TABLE facilities (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    facility_name VARCHAR(150)  NOT NULL,
    facility_type ENUM('classroom','lab','meeting_room','auditorium','equipment') NOT NULL,
    capacity      INT           NOT NULL DEFAULT 1,
    location      VARCHAR(200)  NOT NULL,
    status        ENUM('available','maintenance','inactive') NOT NULL DEFAULT 'available',
    description   TEXT          NULL,
    image_path    VARCHAR(255)  NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: bookings
-- =============================================
CREATE TABLE bookings (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT           NOT NULL,
    facility_id  INT           NOT NULL,
    booking_date DATE          NOT NULL,
    start_time   TIME          NOT NULL,
    end_time     TIME          NOT NULL,
    purpose      TEXT          NOT NULL,
    status       ENUM('pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user     FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_bookings_facility FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: approval_logs
-- =============================================
CREATE TABLE approval_logs (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    booking_id  INT           NOT NULL,
    admin_id    INT           NOT NULL,
    action      ENUM('approved','rejected') NOT NULL,
    note        TEXT          NULL,
    action_time DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_approval_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_approval_admin   FOREIGN KEY (admin_id)   REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: notifications
-- =============================================
CREATE TABLE notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT           NOT NULL,
    title      VARCHAR(200)  NOT NULL,
    message    TEXT          NOT NULL,
    is_read    TINYINT(1)    NOT NULL DEFAULT 0,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: reviews
-- =============================================
CREATE TABLE reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT           NOT NULL,
    user_id    INT           NOT NULL,
    rating     INT           NOT NULL,
    comment    TEXT          NULL,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    UNIQUE KEY uq_review_booking (booking_id),
    CONSTRAINT chk_rating CHECK (rating >= 1 AND rating <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: reports
-- =============================================
CREATE TABLE reports (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    facility_id       INT           NOT NULL,
    user_id           INT           NOT NULL,
    issue_description TEXT          NOT NULL,
    report_status     ENUM('open','in_progress','resolved') NOT NULL DEFAULT 'open',
    admin_note        TEXT          NULL,
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_facility FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE,
    CONSTRAINT fk_reports_user     FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: equipment
-- =============================================
CREATE TABLE equipment (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    facility_id    INT           NOT NULL,
    equipment_name VARCHAR(150)  NOT NULL,
    quantity       INT           NOT NULL DEFAULT 1,
    status         ENUM('good','damaged','missing') NOT NULL DEFAULT 'good',
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipment_facility FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLE: facility_images
-- =============================================
CREATE TABLE facility_images (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT           NOT NULL,
    image_path  VARCHAR(255)  NOT NULL,
    is_primary  TINYINT(1)    NOT NULL DEFAULT 0,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fimages_facility FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INDEXES
-- =============================================
CREATE INDEX idx_bookings_date     ON bookings(booking_date);
CREATE INDEX idx_bookings_status   ON bookings(status);
CREATE INDEX idx_bookings_user     ON bookings(user_id);
CREATE INDEX idx_bookings_facility ON bookings(facility_id);
CREATE INDEX idx_notif_user        ON notifications(user_id);
CREATE INDEX idx_notif_read        ON notifications(is_read);
CREATE INDEX idx_facility_type     ON facilities(facility_type);
CREATE INDEX idx_facility_status   ON facilities(status);

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- SAMPLE DATA: users
-- Passwords:
--   admin@vnuis.edu.vn    => Admin@123
--   student@vnuis.edu.vn  => Student@123
--   lecturer@vnuis.edu.vn => Lecturer@123
--   (others)              => Student@123
-- =============================================
INSERT INTO users (id, full_name, email, password, role, student_code, phone, is_active, created_at) VALUES
(1, 'System Administrator',  'admin@vnuis.edu.vn',     '$2y$10$EcI8gT0sxWuHbQgecyHdV.c93sSOzOmNUjKtvPIWuMhVDwiblFJPK', 'admin',    NULL,          '0901000001', 1, DATE_SUB(NOW(), INTERVAL 90 DAY)),
(2, 'Nguyen Van An',         'student@vnuis.edu.vn',   '$2y$10$EcI8gT0sxWuHbQgecyHdV.c93sSOzOmNUjKtvPIWuMhVDwiblFJPK', 'student',  'SV20210001',  '0901000002', 1, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(3, 'Dr. Tran Thi Bich',     'lecturer@vnuis.edu.vn',  '$2y$10$EcI8gT0sxWuHbQgecyHdV.c93sSOzOmNUjKtvPIWuMhVDwiblFJPK', 'lecturer', 'GV20150012',  '0901000003', 1, DATE_SUB(NOW(), INTERVAL 60 DAY)),
(4, 'Le Minh Duc',           'duc.le@vnuis.edu.vn',    '$2y$10$EcI8gT0sxWuHbQgecyHdV.c93sSOzOmNUjKtvPIWuMhVDwiblFJPK', 'student',  'SV20210002',  '0901000004', 1, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(5, 'Pham Thi Mai',          'mai.pham@vnuis.edu.vn',  '$2y$10$EcI8gT0sxWuHbQgecyHdV.c93sSOzOmNUjKtvPIWuMhVDwiblFJPK', 'student',  'SV20220003',  '0901000005', 1, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(6, 'Prof. Hoang Van Nam',   'nam.hoang@vnuis.edu.vn', '$2y$10$EcI8gT0sxWuHbQgecyHdV.c93sSOzOmNUjKtvPIWuMhVDwiblFJPK', 'lecturer', 'GV20120008',  '0901000006', 1, DATE_SUB(NOW(), INTERVAL 50 DAY));

-- =============================================
-- SAMPLE DATA: facilities
-- =============================================
INSERT INTO facilities (id, facility_name, facility_type, capacity, location, status, description, created_at) VALUES
(1, 'Lecture Hall A101',   'classroom',    80,  'Building A, Floor 1',     'available',   'Spacious lecture hall with HD projector, air conditioning, 80 tiered seats, and high-speed WiFi. Perfect for large lectures and seminars.',   DATE_SUB(NOW(), INTERVAL 90 DAY)),
(2, 'Computer Lab B201',   'lab',          40,  'Building B, Floor 2',     'available',   'Modern computer lab with 40 high-performance workstations, dual monitors, licensed software suite, and gigabit internet access.',              DATE_SUB(NOW(), INTERVAL 90 DAY)),
(3, 'Executive Meeting C3','meeting_room', 15,  'Building C, Floor 3',     'available',   'Premium meeting room with 4K video conferencing, interactive whiteboard, ergonomic seating for 15, and built-in presentation system.',         DATE_SUB(NOW(), INTERVAL 90 DAY)),
(4, 'Grand Auditorium',    'auditorium',   500, 'Central Campus Building', 'available',   'World-class auditorium with 500-seat capacity, professional PA system, stage lighting, simultaneous translation booths, and backstage area.', DATE_SUB(NOW(), INTERVAL 90 DAY)),
(5, 'Physics Lab D101',    'lab',          30,  'Building D, Floor 1',     'available',   'Fully equipped physics laboratory with modern oscilloscopes, power supplies, spectrometers, and comprehensive safety equipment.',              DATE_SUB(NOW(), INTERVAL 90 DAY)),
(6, 'Seminar Room E201',   'meeting_room', 25,  'Building E, Floor 2',     'maintenance', 'Seminar room undergoing HVAC upgrade. Expected to be available within 7 days.',                                                               DATE_SUB(NOW(), INTERVAL 90 DAY)),
(7, 'Smart Classroom F102','classroom',    45,  'Building F, Floor 1',     'available',   'Next-generation smart classroom with interactive display, automated attendance system, 45 ergonomic seats, and studio-quality sound.',         DATE_SUB(NOW(), INTERVAL 90 DAY)),
(8, 'Mobile Projector Kit','equipment',    1,   'Equipment Storage, Bldg A','available',  'Portable Full-HD projector kit with HDMI/VGA adapters, 5m cable, carry bag. Borrow for up to 24 hours.',                                     DATE_SUB(NOW(), INTERVAL 90 DAY)),
(9, 'Chemistry Lab G201',  'lab',          25,  'Building G, Floor 2',     'available',   'Advanced chemistry lab with fume hoods, analytical balances, spectrophotometers, and full safety compliance certification.',                  DATE_SUB(NOW(), INTERVAL 90 DAY));

-- =============================================
-- SAMPLE DATA: bookings
-- =============================================
INSERT INTO bookings (id, user_id, facility_id, booking_date, start_time, end_time, purpose, status, created_at) VALUES
(1,  2, 1, DATE_ADD(CURDATE(), INTERVAL 2  DAY), '08:00:00', '10:00:00', 'Final exam study group session — Software Engineering',         'approved',  DATE_SUB(NOW(), INTERVAL 6 DAY)),
(2,  2, 2, DATE_ADD(CURDATE(), INTERVAL 3  DAY), '13:00:00', '15:00:00', 'Capstone project development and testing',                      'pending',   DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3,  3, 3, DATE_ADD(CURDATE(), INTERVAL 1  DAY), '09:00:00', '11:00:00', 'Faculty meeting — Curriculum revision Q2',                      'approved',  DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4,  2, 7, DATE_SUB(CURDATE(), INTERVAL 5  DAY), '10:00:00', '12:00:00', 'Group presentation rehearsal for CS capstone',                  'completed', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5,  4, 1, DATE_ADD(CURDATE(), INTERVAL 4  DAY), '14:00:00', '16:00:00', 'Academic workshop on Machine Learning fundamentals',            'pending',   DATE_SUB(NOW(), INTERVAL 2 DAY)),
(6,  5, 2, DATE_SUB(CURDATE(), INTERVAL 3  DAY), '08:00:00', '10:00:00', 'Data analysis and visualization project',                      'completed', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(7,  3, 4, DATE_ADD(CURDATE(), INTERVAL 7  DAY), '09:00:00', '17:00:00', 'Annual VNUIS Science Fair & Innovation Expo 2025',              'approved',  DATE_SUB(NOW(), INTERVAL 7 DAY)),
(8,  2, 5, DATE_SUB(CURDATE(), INTERVAL 7  DAY), '13:00:00', '15:00:00', 'Physics lab experiment — Electromagnetic induction',           'completed', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(9,  4, 3, DATE_ADD(CURDATE(), INTERVAL 2  DAY), '15:00:00', '17:00:00', 'Project team meeting for database design assignment',           'rejected',  DATE_SUB(NOW(), INTERVAL 1 DAY)),
(10, 5, 7, DATE_ADD(CURDATE(), INTERVAL 5  DAY), '08:00:00', '10:00:00', 'Morning study session — Data Structures exam preparation',      'pending',   DATE_SUB(NOW(), INTERVAL 1 DAY)),
(11, 2, 8, DATE_ADD(CURDATE(), INTERVAL 1  DAY), '08:00:00', '09:00:00', 'Borrow projector for department seminar presentation',          'approved',  DATE_SUB(NOW(), INTERVAL 2 DAY)),
(12, 3, 1, DATE_SUB(CURDATE(), INTERVAL 2  DAY), '10:00:00', '12:00:00', 'Guest lecture — Artificial Intelligence in Healthcare',         'completed', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(13, 6, 9, DATE_ADD(CURDATE(), INTERVAL 3  DAY), '09:00:00', '12:00:00', 'Advanced organic chemistry lab session for graduate students',  'pending',   DATE_SUB(NOW(), INTERVAL 1 DAY)),
(14, 4, 2, DATE_SUB(CURDATE(), INTERVAL 10 DAY), '14:00:00', '17:00:00', 'Network security penetration testing workshop',                 'completed', DATE_SUB(NOW(), INTERVAL 14 DAY));

-- =============================================
-- SAMPLE DATA: approval_logs
-- =============================================
INSERT INTO approval_logs (booking_id, admin_id, action, note, action_time) VALUES
(1,  1, 'approved', 'Approved. Please ensure the hall is returned clean.',                   DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3,  1, 'approved', 'Approved for faculty meeting.',                                         DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7,  1, 'approved', 'Approved for Science Fair. Security and AV teams notified.',            DATE_SUB(NOW(), INTERVAL 6 DAY)),
(9,  1, 'rejected', 'Time slot conflicts with scheduled maintenance on that floor.',         DATE_SUB(NOW(), INTERVAL 1 DAY)),
(11, 1, 'approved', 'Equipment approved. Return by end of day.',                             DATE_SUB(NOW(), INTERVAL 2 DAY));

-- =============================================
-- SAMPLE DATA: notifications
-- =============================================
INSERT INTO notifications (user_id, title, message, is_read, created_at) VALUES
(2, 'Booking Approved',        'Your booking for Lecture Hall A101 has been approved. Please arrive 10 minutes early.',                 0, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 'Booking Reminder',        'Reminder: Your booking for Computer Lab B201 is scheduled for tomorrow. Bring your student ID.',        0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 'Booking Approved',        'Your meeting room booking for tomorrow has been approved. Video conferencing system will be ready.',    1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 'Booking Rejected',        'Your booking for Executive Meeting C3 was rejected. Reason: Time slot conflict with maintenance.',      0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 'Welcome to VNUIS Booking','Welcome to the VNUIS Campus Booking System! Browse available facilities and make your first booking.',  1, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(2, 'Equipment Ready',         'Your projector kit booking has been approved. Pick up from Equipment Storage, Building A.',            0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'New Booking Request',     'New booking request from Nguyen Van An for Computer Lab B201. Review required.',                       1, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 'Auditorium Confirmed',    'Your auditorium booking for the Annual Science Fair has been confirmed. Setup team has been briefed.',  1, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 'New Booking Request',     'New booking request from Le Minh Duc for Lecture Hall A101. Pending your review.',                     0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'Issue Report Received',   'A new facility issue has been reported for Smart Classroom F102 by Le Minh Duc.',                      0, DATE_SUB(NOW(), INTERVAL 3 DAY));

-- =============================================
-- SAMPLE DATA: reviews
-- =============================================
INSERT INTO reviews (booking_id, user_id, rating, comment, created_at) VALUES
(4,  2, 5, 'Excellent classroom! Super clean, projector worked perfectly, and the smart board was very responsive. Will book again.',     DATE_SUB(NOW(), INTERVAL 4 DAY)),
(6,  5, 4, 'Good computer lab with fast machines. All software was up to date. Internet could be a bit faster but overall great.',       DATE_SUB(NOW(), INTERVAL 2 DAY)),
(8,  2, 5, 'Physics lab is world-class. All equipment is modern and well-maintained. The lab assistant was very helpful.',              DATE_SUB(NOW(), INTERVAL 6 DAY)),
(12, 3, 4, 'Great hall for the guest lecture. Excellent acoustics and the AV team was very professional.',                              DATE_SUB(NOW(), INTERVAL 1 DAY)),
(14, 4, 5, 'Computer lab was perfect for our network security workshop. High-performance machines and great network infrastructure.',    DATE_SUB(NOW(), INTERVAL 5 DAY));

-- =============================================
-- SAMPLE DATA: equipment
-- =============================================
INSERT INTO equipment (facility_id, equipment_name, quantity, status) VALUES
(1, 'Epson HD Projector',          1,  'good'),
(1, 'Wireless Microphone Set',     2,  'good'),
(1, 'Interactive Whiteboard',      1,  'good'),
(1, 'Air Conditioning Units',      4,  'good'),
(1, 'Student Desk Chairs',        80,  'good'),
(2, 'Dell OptiPlex Workstations', 40,  'good'),
(2, 'HP LaserJet Printers',        3,  'good'),
(2, 'Managed Network Switch',      2,  'good'),
(2, 'Dual 24-inch Monitors',      40,  'good'),
(3, '4K Video Conference System',  1,  'good'),
(3, 'Interactive Whiteboard 75"',  1,  'good'),
(3, 'Poly Studio Speakerphone',    1,  'good'),
(4, 'Professional PA System',      1,  'good'),
(4, 'Stage LED Lighting Rig',      1,  'good'),
(4, 'Wireless Podium Mic',         2,  'good'),
(4, 'Stage Microphones',           5,  'good'),
(5, 'Digital Oscilloscopes',      10,  'good'),
(5, 'Variable DC Power Supplies', 15,  'good'),
(5, 'Precision Multimeters',      20,  'good'),
(5, 'Safety Goggles & Lab Coats', 30,  'good'),
(7, 'Samsung Smart Board 86"',     1,  'good'),
(7, 'Ceiling Air Conditioners',    2,  'damaged'),
(7, 'Student Response Clickers',  45,  'good'),
(8, 'BenQ FHD Portable Projector', 3,  'good'),
(8, 'Projection Screen 200cm',     2,  'good'),
(9, 'Chemical Fume Hoods',         6,  'good'),
(9, 'Analytical Balances',         4,  'good'),
(9, 'UV-Vis Spectrophotometers',   2,  'good');

-- =============================================
-- SAMPLE DATA: reports
-- =============================================
INSERT INTO reports (facility_id, user_id, issue_description, report_status, admin_note, created_at) VALUES
(6, 2, 'Air conditioning in Seminar Room E201 has completely stopped working. Temperature is very uncomfortable for studying.',           'in_progress', 'Maintenance team dispatched. HVAC contractor scheduled for Thursday.',      DATE_SUB(NOW(), INTERVAL 10 DAY)),
(7, 4, 'Ceiling air conditioner in Smart Classroom F102 is making loud rattling noise during operation. Very distracting during class.', 'open',        NULL,                                                                       DATE_SUB(NOW(), INTERVAL 3  DAY)),
(2, 5, 'Three desktop computers in Computer Lab B201 (seats 12, 18, 24) are running extremely slowly and crash frequently.',             'resolved',    'All affected machines reimaged with fresh OS. SSD upgrades installed.',      DATE_SUB(NOW(), INTERVAL 20 DAY)),
(4, 6, 'Stage lighting panel in Grand Auditorium is malfunctioning. Several lights flicker and one spotlight is completely dead.',        'in_progress', 'Electrician booked for assessment on Monday.',                              DATE_SUB(NOW(), INTERVAL 5  DAY));

-- =============================================
-- SAMPLE DATA: facility_images
-- =============================================
INSERT INTO facility_images (facility_id, image_path, is_primary) VALUES
(1, 'default/classroom.jpg',    1),
(2, 'default/lab.jpg',          1),
(3, 'default/meeting.jpg',      1),
(4, 'default/auditorium.jpg',   1),
(5, 'default/physics.jpg',      1),
(7, 'default/smartclass.jpg',   1),
(8, 'default/equipment.jpg',    1),
(9, 'default/chemistry.jpg',    1);

-- =============================================
-- END OF SETUP
--
-- Demo Login Credentials (all use password: admin123)
--   Admin:    admin@vnuis.edu.vn    / admin123
--   Student:  student@vnuis.edu.vn  / admin123
--   Lecturer: lecturer@vnuis.edu.vn / admin123
--
-- Hash generated with: password_hash('admin123', PASSWORD_BCRYPT)
-- =============================================
