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


CREATE TABLE IF NOT EXISTS `notice` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INTEGER NOT NULL DEFAULT 0,                     -- (optional) Author: 0 = system generated notice
    `fkey` VARCHAR(64) NOT NULL DEFAULT '',                   -- A foreign key  '\App\Db\Company', '\App\Db\Placement', etc
    `fid` INTEGER NOT NULL DEFAULT 0,                         -- foreign_id     '2'
    `type` VARCHAR(128) NOT NULL DEFAULT '',                  -- Can be used for different messages from the same fkey/id pair
    `subject` TEXT,                                           -- A short subject to be displayed in the notification menu
    `body` TEXT,                                              -- The HTML/Text to be shown in the message? if needed?
    `data` TEXT,                                              -- JSON data object for internal processes (params)
    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY (user_id),
    KEY (fkey),
    KEY (fkey, fid),
    KEY (fkey, fid, type)
) ENGINE=InnoDB;

create table notice_recipient (
    id           int unsigned auto_increment primary key,
    notice_id    int unsigned default 0 not null,
    user_id      int unsigned default 0 not null,            -- Recipient user
    alert        TINYINT(1) NOT NULL DEFAULT 0,              -- Mark alert when the system has alerted the user to the new notification
    viewed       datetime                   null,            -- Mark viewed when the user clicks on the notice
    `read`       datetime                   null,            -- Mark viewed when the user clicks on the notice
    created      datetime               not null,
    constraint notice_recipient_key unique (notice_id, user_id)
) ENGINE=InnoDB;


-- Add PDF attachment functionality to the files
alter table file add label VARCHAR(128) default '' not null after mime;      -- Label text for the PDF image
alter table file add active TINYINT(1) default 0 not null after label;       -- Is attached to the PDF