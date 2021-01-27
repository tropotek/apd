-- ---------------------------------
-- Version: 3.0.4
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


CREATE TABLE IF NOT EXISTS `invoice_item` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `path_case_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `code` VARCHAR(64) NOT NULL DEFAULT '',
    `description` TEXT,
    `qty` FLOAT UNSIGNED NOT NULL DEFAULT 1.0,
    `price` DECIMAL(9,2) DEFAULT 0.0,
    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY (code),
    KEY (path_case_id)
) ENGINE=InnoDB;








