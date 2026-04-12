CREATE TABLE `contact_submissions` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
)

CREATE TABLE `departments` (
  `dept_id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `dept_name` varchar(50) NOT NULL,
  `acronym` varchar(10) DEFAULT NULL
)

CREATE TABLE `event` (
  `event_id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `max_participants` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `venue` varchar(100) NOT NULL,
  `u_id` int(11) NOT NULL,
  `event_image` mediumblob DEFAULT NULL,
  `dept_id` int(11) DEFAULT NULL
)

CREATE TABLE `registration` (
  `reg_id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `u_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `attendance_status` varchar(20) NOT NULL DEFAULT 'registered'
)

CREATE TABLE `users` (
  `u_id` int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` mediumblob NOT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `profile_updated_at` timestamp NULL DEFAULT NULL,
  `password_updated_at` timestamp NULL DEFAULT NULL
)


ALTER TABLE `event`
  ADD KEY `fk_event_user` (`u_id`);
  ADD CONSTRAINT `fk_event_user` FOREIGN KEY (`u_id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE;

ALTER TABLE `registration`
  ADD UNIQUE KEY `unique_registration` (`u_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);
  ADD CONSTRAINT `registration_ibfk_1` FOREIGN KEY (`u_id`) REFERENCES `users` (`u_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `registration_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `users`
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_department` (`dept_id`);
  ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`) ON DELETE SET NULL ON UPDATE CASCADE;