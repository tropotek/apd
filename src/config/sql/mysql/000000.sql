-- ---------------------------------
-- APD Install SQL
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


-- ----------------------------
--  contact table
--  Animal Owner, Client / Student details
-- ----------------------------
CREATE TABLE IF NOT EXISTS contact
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
    user_id INT(10) UNSIGNED NOT NULL DEFAULT 0,                -- Use this if the contact has a login account on the system
    uid VARCHAR(64) NOT NULL DEFAULT '',                        -- Unused at this time, [Farm Shed id???]
    type VARCHAR(64) NOT NULL DEFAULT '',                       -- contact type [client/owner/student]
    account_code VARCHAR(64) NOT NULL DEFAULT '',               -- institution/business accounting code
    name VARCHAR(255) NOT NULL DEFAULT '',                      -- Client, Dep., Business name
    email VARCHAR(255) NOT NULL DEFAULT '',
    phone VARCHAR(32) NOT NULL DEFAULT '',
    fax VARCHAR(32) NOT NULL DEFAULT '',

    -- Optional
    street VARCHAR(255) NOT NULL DEFAULT '',
    city VARCHAR(255) NOT NULL DEFAULT '',
    country VARCHAR(255) NOT NULL DEFAULT '',
    state VARCHAR(255) NOT NULL DEFAULT '',
    postcode VARCHAR(255) NOT NULL DEFAULT '',

    notes TEXT,                                                 -- Staff only Notes
    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY institution_id (institution_id),
    KEY user_id (user_id)
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- contats linked to a case (Generally students)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `path_case_has_contact` (
     `path_case_id` int(10) unsigned NOT NULL,
     `contact_id` int(10) unsigned NOT NULL,
     PRIMARY KEY `path_case_id_contact_id` (`path_case_id`, `contact_id`)
) ENGINE=InnoDB;

