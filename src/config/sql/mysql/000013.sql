-- ---------------------------------
-- Version: 3.4.98
-- ---------------------------------


-- function to set all words to uppercase
DROP FUNCTION IF EXISTS ucwords;
CREATE FUNCTION ucwords(s VARCHAR(255)) RETURNS VARCHAR(255)
BEGIN
  declare c int;
  declare x VARCHAR(255);
  declare y VARCHAR(255);
  declare z VARCHAR(255);

  set x = UPPER( SUBSTRING( s, 1, 1));
  set y = SUBSTR( s, 2);
  set c = instr( y, ' ');

  while c > 0
    do
      set z = SUBSTR( y, 1, c);
      set x = CONCAT( x, z);
      set z = UPPER( SUBSTR( y, c+1, 1));
      set x = CONCAT( x, z);
      set y = SUBSTR( y, c+2);
      set c = INSTR( y, ' ');
  end while;
  set x = CONCAT(x, y);
  return x;
END;


-- Add a setting to disable mentor lists
INSERT IGNORE INTO _data (fid, fkey, `key`, value) VALUES (0,'system', 'site.mentors.enabled', '');
INSERT IGNORE INTO _data (fid, fkey, `key`, value) VALUES (0,'system', 'site.courses.enabled', '');

-- update owner_name field for path cases
UPDATE path_case pc
    LEFT JOIN contact c ON (pc.owner_Id = c.id)
SET pc.owner_name = TRIM(CONCAT_WS(' ', c.name_company, c.name_first, c.name_last))
WHERE pc.owner_id != 0 AND pc.owner_name = '';


-- ----------------------------
--  student table
--  Copy students from contact table to student table
-- ----------------------------
CREATE TABLE IF NOT EXISTS student
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL DEFAULT '',
    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    CONSTRAINT fk_student__institution_id FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

TRUNCATE student;
INSERT INTO student (id, institution_id, name, email, del, modified, created)
    SELECT id, institution_id, replace(TRIM(CONCAT_WS(' ', name_first, name_last)), UNHEX('C2A0'),'') AS name, email ,del, modified, created
    FROM contact
    WHERE type = 'student'
;

