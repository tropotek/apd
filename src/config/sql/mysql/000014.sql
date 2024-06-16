-- ---------------------------------
-- Version: 3.4.118
-- ---------------------------------

SET SQL_SAFE_UPDATES = 0;

-- Update animals list

-- move 15 -> 10, remove 15
UPDATE path_case SET
    animal_type_id = 10
WHERE animal_type_id = 15;

-- move 13, 11 -> 12, remove 13, 11
UPDATE path_case SET
    animal_type_id = 12
WHERE animal_type_id IN (13, 11);

ALTER TABLE animal_type
ADD active BOOL NOT NULL DEFAULT TRUE AFTER description;

-- deactivate unused animals
UPDATE animal_type SET active = FALSE
WHERE id IN (13, 11, 15);


SET SQL_SAFE_UPDATES = 1;