-- ----------------------------
--   case table
-- ----------------------------
CREATE TABLE IF NOT EXISTS `path_case`
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
    user_id INT(10) UNSIGNED NOT NULL DEFAULT 0,              -- Cause author, staff who created the case record
    client_id INT(10) UNSIGNED NOT NULL DEFAULT 0,            -- The submitting contact_id[client] we are billing (auto populate owner_id  if 0 as they are usually the same)
    owner_id INT(10) UNSIGNED NOT NULL DEFAULT 0,             -- The animal owner contact_id[owner]
    pathologist_id INT(10) UNSIGNED NOT NULL DEFAULT 0,       -- Pathologist user_id from the user table

    resident VARCHAR(128) NOT NULL DEFAULT '',                -- Name of the resident Pathologist???

    -- Case
    pathology_id VARCHAR(64) NOT NULL DEFAULT '',             -- Pathology Number  (ie: title, name)
    name VARCHAR(255) DEFAULT '',                             -- The name/title of the case (optional)
    type VARCHAR(64) NOT NULL DEFAULT '',                     -- BIOPSY, NECROPSY, ...
    submission_type VARCHAR(64) NULL DEFAULT '',              -- Direct client/external vet/internal vet/researcher/ Other - Specify

    arrival DATETIME NOT NULL,                                -- Date the case arrived/seen (Generally the same as the created but editable)
    status VARCHAR(64) NOT NULL DEFAULT '',                   -- Pending/frozen storage/examined/reported/awaiting review (if applicable)/completed
    report_status VARCHAR(64) NOT NULL DEFAULT '',            -- The current status of the report.
    billable TINYINT(1) NOT NULL DEFAULT 0,                   -- Is this case billable
    account_status VARCHAR(64) DEFAULT '',                    -- The current status of the billing account [Pending, billed, paid]
    cost DECIMAL(9,2) DEFAULT 0.0,                            -- Money amount to invoice for External
    after_hours TINYINT(1) NOT NULL DEFAULT 0,                -- Was this case an after hours case
    -- TODO: Should we add a date and time for when the job was completed????

    zoonotic TEXT,                                            -- If filled show alert to warn user (use session cookie to only show once pre session)
    zoonotic_alert TINYINT(1) NOT NULL DEFAULT 0,             -- If true then alert user of this info when viewing the case

    issue TEXT,                                              -- Any issues the staff should be alerted to when dealing with this animal
    issue_alert TINYINT(1) NOT NULL DEFAULT 0,               -- If true then alert user of this info when viewing the case

    -- Animal/patient details
    -- TODO: in the future create an animal table
    specimen_count INT(10) UNSIGNED NOT NULL DEFAULT 1,       -- ??? TODO: NOT sure if this is needed
    animal_name VARCHAR(128) NOT NULL DEFAULT '',             --
    species VARCHAR(128) NOT NULL DEFAULT '',                 --
    sex VARCHAR(3) NOT NULL DEFAULT '',                       -- M/F
    desexed TINYINT(1) NOT NULL DEFAULT 0,                    --  ??? (should we use terms as spayed Female, gelding, steer, etc - gets to complex.)
    patient_number VARCHAR(128) NOT NULL DEFAULT '',          --
    microchip VARCHAR(128) NOT NULL DEFAULT '',               --
    origin VARCHAR(128) NOT NULL DEFAULT '',                  -- ?? For now a TEXT box, but NOT sure if this should be lookup table
    breed VARCHAR(128) NOT NULL DEFAULT '',                   --
    colour VARCHAR(128) NOT NULL DEFAULT '',                  --
    weight VARCHAR(128) NOT NULL DEFAULT '',                  --

    dob DATETIME DEFAULT NULL,                                -- Date of birth
    dod DATETIME DEFAULT NULL,                                -- Date and time of death

    euthanised TINYINT(1) NOT NULL DEFAULT 0,                 --
    euthanised_method VARCHAR(255) NULL DEFAULT '',           --
    ac_type VARCHAR(64) NULL DEFAULT '',                      -- after care type: General Disposal/cremation/internal incineration
    ac_hold DATETIME DEFAULT NULL,                            -- after care Date to wait until processing animal
    storage_id INT(10) UNSIGNED NOT NULL DEFAULT 0,           -- The current location of the animal (cleared when disposal is completed)
    disposal DATETIME DEFAULT NULL,                           -- The date the animal was disposed of
    --

    -- Reporting
    student_report TINYINT(1) NOT NULL DEFAULT 0,             -- Is the report written by a student
    collected_samples TEXT,                                   -- Save Tissues/Frozen Samples
    clinical_history TEXT,                                    --
    gross_pathology TEXT,                                     --
    gross_morphological_diagnosis TEXT,                       --
    histopathology TEXT,                                      --
    ancillary_testing TEXT,                                   --
    morphological_diagnosis TEXT,                             --
    cause_of_death TEXT,                                      -- (required) case NOT saved if blank
    comments TEXT,                                            -- public comments
    addendum TEXT,                                            -- Additional notes after reporting has taken place
    --

    notes TEXT,                                               -- Staff only notes

    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY institution_id (institution_id),
    KEY client_id (client_id),
    KEY user_id (user_id),
    KEY pathology_id (pathology_id),
    KEY storage_id (storage_id),
    KEY type (type),
    KEY submission_type (submission_type),
    KEY `status` (status),
    KEY ac_type (ac_type)

) ENGINE=InnoDB;



-- ----------------------------
--  storage table
-- ----------------------------
CREATE TABLE IF NOT EXISTS storage
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
    uid VARCHAR(64) NOT NULL DEFAULT '',                      -- internal location id (room 130)
    name VARCHAR(255) NOT NULL DEFAULT '',                    -- general name of storage location ???
    -- Would be good to have the exact location for maps... Use selected address location as a DEFAULT
    map_zoom DECIMAL(4, 2) NOT NULL DEFAULT 14,
    map_lng DECIMAL(11, 8) NOT NULL DEFAULT 0,
    map_lat DECIMAL(11, 8) NOT NULL DEFAULT 0,
    notes TEXT,                                               -- Staff only notes
    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY institution_id (institution_id)
) ENGINE=InnoDB;

-- ----------------------------
--  service table
-- ----------------------------
CREATE TABLE IF NOT EXISTS service
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT(10) UNSIGNED NOT NULL DEFAULT 0,

    name VARCHAR(64) NOT NULL DEFAULT '',
    cost DECIMAL(9,2) NOT NULL DEFAULT 0.0,                   -- This should be a cost per service
    comments TEXT,                                            -- public comments
    notes TEXT,                                               -- Staff only notes

    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY institution_id (institution_id)
) ENGINE=InnoDB;

