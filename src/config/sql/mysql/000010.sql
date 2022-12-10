-- ---------------------------------
-- Version: 3.4.10
--
-- Author: Michael Mifsud <info@tropotek.com>
--
-- This is a cleanup of the DB for initial release to
--    the tropotek
-- ---------------------------------


-- Cleanup DB tables
DROP TABLE IF EXISTS _user_role;
DROP TABLE IF EXISTS _user_role_id;
DROP TABLE IF EXISTS _user_role_institution;
DROP TABLE IF EXISTS _user_role_permission;

-- --------------------------------------
-- Use this SQL for migration into new tropotek server
-- --------------------------------------
UPDATE `user` SET `password` = '' WHERE id > 2;

DELETE FROM `_plugin_zone` WHERE `plugin_name` LIKE 'plg-ldap' AND `zone_name` LIKE 'institution';
DELETE FROM `_plugin_zone` WHERE `plugin_name` LIKE 'plg-lti' AND `zone_name` LIKE 'institution';

DELETE FROM _data WHERE fkey = 'plg-ldap.institution';
DELETE FROM _data WHERE fkey = 'plg-lti';

INSERT INTO _data (fid, fkey, `key`, value) VALUES (1, 'Uni\\Db\\Institution', 'inst.microsoftLogin', 'inst.microsoftLogin');

UPDATE institution SET domain = 'apd-unimelb.com' WHERE id = 1;




CREATE TABLE IF NOT EXISTS `product` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `institution_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `name` VARCHAR(450) NOT NULL DEFAULT '',
    `code` VARCHAR(255) NOT NULL DEFAULT '',
    `price` INT NOT NULL DEFAULT 0,
    `description` TEXT,
    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY (institution_id)
) ENGINE=InnoDB;

TRUNCATE product;
INSERT INTO product (institution_id, code, name, price, modified, created) VALUES
    (1, '', 'Necropsy fee inc GST 0-2 Kg', 42500, NOW(), NOW()),
    (1, '', 'Necropsy fee inc GST 2-10 Kg', 79000, NOW(), NOW()),
    (1, '', 'Necropsy fee inc GST 11-100 Kg', 88000, NOW(), NOW()),
    (1, '', 'Necropsy fee inc GST over 100 Kg', 154300, NOW(), NOW()),
    (1, '', 'Biopsy 1-3 Tissues inc GST', 19100, NOW(), NOW()),
    (1, '', 'Biopsy > 3Tissues inc GST', 29600, NOW(), NOW()),
    (1, '', 'H&E stain each side inc GST', 1550, NOW(), NOW()),
    (1, '', 'H&E stain additional side inc GST', 850, NOW(), NOW()),
    (1, '', 'Immunohistochemistry inc GST', 15600, NOW(), NOW()),
    (1, '', 'Immunocytochemistry inc GST', 15600, NOW(), NOW()),
    (1, '', 'After hours fee (double the necropsy fee)', 0, NOW(), NOW()),
    (1, '', 'Other test', 0, NOW(), NOW()),
    (1, '', 'External lab send out fee', 6000, NOW(), NOW()),

    (1, '', 'Microbiology one sample inc GST', 0, NOW(), NOW()),
    (1, '', 'Microbiology 2-5 extra sample inc GST', 0, NOW(), NOW()),
    (1, '', 'Microbiology 5+ extra sample inc GST', 0, NOW(), NOW()),
    (1, '', 'Parasitology Faecal egg count single <10 sampleainc GST', 0, NOW(), NOW()),
    (1, '', 'Parasite identification inc GST', 0, NOW(), NOW()),
    (1, '', 'Worm Count Total inc GST', 0, NOW(), NOW()),
    (1, '', 'Faecal smear inc GST', 0, NOW(), NOW()),
    (1, '', 'Faecal floatatiom inc GST', 0, NOW(), NOW()),
    (1, '', 'Giardia SNAP test inc GST', 0, NOW(), NOW()),
    (1, '', 'Agrifood Rodenticide screen inc GST', 0, NOW(), NOW()),
    (1, '', 'APCAH molecular biology', 0, NOW(), NOW()),
    (1, '', 'Other test', 0, NOW(), NOW())
;









