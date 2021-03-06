/* 
SAVE:		DB Creation and Maintenance Script
PROJECT:	Signup Sheets
NOTES:		For testing, create 'dblinktest' table by executing: "db_setup/testing_schema.sql"

FOR TESTING ONLY:
	USE `signup_sheets_development`;
	-- USE `signup_sheets_test_suite`;

	DROP TABLE `terms`;
	DROP TABLE `users`;
	DROP TABLE `courses`;
	DROP TABLE `enrollments`;
	DROP TABLE `sus_sheetgroups`;
	DROP TABLE `sus_sheets`;
	DROP TABLE `sus_openings`;
	DROP TABLE `sus_signups`;
	DROP TABLE `sus_access`;
	DROP TABLE `sus_eventlogs`;
	DROP TABLE `queued_messages`;

	DELETE FROM `terms`;
	DELETE FROM `users`;
	DELETE FROM `courses`;
	DELETE FROM `enrollments`;
	DELETE FROM `sus_sheetgroups`;
	DELETE FROM `sus_sheets`;
	DELETE FROM `sus_openings`;
	DELETE FROM `sus_signups`;
	DELETE FROM `sus_access`;
	DELETE FROM `sus_eventlogs`;
	DELETE FROM `queued_messages`;

	SELECT * FROM `terms`;
	SELECT * FROM `users`;
	SELECT * FROM `courses`;
	SELECT * FROM `enrollments`;
	SELECT * FROM `sus_sheetgroups`;
	SELECT * FROM `sus_sheets`;
	SELECT * FROM `sus_openings`;
	SELECT * FROM `sus_signups`;
	SELECT * FROM `sus_access`;
	SELECT * FROM `sus_eventlogs` ORDER BY eventlog_id DESC;
	SELECT * FROM `queued_messages` ORDER BY queued_message_id DESC;
*/

# ----------------------------
# IMPORTANT: Select which database you wish to create and run this script against
# ----------------------------
# Database for Development work
CREATE SCHEMA IF NOT EXISTS `signup_sheets_development`;
USE `signup_sheets_development`;

# Database for TestSuite (unit testing) work
-- CREATE SCHEMA IF NOT EXISTS `signup_sheets_test_suite`;
-- USE `signup_sheets_test_suite`;

# Database for live (production) work
--  CREATE SCHEMA IF NOT EXISTS `signup_sheets_live`;
-- USE `signup_sheets_live`;

# ----------------------------
# IMPORTANT: For local workstation testing, create web user and enter [DB_NAME, DB_USER, DB_PASS] credentials into "institution.cfg.php" file.
# ----------------------------
-- CREATE USER 'some_dev_username'@'localhost' IDENTIFIED BY 'some_pass_phrase';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON signup_sheets_development.* TO 'some_dev_username'@'localhost';
-- /* CAREFUL!: DROP USER 'some_dev_username'@'localhost'; */

# ----------------------------
# setup database tables
# ----------------------------

