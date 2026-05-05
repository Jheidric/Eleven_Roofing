-- Eleven Roofing Dasma Backup
-- Created: 2026-05-05 06:03:27
-- Database: Elevenroofingdasmadatabase

USE Elevenroofingdasmadatabase;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `about_content`;
CREATE TABLE `about_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_key` varchar(100) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `about_content_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `about_content` (`id`,`section_key`,`title`,`content`,`updated_by`,`updated_at`) VALUES ('1','hero_title','About Eleven Roofing Dasma','Page','7','2026-05-04 14:15:50');
INSERT INTO `about_content` (`id`,`section_key`,`title`,`content`,`updated_by`,`updated_at`) VALUES ('2','hero_subtitle','Professional Roofing Services','Twelve years of excellence, thousands of roofs built, and a commitment to quality.','7','2026-05-04 14:15:50');
INSERT INTO `about_content` (`id`,`section_key`,`title`,`content`,`updated_by`,`updated_at`) VALUES ('3','story_body','Our Story','Eleven Roofing Dasma was founded in 2013 by seasoned professionals who believed every property deserves a roof that stands the test of time. What started as a small crew has grown into one of the most trusted roofing companies in Cavite, with over 500 projects completed.','7','2026-05-04 14:15:50');
INSERT INTO `about_content` (`id`,`section_key`,`title`,`content`,`updated_by`,`updated_at`) VALUES ('4','mission','Our Mission','To deliver world-class roofing solutions with integrity, craftsmanship, and care.','7','2026-05-04 14:15:50');
INSERT INTO `about_content` (`id`,`section_key`,`title`,`content`,`updated_by`,`updated_at`) VALUES ('5','vision','Our Vision','To be the most trusted roofing company in Cavite, known for excellence and lasting client partnerships.','7','2026-05-04 14:15:50');
INSERT INTO `about_content` (`id`,`section_key`,`title`,`content`,`updated_by`,`updated_at`) VALUES ('6','years','Years in Business','12','7','2026-05-04 14:15:50');
INSERT INTO `about_content` (`id`,`section_key`,`title`,`content`,`updated_by`,`updated_at`) VALUES ('7','projects','Projects Completed','500+','7','2026-05-04 14:15:50');
INSERT INTO `about_content` (`id`,`section_key`,`title`,`content`,`updated_by`,`updated_at`) VALUES ('8','team_size','Team Size','50+','7','2026-05-04 14:15:50');

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(200) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('1','6','Login','Auth','User logged in','::1','2026-05-04 06:11:44');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('2','6','New inquiry #1 submitted','Inquiries','','::1','2026-05-04 06:15:06');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('3','7','Login','Auth','User logged in','::1','2026-05-04 06:17:19');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('4','7','Logout','Auth','User logged out','::1','2026-05-04 06:18:45');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('5','7','Login','Auth','User logged in','::1','2026-05-04 06:20:37');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('6','7','Joined live chat #1','LiveChat','','::1','2026-05-04 06:23:05');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('7','7','Replied to inquiry #1','Inquiries','','::1','2026-05-04 06:27:31');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('8','7','Direct inventory add: 100 units for product #9','Inventory','','::1','2026-05-04 06:27:55');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('9','6','Logout','Auth','User logged out','::1','2026-05-04 06:34:16');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('10','7','Login','Auth','User logged in','::1','2026-05-04 06:34:53');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('11','7','Logout','Auth','User logged out','::1','2026-05-04 06:42:49');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('12','7','Login','Auth','User logged in','::1','2026-05-04 06:43:08');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('13','7','Logout','Auth','User logged out','::1','2026-05-04 06:43:11');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('14','7','Login','Auth','User logged in','::1','2026-05-04 06:43:38');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('15','7','Logout','Auth','User logged out','::1','2026-05-04 06:45:25');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('16','7','Login','Auth','User logged in','::1','2026-05-04 06:45:44');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('17','7','Updated feature locks','System','','::1','2026-05-04 06:57:50');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('18','6','Restored backup: backup_2026-05-04_005941.sql (errors: 0)','Backup','','::1','2026-05-04 13:56:53');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('19','6','Added chatbot Q&A','Chatbot','','::1','2026-05-04 14:02:24');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('20','6','Logout','Auth','User logged out','::1','2026-05-04 14:02:54');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('21','8','Login','Auth','User logged in','::1','2026-05-04 14:03:36');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('22','8','New inquiry #2 submitted','Inquiries','','::1','2026-05-04 14:04:09');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('23','8','Logout','Auth','User logged out','::1','2026-05-04 14:04:40');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('24','6','Login','Auth','User logged in','::1','2026-05-04 14:04:47');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('25','8','Login','Auth','User logged in','::1','2026-05-04 14:05:04');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('26','6','Logout','Auth','User logged out','::1','2026-05-04 14:05:12');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('27','7','Login','Auth','User logged in','::1','2026-05-04 14:05:37');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('28','7','Joined live chat #3','LiveChat','','::1','2026-05-04 14:06:56');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('29','7','Logout','Auth','User logged out','::1','2026-05-04 14:08:37');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('30','7','Login','Auth','User logged in','::1','2026-05-04 14:08:56');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('31','7','Updated feature locks','System','','::1','2026-05-04 14:09:03');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('32','7','Added service: 24356789','Services','','::1','2026-05-04 14:09:51');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('33','8','Logout','Auth','User logged out','::1','2026-05-04 14:12:07');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('34','8','Login','Auth','User logged in','::1','2026-05-04 14:12:44');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('35','8','Submitted stock request: remove 100 for product #1','Inventory','','::1','2026-05-04 14:13:09');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('36','7','Approved inventory request #2 (product #1, remove 100)','Inventory','','::1','2026-05-04 14:13:18');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('37','8','Borrowed tool #5 (qty: 1)','Tools','','::1','2026-05-04 14:14:38');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('38','8','Returned borrow #1','Tools','','::1','2026-05-04 14:15:04');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('39','7','Updated About Us content','Content','','::1','2026-05-04 14:15:50');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('40','8','Logout','Auth','User logged out','::1','2026-05-04 14:15:57');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('41','7','Logout','Auth','User logged out','::1','2026-05-04 14:17:33');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('42','7','Login','Auth','User logged in','::1','2026-05-04 14:18:24');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('43','7','Updated feature locks','System','','::1','2026-05-04 14:18:38');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('44','7','Login','Auth','User logged in','::1','2026-05-04 14:20:23');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('45','7','Restored backup: backup_2026-05-04_082049.sql (errors: 0)','Backup','','::1','2026-05-04 14:21:14');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('46','6','Login','Auth','User logged in','::1','2026-05-05 11:56:32');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('47','7','Login','Auth','User logged in','::1','2026-05-05 11:57:23');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('48','6','Logout','Auth','User logged out','::1','2026-05-05 11:59:04');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('49','8','Login','Auth','User logged in','::1','2026-05-05 11:59:46');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('50','8','Submitted stock request: add 100 for product #11','Inventory','','::1','2026-05-05 12:00:20');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('51','7','Approved inventory request #3 (product #11, add 100)','Inventory','','::1','2026-05-05 12:01:12');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('52','7','Logout','Auth','User logged out','::1','2026-05-05 12:01:46');
INSERT INTO `activity_logs` (`log_id`,`user_id`,`action`,`module`,`details`,`ip_address`,`logged_at`) VALUES ('53','7','Login','Auth','User logged in','::1','2026-05-05 12:02:00');

DROP TABLE IF EXISTS `backup_logs`;
CREATE TABLE `backup_logs` (
  `backup_id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`backup_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `backup_logs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `borrowed_tools`;
