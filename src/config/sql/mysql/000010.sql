-- ---------------------------------
-- Version: 3.4.10
--
-- Author: Michael Mifsud <info@tropotek.com>
--
-- This is a cleanup of the DB for initial release to
--    the tropotek
-- ---------------------------------


-- Cleanup DB tables
DROP TABLE IF EXISTS _user_role;
DROP TABLE IF EXISTS _user_role_id;
DROP TABLE IF EXISTS _user_role_institution;
DROP TABLE IF EXISTS _user_role_permission;

-- --------------------------------------
-- Use this SQL for migration into new tropotek server
-- --------------------------------------
UPDATE `user` SET `password` = '' WHERE id > 2;

DELETE FROM `_plugin_zone` WHERE `plugin_name` LIKE 'plg-ldap' AND `zone_name` LIKE 'institution';
DELETE FROM `_plugin_zone` WHERE `plugin_name` LIKE 'plg-lti' AND `zone_name` LIKE 'institution';

DELETE FROM _data WHERE fkey = 'plg-ldap.institution';
DELETE FROM _data WHERE fkey = 'plg-lti';

INSERT INTO _data (fid, fkey, `key`, value) VALUES (1, 'Uni\\Db\\Institution', 'inst.microsoftLogin', 'inst.microsoftLogin');

