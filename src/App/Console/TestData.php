<?php
namespace App\Console;

use App\Db\Address;
use App\Db\AddressMap;
use App\Db\Cassette;
use App\Db\CassetteMap;
use App\Db\Client;
use App\Db\ClientMap;
use App\Db\PathCase;
use App\Db\PathCaseMap;
use App\Db\Request;
use App\Db\Service;
use App\Db\ServiceMap;
use App\Db\Storage;
use App\Db\StorageMap;
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
        $db->exec('DELETE FROM `client` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `storage` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `service` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `path_case` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `cassette` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `request` WHERE `notes` = \'***\' ');




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

//        $db->exec('DELETE FROM `address` WHERE `postcode` = \'0000\'');
//        for($i = 0; $i < 50; $i++) {
//            $address = new Address();
//            $address->setNumber(rand(3, 3982));
//            $address->setStreet($this->createWords(rand(1, 3)));
//            $address->setCity(ucwords($this->createWords(rand(1, 2))));
//            $address->setCountry(ucwords($this->createWords(rand(1, 2))));
//            $address->setState(ucwords($this->createWords(rand(1, 2))));
//            $address->setPostcode('0000');
//            $address->setAddress(
//                $address->getNumber() . ' ' .
//                $address->getStreet() . ' ' .
//                $address->getCity() . ' ' .
//                $address->getState() . ' ' .
//                $address->getCountry() . ' ' .
//                $address->getPostcode()
//            );
//            $address->setMapLat(-40.847602844238);
//            $address->setMapLng(137.701782226560);
//            $address->save();
//        }

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

            $client->setStreet($this->createWords(rand(1, 3)));
            $client->setCity(ucwords($this->createWords(rand(1, 2))));
            $client->setCountry(ucwords($this->createWords(rand(1, 2))));
            $client->setState(ucwords($this->createWords(rand(1, 2))));
            $client->setPostcode($this->createStr(4, '1234567890'));

            if (rand(0, 1)) {
                $client->setUseAddress(false);
                $client->setBStreet($this->createWords(rand(1, 3)));
                $client->setBCity(ucwords($this->createWords(rand(1, 2))));
                $client->setBCountry(ucwords($this->createWords(rand(1, 2))));
                $client->setBState(ucwords($this->createWords(rand(1, 2))));
                $client->setBPostcode($this->createStr(4, '1234567890'));
            }

            $client->setNotes('***');
            $client->save();
        }

        $db->exec('DELETE FROM `storage` WHERE `notes` = \'***\' ');
        for($i = 0; $i < 10; $i++) {
            $storage = new Storage();
            $storage->setUid($this->createStr(6));
            $storage->setName('Building: ' . $this->createName());
            $storage->setMapLat(14.2321);
            $storage->setMapLng(-123.143);
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


        //$db->exec('DELETE FROM `path_case` WHERE `notes` = \'***\' ');
        $db->exec('TRUNCATE  `path_case` ');
        for($i = 0; $i < 100; $i++) {
            $case = new PathCase();
            /** @var Client $client */
            $client = ClientMap::create()->findAll(Tool::create('RAND()'))->current();
            $case->setClientId($client->getId());
            $staff = $this->getConfig()->getUserMapper()->findFiltered(array('type' => 'staff'), Tool::create('RAND()'))->current();
            $case->setUserId($staff->getId());
            $case->setType(rand(0,1) ? PathCase::TYPE_NECROPSY : PathCase::TYPE_BIOPSY);
            $arr = array_values(ObjectUtil::getClassConstants($case, 'SUBMISSION_'));
            $selected = $arr[rand(0, count($arr)-1)];
            if ($selected == 'other') $selected = $this->createStr(rand(8, 23));
            $case->setSubmissionType($selected);
            $staff1 = $this->getConfig()->getUserMapper()->findFiltered(array('type' => 'staff'), Tool::create('RAND()'))->current();
            $case->setPathologistId($staff1->getId());
            $case->setResident($this->createFullName());
            $case->setStudent($this->createFullName());
            $case->setStudentEmail($this->createEmail());

            //$arr = array_values(ObjectUtil::getClassConstants($case, 'STATUS_'));
            //$selected = $arr[rand(0, count($arr)-1)];
            $case->setStatus(PathCase::STATUS_PENDING);

            if (rand(0, 1)) {
                $case->setZoonotic($this->createStr());
            }
            $case->setSpecimenCount(rand(0, 100));      // TODO: do we really need this???
            $case->setAnimalName($this->createName());
            $case->setSpecies($this->createSpecies());
            $case->setSex(rand(0, 1) ? 'M' : 'F');
            $case->setDesexed((bool)rand(0,1));
            $case->setPatientNumber($this->createStr(8, '123456789'));
            $case->setMicrochip($this->createStr(12, '1234567890'));
            $case->setOwnerName($this->createName() . ' ' . $this->createName());
            $case->setOrigin($this->createStr());
            $case->setBreed($this->createBreed());
            $case->setColour($this->createColourString());
            $case->setWeight(rand(0, 50) . '.' . rand(0, 99));
            $case->setDob($this->createRandomDate());
            if ($case->getType() == PathCase::TYPE_NECROPSY)
                $case->setDod($this->createRandomDate($case->getDob()));
            if ($case->getType() == PathCase::TYPE_NECROPSY && rand(0,1))
                $case->setEuthanised(true);
            if ($case->isEuthanised())
                $case->setEuthanisedMethod($this->createStr());

            if ($case->getType() == PathCase::TYPE_NECROPSY) {
                $arr = array_values(ObjectUtil::getClassConstants($case, 'AC_'));
                $selected = $arr[rand(0, count($arr) - 1)];
                $case->setAcType($selected);
                if (rand(0, 1)) {
                    $case->setAcHold($this->createRandomDate($case->getCreated(), $case->getCreated()->add(new \DateInterval('P60D'))));
                }
                if (rand(0,1)) {
                    /** @var Storage $storage */
                    $storage = StorageMap::create()->findAll(Tool::create('RAND()'))->current();
                    $case->setStorageId($storage->getId());
                }
                if (rand(0,1)) {
                    $case->setDisposal($this->createRandomDate($case->getCreated(), $case->getCreated()->add(new \DateInterval('P60D'))));
                }
            }

            if (rand(0,1)) {
                $case->setCollectedSamples($this->createLipsumHtml(rand(1, 8)));
            }
            if (rand(0,1)) {
                $case->setClinicalHistory($this->createLipsumHtml(rand(1, 8)));
            }
            if (rand(0,1)) {
                $case->setGrossPathology($this->createLipsumHtml(rand(1, 8)));
            }
            if (rand(0,1)) {
                $case->setGrossMorphologicalDiagnosis($this->createLipsumHtml(rand(1, 8)));
            }
            if (rand(0,1)) {
                $case->setHistopathology($this->createLipsumHtml(rand(1, 8)));
            }
            if (rand(0,1)) {
                $case->setAncillaryTesting($this->createLipsumHtml(rand(1, 8)));
            }
            if (rand(0,1)) {
                $case->setMorphologicalDiagnosis($this->createLipsumHtml(rand(1, 8)));
            }
            if (rand(0,1)) {
                $case->setCauseOfDeath($this->createLipsumHtml(rand(1, 8)));
            }
            if (rand(0,1)) {
                $case->setComments($this->createLipsumHtml(rand(1, 8)));
            }
            if (rand(0,1)) {
                $case->setNotes($this->createLipsumStr(rand(1, 2)));
            }

            $case->setNotes('***');
            $case->save();
        }

        $db->exec('DELETE FROM `cassette` WHERE `notes` = \'***\' ');
        for($i = 0; $i < 450; $i++) {
            $cassette = new Cassette();
            /** @var PathCase $case */
            $case = PathCaseMap::create()->findAll(Tool::create('RAND()'))->current();
            $cassette->setPathCaseId($case->getId());
            /** @var Storage $storage */
            $storage = StorageMap::create()->findAll(Tool::create('RAND()'))->current();
            $cassette->setStorageId($storage->getId());
            if (rand(0, 1)) {
                //$cassette->setContainer($this->createStr());
            }
            $cassette->setNumber(Cassette::getNextNumber($cassette->getPathCaseId()));
            $cassette->setName($this->createStr());
            $cassette->setQty(rand(1, 9));
            $cassette->setPrice(rand(1, 50) . '.' . rand(0, 99));
            if (rand(0,1)) {
                $cassette->setComments($this->createLipsumHtml(rand(1, 8)));
            }

            $cassette->setNotes('***');
            $cassette->save();
        }

        $db->exec('DELETE FROM `request` WHERE `notes` = \'***\' ');
        for($i = 0; $i < 100; $i++) {
            $request = new Request();
            /** @var PathCase $case */
            $case = PathCaseMap::create()->findAll(Tool::create('RAND()'))->current();
            $request->setPathCaseId($case->getId());
            /** @var Cassette $cassette */
            $cassette = CassetteMap::create()->findFiltered(array('pathCaseId' => $request->getPathCaseId()), Tool::create('RAND()'))->current();
            if (!$cassette) continue;
            $request->setCassetteId($cassette->getId());
            /** @var Service $service */
            $service = ServiceMap::create()->findAll(Tool::create('RAND()'))->current();
            $request->setServiceId($service->getId());
            /** @var Client $client */
            $client = ClientMap::create()->findAll(Tool::create('RAND()'))->current();
            $request->setClientId($client->getId());

            $request->setQty(rand(1, $cassette->getQty()));
            $request->setPrice($service->getPrice()*$request->getQty());
            if (rand(0,1)) {
                $request->setComments($this->createLipsumHtml(rand(1, 8)));
            }

            $request->setNotes('***');
            $request->save();
        }


    }



}
