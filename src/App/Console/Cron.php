<?php
namespace App\Console;

use App\Db\PathCase;
use App\Db\PathCaseMap;
use App\Listener\MailTemplateHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Config;
use Uni\Db\Institution;
use Uni\Db\InstitutionMap;

class Cron extends \Bs\Console\Iface
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('cron')
            ->setDescription(
                sprintf('Run cron script nightly:     "0  18   *   *   *      php %s/bin/cmd cron > /dev/null 2>&1"',
                    Config::getInstance()->getSrcPath())
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);

        $institutionList = InstitutionMap::create()->findFiltered(['active' => true]);
        foreach ($institutionList as $institution) {
            //$this->sendDisposalReminders($institution);

            $this->sendCompleteReportReminders($institution);

            $this->sendNecropsyCompletionReminders($institution);
        }

        $this->write('', OutputInterface::VERBOSITY_VERBOSE);
        return 0;
    }

    /**
     * For biopsy cases when all Histology requests are completed, send a reminder to
     * pathologist (cc site admin) after 24 hours to `complete` the report.
     */
    public function sendCompleteReportReminders(Institution $institution)
    {
        // Find uncompleted cases
        $this->write('Send Complete Case Report Reminders for '.$institution->getName().': ');

        $sql = <<<SQL
WITH
completed AS (
    SELECT
        r.path_case_id,
        COUNT(*) as cnt,
        MAX(s.created) AS requests_completed
    FROM status s
    JOIN request r on (s.fid = r.id)
    WHERE s.fkey = 'App\\Db\\Request'
    AND NOT s.del
    AND s.name = 'completed'
    GROUP BY r.path_case_id
),
requests AS (
    SELECT
        r.path_case_id,
        p.pathologist_id,
        -- p.account_status,
        COUNT(*) AS total,
        SUM(IF(r.status = 'pending', 1, 0)) AS pending_cnt,
        SUM(IF(r.status = 'completed', 1, 0)) AS complete_cnt,
        c.requests_completed,
        p.report_status,
        p.status AS 'case_status'
        -- p.created AS 'case_created'
    FROM request r
    JOIN path_case p ON (r.path_case_id = p.id)
    LEFT JOIN completed c ON (p.id = c.path_case_id)
    WHERE
        r.status != 'cancelled'
        AND p.type = 'biopsy'
        AND p.pathologist_id > 0
        AND c.requests_completed < NOW() - INTERVAL 1 DAY
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
        $pathologistList = $this->getDb()->query($sql);

        foreach ($pathologistList as $row) {
            $pathologist = $this->getConfig()->getUserMapper()->find($row->pathologist_id);
            // send email with row->reports_due number in it and a link to the users dashboard.

        }

    }


    /**
     * If case not completed after 15 working days from the `necropsyPerformedOn` date,
     *   send a reminder to the pathologist (CC site admin)
     */
    public function sendNecropsyCompletionReminders(Institution $institution)
    {
        // Find uncompleted cases
        $this->write('Send Necropsy Complete Case Reminders for '.$institution->getName().': ');

        $sql = <<<SQL
SELECT
    pathologist_id,
    COUNT(*) AS cases
FROM path_case
WHERE necropsy_performed_on IS NOT NULL
    AND type = 'necropsy'
    AND status NOT IN ('complete', 'cancelled')
    AND DATE(necropsy_performed_on) <= CURRENT_DATE - INTERVAL 15 DAY
    AND pathologist_id != 0
GROUP BY pathologist_id
SQL;
        $pathologistList = $this->getDb()->query($sql);

        foreach ($pathologistList as $row) {
            $pathologist = $this->getConfig()->getUserMapper()->find($row->pathologist_id);
            // send email with row->cases number in it and a link to the users dashboard.

        }





    }


    /**
     * If a dispose_on date has been set then check for any t
     */
    public function sendDisposalReminders(Institution $institution)
    {
        // Find all cases that require disposal in 3 days
        $this->write('Send Disposal Reminders for '.$institution->getName().': ');

        $caseList = PathCaseMap::create()->findFiltered([
            'institutionId' => $institution->getId(),
            'status' => [PathCase::STATUS_PENDING, PathCase::STATUS_REPORTED, PathCase::STATUS_EXAMINED, PathCase::STATUS_HOLD, PathCase::STATUS_FROZEN_STORAGE],
            'disposedAfter' => \Tk\Date::create()->add(new \DateInterval('P3D')),
        ]);
        $sent = 0;
        $spc = '  ';
        foreach ($caseList as $case) {
            $subject = 'Pathology Case ['. $case->getPathologyId() . '] Disposal Reminder for ' . $case->getDisposeOn(\Tk\Date::FORMAT_LONG_DATE);
            $messageList = MailTemplateHandler::createMessageList(PathCase::REMINDER_STATUS_DISPOSAL, $case, $subject);
            $sent += MailTemplateHandler::sendMessageList($messageList);

            $this->writeGreen($spc . 'Sending Reminder For Case: #' . $case->getPathologyId(), OutputInterface::VERBOSITY_VERBOSE);
        }
        if ($sent)
            $this->write($spc . 'Successfully Sent '.$sent.' reminder emails.');

    }


}
