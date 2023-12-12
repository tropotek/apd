<?php
namespace App\Console;

use App\Db\PathCase;
use App\Db\PathCaseMap;
use App\Listener\MailTemplateHandler;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Config;
use Tk\ExtAuth\Microsoft\TokenMap;
use Uni\Db\Institution;
use Uni\Db\InstitutionMap;
use Uni\Db\User;

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

            $this->expireMicrosoftTokens($institution);

            // reminders
            $this->sendDisposalReminders($institution);
            $this->sendNecropsyCompleteCaseReminders($institution);
            $this->sendBiopsyCompleteReportReminders($institution);
        }

        $this->write('', OutputInterface::VERBOSITY_VERBOSE);
        return 0;
    }


    public function expireMicrosoftTokens(Institution $institution)
    {
        $this->write('Clearing expired Microsoft login tokens. ');
        TokenMap::create()->cleanExpired();
    }


    /**
     * If a dispose_on date has been set then check for any t
     */
    public function sendDisposalReminders(Institution $institution)
    {
        // Find all cases that require disposal in 3 days
        $this->write('Send disposal reminders for '.$institution->getName().': ');

        $caseList = PathCaseMap::create()->findFiltered([
            'institutionId' => $institution->getId(),
            'status' => [PathCase::STATUS_PENDING, PathCase::STATUS_REPORTED, PathCase::STATUS_EXAMINED, PathCase::STATUS_HOLD, PathCase::STATUS_FROZEN_STORAGE],
            'disposedAfter' => \Tk\Date::create()->add(new \DateInterval('P3D')),
            'billable' => true,
        ]);
        $sent = 0;
        $spc = '  ';
        foreach ($caseList as $case) {
            $subject = '['. $case->getPathologyId() . '] '.ucfirst($case->getType()).' Case Disposal Reminder';
            $messageList = MailTemplateHandler::createMessageList(PathCase::REMINDER_STATUS_DISPOSAL, $case, $subject);
            $sent += MailTemplateHandler::sendMessageList($messageList);
            $this->writeGreen($spc . 'Case: #' . $case->getPathologyId(), OutputInterface::VERBOSITY_VERY_VERBOSE);
        }
        if ($sent) $this->write($sent.' emails sent.');
    }

    /**
     * If case not completed after 15 working days from the `servicesCompletedOn` date,
     *   send a reminder to the pathologist (CC site admin)
     */
    public function sendNecropsyCompleteCaseReminders(Institution $institution)
    {
        // Find uncompleted cases
        $this->write('Send necropsy complete case reminders for '.$institution->getName().': ');

        $messageList = MailTemplateHandler::createMessageList(PathCase::REMINDER_NECROPSY_COMPLETE_CASE, $institution);
        $sent = MailTemplateHandler::sendMessageList($messageList);
        $this->write($sent.' emails sent.');
    }

    /**
     * For biopsy cases when all Histology requests are completed, send a reminder to
     * pathologist (cc site admin) after 24 hours to `complete` the report.
     */
    public function sendBiopsyCompleteReportReminders(Institution $institution)
    {
        // Find uncompleted cases
        $this->write('Send Biopsy Complete Case Report Reminders for '.$institution->getName().': ');

        $messageList = MailTemplateHandler::createMessageList(PathCase::REMINDER_BIOPSY_COMPLETE_REPORT, $institution);
        $sent = MailTemplateHandler::sendMessageList($messageList);
        $this->write($sent.' emails sent.');
    }


}
