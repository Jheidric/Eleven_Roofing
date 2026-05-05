-- ============================================================
-- ELEVEN ROOFING DASMA — Complete Database Schema
-- Database: Elevenroofingdasmadatabase
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS Elevenroofingdasmadatabase;
CREATE DATABASE Elevenroofingdasmadatabase CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Elevenroofingdasmadatabase;

CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL,
    level INT DEFAULT 0
);
INSERT INTO roles (role_id,role_name,level) VALUES
(1,'Owner',100),(2,'System Admin',80),(3,'Administrator',60),(4,'Staff',40),(5,'Customer',10);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20),
    address TEXT,
    role_id INT DEFAULT 5,
    status ENUM('active','inactive','locked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);
INSERT INTO users (full_name,email,password,role_id,status) VALUES
('Owner','owner@elevenroofingdasma.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,'active'),
('System Admin','sysadmin@elevenroofingdasma.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',2,'active'),
('Administrator','admin@elevenroofingdasma.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',3,'active'),
('Staff Member','staff@elevenroofingdasma.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',4,'active'),
('Juan Customer','juan@email.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',5,'active');

CREATE TABLE system_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    locked_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (locked_by) REFERENCES users(user_id) ON DELETE SET NULL
);
INSERT INTO system_settings (setting_key,setting_value,locked_by) VALUES
('lock_services','0',NULL),('lock_products','0',NULL),('lock_chatbot','0',NULL),
('lock_about','0',NULL),('lock_contact','0',NULL),('lock_inventory','0',NULL),
('site_name','Eleven Roofing Dasma',NULL),('maintenance_mode','0',NULL);

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
);
INSERT INTO categories (category_name) VALUES
('Roofing Sheets'),('Structural'),('Insulation'),('Fasteners'),('Sealants'),('Accessories');

CREATE TABLE services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    price_from DECIMAL(12,2),
    duration VARCHAR(60),
    image_path VARCHAR(500),
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
INSERT INTO services (service_name,description,category,price_from,duration) VALUES
('Complete Roof Installation','Full new roofing system for residential and commercial.','Installation',45000,'3-7 days'),
('Roof Leak Repair','Expert leak detection and targeted repair.','Repair',3500,'1-2 days'),
('Preventive Maintenance','Scheduled maintenance to extend roof lifespan.','Maintenance',1800,'Half day'),
('Roof Replacement','Complete tear-off and replacement.','Installation',60000,'5-10 days'),
('Emergency Repair','24/7 emergency services for storm damage.','Repair',5000,'Same day'),
('Roof Inspection','Comprehensive assessment with written report.','Inspection',1200,'2-4 hours'),
('Waterproofing Application','Professional waterproofing for flat roofs.','Maintenance',4500,'1-2 days'),
('Gutter Installation','Design and installation of drainage systems.','Installation',8000,'1-2 days');

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    product_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(12,2),
    stock_quantity INT DEFAULT 0,
    min_stock INT DEFAULT 50,
    image_path VARCHAR(500),
    icon_emoji VARCHAR(10) DEFAULT '📦',
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);
INSERT INTO products (category_id,product_name,description,price,stock_quantity,min_stock,icon_emoji) VALUES
(1,'Corrugated G.I. Sheet 0.5mm','Standard corrugated galvanized iron sheets.',280,240,100,'🟫'),
(1,'Pre-painted Color Roofing','UV-resistant pre-painted panels.',320,180,80,'🎨'),
(1,'Long Span Rib-type Roofing','Heavy-duty commercial roofing.',410,95,50,'🟤'),
(2,'Roof Truss Steel Frame','Engineered steel truss systems.',12500,48,20,'🪵'),
(2,'Purlins C-Channel 2mm','Standard C-channel purlins.',680,320,100,'⬛'),
(3,'Thermal Foam Insulation Board','High-density foam insulation.',850,120,40,'🧱'),
(3,'Bubble Foil Insulation Roll','Double-sided aluminum bubble foil.',1200,75,30,'✨'),
(4,'Roofing Tek Screws (Box)','Self-drilling hex head screws. 250pcs/box.',320,380,100,'🔩'),
(4,'J-Bolt Anchor Set','Heavy-duty J-bolts for securing purlins.',45,12,50,'🪛'),
(5,'Polyurethane Roof Sealant','Flexible polyurethane sealant.',580,200,60,'🧴'),
(5,'Butyl Tape 2x10m','Self-adhesive butyl rubber tape.',280,150,50,'📦'),
(6,'Ridge Roll Cap','Standard ridge cap for waterproofing.',150,500,100,'🔺'),
(6,'Gutter Hanger Set','Adjustable gutter brackets.',35,700,150,'🔗');

