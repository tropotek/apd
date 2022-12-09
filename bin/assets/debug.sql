-- ------------------------------------------------------
-- Author: Michael Mifsud
-- Date: 06/04/17
-- ------------------------------------------------------

-- --------------------------------------
-- Change all passwords to 'password' for debug mode
-- --------------------------------------
UPDATE `user` SET `password` = MD5(CONCAT('password', `hash`)) WHERE 1;

-- --------------------------------------
-- Disable Domains for institutions
# UPDATE `institution` SET `domain` = '';

