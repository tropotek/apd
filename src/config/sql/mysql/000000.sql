-- ---------------------------------
-- APD Install SQL
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------




-- ----------------------------
--  address table
-- ----------------------------
# CREATE TABLE IF NOT EXISTS address
# (
#     id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
#     institution_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
#
#     street VARCHAR(255) NOT NULL DEFAULT '',
#     city VARCHAR(255) NOT NULL DEFAULT '',
#     country VARCHAR(255) NOT NULL DEFAULT '',
#     state VARCHAR(255) NOT NULL DEFAULT '',
#     postcode VARCHAR(255) NOT NULL DEFAULT '',
#     map_zoom DECIMAL(4, 2) NOT NULL DEFAULT 14,
#     map_lng DECIMAL(11, 8) NOT NULL DEFAULT 0,
#     map_lat DECIMAL(11, 8) NOT NULL DEFAULT 0,
#
#     modified DATETIME NOT NULL,
#     created DATETIME NOT NULL,
#     KEY institution_id (institution_id)
# ) ENGINE=InnoDB;

-- ----------------------------
--  company/client table
-- ----------------------------
CREATE TABLE IF NOT EXISTS client
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
    user_id INT(10) UNSIGNED NOT NULL DEFAULT 0,                -- use this if the client is a staff member
    uid VARCHAR(64) NOT NULL DEFAULT '',                        -- Farm Shed id
    account_code VARCHAR(64) NOT NULL DEFAULT '',                        -- Farm Shed id
    name VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL DEFAULT '',
    billing_email VARCHAR(255) NOT NULL DEFAULT '',             -- use email if blank
    phone VARCHAR(32) NOT NULL DEFAULT '',
    fax VARCHAR(32) NOT NULL DEFAULT '',

    street VARCHAR(255) NOT NULL DEFAULT '',
    city VARCHAR(255) NOT NULL DEFAULT '',
    country VARCHAR(255) NOT NULL DEFAULT '',
    state VARCHAR(255) NOT NULL DEFAULT '',
    postcode VARCHAR(255) NOT NULL DEFAULT '',

    use_address TINYINT(1) NOT NULL DEFAULT 1,                  -- use address as billing address
    b_street VARCHAR(255) NOT NULL DEFAULT '',
    b_city VARCHAR(255) NOT NULL DEFAULT '',
    b_country VARCHAR(255) NOT NULL DEFAULT '',
    b_state VARCHAR(255) NOT NULL DEFAULT '',
    b_postcode VARCHAR(255) NOT NULL DEFAULT '',

    NOTes TEXT,                                                 -- Staff only NOTes
    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY institution_id (institution_id),
    KEY user_id (user_id)
) ENGINE=InnoDB;

