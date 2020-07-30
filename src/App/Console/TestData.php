<?php
namespace App\Console;

use App\Db\Address;
use App\Db\AddressMap;
use App\Db\Client;
use App\Db\ClientMap;
use App\Db\PathCase;
use App\Db\Service;
use App\Db\Storage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tk\Db\Tool;
use Tk\ObjectUtil;
use Uni\Db\Permission;
use Uni\Db\User;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Michael Mifsud
 */
class TestData extends \Bs\Console\TestData
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('testData')
            ->setAliases(array('td'))
            ->setDescription('Fill the database with test data');
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

        // required vars
        $config = \App\Config::getInstance();
        $db = $this->getConfig()->getDb();

        $this->write('Institution: ' . $config->getInstitution()->getName());

        if (!$config->isDebug()) {
            $this->writeError('Error: Only run this command in a debug environment.');
            return;
        }

        // Clear DB: Make this a command on it own
        $db->exec('DELETE FROM `user` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `address` WHERE `postcode` = \'0000\'');
        $db->exec('DELETE FROM `client` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `storage` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `service` WHERE `notes` = \'***\' ');




        /** @var \Uni\Db\Institution $institution */
        $institution = $config->getInstitutionMapper()->find(1);

        $db->exec('DELETE FROM `user` WHERE `notes` = \'***\' ');
        for($i = 0; $i < 25; $i++) {
            $user = $config->createUser();
            $user->setInstitutionId($institution->getId());
            $user->setName($this->createFullName());
            do {
                $user->setUsername(strtolower($this->createName()) . '.' . rand(1000, 10000000));
            } while($config->getUserMapper()->findByUsername($user->getUsername()) != null);
            $user->setEmail($this->createUniqueEmail());
            //$user->setType((rand(1, 10) <= 5) ? \Uni\Db\User::TYPE_STAFF : \Uni\Db\User::TYPE_STUDENT);
            $user->setType(\Uni\Db\User::TYPE_STAFF);
            $user->setNotes('***');
            $user->save();
            $user->setNewPassword('password');
            $user->save();
            //$user->addPermission(\Uni\Db\Permission::getDefaultPermissionList($user->getType()));
            if ((rand(1, 10) <= 5)) {
                $user->addPermission(Permission::MANAGE_SITE);
                $user->addPermission(Permission::MANAGE_STAFF);
                $user->addPermission(Permission::CAN_MASQUERADE);
            }
        }

        $db->exec('DELETE FROM `address` WHERE `postcode` = \'0000\'');
        for($i = 0; $i < 50; $i++) {
            $address = new Address();
            $address->setNumber(rand(3, 3982));
            $address->setStreet($this->createWords(rand(1, 3)));
            $address->setCity(ucwords($this->createWords(rand(1, 2))));
            $address->setCountry(ucwords($this->createWords(rand(1, 2))));
            $address->setState(ucwords($this->createWords(rand(1, 2))));
            $address->setPostcode('0000');
            $address->setAddress(
                $address->getNumber() . ' ' .
                $address->getStreet() . ' ' .
                $address->getCity() . ' ' .
                $address->getState() . ' ' .
                $address->getCountry() . ' ' .
                $address->getPostcode()
            );
            $address->setMapLat(-40.847602844238);
            $address->setMapLng(137.701782226560);
            $address->save();
        }

        $db->exec('DELETE FROM `client` WHERE `notes` = \'***\' ');
        for($i = 0; $i < 25; $i++) {
            $client = new Client();
            //$client->setUserId();     // TODO
            $client->setUid($this->createStr(6));
            $client->setName($this->createName());
            $client->setEmail($this->createUniqueEmail());
            if (rand(0, 1))
                $client->setBillingEmail($this->createUniqueEmail());
            $client->setPhone($this->createStr(10, '1234567890'));
            if (rand(0, 1))
                $client->setFax($this->createStr(10, '1234567890'));
            $address = AddressMap::create()->findAll(Tool::create('RAND()'))->current();
            $client->setAddressId($address->getId());
            if (rand(0, 1)) {
                if (rand(0, 1))
                    $address = AddressMap::create()->findAll(Tool::create('RAND()'))->current();
                $client->setBillingAddressId($address->getId());
            }
            $client->setNotes('***');
            $client->save();
        }

        $db->exec('DELETE FROM `storage` WHERE `notes` = \'***\' ');
        for($i = 0; $i < 10; $i++) {
            $storage = new Storage();
            /** @var Address $address */
            $address = AddressMap::create()->findAll(Tool::create('RAND()'))->current();
            $storage->setAddressId($address->getId());
            $storage->setUid($this->createStr(6));
            $storage->setName($this->createName());
            $storage->setMapLat($address->getMapLat());
            $storage->setMapLng($address->getMapLng());
            $storage->setNotes('***');
            $storage->save();
        }

        $db->exec('DELETE FROM `service` WHERE `notes` = \'***\' ');
        for($i = 0; $i < 10; $i++) {
            $service = new Service();
            $service->setName($this->createName());
            $service->setPrice(rand(1, 50) . '.' . rand(0, 99));
            $service->setNotes('***');
            $service->save();
        }


        $db->exec('DELETE FROM `path_case` WHERE `notes` = \'***\' ');
        for($i = 0; $i < 100; $i++) {
            $case = new PathCase();
            /** @var Client $client */
            $client = ClientMap::create()->findAll(Tool::create('RAND()'))->current();
            $case->setClientId($client->getId());
            $case->setPathologyId(rand(100, 999) . '-' . rand(1, 99));
            $case->setType(rand(0,1) ? PathCase::TYPE_NECROPSY : PathCase::TYPE_BIOPSY);
            $arr = ObjectUtil::getClassConstants($case, 'SUBMISSION_');
            $selected = $arr[rand(0, count($arr)-1)];
            if ($selected == 'other') $selected = $this->createStr(rand(8, 23));
            $case->setSubmissionType($selected);
            $arr = ObjectUtil::getClassConstants($case, 'STATUS_');
            $selected = $arr[rand(0, count($arr)-1)];
            $case->setStatus($selected);

            // TODO: these fields will be redundant when using the status log
            if (rand(0, 1)) {
                $case->setSubmitted($this->createRandomDate());
                $case->setStatus(PathCase::STATUS_PENDING);
                if (rand(0, 1)) {
                    $case->setExamined($this->createRandomDate($case->getSubmitted()));
                    $case->setStatus(PathCase::STATUS_EXAMINED);
                    if (rand(0, 1)) {
                        $case->setFinalised($this->createRandomDate($case->getExamined()));
                        $case->setStatus(PathCase::STATUS_COMPLETED);
                    }
                }
            }

            if (rand(0, 1)) {
                $case->setZootonicDisease($this->createStr());
                if (rand(0, 1))
                    $case->setZootonicResult(rand(0, 1) ? PathCase::ZOO_POSITIVE : PathCase::ZOO_NEGATIVE);
            }
            $case->setSpecimenCount(rand(0, 100));      // TODO: do we really need this???
            



            $case->setNotes('***');
            $case->save();
        }




    }



}
