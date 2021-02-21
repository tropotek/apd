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

/**
 * Cron job to be run nightly
 *
 * # run Nightly site cron job
 *   0  4,16  *   *   *      php /home/user/public_html/bin/cmd cron > /dev/null 2>&1
 *
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class Cron extends \Bs\Console\Iface
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('cron')
            ->setDescription(
                sprintf('Run the site cron script:     "*/10  *   *   *   *      php %s/bin/cmd cron > /dev/null 2>&1"',
                    Config::getInstance()->getSrcPath())
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);

        $institutionList = InstitutionMap::create()->findFiltered(['active' => true]);
        foreach ($institutionList as $institution) {
            $this->sendDisposalReminders($institution);

        }

        $this->write('', OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * @param Institution $institution
     * @throws \Exception
     */
    public function sendDisposalReminders(Institution $institution)
    {
        // Find all cases that require disposal in 3 days (60*60*24*3 = sec)
        $days = 3;      // The number of days before disposal to send reminders
        $spc = '  ';
        $this->write('Send Disposal Reminders for '.$institution->getName().': ');

        // TODO: REMOVE THIS ........!!!!!!!!!!!!!!!
        //$this->getConfig()->getDb()->exec('TRUNCATE `mail_sent`');

        $caseList = PathCaseMap::create()->findFiltered([
            'institutionId' => $institution->getId(),
            'isDisposed' => true,
            'disposedAfter' => \Tk\Date::create()->add(new \DateInterval('P'.$days.'D')),
            'reminderSent' => false     // TODO:
        ]);
        $sent = 0;
        foreach ($caseList as $case) {
            $subject = 'Pathology Case ['. $case->getPathologyId() . '] Disposal Reminder for ' . $case->getDisposal(\Tk\Date::FORMAT_LONG_DATE);
            $messageList = MailTemplateHandler::createMessageList(PathCase::REMINDER_STATUS_DISPOSAL, $case, $subject);
            $sent += MailTemplateHandler::sendMessageList($messageList);

            // Flag this case as disposal email reminder sent
            PathCaseMap::create()->addMailSent($case->getId(), PathCase::REMINDER_SENT_TYPE);
            //$date = PathCaseMap::create()->hasMailSent($case->getId(), PathCase::REMINDER_SENT_TYPE);

            $this->writeGreen($spc . 'Sending Reminder For Case: #' . $case->getPathologyId(), OutputInterface::VERBOSITY_VERBOSE);
        }
        if ($sent)
            $this->write($spc . 'Successfully Sent '.$sent.' reminder emails.');

    }


}
