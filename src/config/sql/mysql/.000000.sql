-- ---------------------------------
-- APD Install SQL
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------

-- TODO: see if this can be run here??
-- UPDATE dev_apd.institution t SET t.name = 'University of Melbourne Veterinary Anatomic Pathology', t.email = 'anat-vet@unimelb.edu.au' WHERE t.id = 1;


-- ----------------------------
--  TEST DATA
-- ----------------------------
# INSERT INTO `user` (`role_id`, `institution_id`, `username`, `password` ,`name`, `email`, `active`, `hash`, `modified`, `created`)
# VALUES
#   (1, 0, 'admin', MD5(CONCAT('password', MD5('10admin'))), 'Administrator', 'admin@example.com', 1, MD5('10admin'), NOW(), NOW()),
#   (2, 0, 'unimelb', MD5(CONCAT('password', MD5('20unimelb'))), 'The University Of Melbourne', 'fvas@unimelb.edu.au', 1, MD5('20unimelb'), NOW(), NOW()),
#   (3, 1, 'staff', MD5(CONCAT('password', MD5('31staff'))), 'Unimelb Staff', 'staff@unimelb.edu.au', 1, MD5('31staff'), NOW(), NOW()),
#   (4, 1, 'student', MD5(CONCAT('password', MD5('41student'))), 'Unimelb Student', 'student@unimelb.edu.au', 1, MD5('41student'), NOW(), NOW())
# ;

# INSERT INTO `institution` (`user_id`, `name`, `email`, `description`, `logo`, `active`, `hash`, `modified`, `created`)
#   VALUES
#     (2, 'The University Of Melbourne', 'admin@unimelb.edu.au', 'This is a test institution for this app', '', 1, MD5('1'), NOW(), NOW())
# ;
#
# INSERT INTO `subject` (`institution_id`, `name`, `code`, `email`, `description`, `date_start`, `date_end`, `modified`, `created`)
#   VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'subject@unimelb.edu.au', '',  NOW(), DATE_ADD(NOW(), INTERVAL 190 DAY), NOW(), NOW() )
# --  VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'subject@unimelb.edu.au', '',  NOW(), DATE_ADD(CURRENT_DATETIME, INTERVAL 190 DAY), NOW(), NOW() )
# ;
#
# INSERT INTO `subject_has_user` (`user_id`, `subject_id`)
# VALUES
#   (3, 1),
#   (4, 1)
# ;
#
# INSERT INTO `subject_pre_enrollment` (`subject_id`, `email`)
# VALUES
#   (1, 'student@unimelb.edu.au')
# ;

-- ----------------------------
--  address table
-- ----------------------------
CREATE TABLE IF NOT EXISTS `address`
(
    `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `number` VARCHAR(255) NOT NULL DEFAULT '',
    `street` VARCHAR(255) NOT NULL DEFAULT '',
    `city` VARCHAR(255) NOT NULL DEFAULT '',
    `country` VARCHAR(255) NOT NULL DEFAULT '',
    `state` VARCHAR(255) NOT NULL DEFAULT '',
    `postcode` VARCHAR(255) NOT NULL DEFAULT '',
    `address` VARCHAR(255) NOT NULL DEFAULT '', -- google address string

    `map_zoom` DECIMAL(4, 2) NOT NULL DEFAULT 14,
    `map_lng` DECIMAL(11, 8) NOT NULL DEFAULT 0,
    `map_lat` DECIMAL(11, 8) NOT NULL DEFAULT 0,

    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL
) ENGINE=InnoDB;

-- ----------------------------
--  company/client table
-- ----------------------------
CREATE TABLE IF NOT EXISTS `company`
(
    `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `institution_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `email` VARCHAR(255) NOT NULL DEFAULT '',
    `billing_email` VARCHAR(255) NOT NULL DEFAULT '',           -- use email if blank
    `phone` VARCHAR(32) NOT NULL DEFAULT '',
    `fax` VARCHAR(32) NOT NULL DEFAULT '',
    `address_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `billing_address_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `notes` TEXT,
    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY `institution` (`institution_id`)
) ENGINE=InnoDB;

-- ----------------------------
--   case table
-- ----------------------------
CREATE TABLE IF NOT EXISTS `case`
(
    `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `institution_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `animal_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,

    `type` VARCHAR(64) NOT NULL DEFAULT '',                     -- BIOPSY, NECROPSY, ???
    `submission_type` VARCHAR(64) NOT NULL DEFAULT '',          -- direct client/external vet/internal vet/researcher/ Other - Specify
    `status` VARCHAR(64) NOT NULL DEFAULT '',                   -- Pending/frozen storage/examined/reported/awaiting review (if applicable)/completed

    `dod` DATETIME NOT NULL,                                    -- Date of death
    `euthanised` TINYINT(1) NOT NULL DEFAULT 0,
    `euthanised_method` VARCHAR(255) NOT NULL DEFAULT '',

    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY `institution` (`institution_id`)
) ENGINE=InnoDB;

-- ----------------------------
-- case_user table
--  Not sure if this is the best way to handle this.
-- ----------------------------
CREATE TABLE IF NOT EXISTS `case_user`
(
    `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `case_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,

    `type` VARCHAR(64) NOT NULL DEFAULT '',             -- Pathologist, student, Clinician, ????
    `` VARCHAR(64) NOT NULL DEFAULT '',
    `` VARCHAR(64) NOT NULL DEFAULT '',

    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY `case_user` (`case_id`, `user_id`)
) ENGINE=InnoDB;





-- ----------------------------
--  animal table
--  NOTE: Assuming that one animal can be involved in multiple cases
--   For example a biopsy at one point then a necropsy or multiples of each
-- ----------------------------
CREATE TABLE IF NOT EXISTS `animal`
(
    `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `institution_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,

    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY `institution` (`institution_id`)
) ENGINE=InnoDB;

-- ----------------------------
--   table
-- ----------------------------
CREATE TABLE IF NOT EXISTS ``
(
    `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `institution_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,

    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY `institution` (`institution_id`)
) ENGINE=InnoDB;

-- ----------------------------
--   table
-- ----------------------------
CREATE TABLE IF NOT EXISTS ``
(
    `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `institution_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,

    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY `institution` (`institution_id`)
) ENGINE=InnoDB;

-- ----------------------------
--   table
-- ----------------------------
CREATE TABLE IF NOT EXISTS ``
(
    `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `institution_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,

    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY `institution` (`institution_id`)
) ENGINE=InnoDB;
























