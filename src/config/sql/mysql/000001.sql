-- ---------------------------------
-- APD Install SQL
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------

-- Update Contact table
alter table contact add name_company varchar(255) default '' not null after account_code;
alter table contact change name name_first varchar(255) default '' not null;
alter table contact add name_last varchar(255) default '' not null after name_first;