CREATE TABLE `borrowed_tools` (
  `borrow_id` int(11) NOT NULL AUTO_INCREMENT,
  `tool_id` int(11) DEFAULT NULL,
  `borrowed_by` varchar(100) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `borrow_date` date NOT NULL,
  `expected_return` date DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `condition_out` varchar(100) DEFAULT 'Good',
  `condition_in` varchar(100) DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') DEFAULT 'borrowed',
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`borrow_id`),
  KEY `tool_id` (`tool_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `borrowed_tools_ibfk_1` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`tool_id`) ON DELETE CASCADE,
  CONSTRAINT `borrowed_tools_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `borrowed_tools` (`borrow_id`,`tool_id`,`borrowed_by`,`quantity`,`borrow_date`,`expected_return`,`return_date`,`condition_out`,`condition_in`,`status`,`recorded_by`,`created_at`) VALUES ('1','5','test3 test3','1','2026-05-04','2026-05-07','2026-05-04','Good','Good','returned','8','2026-05-04 14:14:38');

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`category_id`,`category_name`) VALUES ('1','Roofing Sheets');
INSERT INTO `categories` (`category_id`,`category_name`) VALUES ('2','Structural');
INSERT INTO `categories` (`category_id`,`category_name`) VALUES ('3','Insulation');
INSERT INTO `categories` (`category_id`,`category_name`) VALUES ('4','Fasteners');
INSERT INTO `categories` (`category_id`,`category_name`) VALUES ('5','Sealants');
INSERT INTO `categories` (`category_id`,`category_name`) VALUES ('6','Accessories');

DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `sender_name` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `msg_type` enum('user','bot','agent') DEFAULT 'user',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`message_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`session_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('1','1','Assistant','Hi test 1 test1! 👋 I\'m the Eleven Roofing Dasma assistant. How can I help you today? Type your question or pick one from the left.','bot','2026-05-04 06:15:14');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('2','1','test 1 test1','How can I contact you?','user','2026-05-04 06:15:16');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('3','1','Assistant','Phone: (046) 123-4567 | Email: info@elevenroofingdasma.com | Hours: Mon-Sat 8AM-5PM','bot','2026-05-04 06:15:16');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('4','1','test 1 test1','Do you offer emergency repair services?','user','2026-05-04 06:15:17');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('5','1','Assistant','Yes! 24/7 emergency hotline: (046) 123-4568. Same-day dispatch available.','bot','2026-05-04 06:15:17');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('6','1','test 1 test1','How much does a roof repair cost?','user','2026-05-04 06:15:18');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('7','1','Assistant','Repairs start from P3,500. Emergency repairs from P5,000. Contact us for a free site assessment.','bot','2026-05-04 06:15:18');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('8','1','test 1 test1','How do I request a service?','user','2026-05-04 06:15:20');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('9','1','Assistant','Submit an inquiry through our Inquiry Form and our team will contact you within 24 hours.','bot','2026-05-04 06:15:20');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('10','1','test 1 test1','How do I request a service?','user','2026-05-04 06:15:21');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('11','1','Assistant','Submit an inquiry through our Inquiry Form and our team will contact you within 24 hours.','bot','2026-05-04 06:15:21');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('12','1','test 1 test1','How do I request a service?','user','2026-05-04 06:15:24');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('13','1','Assistant','Submit an inquiry through our Inquiry Form and our team will contact you within 24 hours.','bot','2026-05-04 06:15:24');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('14','1','Assistant','No problem! Connecting you to a human agent now. Please hold on — a team member will join shortly. 🙏','bot','2026-05-04 06:15:34');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('15','1','test2 test2','Hello! I\'m test2 test2 from Eleven Roofing Dasma. How can I help you today?','agent','2026-05-04 06:23:05');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('16','1','test2 test2','sabsafas','agent','2026-05-04 06:23:08');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('17','1','test2 test2','Helllo i am jan jheidric S dumagat I am a shitty wanna shesssshhhhh','agent','2026-05-04 06:25:40');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('18','1','test2 test2','zesretfyuhiopp','agent','2026-05-04 06:25:45');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('19','1','test2 test2','dfdgbcnvubvauvbbjvsvcusnkncsayvuas','agent','2026-05-04 06:25:49');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('20','2','Assistant','Hi test 1 test1! 👋 I\'m the Eleven Roofing Dasma assistant. How can I help you today? Type your question or pick one from the left.','bot','2026-05-04 06:27:21');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('21','3','Assistant','Hi test3 test3! 👋 I\'m the Eleven Roofing Dasma assistant. How can I help you today? Type your question or pick one from the left.','bot','2026-05-04 14:05:49');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('22','3','test3 test3','How are you today','user','2026-05-04 14:05:57');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('23','3','Assistant','Fine','bot','2026-05-04 14:05:58');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('24','3','test3 test3','How are you feeling today','user','2026-05-04 14:06:08');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('25','3','Assistant','Fine','bot','2026-05-04 14:06:08');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('26','3','test3 test3','How can i contact','user','2026-05-04 14:06:35');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('27','3','Assistant','I\'m sorry, I couldn\'t find a specific answer for that. Try one of the suggested questions, or click **Request Human Agent** for direct support from our team.','bot','2026-05-04 14:06:35');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('28','3','Assistant','No problem! Connecting you to a human agent now. Please hold on — a team member will join shortly. 🙏','bot','2026-05-04 14:06:43');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('29','3','test2 test2','Hello! I\'m test2 test2 from Eleven Roofing Dasma. How can I help you today?','agent','2026-05-04 14:06:56');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('30','3','test3 test3','sfasfhi','user','2026-05-04 14:07:24');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('31','3','test2 test2','afgqufuagfugasufsu','agent','2026-05-04 14:07:32');
INSERT INTO `chat_messages` (`message_id`,`session_id`,`sender_name`,`message`,`msg_type`,`sent_at`) VALUES ('32','3','test2 test2','sfusaihusfaisfiiasfaysfahsufuasugfy','agent','2026-05-04 14:07:52');

DROP TABLE IF EXISTS `chat_sessions`;
CREATE TABLE `chat_sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `status` enum('bot','waiting','active','closed') DEFAULT 'bot',
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `assigned_to` (`assigned_to`),
  CONSTRAINT `chat_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `chat_sessions_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chat_sessions` (`session_id`,`user_id`,`user_name`,`status`,`assigned_to`,`created_at`,`updated_at`) VALUES ('1','6','test 1 test1','closed','7','2026-05-04 06:15:14','2026-05-04 06:27:16');
