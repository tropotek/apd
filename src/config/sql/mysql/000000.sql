-- ---------------------------------
-- APD Install SQL
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------

-- TODO: see if this can be run here??
-- UPDATE dev_apd.institution t SET t.name = 'University of Melbourne Veterinary Anatomic Pathology', t.email = 'anat-vet@unimelb.edu.au' WHERE t.id = 1;


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
