CREATE TABLE IF NOT EXISTS `terms` (
	`term_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`canvas_term_id` INT NOT NULL DEFAULT 0,
	`term_idstr` VARCHAR(255) NOT NULL,
	`name` VARCHAR(255) NULL,
	`start_date` TIMESTAMP,
	`end_date` TIMESTAMP,
	`flag_delete` BIT(1) NOT NULL DEFAULT 0,
	INDEX `canvas_term_id` (`canvas_term_id`),
	INDEX `term_idstr` (`term_idstr`),
	INDEX `name` (`name`),
	INDEX `start_date` (`start_date`),
	INDEX `end_date` (`end_date`),
	INDEX `flag_delete` (`flag_delete`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';

CREATE TABLE IF NOT EXISTS `users` (
	`user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`canvas_user_id` INT NOT NULL DEFAULT 0,
	`sis_user_id` INT NOT NULL DEFAULT 0,
	`username` VARCHAR(255) NOT NULL,
	`email` VARCHAR(255) NULL,
	`first_name` VARCHAR(255) NULL,
	`last_name` VARCHAR(255) NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NULL,
	`flag_is_system_admin` BIT(1) NOT NULL DEFAULT 0,
	`flag_is_banned` BIT(1) NOT NULL DEFAULT 0,
	`flag_delete` BIT(1) NOT NULL DEFAULT 0,
	INDEX `canvas_user_id` (`canvas_user_id`),
	INDEX `sis_user_id` (`sis_user_id`),
	INDEX `username` (`username`),
	INDEX `flag_is_system_admin` (`flag_is_system_admin`),
	INDEX `flag_is_banned` (`flag_is_banned`),
	INDEX `flag_delete` (`flag_delete`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';
/* field 'username' corresponds to Canvas LMS field called 'login_id' */

CREATE TABLE IF NOT EXISTS `courses` (
	`course_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`course_idstr` VARCHAR(255) NOT NULL,
	`short_name` VARCHAR(255) NOT NULL,
	`long_name` VARCHAR(255) NOT NULL,
	`account_idstr` VARCHAR(255) NULL,
	`term_idstr` VARCHAR(255) NULL,
	`canvas_course_id` INT NOT NULL DEFAULT 0,
	`begins_at_str` VARCHAR(24) NULL,
	`ends_at_str` VARCHAR(24) NULL,
	`flag_delete` BIT(1) NOT NULL DEFAULT 0,
	INDEX `course_idstr` (`course_idstr`),
	INDEX `term_idstr` (`term_idstr`),
	INDEX `canvas_course_id` (`canvas_course_id`),
	INDEX `flag_delete` (`flag_delete`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';

CREATE TABLE IF NOT EXISTS `enrollments` (
	`enrollment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`canvas_user_id` INT NOT NULL DEFAULT 0,
	`canvas_course_id` INT NOT NULL DEFAULT 0,
	`canvas_role_name` VARCHAR(255) NULL,
	`course_idstr` VARCHAR(255) NOT NULL,
	`course_role_name` VARCHAR(48) NOT NULL,
	`section_idstr` VARCHAR(255) NULL,
	`flag_delete` BIT(1) NOT NULL DEFAULT 0,
	INDEX `canvas_user_id` (`canvas_user_id`),
	INDEX `canvas_course_id` (`canvas_course_id`),
	INDEX `canvas_role_name` (`canvas_role_name`),
	INDEX `course_idstr` (`course_idstr`),
	INDEX `course_role_name` (`course_role_name`),
	INDEX `section_idstr` (`section_idstr`),
	INDEX `flag_delete` (`flag_delete`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';

CREATE TABLE IF NOT EXISTS `sus_sheetgroups` (
	`sheetgroup_id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NULL,
	`flag_delete` tinyint(1) unsigned default NULL,
	`owner_user_id` bigint(10) unsigned default NULL,
	`flag_is_default` int(1) NOT NULL default '0',
	`name` varchar(255) default NULL,
	`description` text,
	`max_g_total_user_signups` smallint signed default -1,
	`max_g_pending_user_signups` smallint signed default -1,
	INDEX `flag_delete` (`flag_delete`),
	INDEX `owner_user_id` (`owner_user_id`),
	INDEX `flag_is_default` (`flag_is_default`),
	INDEX `name` (`name`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='For managing collections of related sheets';

CREATE TABLE IF NOT EXISTS `sus_sheets` (
	`sheet_id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NULL,
	`flag_delete` tinyint(1) unsigned default NULL,
	`owner_user_id` bigint(10) unsigned default NULL,
	`sheetgroup_id` bigint(10) unsigned default NULL,
	`name` varchar(255) default NULL,
	`description` text,
	`type` varchar(32) default NULL,
	`begin_date` TIMESTAMP NULL,
	`end_date` TIMESTAMP NULL,
	`max_total_user_signups` smallint signed default -1,
	`max_pending_user_signups` smallint signed default -1,
	`flag_alert_owner_change` tinyint(1) unsigned default NULL,
	`flag_alert_owner_signup` tinyint(1) unsigned default NULL,
	`flag_alert_owner_imminent` tinyint(1) unsigned default NULL,
	`flag_alert_admin_change` tinyint(1) unsigned default NULL,
	`flag_alert_admin_signup` tinyint(1) unsigned default NULL,
	`flag_alert_admin_imminent` tinyint(1) unsigned default NULL,
	`flag_private_signups` int(1) default '1',
	INDEX `flag_delete` (`flag_delete`),
	INDEX `owner_user_id` (`owner_user_id`),
	INDEX `sheetgroup_id` (`sheetgroup_id`),
	INDEX `name` (`name`),
	INDEX `type` (`type`),
	INDEX `begin_date` (`begin_date`),
	INDEX `end_date` (`end_date`),
	INDEX `flag_alert_owner_change` (`flag_alert_owner_change`),
	INDEX `flag_alert_owner_signup` (`flag_alert_owner_signup`),
	INDEX `flag_alert_owner_imminent` (`flag_alert_owner_imminent`),
	INDEX `flag_alert_admin_change` (`flag_alert_admin_change`),
	INDEX `flag_alert_admin_signup` (`flag_alert_admin_signup`),
	INDEX `flag_alert_admin_imminent` (`flag_alert_admin_imminent`),
	INDEX `flag_private_signups` (`flag_private_signups`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Contains the high-level sheet data (name, descr, etc.)';

CREATE TABLE IF NOT EXISTS `sus_openings` (
	`opening_id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NULL,
	`flag_delete` tinyint(1) unsigned default NULL,
	`sheet_id` bigint(10) unsigned default NULL,
	`opening_group_id` bigint(20) unsigned default NULL,
	`name` varchar(255) default NULL,
	`description` text,
	`max_signups` smallint signed default 1,
	`begin_datetime` TIMESTAMP NULL,
	`end_datetime` TIMESTAMP NULL,
	`location` varchar(255) default NULL,
	`admin_comment` varchar(255) default NULL,
	INDEX `flag_delete` (`flag_delete`),
	INDEX `sheet_id` (`sheet_id`),
	INDEX `opening_group_id` (`opening_group_id`),
	INDEX `name` (`name`),
	INDEX `begin_datetime` (`begin_datetime`),
	INDEX `end_datetime` (`end_datetime`),
	INDEX `location` (`location`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Places users can sign up - a single sheet may have multiple ';

CREATE TABLE IF NOT EXISTS `sus_signups` (
	`signup_id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NULL,
	`flag_delete` tinyint(1) unsigned default NULL,
	`opening_id` bigint(10) unsigned default NULL,
	`signup_user_id` bigint(10) unsigned default NULL,
	`admin_comment` varchar(255) default NULL,
	INDEX `flag_delete` (`flag_delete`),
	INDEX `opening_id` (`opening_id`),
	INDEX `signup_user_id` (`signup_user_id`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Users signing up for openings - analogous to a list of times and dates on a piece of paper that is passed around or posted on a door and on which people would put their name';

CREATE TABLE IF NOT EXISTS `sus_access` (
	`access_id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated_at` TIMESTAMP NULL,
	`sheet_id` bigint(10) unsigned default NULL,
	`type` varchar(48) default NULL,
	`constraint_id` bigint(10) unsigned default NULL,
	`constraint_data` varchar(255) default NULL,
	`broadness` int(11) default NULL,
	INDEX `sheet_id` (`sheet_id`),
	INDEX `type` (`type`),
	INDEX `constraint_id` (`constraint_id`),
	INDEX `constraint_data` (`constraint_data`),
	INDEX `broadness` (`broadness`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='which users can signup on which sheets';

CREATE TABLE IF NOT EXISTS `sus_eventlogs` (
	`eventlog_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`user_id` bigint(10) unsigned default NULL,
	`flag_success` tinyint(1) unsigned default 1,
	`event_action` VARCHAR(255) NULL,
	`event_action_id` bigint(10) unsigned default NULL,
	`event_action_target_type` VARCHAR(255) NULL,
	`event_note` VARCHAR(2000) NULL,
	`event_dataset` VARCHAR(2000) NULL,
	`event_filepath` VARCHAR(1000) NULL,
	`user_agent_string` VARCHAR(1000) NULL,
	`event_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	INDEX `eventlog_id` (`eventlog_id`),
	INDEX `user_id` (`user_id`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Event logs maintain an audit of user actions';

# notes: a msg to 10 people will create 10 separate queued messages (to enable 1 user to delete their signup w/o effecting others)
CREATE TABLE IF NOT EXISTS `queued_messages` (
	`queued_message_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`user_id` bigint(10) unsigned default NULL,
	`sheet_id` bigint(10) unsigned default NULL,
	`opening_id` bigint(10) unsigned default NULL,
	`delivery_type` VARCHAR(16) NULL, /*email (future may support other types such as sms/text) */
	`flag_is_delivered` BIT(1) NOT NULL DEFAULT 0,
#`hold_until_datetime` DATETIME NULL, /* not in use for this application */
	`target` VARCHAR(255) NULL, /*email address, or perhaps phone number or other contact address/target */
	`summary` VARCHAR(255) NULL, /* short version / description; used as subject for email messages */
	`body` TEXT NULL,
	`action_datetime` DATETIME NULL,
	`action_status` VARCHAR(16) NULL, /* SUCCESS|FAILURE */
	`action_notes` TEXT NULL, /* any more detailed messages/notes about the action */
	`flag_delete` BIT(1) NOT NULL DEFAULT 0,
	INDEX `user_id` (`user_id`),
	INDEX `sheet_id` (`sheet_id`),
	INDEX `opening_id` (`opening_id`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='holds messages in queue prior to independent sending mechanism';

