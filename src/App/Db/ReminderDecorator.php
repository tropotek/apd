<?php

namespace App\Db;

use App\Config;
use App\Listener\MailTemplateHandler;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Collection;
use Tk\Date;
use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
use Tk\ObjectUtil;
use Uni\Db\Institution;
use Uni\Db\User;
use Uni\Uri;

class ReminderDecorator
{

    /**
     * @todo This could be refactored specific to the disposal reminder emails, was copied from PathCaseDecorator
     */
    public static function onDisposalReminder(PathCase $case, MailTemplate $mailTemplate, ?string $subject = null): array
    {
        $status = $case->getCurrentStatus();
        $messageList = [];
        $config = Config::getInstance();

        $recipientList = PathCaseDecorator::getRecipients($case, $mailTemplate);
        foreach ($recipientList as $recipient) {
            $message = CurlyMessage::create($mailTemplate->getTemplate());
            $message->set('_mailTemplate', $mailTemplate);
            if (!$subject) {
                $subject = '[' . $case->getPathologyId() . '] ' . ObjectUtil::basename($case) . ' ' . ucfirst($status->getName());
            }
            $message->setSubject($subject);

            $message->setFrom(Message::joinEmail($config->get('site.email'),
                $case->getInstitution()->getName()));
            $message->setReplyTo(Message::joinEmail($case->getInstitution()->getEmail(), $case->getInstitution()->getName()));

            $message->addTo($recipient['email']);
            $message->addBcc($case->getInstitution()->getEmail());
            $message->replace(Collection::prefixArrayKeys($recipient, 'recipient::'));

            $message->replace(Collection::prefixArrayKeys([
                'id' => $status->getId(),
                'name' => $status->getName(),
                'message' => nl2br($status->getMessage()),
                'event' => $status->getEvent()
            ], 'status::'));
            $message->set('pathCase::url', Uri::create('/staff/pathCaseEdit.html')
                ->setScheme(Uri::SCHEME_HTTP_SSL)
                ->set('pathCaseId', $case->getId())->toString());
            $message->replace(Collection::prefixArrayKeys(\App\Db\PathCaseMap::create()->unmapForm($case), 'pathCase::'));
            $message->set('pathCase::disposeOn', $case->getDisposeOn(Date::FORMAT_MED_DATE) ?? '');
            if ($case->getInstitution())
                $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()->unmapForm($case->getInstitution()), 'institution::'));
            if ($case->getCompany())
                $message->replace(Collection::prefixArrayKeys(\App\Db\ContactMap::create()->unmapForm($case->getCompany()), 'client::'));
            /** @var User $pathologist */
            $pathologist = $config->getUserMapper()->find($case->pathologistId);
            if ($pathologist) {
                $message->set('pathCase::pathologist', $pathologist->getName());
            }
            $messageList[] = $message;
        }
        return $messageList;
    }

    /**
     * After 15 working days from the `necropsyPerformedOn` date,
     *   send a reminder to the pathologist (CC site admin) to complete the open Cases
     */
    public static function onNecropsyCompleteCase(Institution $institution, MailTemplate $mailTemplate, ?string $subject = null): array
    {
        $config = \Uni\Config::getInstance();
        $messageList = [];

        $sql = <<<SQL
SELECT
    pathologist_id,
    COUNT(*) AS cases_due
FROM path_case
WHERE necropsy_performed_on IS NOT NULL
    AND type = 'necropsy'
    AND status NOT IN ('complete', 'cancelled')
    AND DATE(necropsy_performed_on) <= CURRENT_DATE - INTERVAL 15 DAY
    AND pathologist_id != 0
GROUP BY pathologist_id
SQL;
        $rows = $config->getDb()->query($sql);

        foreach ($rows as $row) {
            /** @var User $pathologist */
            $pathologist = $config->getUserMapper()->find($row->pathologist_id);
            if (!($pathologist || $pathologist->isActive())) continue;

            $message = CurlyMessage::create($mailTemplate->getTemplate());
            $message->set('_mailTemplate', $mailTemplate);
            $subject = sprintf('You have %d Necropsy cases requiring completion', $row->cases_due);
            $message->setSubject($subject);

            // Set this to avoid Unimelb domain SPF errors
            $message->setFrom(Message::joinEmail($config->get('site.email'), $institution->getName()));
            $message->setReplyTo(Message::joinEmail($institution->getEmail(), $institution->getName()));
            $message->addTo(Message::joinEmail($pathologist->getEmail(), $pathologist->getName()));
            $message->addBcc($institution->getEmail());

            // Set message placeholders
            $message->set('cases_due', $row->cases_due);
            $message->set('user::homeUrl', Uri::createHomeUrl('/index.html', $pathologist)->toString());
            $message->set('recipient::email', $pathologist->getEmail());
            $message->set('recipient::name', $pathologist->getName());
            $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()->unmapForm($institution), 'institution::'));

            $messageList[] = $message;
        }

        return $messageList;
    }

    /**
     * For biopsy cases when all Histology requests are completed, send a reminder to
     * pathologist (cc site admin) after 24 hours to `complete` the report.
     */
    public static function onBiopsyCompleteReport(Institution $institution, MailTemplate $mailTemplate, ?string $subject = null): array
    {
        $config = \Uni\Config::getInstance();
        $messageList = [];

        $sql = <<<SQL
WITH
completed AS (
    SELECT
        r.path_case_id,
        COUNT(*) as cnt,
        MAX(s.created) AS cases_due
    FROM status s
    JOIN request r on (s.fid = r.id)
    WHERE s.fkey = 'App\\\Db\\\Request'
    AND NOT s.del
    AND s.name = 'completed'
    GROUP BY r.path_case_id
),
requests AS (
    SELECT
        r.path_case_id,
        p.pathologist_id,
        COUNT(*) AS total,
        SUM(IF(r.status = 'pending', 1, 0)) AS pending_cnt,
        SUM(IF(r.status = 'completed', 1, 0)) AS complete_cnt,
        c.cases_due,
        p.report_status,
        p.status AS 'case_status'
    FROM request r
    JOIN path_case p ON (r.path_case_id = p.id)
    LEFT JOIN completed c ON (p.id = c.path_case_id)
    WHERE
        r.status != 'cancelled'
        AND p.type = 'biopsy'
        AND p.pathologist_id > 0
        AND c.cases_due < NOW() - INTERVAL 1 DAY
    GROUP BY r.path_case_id
)
SELECT
    r.pathologist_id,
    COUNT(*) AS reports_due
FROM requests r
WHERE r.report_status != 'completed'
AND r.case_status != 'cancelled'
AND r.pending_cnt = 0
GROUP BY r.pathologist_id
SQL;
        $rows = $config->getDb()->query($sql);

        foreach ($rows as $row) {
            /** @var User $pathologist */
            $pathologist = $config->getUserMapper()->find($row->pathologist_id);
            if (!($pathologist || $pathologist->isActive())) continue;

            $message = CurlyMessage::create($mailTemplate->getTemplate());
            $message->set('_mailTemplate', $mailTemplate);
            $subject = sprintf('You have %d Biopsy cases due for report completion', $row->reports_due);
            $message->setSubject($subject);

            // Set this to avoid Unimelb domain SPF errors
            $message->setFrom(Message::joinEmail($config->get('site.email'), $institution->getName()));
            $message->setReplyTo(Message::joinEmail($institution->getEmail(), $institution->getName()));
            $message->addTo(Message::joinEmail($pathologist->getEmail(), $pathologist->getName()));
            $message->addBcc($institution->getEmail());

            // Set message placeholders
            $message->set('reports_due', $row->reports_due);
            $message->set('user::homeUrl', Uri::createHomeUrl('/index.html', $pathologist)->toString());
            $message->set('recipient::email', $pathologist->getEmail());
            $message->set('recipient::name', $pathologist->getName());
            $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()->unmapForm($institution), 'institution::'));

            $messageList[] = $message;
        }

        return $messageList;
    }
}