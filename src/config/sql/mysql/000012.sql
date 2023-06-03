-- ---------------------------------
-- Version: 3.4.72
-- ---------------------------------



ALTER TABLE path_case
ADD owner_name VARCHAR(255) NOT NULL DEFAULT '' AFTER specimen_count;

# SELECT p.id, p.owner_id, TRIM(CONCAT_WS(' ', c.name_first, c.name_last))
# FROM path_case p
#     LEFT JOIN contact c ON (p.owner_id = c.id AND c.type = 'owner' AND !c.del)
# ;

UPDATE path_case p
    LEFT JOIN contact c ON (p.owner_id = c.id AND c.type = 'owner' AND !c.del)
SET p.owner_name = TRIM(CONCAT_WS(' ', c.name_first, c.name_last))
WHERE !p.del
;
