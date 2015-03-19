/* 
SAVE:
	DB Creation and Maintenance Script

PROJECT:
	Signup Sheets (lti-signup-sheets)

NOTES:
	For testing, create 'dblinktest' table by executing: "db_setup/testing_schema.sql"

FOR TESTING ONLY:
	USE lti_signup_sheets_test;

	DROP TABLE `terms`;
	DROP TABLE `users`;
	DROP TABLE `courses`;
	DROP TABLE `enrollments`;
	-- DROP TABLE `course_roles`;
	DROP TABLE `sus_sheetgroups`;
	DROP TABLE `sus_sheets`;
	DROP TABLE `sus_openings`;
	DROP TABLE `sus_signups`;
	DROP TABLE `sus_access`;

	DELETE FROM `terms`;
	DELETE FROM `users`;
	DELETE FROM `courses`;
	DELETE FROM `enrollments`;
	DELETE FROM `course_roles`;
	DELETE FROM `sus_sheetgroups`;
	DELETE FROM `sus_sheets`;
	DELETE FROM `sus_openings`;
	DELETE FROM `sus_signups`;
	DELETE FROM `sus_access`;

	Select * From `terms`;
	Select * From `users`;
	Select * From `courses`;
	Select * From `enrollments`;
	Select * From `course_roles`;
	Select * From `sus_sheetgroups`;
	Select * From `sus_sheets`;
	Select * From `sus_openings`;
	Select * From `sus_signups`;
	Select * From `sus_access`;
*/

# ----------------------------
# IMPORTANT: Select which database you wish to run this script against
# ----------------------------
CREATE SCHEMA IF NOT EXISTS `lti_signup_sheets_test`;
USE lti_signup_sheets_test;

-- CREATE USER 'usrname' IDENTIFIED BY 'usrpwd';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE lti_signup_sheets_test.* TO 'usrname';

-- CREATE SCHEMA IF NOT EXISTS `lti_signup_sheets`;
-- USE lti_signup_sheets;

# ----------------------------
# basic application infrastructure
# ----------------------------

