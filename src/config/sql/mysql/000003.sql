-- ---------------------------------
-- Version: 3.0.4
--
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


-- --------------------------------------------------------
-- Link users to service records for email notifications
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `service_has_user` (
   `service_id` int(10) unsigned NOT NULL,
   `user_id` int(10) unsigned NOT NULL,
   PRIMARY KEY (`service_id`, `user_id`)
) ENGINE=InnoDB;


INSERT INTO mail_template (institution_id, mail_template_event_id, recipient_type, template, active, modified, created)
VALUES (1, 8, 'serviceTeam', '<p>Hi {recipient::name},</p>
<p>A new pathology request has been submitted:</p>
<ul>
<li>Pathology #: <a href="{pathCase::url}">{pathCase::pathologyId}</a></li>
<li>Institution ID: {pathCase::institutionId}</li>
<li>Client ID: {pathCase::clientId}</li>
<li>Type: {pathCase::type}</li>
<li>Service Name: {service::name}</li>
{test::block}<li>Test Name: {test::name}</li>{/test:block}
<li>Submission Type: {pathCase::submissionType}</li>
<li>Status: {pathCase::status}</li>
<li>Animal Name: {pathCase::animalName}</li>
<li>Breed: {pathCase::breed}</li>
</ul>
<p>&nbsp;</p>
<p>STATUS</p>
<ul>
<li>Name: {status::name}</li>
<li>Event: {status::event}</li>
<li>Message: {status::message}</li>
</ul>
<p>&nbsp;</p>
<p>Thanks,</p>
<p>{institution::name}</p>', 1, NOW(), NOW());


