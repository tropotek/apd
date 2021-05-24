-- ---------------------------------
-- Version: 3.0.96
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------



alter table path_case
    add bio_samples int default 1 not null after issue_alert;

alter table path_case
    add bio_notes TEXT default '' not null after bio_samples;