-- ----------------------------
--  cassette table
--  A cassette contains a qty of tissue slides/samples
-- ----------------------------
CREATE TABLE IF NOT EXISTS cassette
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    path_case_id INT(10) UNSIGNED NOT NULL DEFAULT 0,         --
    storage_id INT(10) UNSIGNED NOT NULL DEFAULT 0,           -- Storage location if available

    container VARCHAR(64) NOT NULL DEFAULT '',                -- Not sure if we will be using this or storage_id or both???
    number VARCHAR(64) NOT NULL DEFAULT '',                   -- Generally just increments by 1 for each group in a case
    name VARCHAR(64) NOT NULL DEFAULT '',                     -- Usually the tissue type name
    qty INT(10) NOT NULL DEFAULT 0,                           -- Quantity of samples available
    cost DECIMAL(9,2) NOT NULL DEFAULT 0.0,                   -- I assume this is price per sample ???
    comments TEXT,                                            -- public comments
    notes TEXT,                                               -- Staff only notes

    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY path_case_id (path_case_id),
    KEY storage_id (storage_id)
) ENGINE=InnoDB;

-- ----------------------------
--  request table
--  Pathology samples request that are handled by the lab
-- ----------------------------
CREATE TABLE IF NOT EXISTS request
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    path_case_id INT(10) UNSIGNED NOT NULL DEFAULT 0,         --
    cassette_id INT(10) UNSIGNED NOT NULL DEFAULT 0,          --
    service_id INT(10) UNSIGNED NOT NULL DEFAULT 0,           --
    client_id INT(10) UNSIGNED NOT NULL DEFAULT 0,            -- The contact_id[client] requesting the samples (NOT sure if this could be staff, client, etc)
    status VARCHAR(64) NOT NULL DEFAULT '',                   -- ???
    qty INT(10) NOT NULL DEFAULT 0,                           -- Quantity of samples requested (check available tissue.qty on submit)
    cost DECIMAL(9,2) NOT NULL DEFAULT 0.0,                   -- The total cost based on qty requested + the service cost
    comments TEXT,                                            -- public comments
    notes TEXT,                                               -- Staff only notes

    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY path_case_id (path_case_id),
    KEY cassette_id (cassette_id),
    KEY service_id (service_id),
    KEY client_id (client_id)
) ENGINE=InnoDB;


-- ----------------------------
--  file table
--  Store all case files and media here
-- ----------------------------
CREATE TABLE IF NOT EXISTS file
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
    fkey VARCHAR(64) DEFAULT '' NOT NULL,
    fid INT DEFAULT 0 NOT NULL,
    path TEXT NULL,
    bytes INT DEFAULT 0 NOT NULL,
    mime VARCHAR(255) DEFAULT '' NOT NULL,
    notes TEXT NULL,
    hash VARCHAR(128) DEFAULT '' NOT NULL,
    modified datetime NOT NULL,
    created datetime NOT NULL,
    KEY user_id (user_id),
    KEY fkey (fkey),
    KEY fkey_2 (fkey, fid)
);


CREATE TABLE mail_template
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT UNSIGNED NOT NULL DEFAULT 0,
    mail_template_event_id INT UNSIGNED NOT NULL DEFAULT 0,     -- mail_template_event.id
    recipient_type VARCHAR(64) NOT NULL DEFAULT '',             -- Identify the recipient type of this template (staff, client, etc...)
    template TEXT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY institution_id (institution_id),
    KEY mail_template_event_id (mail_template_event_id)
);
create table mail_template_event
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64) DEFAULT '' NOT NULL,                       -- The human readable name for this email event
    event VARCHAR(64) DEFAULT '' NOT NULL,                      -- The event value that links the Status.event triggered to this mail Event status
    callback VARCHAR(128) DEFAULT '' NOT NULL,                  -- This is the callable function in the format of "MyNameSpc\MyClass::myCallbackMethod"
                                                                --      This callback method is used to render the mail message template
    description TEXT NULL,
    tags  TEXT NULL,                                            -- (Array) available tags that can be used in the template content {tag} = "Some dynamic value"
    CONSTRAINT `event` UNIQUE (`event`),
    KEY name (name)
);

