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
            $this->sendDisposalReminders($institution);

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
        $this->write('Send Disposal Reminders for '.$institution->getName().': ');

    }

    /**
     * If case not completed after 15 working days from the `necropsyPerformedOn` date,
     *   send a reminder to the pathologist (CC site admin)
     */
    public function sendNecropsyCompletionReminders(Institution $institution)
    {

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
