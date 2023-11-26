<?php
namespace App\Console;

use App\Db\CompanyContact;
use App\Db\CompanyContactMap;
use App\Db\PathCaseMap;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * This script is to be run once after the 2023 updates are released
 */
class RunOnce extends \Bs\Console\Iface
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('runonce')
            ->setAliases(array('ro'))
            ->setDescription('This script is to be run once after the 2023 updates are released');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $config = \App\Config::getInstance();
        $db = $config->getDb();

        if (!$db->hasTable('company')) {
            $this->writeError('Error: Run the upgrade script first!');
            return 1;
        }

        // get all client contacts with email_cc contacts
        $rows = $db->query("
            SELECT DISTINCT
                pc.id AS path_case_id,
                pc.company_id,
                LOWER(REPLACE(REPLACE(cn.email_cc, ' ', ''), ';',',')) AS email_cc
            FROM path_case pc
            LEFT JOIN contact cn ON (pc.client_id = cn.id)
            LEFT JOIN company_contact cc ON (FIND_IN_SET(LOWER(cc.email), LOWER(REPLACE(REPLACE(cn.email_cc, ' ', ''), ';',','))) > 0)
            WHERE cn.email_cc != ''
            AND LOWER(cn.email) != LOWER(cn.email_cc)
            AND cc.id IS NULL
        ");

        foreach ($rows as $row) {
            //vd($row);
            $emails = explode(',', $row->email_cc);
            foreach ($emails as $email) {
                //vd($email);
                $contact = CompanyContactMap::create()->findFiltered(
                    [
                        'companyId' => $row->company_id,
                        'email' => $email
                    ]
                )->current();

                if (!$contact && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // create new contact
                    $contact = new CompanyContact();
                    $contact->setCompanyId($row->company_id);
                    [$username, $domain] = explode('@', $email);
                    $contact->setName($username);
                    $contact->setEmail($email);
                    $contact->save();
                }

                // Add contact to path case `path_case_has_company_contact`
                PathCaseMap::create()->addContact($row->path_case_id, $contact->getId());
            }
        }




        $output->writeln('Complete!!!');
        return 0;
    }



}
