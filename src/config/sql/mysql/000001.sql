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


insert into mail_template_event (name, event, callback, description)  VALUES
('Case - Disposal Reminder', 'status.app.pathCase.disposalReminder', 'App\\Db\\PathCaseDecorator::onCreateMessages', 'Triggered when a case disposal reminder email is triggered')
;


TRUNCATE mail_template;
INSERT INTO mail_template (institution_id, mail_template_event_id, recipient_type, template, active, modified, created)
VALUES (1, 1, 'pathologist', '<p>Hi {recipient::name},</p>
<p>A new pathology case has been created:</p>
<ul>
<li>Pathology #: <a href="{pathCase::url}">{pathCase::pathologyId}</a></li>
<li>Institution ID: {pathCase::institutionId}</li>
<li>Client ID: {pathCase::clientId}</li>
<li>Type: {pathCase::type}</li>
<li>Submission Type: {pathCase::submissionType}</li>
<li>Status: {pathCase::status}</li>
<li>Animal Name: {pathCase::animalName}</li>
<li>Breed: {pathCase::breed}</li>
</ul>
<p>&nbsp;</p>
<p>STATUS</p>
<ul>
<li>Name: {status::name}</li>
<li>Event: {status::event}</li>
<li>Message: {status::message}</li>
</ul>
<p>&nbsp;</p>
<p>Thanks,</p>
<p>{institution::name}</p>', 1, NOW(), NOW());
INSERT INTO mail_template (institution_id, mail_template_event_id, recipient_type, template, active, modified, created)
VALUES (1, 11, 'pathologist', '<p>Hi {recipient::name},</p>
<p>A new pathology case is due for disposal on {pathCase::disposal}</p>
<ul>
<li>Pathology #: <a href="{pathCase::url}">{pathCase::pathologyId}</a></li>
<li>Institution ID: {pathCase::institutionId}</li>
<li>Client ID: {pathCase::clientId}</li>
<li>Type: {pathCase::type}</li>
<li>Submission Type: {pathCase::submissionType}</li>
<li>Status: {pathCase::status}</li>
<li>Animal Name: {pathCase::animalName}</li>
<li>Breed: {pathCase::breed}</li>
<li>Method Of Disposal: {pathCase::acType}</li>
<li>Disposal Date: {pathCase::disposal}</li>
</ul>
<p>&nbsp;</p>
<p>STATUS</p>
<ul>
<li>Name: {status::name}</li>
<li>Event: {status::event}</li>
<li>Message: {status::message}</li>
</ul>
<p>&nbsp;</p>
<p>Thanks,</p>
<p>{institution::name}</p>', 1, NOW(), NOW());




-- --------------------------------------------------------
-- table to track sent reminder emails to avoid duplicates
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `mail_sent` (
     `path_case_id` int(10) unsigned NOT NULL,
     `type` VARCHAR(128) NOT NULL DEFAULT '',
     `date` DATETIME DEFAULT NULL,
     PRIMARY KEY `path_case_id` (`path_case_id`, `type`)
) ENGINE=InnoDB;