INSERT INTO `chat_sessions` (`session_id`,`user_id`,`user_name`,`status`,`assigned_to`,`created_at`,`updated_at`) VALUES ('2','6','test 1 test1','bot',NULL,'2026-05-04 06:27:21','2026-05-04 06:27:21');
INSERT INTO `chat_sessions` (`session_id`,`user_id`,`user_name`,`status`,`assigned_to`,`created_at`,`updated_at`) VALUES ('3','8','test3 test3','active','7','2026-05-04 14:05:49','2026-05-04 14:07:52');

DROP TABLE IF EXISTS `chatbot_qa`;
CREATE TABLE `chatbot_qa` (
  `qa_id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(60) DEFAULT 'General',
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`qa_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `chatbot_qa_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chatbot_qa` (`qa_id`,`category`,`question`,`answer`,`is_active`,`created_by`,`created_at`,`updated_at`) VALUES ('1','Services','What roofing services do you offer?','We offer Complete Roof Installation, Leak Repair, Preventive Maintenance, Emergency Repairs (24/7), Roof Inspection, Waterproofing, and Gutter Installation.','1',NULL,'2026-05-03 23:42:17','2026-05-03 23:42:17');
INSERT INTO `chatbot_qa` (`qa_id`,`category`,`question`,`answer`,`is_active`,`created_by`,`created_at`,`updated_at`) VALUES ('2','Pricing','How much does a roof repair cost?','Repairs start from P3,500. Emergency repairs from P5,000. Contact us for a free site assessment.','1',NULL,'2026-05-03 23:42:17','2026-05-03 23:42:17');
INSERT INTO `chatbot_qa` (`qa_id`,`category`,`question`,`answer`,`is_active`,`created_by`,`created_at`,`updated_at`) VALUES ('3','Pricing','How much does a new roof installation cost?','Installations start from P45,000. Final price depends on roof size and material choice.','1',NULL,'2026-05-03 23:42:17','2026-05-03 23:42:17');
INSERT INTO `chatbot_qa` (`qa_id`,`category`,`question`,`answer`,`is_active`,`created_by`,`created_at`,`updated_at`) VALUES ('4','Process','How do I request a service?','Submit an inquiry through our Inquiry Form and our team will contact you within 24 hours.','1',NULL,'2026-05-03 23:42:17','2026-05-03 23:42:17');
INSERT INTO `chatbot_qa` (`qa_id`,`category`,`question`,`answer`,`is_active`,`created_by`,`created_at`,`updated_at`) VALUES ('5','Emergency','Do you offer emergency repair services?','Yes! 24/7 emergency hotline: (046) 123-4568. Same-day dispatch available.','1',NULL,'2026-05-03 23:42:17','2026-05-03 23:42:17');
INSERT INTO `chatbot_qa` (`qa_id`,`category`,`question`,`answer`,`is_active`,`created_by`,`created_at`,`updated_at`) VALUES ('6','Warranty','Do your installations come with a warranty?','All installations come with a 1-3 year workmanship warranty plus manufacturer warranty on materials.','1',NULL,'2026-05-03 23:42:17','2026-05-03 23:42:17');
INSERT INTO `chatbot_qa` (`qa_id`,`category`,`question`,`answer`,`is_active`,`created_by`,`created_at`,`updated_at`) VALUES ('7','Contact','How can I contact you?','Phone: (046) 123-4567 | Email: info@elevenroofingdasma.com | Hours: Mon-Sat 8AM-5PM','1',NULL,'2026-05-03 23:42:17','2026-05-03 23:42:17');
INSERT INTO `chatbot_qa` (`qa_id`,`category`,`question`,`answer`,`is_active`,`created_by`,`created_at`,`updated_at`) VALUES ('8','Services','How are you today','Fine','1','6','2026-05-04 14:02:24','2026-05-04 14:02:24');

DROP TABLE IF EXISTS `contact_content`;
CREATE TABLE `contact_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_key` varchar(100) NOT NULL,
  `field_label` varchar(100) DEFAULT NULL,
  `field_value` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `field_key` (`field_key`),
  KEY `updated_by` (`updated_by`),
  CONSTRAINT `contact_content_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contact_content` (`id`,`field_key`,`field_label`,`field_value`,`updated_by`,`updated_at`) VALUES ('1','address','Main Office','123 Roofing Ave, Brgy. Salitran, Dasmarinas, Cavite 4114',NULL,'2026-05-03 23:42:17');
INSERT INTO `contact_content` (`id`,`field_key`,`field_label`,`field_value`,`updated_by`,`updated_at`) VALUES ('2','phone','Main Phone','(046) 123-4567',NULL,'2026-05-03 23:42:17');
INSERT INTO `contact_content` (`id`,`field_key`,`field_label`,`field_value`,`updated_by`,`updated_at`) VALUES ('3','emergency_phone','Emergency Hotline','(046) 123-4568',NULL,'2026-05-03 23:42:17');
INSERT INTO `contact_content` (`id`,`field_key`,`field_label`,`field_value`,`updated_by`,`updated_at`) VALUES ('4','email','Email Address','info@elevenroofingdasma.com',NULL,'2026-05-03 23:42:17');
INSERT INTO `contact_content` (`id`,`field_key`,`field_label`,`field_value`,`updated_by`,`updated_at`) VALUES ('5','hours_weekday','Weekday Hours','Monday - Friday: 8:00 AM - 5:00 PM',NULL,'2026-05-03 23:42:17');
INSERT INTO `contact_content` (`id`,`field_key`,`field_label`,`field_value`,`updated_by`,`updated_at`) VALUES ('6','hours_saturday','Saturday Hours','Saturday: 8:00 AM - 12:00 PM',NULL,'2026-05-03 23:42:17');
INSERT INTO `contact_content` (`id`,`field_key`,`field_label`,`field_value`,`updated_by`,`updated_at`) VALUES ('7','branch_1','Main Branch - Dasmarinas','123 Roofing Ave, Brgy. Salitran, Dasmarinas',NULL,'2026-05-03 23:42:17');
INSERT INTO `contact_content` (`id`,`field_key`,`field_label`,`field_value`,`updated_by`,`updated_at`) VALUES ('8','branch_2','Imus Branch','456 Construction Road, Imus, Cavite',NULL,'2026-05-03 23:42:17');
INSERT INTO `contact_content` (`id`,`field_key`,`field_label`,`field_value`,`updated_by`,`updated_at`) VALUES ('9','branch_3','Bacoor Branch','789 Builder Street, Bacoor, Cavite',NULL,'2026-05-03 23:42:17');

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(60) DEFAULT NULL,
  `last_name` varchar(60) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `inquiries`;
CREATE TABLE `inquiries` (
  `inquiry_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(60) DEFAULT NULL,
  `last_name` varchar(60) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','in_progress','resolved') DEFAULT 'pending',
  `response` text DEFAULT NULL,
  `responded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`inquiry_id`),
  KEY `user_id` (`user_id`),
  KEY `responded_by` (`responded_by`),
  CONSTRAINT `inquiries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `inquiries_ibfk_2` FOREIGN KEY (`responded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inquiries` (`inquiry_id`,`user_id`,`first_name`,`last_name`,`email`,`contact`,`service_type`,`subject`,`message`,`status`,`response`,`responded_by`,`created_at`,`updated_at`) VALUES ('1','6','234567890','23456789','Owner@gmail.com','3456789','Emergency Repair','454dbvkbvz','vv dv zj xj dzv z  izv znxvzouovnzvz','resolved','We are currently jabufuafuaf','7','2026-05-04 06:15:06','2026-05-04 06:27:31');
INSERT INTO `inquiries` (`inquiry_id`,`user_id`,`first_name`,`last_name`,`email`,`contact`,`service_type`,`subject`,`message`,`status`,`response`,`responded_by`,`created_at`,`updated_at`) VALUES ('2','8','test3@gmail.com','test3@gmail.com','test3@gmail.com','87654345678','Emergency Repair','roof repair','w4ertui','pending',NULL,NULL,'2026-05-04 14:04:09','2026-05-04 14:04:09');

DROP TABLE IF EXISTS `inventory_logs`;
CREATE TABLE `inventory_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `change_type` enum('add','remove','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `old_stock` int(11) DEFAULT NULL,
  `new_stock` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `logged_by` int(11) DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `product_id` (`product_id`),
  KEY `logged_by` (`logged_by`),
  CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`logged_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inventory_logs` (`log_id`,`product_id`,`change_type`,`quantity`,`old_stock`,`new_stock`,`notes`,`logged_by`,`logged_at`) VALUES ('1','9','add','100','12','112','Admin direct: Shesh','7','2026-05-04 06:27:55');
INSERT INTO `inventory_logs` (`log_id`,`product_id`,`change_type`,`quantity`,`old_stock`,`new_stock`,`notes`,`logged_by`,`logged_at`) VALUES ('2','1','remove','100','240','140','Approved staff request #2: Some buy','7','2026-05-04 14:13:18');
INSERT INTO `inventory_logs` (`log_id`,`product_id`,`change_type`,`quantity`,`old_stock`,`new_stock`,`notes`,`logged_by`,`logged_at`) VALUES ('3','11','add','100','150','250','Approved staff request #3: wutduteu','7','2026-05-05 12:01:12');

DROP TABLE IF EXISTS `inventory_requests`;
CREATE TABLE `inventory_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `change_type` enum('add','remove') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `requested_by` int(11) DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `review_note` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `product_id` (`product_id`),
  KEY `requested_by` (`requested_by`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `inventory_requests_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_requests_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_requests_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inventory_requests` (`request_id`,`product_id`,`change_type`,`quantity`,`reason`,`status`,`requested_by`,`reviewed_by`,`review_note`,`requested_at`,`reviewed_at`) VALUES ('2','1','remove','100','Some buy','approved','8','7','Approved','2026-05-04 14:13:09','2026-05-04 14:13:18');
INSERT INTO `inventory_requests` (`request_id`,`product_id`,`change_type`,`quantity`,`reason`,`status`,`requested_by`,`reviewed_by`,`review_note`,`requested_at`,`reviewed_at`) VALUES ('3','11','add','100','wutduteu','approved','8','7','Approved','2026-05-05 12:00:20','2026-05-05 12:01:12');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `min_stock` int(11) DEFAULT 50,
  `image_path` varchar(500) DEFAULT NULL,
  `icon_emoji` varchar(10) DEFAULT '?',
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('1','1','Corrugated G.I. Sheet 0.5mm','Standard corrugated galvanized iron sheets.','280.00','140','100',NULL,'🟫','1','2026-05-03 23:42:16','2026-05-04 14:13:18');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('2','1','Pre-painted Color Roofing','UV-resistant pre-painted panels.','320.00','180','80',NULL,'🎨','1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('3','1','Long Span Rib-type Roofing','Heavy-duty commercial roofing.','410.00','95','50',NULL,'🟤','1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('4','2','Roof Truss Steel Frame','Engineered steel truss systems.','12500.00','48','20',NULL,'🪵','1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('5','2','Purlins C-Channel 2mm','Standard C-channel purlins.','680.00','320','100',NULL,'⬛','1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('6','3','Thermal Foam Insulation Board','High-density foam insulation.','850.00','120','40',NULL,'🧱','1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('7','3','Bubble Foil Insulation Roll','Double-sided aluminum bubble foil.','1200.00','75','30',NULL,'✨','1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('8','4','Roofing Tek Screws (Box)','Self-drilling hex head screws. 250pcs/box.','320.00','380','100',NULL,'🔩','1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('9','4','J-Bolt Anchor Set','Heavy-duty J-bolts for securing purlins.','45.00','112','50',NULL,'🪛','1','2026-05-03 23:42:16','2026-05-04 06:27:55');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('10','5','Polyurethane Roof Sealant','Flexible polyurethane sealant.','580.00','200','60',NULL,'🧴','1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('11','5','Butyl Tape 2x10m','Self-adhesive butyl rubber tape.','280.00','250','50',NULL,'📦','1','2026-05-03 23:42:16','2026-05-05 12:01:12');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('12','6','Ridge Roll Cap','Standard ridge cap for waterproofing.','150.00','500','100',NULL,'🔺','1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `products` (`product_id`,`category_id`,`product_name`,`description`,`price`,`stock_quantity`,`min_stock`,`image_path`,`icon_emoji`,`is_active`,`created_at`,`updated_at`) VALUES ('13','6','Gutter Hanger Set','Adjustable gutter brackets.','35.00','700','150',NULL,'🔗','1','2026-05-03 23:42:16','2026-05-03 23:42:16');

DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` varchar(60) DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`report_id`),
  KEY `generated_by` (`generated_by`),
  CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reports` (`report_id`,`report_type`,`generated_by`,`generated_at`) VALUES ('1','inquiries','7','2026-05-04 06:26:37');
INSERT INTO `reports` (`report_id`,`report_type`,`generated_by`,`generated_at`) VALUES ('2','inventory','7','2026-05-04 06:26:40');
INSERT INTO `reports` (`report_id`,`report_type`,`generated_by`,`generated_at`) VALUES ('3','products','7','2026-05-04 06:26:47');
INSERT INTO `reports` (`report_id`,`report_type`,`generated_by`,`generated_at`) VALUES ('4','products','7','2026-05-04 06:28:35');
INSERT INTO `reports` (`report_id`,`report_type`,`generated_by`,`generated_at`) VALUES ('5','inquiries','7','2026-05-04 14:16:15');
INSERT INTO `reports` (`report_id`,`report_type`,`generated_by`,`generated_at`) VALUES ('6','inventory','7','2026-05-04 14:16:20');
INSERT INTO `reports` (`report_id`,`report_type`,`generated_by`,`generated_at`) VALUES ('7','products','7','2026-05-04 14:16:41');
INSERT INTO `reports` (`report_id`,`report_type`,`generated_by`,`generated_at`) VALUES ('8','tools','7','2026-05-04 14:16:41');
INSERT INTO `reports` (`report_id`,`report_type`,`generated_by`,`generated_at`) VALUES ('9','inventory','7','2026-05-04 14:16:57');
INSERT INTO `reports` (`report_id`,`report_type`,`generated_by`,`generated_at`) VALUES ('10','inquiries','7','2026-05-04 14:16:59');

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `level` int(11) DEFAULT 0,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`role_id`,`role_name`,`level`) VALUES ('1','Owner','100');
INSERT INTO `roles` (`role_id`,`role_name`,`level`) VALUES ('2','System Admin','80');
INSERT INTO `roles` (`role_id`,`role_name`,`level`) VALUES ('3','Administrator','60');
INSERT INTO `roles` (`role_id`,`role_name`,`level`) VALUES ('4','Staff','40');
INSERT INTO `roles` (`role_id`,`role_name`,`level`) VALUES ('5','Customer','10');

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price_from` decimal(12,2) DEFAULT NULL,
  `duration` varchar(60) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`service_id`,`service_name`,`description`,`category`,`price_from`,`duration`,`image_path`,`is_active`,`created_at`,`updated_at`) VALUES ('1','Complete Roof Installation','Full new roofing system for residential and commercial.','Installation','45000.00','3-7 days',NULL,'1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `services` (`service_id`,`service_name`,`description`,`category`,`price_from`,`duration`,`image_path`,`is_active`,`created_at`,`updated_at`) VALUES ('2','Roof Leak Repair','Expert leak detection and targeted repair.','Repair','3500.00','1-2 days',NULL,'1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `services` (`service_id`,`service_name`,`description`,`category`,`price_from`,`duration`,`image_path`,`is_active`,`created_at`,`updated_at`) VALUES ('3','Preventive Maintenance','Scheduled maintenance to extend roof lifespan.','Maintenance','1800.00','Half day',NULL,'1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `services` (`service_id`,`service_name`,`description`,`category`,`price_from`,`duration`,`image_path`,`is_active`,`created_at`,`updated_at`) VALUES ('4','Roof Replacement','Complete tear-off and replacement.','Installation','60000.00','5-10 days',NULL,'1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `services` (`service_id`,`service_name`,`description`,`category`,`price_from`,`duration`,`image_path`,`is_active`,`created_at`,`updated_at`) VALUES ('5','Emergency Repair','24/7 emergency services for storm damage.','Repair','5000.00','Same day',NULL,'1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `services` (`service_id`,`service_name`,`description`,`category`,`price_from`,`duration`,`image_path`,`is_active`,`created_at`,`updated_at`) VALUES ('6','Roof Inspection','Comprehensive assessment with written report.','Inspection','1200.00','2-4 hours',NULL,'1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `services` (`service_id`,`service_name`,`description`,`category`,`price_from`,`duration`,`image_path`,`is_active`,`created_at`,`updated_at`) VALUES ('7','Waterproofing Application','Professional waterproofing for flat roofs.','Maintenance','4500.00','1-2 days',NULL,'1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `services` (`service_id`,`service_name`,`description`,`category`,`price_from`,`duration`,`image_path`,`is_active`,`created_at`,`updated_at`) VALUES ('8','Gutter Installation','Design and installation of drainage systems.','Installation','8000.00','1-2 days',NULL,'1','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `services` (`service_id`,`service_name`,`description`,`category`,`price_from`,`duration`,`image_path`,`is_active`,`created_at`,`updated_at`) VALUES ('9','24356789','ijdfguuggyfug','Installation','244.00','1 day','assets/images/uploads/svc_69f8382fbcf88.png','1','2026-05-04 14:09:51','2026-05-04 14:09:51');

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `locked_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`),
  KEY `locked_by` (`locked_by`),
  CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`locked_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`setting_key`,`setting_value`,`locked_by`,`updated_at`) VALUES ('lock_about','0','7','2026-05-04 06:57:50');
INSERT INTO `system_settings` (`setting_key`,`setting_value`,`locked_by`,`updated_at`) VALUES ('lock_chatbot','1','7','2026-05-04 14:18:38');
INSERT INTO `system_settings` (`setting_key`,`setting_value`,`locked_by`,`updated_at`) VALUES ('lock_contact','0','7','2026-05-04 06:57:50');
INSERT INTO `system_settings` (`setting_key`,`setting_value`,`locked_by`,`updated_at`) VALUES ('lock_inventory','0','7','2026-05-04 06:57:50');
INSERT INTO `system_settings` (`setting_key`,`setting_value`,`locked_by`,`updated_at`) VALUES ('lock_products','0','7','2026-05-04 06:57:50');
INSERT INTO `system_settings` (`setting_key`,`setting_value`,`locked_by`,`updated_at`) VALUES ('lock_services','1','7','2026-05-04 14:18:38');
INSERT INTO `system_settings` (`setting_key`,`setting_value`,`locked_by`,`updated_at`) VALUES ('maintenance_mode','0',NULL,'2026-05-03 23:42:16');
INSERT INTO `system_settings` (`setting_key`,`setting_value`,`locked_by`,`updated_at`) VALUES ('site_name','Eleven Roofing Dasma',NULL,'2026-05-03 23:42:16');

DROP TABLE IF EXISTS `tools`;
CREATE TABLE `tools` (
  `tool_id` int(11) NOT NULL AUTO_INCREMENT,
  `tool_name` varchar(100) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `available` int(11) DEFAULT 0,
  PRIMARY KEY (`tool_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tools` (`tool_id`,`tool_name`,`quantity`,`available`) VALUES ('1','Electric Drill','5','3');
INSERT INTO `tools` (`tool_id`,`tool_name`,`quantity`,`available`) VALUES ('2','Roofing Hammer','20','15');
INSERT INTO `tools` (`tool_id`,`tool_name`,`quantity`,`available`) VALUES ('3','Scaffolding Set','3','2');
INSERT INTO `tools` (`tool_id`,`tool_name`,`quantity`,`available`) VALUES ('4','Safety Harness','10','7');
INSERT INTO `tools` (`tool_id`,`tool_name`,`quantity`,`available`) VALUES ('5','Circular Saw','4','4');
INSERT INTO `tools` (`tool_id`,`tool_name`,`quantity`,`available`) VALUES ('6','Angle Grinder','6','5');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role_id` int(11) DEFAULT 5,
  `status` enum('active','inactive','locked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`user_id`,`full_name`,`email`,`password`,`contact_number`,`address`,`role_id`,`status`,`created_at`,`updated_at`) VALUES ('1','a','saubsfb@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'4','active','2026-05-03 23:42:16','2026-05-04 06:18:13');
INSERT INTO `users` (`user_id`,`full_name`,`email`,`password`,`contact_number`,`address`,`role_id`,`status`,`created_at`,`updated_at`) VALUES ('2','System Admin','sysadmin@elevenroofingdasma.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'2','active','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `users` (`user_id`,`full_name`,`email`,`password`,`contact_number`,`address`,`role_id`,`status`,`created_at`,`updated_at`) VALUES ('3','Administrator','admin@elevenroofingdasma.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'3','active','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `users` (`user_id`,`full_name`,`email`,`password`,`contact_number`,`address`,`role_id`,`status`,`created_at`,`updated_at`) VALUES ('4','Staff Member','staff@elevenroofingdasma.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'4','active','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `users` (`user_id`,`full_name`,`email`,`password`,`contact_number`,`address`,`role_id`,`status`,`created_at`,`updated_at`) VALUES ('5','Juan Customer','juan@email.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NULL,NULL,'5','active','2026-05-03 23:42:16','2026-05-03 23:42:16');
INSERT INTO `users` (`user_id`,`full_name`,`email`,`password`,`contact_number`,`address`,`role_id`,`status`,`created_at`,`updated_at`) VALUES ('6','test 1 test1','Owner@gmail.com','$2y$10$XtJw.3Zyd9di9mQ/dD5CxO4aimSE1RVCAYVC2E0Qj9BkZbsFEyrLa','12345678901','1234567890','5','active','2026-05-04 06:11:39','2026-05-04 06:11:39');
INSERT INTO `users` (`user_id`,`full_name`,`email`,`password`,`contact_number`,`address`,`role_id`,`status`,`created_at`,`updated_at`) VALUES ('7','test2 test2','Customer@gmai.com','$2y$10$MXJT7UrbgASUNE6H0DCU9.IrLC6sxuIHCfGSdFFPJgWPRPUM./9ba','36623657392797','bab','1','active','2026-05-04 06:17:14','2026-05-04 14:08:30');
INSERT INTO `users` (`user_id`,`full_name`,`email`,`password`,`contact_number`,`address`,`role_id`,`status`,`created_at`,`updated_at`) VALUES ('8','test3 test3','test3@gmail.com','$2y$10$BELNMUeSu1O1ovFMaDvjWurKZDI3RCX0lHYkYzLYJDTBpWxtnAHOi','12345678989','23456789','4','active','2026-05-04 14:03:29','2026-05-04 14:12:28');

SET FOREIGN_KEY_CHECKS=1;