TRUNCATE mail_template_event;
insert into mail_template_event (name, event, callback, description)  VALUES
('Case - Status Change - Pending', 'status.app.pathCase.pending', 'App\\Db\\PathCaseDecorator::onCreateMessages', 'Triggered when a case status is set to Pending'),
('Case - Status Change - Hold', 'status.app.pathCase.hold', 'App\\Db\\PathCaseDecorator::onCreateMessages', 'Triggered when a case status is set to Hold'),
('Case - Status Change - Frozen Storage', 'status.app.pathCase.frozenStorage', 'App\\Db\\PathCaseDecorator::onCreateMessages', 'Triggered when a case status is set to Frozen Storage'),
('Case - Status Change - Examined', 'status.app.pathCase.examined', 'App\\Db\\PathCaseDecorator::onCreateMessages', 'Triggered when a case status is set to Examined'),
('Case - Status Change - Reported', 'status.app.pathCase.reported', 'App\\Db\\PathCaseDecorator::onCreateMessages', 'Triggered when a case status is set to Reported'),
('Case - Status Change - Completed', 'status.app.pathCase.completed', 'App\\Db\\PathCaseDecorator::onCreateMessages', 'Triggered when a case status is set to Completed'),
('Case - Status Change - Cancelled', 'status.app.pathCase.cancelled', 'App\\Db\\PathCaseDecorator::onCreateMessages', 'Triggered when a case status is set to Cancelled'),
('Request - Status Change - Pending', 'status.app.request.pending', 'App\\Db\\RequestDecorator::onCreateMessages', 'Triggered when a request status is set to Pending'),
('Request - Status Change - Completed', 'status.app.request.completed', 'App\\Db\\RequestDecorator::onCreateMessages', 'Triggered when a request status is set to Completed'),
('Request - Status Change - Cancelled', 'status.app.request.cancelled', 'App\\Db\\RequestDecorator::onCreateMessages', 'Triggered when a request status is set to Cancelled')
;

CREATE TABLE IF NOT EXISTS `status` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL DEFAULT 0,              -- The user who performed the activity
    `msq_user_id` INT UNSIGNED NOT NULL DEFAULT 0,          -- If the user was masquerading who was the root masquerading user
    `course_id` INTEGER NOT NULL DEFAULT 0,                 -- Ignored in APD
    `subject_id` INTEGER NOT NULL DEFAULT 0,                -- Ignored in APD
    `fkey` VARCHAR(64) NOT NULL DEFAULT '',                 -- A foreign key as a string (usually the object name)
    `fid` INTEGER NOT NULL DEFAULT 0,                       -- foreign_id
    `name` VARCHAR(32) NOT NULL DEFAULT '',                 -- pending|approved|not_approved
    `event` VARCHAR(128) NOT NULL DEFAULT '',               -- the name of the event triggered if any (link: mail_template_event.event)
    `notify` BOOL NOT NULL DEFAULT 1,                       -- Was the message email sent
    `message` TEXT,                                         -- A status update log message
    `serial_data` TEXT,                                     -- json/serialized data of any related objects pertaining to this activity
    `del` BOOL NOT NULL DEFAULT 0,                          -- This value should mirror its model `del` value
    `created` DATETIME NOT NULL,
    KEY (`user_id`),
    KEY (`msq_user_id`),
    KEY (`course_id`),
    KEY (`subject_id`),
    KEY (`fid`),
    KEY (`fkey`),
    KEY (`fid`, `id`)
) ENGINE = InnoDB;


-- --------------------------------------------------
--
--
--
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS `note` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INTEGER NOT NULL DEFAULT 0,                       -- (optional) Author: If 0 then this is assumed to be a system generated note
    `fkey` VARCHAR(64) NOT NULL DEFAULT '',                     -- Foreign object key  EG: '\App\Db\Company', '\App\Db\Placement', etc
    `fid` INTEGER NOT NULL DEFAULT 0,                           -- foreign id  EG: '2'
    `message` TEXT,                                             -- The HTML Text to be shown in the message?
    `data` TEXT,                                                -- (optional) JSON data object for internal processes
    `del` TINYINT(1) NOT NULL DEFAULT 0,
    `modified` DATETIME NOT NULL,
    `created` DATETIME NOT NULL,
    KEY (user_id),
    KEY (fkey, fid)
) ENGINE=InnoDB;


