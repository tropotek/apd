-- ---------------------------------
-- Version: 3.0.142
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


INSERT INTO user_permission (user_id, name)
(
    SELECT id, 'perm.is.pathologist'
    FROM user
    where del = 0
)
;

UPDATE user SET del = 0 WHERE id = 9;

TRUNCATE service_has_user;
INSERT INTO service_has_user VALUES (40, 38);
INSERT INTO service_has_user VALUES (40, 34);