CREATE TABLE IF NOT EXISTS `terms` (
    `term_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `term_idstr` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NULL,
    `start_date` TIMESTAMP,
    `end_date` TIMESTAMP,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';

CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `first_name` VARCHAR(255) NULL,
    `last_name` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL,
    `flag_is_system_admin` BIT(1) NOT NULL DEFAULT 0,
    `flag_is_banned` BIT(1) NOT NULL DEFAULT 0,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';
/* field 'username' corresponds to Canvas LMS field called 'login_id' */

CREATE TABLE IF NOT EXISTS `courses` (
    `course_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_idstr` VARCHAR(255) NOT NULL,
    `short_name` VARCHAR(255) NOT NULL,
    `long_name` VARCHAR(255) NOT NULL,
    `account_idstr` VARCHAR(255) NULL,
    `term_idstr` VARCHAR(255) NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';

CREATE TABLE IF NOT EXISTS `enrollments` (
    `enrollment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_idstr` VARCHAR(255) NOT NULL,
    `user_id` INT NOT NULL,
    `course_role_name` VARCHAR(255) NOT NULL,
    `section_idstr` VARCHAR(255) NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';

CREATE TABLE IF NOT EXISTS `course_roles` (
    `course_role_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `priority` INT NOT NULL,
    `course_role_name` VARCHAR(255) NOT NULL,
    `flag_delete` BIT(1) NOT NULL DEFAULT 0
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Sync with data sent from PS to Canvas';
/* priority: Highest teacher role is priority = 10; lowest alumni priority is > 30 */

CREATE TABLE IF NOT EXISTS `sus_sheetgroups` (
    `sheetgroup_id` bigint(10) unsigned NOT NULL auto_increment,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL,
    `flag_delete` tinyint(1) unsigned default NULL,
    `owner_user_id` bigint(10) unsigned default NULL,
    `flag_is_default` int(1) NOT NULL default '0',
    `name` varchar(255) default NULL,
    `description` text,
    `max_g_total_user_signups` smallint signed default -1,
    `max_g_pending_user_signups` smallint signed default -1,
    PRIMARY KEY (`sheetgroup_id`),
    KEY `flag_delete` (`flag_delete`),
    KEY `owner_user_id` (`owner_user_id`),
    KEY `flag_is_default` (`flag_is_default`),
    KEY `name` (`name`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='For managing collections of related sheets';

CREATE TABLE IF NOT EXISTS `sus_sheets` (
    `sheet_id` bigint(10) unsigned NOT NULL auto_increment,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL,
    `flag_delete` tinyint(1) unsigned default NULL,
    `owner_user_id` bigint(10) unsigned default NULL,
    `sheetgroup_id` bigint(10) unsigned default NULL,
    `name` varchar(255) default NULL,
    `description` text,
    `type` varchar(32) default NULL,
    `date_opens` TIMESTAMP NULL,
    `date_closes` TIMESTAMP NULL,
    `max_total_user_signups` smallint signed default -1,
    `max_pending_user_signups` smallint signed default -1,
    `flag_alert_owner_change` tinyint(1) unsigned default NULL,
    `flag_alert_owner_signup` tinyint(1) unsigned default NULL,
    `flag_alert_owner_imminent` tinyint(1) unsigned default NULL,
    `flag_alert_admin_change` tinyint(1) unsigned default NULL,
    `flag_alert_admin_signup` tinyint(1) unsigned default NULL,
    `flag_alert_admin_imminent` tinyint(1) unsigned default NULL,
    `flag_private_signups` int(1) default '1',
    PRIMARY KEY (`sheet_id`),
    KEY `flag_delete` (`flag_delete`),
    KEY `owner_user_id` (`owner_user_id`),
    KEY `sheetgroup_id` (`sheetgroup_id`),
    KEY `name` (`name`),
    KEY `type` (`type`),
    KEY `date_opens` (`date_opens`),
    KEY `date_closes` (`date_closes`),
    KEY `flag_alert_owner_change` (`flag_alert_owner_change`),
    KEY `flag_alert_owner_signup` (`flag_alert_owner_signup`),
    KEY `flag_alert_owner_imminent` (`flag_alert_owner_imminent`),
    KEY `flag_alert_admin_change` (`flag_alert_admin_change`),
    KEY `flag_alert_admin_signup` (`flag_alert_admin_signup`),
    KEY `flag_alert_admin_imminent` (`flag_alert_admin_imminent`),
    KEY `flag_private_signups` (`flag_private_signups`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Contains the high-level sheet data (name, descr, etc.)';

CREATE TABLE IF NOT EXISTS `sus_openings` (
    `opening_id` bigint(10) unsigned NOT NULL auto_increment,
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
    PRIMARY KEY (`opening_id`),
    KEY `flag_delete` (`flag_delete`),
    KEY `sheet_id` (`sheet_id`),
    KEY `opening_group_id` (`opening_group_id`),
    KEY `begin_datetime` (`begin_datetime`),
    KEY `end_datetime` (`end_datetime`),
    KEY `location` (`location`),
    KEY `name` (`name`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Places users can sign up - a single sheet may have multiple ';

CREATE TABLE IF NOT EXISTS `sus_signups` (
    `signup_id` bigint(10) unsigned NOT NULL auto_increment,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL,
    `flag_delete` tinyint(1) unsigned default NULL,
    `opening_id` bigint(10) unsigned default NULL,
    `signup_user_id` bigint(10) unsigned default NULL,
    `admin_comment` varchar(255) default NULL,
    PRIMARY KEY (`signup_id`),
    KEY `flag_delete` (`flag_delete`),
    KEY `opening_id` (`opening_id`),
    KEY `signup_user_id` (`signup_user_id`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='Users signing up for openings - analogous to a list of times and dates on a piece of paper that is passed around or posted on a door and on which people would put their name';

CREATE TABLE IF NOT EXISTS `sus_access` (
    `access_id` bigint(10) unsigned NOT NULL auto_increment,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL,
    `sheet_id` bigint(10) unsigned default NULL,
    `type` varchar(48) default NULL,
    `constraint_id` bigint(10) unsigned default NULL,
    `constraint_data` varchar(32) default NULL,
    `broadness` int(11) default NULL,
    PRIMARY KEY (`access_id`),
    KEY `sheet_id` (`sheet_id`),
    KEY `type` (`type`),
    KEY `constraint_id` (`constraint_id`),
    KEY `constraint_data` (`constraint_data`),
    KEY `broadness` (`broadness`)
)  ENGINE=innodb DEFAULT CHARACTER SET=utf8 COLLATE utf8_general_ci COMMENT='which users can signup on which sheets';


# ----------------------------
# Required: The Absolute Minimalist Approach to Initial Data Population
# ----------------------------

# Required constant values for 'course_roles' table
INSERT INTO
	course_roles
VALUES
	(1,10,'teacher',0),
	(2,20,'student',0),
	(3,30,'observer',0),
	(4,40,'alumni',0);