CREATE TABLE inquiries (
    inquiry_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    first_name VARCHAR(60),
    last_name VARCHAR(60),
    email VARCHAR(100),
    contact VARCHAR(20),
    service_type VARCHAR(100),
    subject VARCHAR(200),
    message TEXT,
    status ENUM('pending','in_progress','resolved') DEFAULT 'pending',
    response TEXT,
    responded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (responded_by) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE inventory_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    change_type ENUM('add','remove','adjustment') NOT NULL,
    quantity INT NOT NULL,
    old_stock INT,
    new_stock INT,
    notes TEXT,
    logged_by INT,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (logged_by) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE tools (
    tool_id INT AUTO_INCREMENT PRIMARY KEY,
    tool_name VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 0,
    available INT DEFAULT 0
);
INSERT INTO tools (tool_name,quantity,available) VALUES
('Electric Drill',5,3),('Roofing Hammer',20,15),('Scaffolding Set',3,2),
('Safety Harness',10,7),('Circular Saw',4,4),('Angle Grinder',6,5);

CREATE TABLE borrowed_tools (
    borrow_id INT AUTO_INCREMENT PRIMARY KEY,
    tool_id INT,
    borrowed_by VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 1,
    borrow_date DATE NOT NULL,
    expected_return DATE,
    return_date DATE,
    condition_out VARCHAR(100) DEFAULT 'Good',
    condition_in VARCHAR(100),
    status ENUM('borrowed','returned','overdue') DEFAULT 'borrowed',
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tool_id) REFERENCES tools(tool_id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE chatbot_qa (
    qa_id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(60) DEFAULT 'General',
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    is_active TINYINT DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);
INSERT INTO chatbot_qa (category,question,answer) VALUES
('Services','What roofing services do you offer?','We offer Complete Roof Installation, Leak Repair, Preventive Maintenance, Emergency Repairs (24/7), Roof Inspection, Waterproofing, and Gutter Installation.'),
('Pricing','How much does a roof repair cost?','Repairs start from P3,500. Emergency repairs from P5,000. Contact us for a free site assessment.'),
('Pricing','How much does a new roof installation cost?','Installations start from P45,000. Final price depends on roof size and material choice.'),
('Process','How do I request a service?','Submit an inquiry through our Inquiry Form and our team will contact you within 24 hours.'),
('Emergency','Do you offer emergency repair services?','Yes! 24/7 emergency hotline: (046) 123-4568. Same-day dispatch available.'),
('Warranty','Do your installations come with a warranty?','All installations come with a 1-3 year workmanship warranty plus manufacturer warranty on materials.'),
('Contact','How can I contact you?','Phone: (046) 123-4567 | Email: info@elevenroofingdasma.com | Hours: Mon-Sat 8AM-5PM');

CREATE TABLE chat_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_name VARCHAR(100),
    status ENUM('bot','waiting','active','closed') DEFAULT 'bot',
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE chat_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    sender_name VARCHAR(100),
    message TEXT NOT NULL,
    msg_type ENUM('user','bot','agent') DEFAULT 'user',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES chat_sessions(session_id) ON DELETE CASCADE
);

CREATE TABLE about_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(200),
    content TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
);
INSERT INTO about_content (section_key,title,content) VALUES
('hero_title','About Eleven Roofing Dasma','Your trusted roofing partner in Dasmarinas, Cavite'),
('hero_subtitle','Professional Roofing Services','Twelve years of excellence, thousands of roofs built, and a commitment to quality.'),
('story_body','Our Story','Eleven Roofing Dasma was founded in 2013 by seasoned professionals who believed every property deserves a roof that stands the test of time. What started as a small crew has grown into one of the most trusted roofing companies in Cavite, with over 500 projects completed.'),
('mission','Our Mission','To deliver world-class roofing solutions with integrity, craftsmanship, and care.'),
('vision','Our Vision','To be the most trusted roofing company in Cavite, known for excellence and lasting client partnerships.'),
('years','Years in Business','12'),
('projects','Projects Completed','500+'),
('team_size','Team Size','50+');

CREATE TABLE contact_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_key VARCHAR(100) UNIQUE NOT NULL,
    field_label VARCHAR(100),
    field_value TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
);
INSERT INTO contact_content (field_key,field_label,field_value) VALUES
('address','Main Office','123 Roofing Ave, Brgy. Salitran, Dasmarinas, Cavite 4114'),
('phone','Main Phone','(046) 123-4567'),
('emergency_phone','Emergency Hotline','(046) 123-4568'),
('email','Email Address','info@elevenroofingdasma.com'),
('hours_weekday','Weekday Hours','Monday - Friday: 8:00 AM - 5:00 PM'),
('hours_saturday','Saturday Hours','Saturday: 8:00 AM - 12:00 PM'),
('branch_1','Main Branch - Dasmarinas','123 Roofing Ave, Brgy. Salitran, Dasmarinas'),
('branch_2','Imus Branch','456 Construction Road, Imus, Cavite'),
('branch_3','Bacoor Branch','789 Builder Street, Bacoor, Cavite');

CREATE TABLE contact_messages (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(60),
    last_name VARCHAR(60),
    email VARCHAR(100),
    phone VARCHAR(20),
    subject VARCHAR(100),
    message TEXT,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE backup_logs (
    backup_id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255),
    file_size INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes VARCHAR(255),
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(200),
    module VARCHAR(100),
    details TEXT,
    ip_address VARCHAR(50),
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(60),
    generated_by INT,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(user_id) ON DELETE SET NULL
);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- INVENTORY REQUEST SYSTEM (Staff → Admin Approval Workflow)
-- ============================================================
CREATE TABLE inventory_requests (
    request_id   INT AUTO_INCREMENT PRIMARY KEY,
    product_id   INT NOT NULL,
    change_type  ENUM('add','remove') NOT NULL,
    quantity     INT NOT NULL,
    reason       TEXT,
    status       ENUM('pending','approved','rejected') DEFAULT 'pending',
    requested_by INT,
    reviewed_by  INT,
    review_note  TEXT,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at  TIMESTAMP NULL,
    FOREIGN KEY (product_id)   REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by)  REFERENCES users(user_id) ON DELETE SET NULL
);
