<?php
/**
 * @version: 3.4.98
 */

use App\Db\CompanyContact;
use App\Db\CompanyContactMap;
use App\Db\PathCaseMap;

try {
    $config = \Uni\Config::getInstance();
    $db = $config->getDb();

    if (!$db->hasTable('company')) {
        error_log('company table not found!');
        return;
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
        $emails = explode(',', $row->email_cc);
        foreach ($emails as $email) {
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
} catch (\Exception $e) { error_log($e->__toString()); }








