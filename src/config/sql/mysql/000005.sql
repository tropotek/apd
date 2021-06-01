-- ---------------------------------
-- Version: 3.0.108
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------

-- Create case edit log table

-- --------------------------------------------------
--
--
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS `edit_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INTEGER NOT NULL DEFAULT 0,                       -- User who edited the object: If 0 then this is assumed to be a system update
  `fkey` VARCHAR(64) NOT NULL DEFAULT '',                     -- Foreign object key  EG: '\App\Db\Company', '\App\Db\Placement', etc
  `fid` INTEGER NOT NULL DEFAULT 0,                           -- foreign id  EG: '2'
  `state` TEXT,                                               -- (optional) JSON data object. Save the sate of the object in JSON or PHP serialised format
  `message` TEXT,                                             -- The HTML Text to be shown in the message?
  `del` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  KEY (user_id),
  KEY (fkey, fid)
) ENGINE=InnoDB;

#
# CREATE TABLE IF NOT EXISTS `service_has_user` (
#    `service_id` int(10) unsigned NOT NULL,
#    `user_id` int(10) unsigned NOT NULL,
#    PRIMARY KEY (`service_id`, `user_id`)
# ) ENGINE=InnoDB;