CREATE TABLE IF NOT EXISTS path_case_has_student (
    path_case_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    PRIMARY KEY path_case_id__student_id (path_case_id, student_id),
    CONSTRAINT fk_path_case_has_student__path_case_id FOREIGN KEY (path_case_id) REFERENCES path_case (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_path_case_has_student__student_id FOREIGN KEY (student_id) REFERENCES student (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

TRUNCATE path_case_has_student;
INSERT INTO path_case_has_student (path_case_id, student_id)
    SELECT hc.path_case_id, hc.contact_id
    FROM path_case_has_contact hc
    LEFT JOIN contact c ON (c.id = hc.contact_id AND c.type = 'student')
    WHERE c.id IS NOT NULL
;

-- Delete all students with 'None'
DELETE FROM student WHERE id IN (164, 180);

-- Fix duplicate student record
UPDATE path_case_has_student SET student_id = 57 WHERE path_case_id = 32;
DELETE FROM student WHERE id = 59;

UPDATE student SET name = 'usaliha', email = 'usaliha@student.unimelb.edu.au' WHERE id = 318;

-- ----------------------------
--  client table
-- ----------------------------

CREATE TABLE IF NOT EXISTS company
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
    contact_id VARCHAR(1000) NOT NULL DEFAULT '',         -- temp param to migrate cases when ready
    account_code VARCHAR(64) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL DEFAULT '',
    phone VARCHAR(32) NOT NULL DEFAULT '',
    fax VARCHAR(32) NOT NULL DEFAULT '',
    street VARCHAR(255) NOT NULL DEFAULT '',
    city VARCHAR(255) NOT NULL DEFAULT '',
    country VARCHAR(255) NOT NULL DEFAULT '',
    state VARCHAR(255) NOT NULL DEFAULT '',
    postcode VARCHAR(255) NOT NULL DEFAULT '',
    notes TEXT,
    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    CONSTRAINT fk_company__institution_id FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS company_contact
(
    id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT(10) UNSIGNED NOT NULL DEFAULT 0,
    contact_id INT(10) UNSIGNED NOT NULL DEFAULT 0,         -- temp param to migrate cases when ready
    name VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL DEFAULT '',
    phone VARCHAR(32) NOT NULL DEFAULT '',
    del TINYINT(1) NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    CONSTRAINT fk_company_contact__company_id FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Add company ID to case table
ALTER TABLE path_case ADD company_id INT UNSIGNED NULL AFTER client_id;
ALTER TABLE path_case ADD
    CONSTRAINT fk_path_case__company_id FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE RESTRICT ON UPDATE CASCADE;

UPDATE contact SET name_company = TRIM(REPLACE(name_company, UNHEX('C2A0'),'')) WHERE 1;
UPDATE contact SET notes = TRIM(REPLACE(notes, UNHEX('C2A0'),'')) WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, ' and ', ' & ') WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, ' And ', ' & ') WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, ' Pty Ltd', '') WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, ' Pty Lyd', '') WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, ' P/L', '') WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, 'U-Vet" ', 'U-Vet') WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, 'U-vet: ', 'U-Vet') WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, 'U-vet: ', 'U-Vet') WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, 'UVET', 'U-Vet') WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, 'UVet', 'U-Vet') WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, 'Uvet', 'U-Vet') WHERE 1;
UPDATE contact SET name_company = REPLACE(name_company, 'U Vet', 'U-Vet') WHERE 1;
UPDATE contact SET name_company = 'APCAH' WHERE id = 654;
UPDATE contact SET name_company = 'APCAH' WHERE id = 611;
UPDATE contact SET name_company = 'APCAH' WHERE id = 129;
UPDATE contact SET name_company = REPLACE(name_company, 'APCAH-', 'APCAH') WHERE id = 753;
UPDATE contact SET name_company = REPLACE(name_company, 'Amir- BLS project PHA', 'Amir - BLS project PHA') WHERE id = 1975;
UPDATE contact SET name_company = 'Advanced VetCare' WHERE id = 1913;
UPDATE contact SET name_company = 'Advantage Equine' WHERE id = 1716;
UPDATE contact SET name_company = 'Amir - Teaching' WHERE id = 922;
UPDATE contact SET name_first = 'Amir', name_last = 'Hadjinoormohammadi' WHERE id IN (1580);
UPDATE contact SET name_first = 'Amir', name_last = 'Hadjinoormohammadi' WHERE id IN (922);
UPDATE contact SET name_company = 'Avenel Equine Hospital' WHERE id = 1908;
UPDATE contact SET name_company = 'Boronia Veterinary Clinic' WHERE id = 892;
UPDATE contact SET name_company = 'Bundoora Vet Clinic/Hospital' WHERE id IN (1751,1785,1812,1813,1814,1947);
UPDATE contact SET name_company = 'CSIRO' WHERE id IN (757, 1581,196);
UPDATE contact SET name_company = 'Centre for Animal Referral & Emergency' WHERE id IN (1922);
UPDATE contact SET name_company = 'Crown Equine' WHERE id IN (683);
UPDATE contact SET name_company = 'DELWP', name_first = 'Marine Mammal Submission' WHERE id IN (825);
UPDATE contact SET name_company = 'Department of Environment Land, Water and Planning' WHERE id IN (771);
UPDATE contact SET name_company = 'Direct vets', name_first = 'Stray' WHERE id IN (1176);
UPDATE contact SET name_company = 'Greencross Vet Hospital' WHERE id IN (1690, 1929);
UPDATE contact SET name_company = 'Greencross Vet Hospital', name_first = 'Point Cook', name_last = 'J. Lynch' WHERE id IN (480);
UPDATE contact SET name_company = 'Greencross Vet Hospital', name_first = 'Werribee', name_last = '' WHERE id IN (1883);
UPDATE contact SET name_company = 'Greencross Vet Hospital', name_first = 'Kilsyth', name_last = 'Dr Georgia Hockins' WHERE id IN (1990);
UPDATE contact SET name_company = 'Greencross Vet Hospital', name_first = 'Glen Eira', name_last = 'Nicholas Garrett' WHERE id IN (1954);
UPDATE contact SET name_company = 'Greencross Vet Hospital', name_first = 'White Hills', name_last = 'Dr Jack Lans' WHERE id IN (1956);
UPDATE contact SET name_company = 'Greencross Vet Hospital', name_first = 'Werribee', name_last = 'R. Lui' WHERE id IN (139);
UPDATE contact SET name_company = 'Mackinnon Project' WHERE id IN (942, 338);
UPDATE contact SET name_company = 'Parks Victoria', name_first = 'Serendip Sanctuary' WHERE id IN (910);
UPDATE contact SET name_company = 'RSPCA' WHERE id IN (1305,1730,1094,1333,1265,1343,1307,872,549,1400,1562);
UPDATE contact SET name_company = 'Scolexia' WHERE id IN (98);
UPDATE contact SET name_company = 'University of Melbourne' WHERE id IN (291,148);
UPDATE contact SET name_company = 'U-Vet' WHERE id IN (364,108);
UPDATE contact SET name_company = 'U-Vet', name_first = 'Courtney', name_last = 'Dunne' WHERE id IN (481);
UPDATE contact SET name_company = 'U-Vet', name_first = 'Equine section', name_last = 'Jenni Bauquier' WHERE id IN (364);
UPDATE contact SET name_company = 'U-Vet', name_first = 'Surgery', name_last = 'Peta Rak' WHERE id IN (612);
UPDATE contact SET name_company = 'U-Vet', name_first = 'Werribee', name_last = 'Dr Jee Wijesinghe' WHERE id IN (120);
UPDATE contact SET name_company = 'U-Vet', name_first = 'Macedon Ranges Equine Vet', name_last = 'Olivia Greenwood' WHERE id IN (83);
UPDATE contact SET name_company = 'U-Vet', name_first = 'Thurid', name_last = 'Johnstone' WHERE id IN (687);
UPDATE contact SET name_company = 'U-Vet', name_first = 'Bonnie', name_last = 'Purcell' WHERE id IN (103);
UPDATE contact SET name_company = 'U-Vet', name_first = 'Ide', name_last = 'Gillespie' WHERE id IN (70);
UPDATE contact SET name_company = 'U-Vet', name_first = 'Hannah', name_last = 'Reeves' WHERE id IN (153);
UPDATE contact SET name_company = 'University of Melbourne', name_first = 'Equine', name_last = '' WHERE id IN (1931);
UPDATE contact SET name_company = 'University of Melbourne', name_first = 'Parasitology', name_last = 'Abdul Jabbar' WHERE id IN (173);
UPDATE contact SET name_company = 'University of Melbourne', name_first = 'Bioresources 4', name_last = 'Denise Noonan' WHERE id IN (1958);
UPDATE contact SET name_company = 'University of Melbourne', name_first = 'Newcastle Conservation Science Research Group', name_last = 'Dr Jenny Smart' WHERE id IN (1937);
UPDATE contact SET name_company = 'University of Melbourne', name_first = 'Equine Centre', name_last = '' WHERE id IN (1944);
UPDATE contact SET name_company = 'University of Melbourne', name_first = 'Parasitology', name_last = 'Abdul Jabbar' WHERE id IN (173);
UPDATE contact SET name_company = 'University of Melbourne', name_first = 'MVS', name_last = 'Joanne Devlin' WHERE id IN (1707);
UPDATE contact SET name_company = 'Agriculture Victoria', name_first = 'Research AgriBio', name_last = 'Grant Rawlin' WHERE id IN (1946);
UPDATE contact SET name_company = 'Department of Environment Land, Water and Planning (DELWP)' WHERE id IN (825,826,771,1214,1471);
UPDATE contact SET name_company = 'Department of Environment Land, Water and Planning (DELWP)', name_first = 'Marine Mammal Submission', name_last = '' WHERE id IN (825);
UPDATE contact SET name_company = 'Department of Microbiology and Immunology, The Peter Doherty Institute for Infection and Immunity' WHERE id IN (727);
UPDATE contact SET name_company = 'Department of Energy, Environment and Climate Action (DEECA)' WHERE id IN (1995);
UPDATE contact SET name_company = 'Department of Natural Resources and Environment Tasmainia (DPIPWE)' WHERE id IN (1508,1521,1522);
UPDATE contact SET name_company = 'Turosi Food Solutions Group' WHERE id IN (1829,1720,368);
UPDATE contact SET name_company = 'Veterinary Diagnostic Services - Agriculture Victoria Research (Agribio)' WHERE id IN (1926);
UPDATE contact SET name_company = 'Melbourne Zoo', name_first = 'Marine Response Unit', name_last = 'J. Ring' WHERE id IN (969);
UPDATE contact SET name_company = 'D A Halls / Scolexia' WHERE id IN (1073);
UPDATE contact SET name_company = 'RSPCA', name_first = 'Inspectorate Victoria' WHERE id IN (1265);
UPDATE contact SET name_company = 'RSPCA' WHERE id IN (1914);
UPDATE contact SET name_company = '', name_first = 'S', name_last = 'Harvey' WHERE id IN (61);
UPDATE contact SET name_company = '', name_first = 'Smitha', name_last = 'Georgy' WHERE id IN (1098);


SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE company_contact;
TRUNCATE company;
SET FOREIGN_KEY_CHECKS = 1;

-- Create company for contacts not linked to a company or business
INSERT INTO company (id, institution_id, contact_id, name, email, modified, created)
(
    SELECT
        1,
        1,
        GROUP_CONCAT(id) AS contact_id,
        'Private Clients' AS name,
        'anat-vet@unimelb.edu.au',
        NOW() AS modified,
        NOW() AS created
    FROM contact
    WHERE name_company = '' AND name_first != ''
    AND type = 'client'
    GROUP BY institution_id
);

-- Populate the company table with new companies
INSERT INTO company (institution_id, contact_id, account_code, name, email, phone, fax, street, city, country, state, postcode, notes, del, modified, created)
(
    SELECT
        institution_id,
        GROUP_CONCAT(id) AS contact_id,
        MAX(account_code) AS account_code,
        ucwords(REPLACE(TRIM(name_company), UNHEX('C2A0'),'')) AS name,
        LOWER(MAX(email)) AS email,
        MAX(phone),
        MAX(fax),
        MAX(street),
        MAX(city),
        MAX(country),
        MAX(state),
        MAX(postcode),
        TRIM(notes) as notes,
        0 AS del,
        MAX(modified),
        MIN(created)
    FROM contact
    WHERE name_company != ''
    AND type = 'client'
    AND id != 1     -- ignore first test client as it has already been replaced
    GROUP BY name_company
);

