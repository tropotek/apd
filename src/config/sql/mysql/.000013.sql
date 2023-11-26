-- ---------------------------------
-- Version: 3.4.98
-- ---------------------------------


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
    KEY institution_id (institution_id),
    CONSTRAINT fk_student__institution_id FOREIGN KEY (institution_id) REFERENCES institution (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

TRUNCATE student;
INSERT INTO student (id, institution_id, name, email, del, modified, created)
    SELECT id, institution_id, replace(TRIM(CONCAT_WS(' ', name_first, name_last)), UNHEX('C2A0'),'') AS name, email ,del, modified, created
    FROM contact
    WHERE type = 'student'
;

CREATE TABLE IF NOT EXISTS `path_case_has_student` (
    `path_case_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY `path_case_id__student_id` (`path_case_id`, `student_id`),
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

-- TODO: Student - remove any called None and set to 0/null, student is a non-required field,




-- ----------------------------
--  client table
-- ----------------------------








-- TODO: Case - drop owner_id field on release
-- ALTER TABLE path_case DROP COLUMN owner_id;