/*
-- TODO:
--   Teaching specimens (NOT critical but would be good to incorporate):
--       Teaching specimens collected – YES/NO. Specimen. Teaching tub. Transferred to tub – YES/NO
--   This allows us to track material collected during necropsy which will be used in other teaching classes
-- ----------------------------
--  specimen table
-- ----------------------------
CREATE TABLE IF NOT EXISTS specimen
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id INT(10) UNSIGNED NOT NULL DEFAULT 0,

    name VARCHAR(64) NOT NULL DEFAULT '',
    tub_id VARCHAR(64) NOT NULL DEFAULT '',
    status VARCHAR(64) NOT NULL DEFAULT '',       -- pending/complete

    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY case (case_id)
) ENGINE=InnoDB;
*/

/*
-- ----------------------------
--   table
-- ----------------------------
CREATE TABLE IF NOT EXISTS 
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT(10) UNSIGNED NOT NULL DEFAULT 0,

    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY institution (institution_id)
) ENGINE=InnoDB;
*/

-- FOR TEMPLATE TESTING (REMOVE FOR LIVE INSTALL)

TRUNCATE mail_template;
INSERT INTO dev_apd.mail_template (id, institution_id, mail_template_event_id, recipient_type, template, active, modified, created) VALUES (1, 1, 1, 'client', '<p>Hi {recipient::name},</p>
<p>Pathology Case:</p>
<ul>
<li>Pathology #: {pathCase::pathologyId}</li>
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
INSERT INTO dev_apd.mail_template (id, institution_id, mail_template_event_id, recipient_type, template, active, modified, created) VALUES (2, 1, 2, 'client', '<p>Hi {recipient::name},</p>
<p>Case status is ON HOLD</p>
<p>Pathology Case:</p>
<ul>
<li>Pathology #: {pathCase::pathologyId}</li>
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
INSERT INTO dev_apd.mail_template (id, institution_id, mail_template_event_id, recipient_type, template, active, modified, created) VALUES (3, 1, 3, 'client', '<p>Hi {recipient::name},</p>
<p>Case status is FROZEN STORAGE</p>
<p>Pathology Case:</p>
<ul>
<li>Pathology #: {pathCase::pathologyId}</li>
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
INSERT INTO dev_apd.mail_template (id, institution_id, mail_template_event_id, recipient_type, template, active, modified, created) VALUES (4, 1, 4, 'staff', '<p>Hi {recipient::name},</p>
<p>Case status is APPROVED</p>
<p>Pathology Case:</p>
<ul>
<li>Pathology #: {pathCase::pathologyId}</li>
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
INSERT INTO dev_apd.mail_template (id, institution_id, mail_template_event_id, recipient_type, template, active, modified, created) VALUES (5, 1, 5, 'staff', '<p>Hi {recipient::name},</p>
<p>Case status is REPORTED</p>
<p>Pathology Case:</p>
<ul>
<li>Pathology #: {pathCase::pathologyId}</li>
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
INSERT INTO dev_apd.mail_template (id, institution_id, mail_template_event_id, recipient_type, template, active, modified, created) VALUES (6, 1, 8, 'client', '<p>Hi {recipient::name},</p>
<p>Request status is PENDING</p>
<p>Pathology Case:</p>
<ul>
<li>Pathology Case ID: {request::pathCaseId}</li>
<li>Cassette ID: {request::cassetteId}</li>
<li>Service ID: {request::serviceId}</li>
<li>Client ID: {request::clientId}</li>
<li>Qty: {request::qty}</li>
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
INSERT INTO dev_apd.mail_template (id, institution_id, mail_template_event_id, recipient_type, template, active, modified, created) VALUES (7, 1, 9, 'client', '<p>Hi {recipient::name},</p>
<p>Request status is PROCESSING</p>
<p>Pathology Case:</p>
<ul>
<li>Pathology Case ID: {request::pathCaseId}</li>
<li>Cassette ID: {request::cassetteId}</li>
<li>Service ID: {request::serviceId}</li>
<li>Client ID: {request::clientId}</li>
<li>Qty: {request::qty}</li>
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
INSERT INTO dev_apd.mail_template (id, institution_id, mail_template_event_id, recipient_type, template, active, modified, created) VALUES (8, 1, 10, 'staff', '<p>Hi {recipient::name},</p>
<p>Request status is COMPLETED</p>
<p>Pathology Case:</p>
<ul>
<li>Pathology Case ID: {request::pathCaseId}</li>
<li>Cassette ID: {request::cassetteId}</li>
<li>Service ID: {request::serviceId}</li>
<li>Client ID: {request::clientId}</li>
<li>Qty: {request::qty}</li>
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