-- Create all company_contacts for each company record
INSERT INTO company_contact (company_id, contact_id, name, email, phone, del, modified, created)
(
    SELECT
        co.id AS company_id,
        c.id AS contact_id,
        ucwords(TRIM(CONCAT_WS(' ', c.name_first, c.name_last))) AS name,
        LOWER(c.email),
        c.phone,
        c.del,
        c.modified,
        c.created
    FROM company co
    JOIN contact c ON (FIND_IN_SET(c.id, co.contact_id) > 0)
    WHERE TRIM(CONCAT_WS(' ', c.name_first, c.name_last)) != ''
);

-- STEP 1: Set the path_case.company_id field by searching all contacts for the client contact_id (in path_case_has_contact)
UPDATE path_case p
LEFT JOIN company c ON (FIND_IN_SET(p.client_id, c.contact_id) > 0)
SET p.company_id = c.id
WHERE 1;

UPDATE path_case p SET p.company_id = 1 WHERE p.company_id IS NULL;

-- STEP 2: Set the path_case_has_company_contact entries
CREATE TABLE IF NOT EXISTS path_case_has_company_contact (
    path_case_id INT UNSIGNED NOT NULL,
    company_contact_id INT UNSIGNED NOT NULL,
    PRIMARY KEY path_case_id__student_id (path_case_id, company_contact_id),
    CONSTRAINT fk_path_case_has_company_contact__path_case_id FOREIGN KEY (path_case_id) REFERENCES path_case (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_path_case_has_company_contact__company_contact_id FOREIGN KEY (company_contact_id) REFERENCES company_contact (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Set company contact if found
INSERT INTO path_case_has_company_contact (path_case_id, company_contact_id)
(
    SELECT
        pc.id AS path_case_id,
        cc.id AS company_contact_id
    FROM path_case pc
    JOIN company c ON (pc.company_id = c.id)
    LEFT JOIN company_contact cc ON (pc.client_id = cc.contact_id)
    WHERE cc.id IS NOT NULL
);


-- Add reviewedById to case table
ALTER TABLE path_case ADD reviewed_by_id INT UNSIGNED NULL AFTER addendum;
ALTER TABLE path_case ADD
    CONSTRAINT fk_path_case__reviewed_by_id FOREIGN KEY (reviewed_by_id) REFERENCES user (id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE path_case ADD reviewed_on DATETIME NULL AFTER reviewed_by_id;

ALTER TABLE path_case ADD necropsy_performed_on DATETIME NULL AFTER arrival;

-- Add Smitha as a user that can review cases
INSERT IGNORE INTO user_permission VALUES
(15, 'perm.case.can.review')
;


ALTER TABLE path_case CHANGE COLUMN ac_type  dispose_method VARCHAR(64) NULL DEFAULT '';
ALTER TABLE path_case CHANGE COLUMN disposal dispose_on DATETIME DEFAULT NULL;

-- fix a case invalid date
UPDATE path_case set path_case.dispose_on = '2023-09-13 09:03:46' WHERE id = 2911;

-- Update mail template params
UPDATE mail_template SET template = REPLACE(template, '{pathCase::clientId}', '{pathCase::companyId}') WHERE 1;
UPDATE mail_template SET template = REPLACE(template, '{pathCase::acType}', '{pathCase::disposeMethod}') WHERE 1;
UPDATE mail_template SET template = REPLACE(template, '{pathCase::disposal}', '{pathCase::disposeOn}') WHERE 1;

-- Update mail template content





-- TODO: uncomment for release
--       Probably best to wait until all updates are tested and approved before removing the following

-- ALTER TABLE company DROP COLUMN contact_id;
-- ALTER TABLE company_contact DROP COLUMN contact_id;

-- TODO: Uncomment for release of new client/student updates
-- ALTER TABLE path_case DROP COLUMN owner_id;
-- DROP TABLE path_case_has_contact;
-- DROP TABLE contact;
DROP FUNCTION IF EXISTS ucwords;






