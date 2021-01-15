-- ---------------------------------
-- Version: 3.0.2
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------

-- Update Contact table
alter table contact add name_company varchar(255) default '' not null after account_code;
alter table contact change name name_first varchar(255) default '' not null;
alter table contact add name_last varchar(255) default '' not null after name_first;

alter table path_case
    add submission_received TINYINT default 0 not null after submission_type;
alter table path_case
    add animal_type_id INT default 0 not null after animal_name;
alter table path_case modify breed varchar(128) default '' not null after species;



CREATE TABLE IF NOT EXISTS `animal_type` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `institution_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `parent_id` INT UNSIGNED NOT NULL DEFAULT 0,                -- Use this if they want to have animal types -> species as controllable dropdowns
    `name` VARCHAR(128) NOT NULL DEFAULT '',
    `description` TEXT,
    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY (name),
    KEY (institution_id),
    KEY (institution_id, parent_id)
) ENGINE=InnoDB;

TRUNCATE animal_type;
insert into animal_type (institution_id, name, modified, created) values (1, 'Dogs', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Cats', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Horses', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Cows', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Sheep', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Pigs', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Birds', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Pocket Pets', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Reptiles', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Fish', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Wildlife', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Other large animal', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Other exotic', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Goats', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Fish', NOW(), NOW());
insert into animal_type (institution_id, name, modified, created) values (1, 'Alpacas', NOW(), NOW());


alter table request change client_id test_id int unsigned default 0 not null;

CREATE TABLE IF NOT EXISTS `test` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `institution_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `name` VARCHAR(128) NOT NULL DEFAULT '',
    `description` TEXT,
    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY (name),
    KEY (institution_id)
) ENGINE=InnoDB;

insert into test (institution_id, name, modified, created) values (1, 'H&E', NOW(), NOW());

-- Disable request complete emails until I figure out how we can maybe make a daily email or something
UPDATE mail_template t SET t.active = 0 WHERE t.id = 4;

