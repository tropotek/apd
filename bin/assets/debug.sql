-- ------------------------------------------------------
-- Author: Michael Mifsud
-- ------------------------------------------------------

-- --------------------------------------
-- Change all passwords to 'password' for debug mode
-- --------------------------------------
UPDATE `user` SET `password` = MD5(CONCAT('password', `hash`)) WHERE `password` != '';

-- --------------------------------------
-- Disable Domains for institutions
UPDATE `institution` SET `domain` = '' WHERE 1;