-- ----------------------------
--   case table
-- ----------------------------
CREATE TABLE IF NOT EXISTS `path_case`
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
    client_id INT(10) UNSIGNED NOT NULL DEFAULT 0,            -- Client/Clinician

    -- Case
    pathology_id VARCHAR(64) NOT NULL DEFAULT '',             -- Pathology Number
    type VARCHAR(64) NOT NULL DEFAULT '',                     -- BIOPSY, NECROPSY
    submission_type VARCHAR(64) NULL DEFAULT '',          -- direct client/external vet/INTernal vet/researcher/ Other - Specify
    status VARCHAR(64) NOT NULL DEFAULT '',                   -- Pending/frozen storage/examined/reported/awaiting review (if applicable)/completed

    -- TODO: These fields will be redundant when using the status log
    --       We should remove these fields...
    submitted DATETIME DEFAULT NULL,                          --
    examined DATETIME DEFAULT NULL,                           --
    finalised DATETIME DEFAULT NULL,                          --

    zootonic_disease VARCHAR(128) NOT NULL DEFAULT '',        -- A dropdown of entered diseases
    zootonic_result VARCHAR(128) NOT NULL DEFAULT '',         -- Positive/Negative ????
    --

    -- Animal/patient details
    specimen_count INT(10) UNSIGNED NOT NULL DEFAULT 1,       -- ??? TODO: NOT sure if this is needed
    animal_name VARCHAR(128) NOT NULL DEFAULT '',             --
    species VARCHAR(128) NOT NULL DEFAULT '',                 --
    gender VARCHAR(64) NOT NULL DEFAULT '',                   -- Male/Female
    desexed TINYINT(1) NOT NULL DEFAULT 0,                    --  ??? (should we use terms as spayed Female, gelding, steer, etc - gets to complex.)
    patient_number VARCHAR(128) NOT NULL DEFAULT '',          --
    microchip VARCHAR(128) NOT NULL DEFAULT '',               --
    owner_name VARCHAR(128) NOT NULL DEFAULT '',              --
    origin VARCHAR(128) NOT NULL DEFAULT '',                  -- ?? For now a TEXT box, but NOT sure if this should be lookup table
    breed VARCHAR(128) NOT NULL DEFAULT '',                   --
    vmis_weight VARCHAR(128) NOT NULL DEFAULT '',             --
    neco_weight VARCHAR(128) NOT NULL DEFAULT '',             --

    dob DATETIME DEFAULT NULL,                                -- Date of birth
    dod DATETIME DEFAULT NULL,                                -- Date and time of death

    euthanised TINYINT(1) NOT NULL DEFAULT 0,                 --
    euthanised_method VARCHAR(255) NULL DEFAULT '',       --
    ac_type VARCHAR(64) NULL DEFAULT '',                  -- after care type: General Disposal/cremation/INTernal incineration
    ac_hold DATETIME DEFAULT NULL,                            -- after care Date to wait until processing animal
    storage_id INT(10) UNSIGNED NOT NULL DEFAULT 0,           -- The current location of the animal (cleared when disposal is completed)
    disposal DATETIME DEFAULT NULL,
    --

    -- Reporting
    clinical_history TEXT,                                    --
    gross_pathology TEXT,                                     --
    gross_morphological_diagnosis TEXT,                         --
    histopathology TEXT,                                      --
    ancillary_testing TEXT,
    morphological_diagnosis TEXT,
    cause_of_death TEXT,                                      -- (required) case NOT saved if blank
    comments TEXT,                                            -- public comments
    --

    NOTes TEXT,                                               -- Staff only NOTes

    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY institution_id (institution_id),
    KEY client_id (client_id),
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
    uid VARCHAR(64) NOT NULL DEFAULT '',                      -- INTernal location id (room 130)
    name VARCHAR(255) NOT NULL DEFAULT '',                    -- general name of storage location ???
    -- Would be good to have the exact location for maps... Use selected address location as a DEFAULT
    map_zoom DECIMAL(4, 2) NOT NULL DEFAULT 14,
    map_lng DECIMAL(11, 8) NOT NULL DEFAULT 0,
    map_lat DECIMAL(11, 8) NOT NULL DEFAULT 0,
    NOTes TEXT,                                               -- Staff only NOTes
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
    price DECIMAL(9,2) NOT NULL DEFAULT 0.0,                  -- This should be a cost per service
    comments TEXT,                                            -- public comments
    NOTes TEXT,                                               -- Staff only NOTes

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
    price DECIMAL(9,2) NOT NULL DEFAULT 0.0,                  -- I assume this is price per sample ???
    comments TEXT,                                            -- public comments
    NOTes TEXT,                                               -- Staff only NOTes

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
    client_id INT(10) UNSIGNED NOT NULL DEFAULT 0,            -- The client requesting the samples (NOT sure if this could be staff, client, etc)
    status VARCHAR(64) NOT NULL DEFAULT '',                   -- ???
    qty INT(10) NOT NULL DEFAULT 0,                           -- Quantity of samples requested (check available tissue.qty on submit)
    price DECIMAL(9,2) NOT NULL DEFAULT 0.0,                  -- The total cost based on qty requested + the service cost
    comments TEXT,                                            -- public comments
    NOTes TEXT,                                               -- Staff only NOTes

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
    fKEY VARCHAR(64) DEFAULT '' NOT NULL,
    fid INT DEFAULT 0 NOT NULL,
    path TEXT NULL,
    bytes INT DEFAULT 0 NOT NULL,
    mime VARCHAR(255) DEFAULT '' NOT NULL,
    NOTes TEXT NULL,
    hash VARCHAR(128) DEFAULT '' NOT NULL,
    modified datetime NOT NULL,
    created datetime NOT NULL,
    KEY fKEY (fKEY),
    KEY fKEY_2 (fKEY, fid)
);



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


CREATE TABLE mail_template
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT UNSIGNED NOT NULL DEFAULT 0,
    event VARCHAR(64) NOT NULL DEFAULT '',              -- The mail template event name that triggers the sending of this template
    recipient_type VARCHAR(64) NOT NULL DEFAULT '',     -- Identify the recipient type of this template (staff, client, etc...)
    template TEXT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    modified datetime NOT NULL,
    created datetime NOT NULL,
    KEY institution_id (institution_id)
);

    
create table mail_template_event
(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64) DEFAULT '' NOT NULL,               -- The human readable name for this email event
    event VARCHAR(64) DEFAULT '' NOT NULL,              -- The event value that is sent when this event is triggered
    description TEXT NULL,
    email_tags  TEXT NULL,                              -- (Array) available tags that can be used in the template content {tag} = "Some dynamic value"
    CONSTRAINT `event` UNIQUE (`event`),
    KEY name (name)
);







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
