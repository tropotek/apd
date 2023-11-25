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




-- TODO: drop owner_id field on release
-- ALTER TABLE path_case DROP COLUMN owner_id;









