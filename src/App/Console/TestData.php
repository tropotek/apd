<?php
namespace App\Console;

use App\Db\Address;
use App\Db\AddressMap;
use App\Db\Cassette;
use App\Db\CassetteMap;
use App\Db\Contact;
use App\Db\ContactMap;
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
use Tk\Exception;
use Tk\Money;
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

        if (!$config->getInstitution()) {
            throw new Exception('Have you visited the site and finished the install yet! There are no institutions.');
        }

        $this->write('Institution: ' . $config->getInstitution()->getName());

        if (!$config->isDebug()) {
            $this->writeError('Error: Only run this command in a debug environment.');
            return;
        }

        // Clear DB: Make this a command on it own
        $db->exec('DELETE FROM `user` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `storage` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `service` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `path_case` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `cassette` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `contact` WHERE `notes` = \'***\' ');
        $db->exec('DELETE FROM `request` WHERE `notes` = \'***\' ');




        /** @var \Uni\Db\Institution $institution */
        $institution = $config->getInstitutionMapper()->find(1);

        $db->exec('DELETE FROM `user` WHERE `notes` = \'***\' ');
        for($i = 0; $i < 25; $i++) {
            $user = $config->createUser();
            $user->setInstitutionId($institution->getId());
            $user->setName($this->createFullName());
            $tit = array_keys(\Bs\Db\User::getTitleList());
            $user->setTitle($tit[rand(0, count($tit)-1)]);
            do {
                $user->setUsername(strtolower($this->createName()) . '.' . rand(1000, 10000000));
            } while($config->getUserMapper()->findByUsername($user->getUsername()) != null);
            $user->setEmail($this->createUniqueEmail());
            //$user->setType((rand(1, 10) <= 5) ? \Uni\Db\User::TYPE_STAFF : \Uni\Db\User::TYPE_STUDENT);
            $user->setType(\Uni\Db\User::TYPE_STAFF);
            $user->setCredentials('BVSc, MPhil, MANZCVSc, Dip ACVP');
            $user->setPosition('Senior Lecturer');
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

        //$db->exec('DELETE FROM `contact` WHERE `notes` = \'***\' ');
        $db->exec('TRUNCATE `contact`');
        foreach (Contact::getTypeList() as $type) {
            for($i = 0; $i < 25; $i++) {
                $contact = new Contact();
                //$client->setUserId();     // TODO
                $contact->setUid($this->createStr(6));
                $contact->setType($type);
                $contact->setName($this->createName());
                $contact->setEmail($this->createUniqueEmail());

                $contact->setPhone($this->createStr(10, '1234567890'));
                if (rand(0, 1))
                    $contact->setFax($this->createStr(10, '1234567890'));

                $contact->setStreet($this->createWords(rand(1, 3)));
                $contact->setCity(ucwords($this->createWords(rand(1, 2))));
                $contact->setCountry(ucwords($this->createWords(rand(1, 2))));
                $contact->setState(ucwords($this->createWords(rand(1, 2))));
                $contact->setPostcode($this->createStr(4, '1234567890'));

                $contact->setNotes('***');
                $contact->save();
            }

        }

        //$db->exec('DELETE FROM `storage` WHERE `notes` = \'***\' ');
        $db->exec('TRUNCATE `storage`');
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
            $service->setCost(rand(1, 50) . '.' . rand(0, 99));
            $service->setNotes('***');
            $service->save();
        }


        //$db->exec('DELETE FROM `path_case` WHERE `notes` = \'***\' ');
        $db->exec('TRUNCATE  `path_case` ');
        for($i = 0; $i < 100; $i++) {
            $case = new PathCase();
            /** @var Contact $contact */
            $contact = ContactMap::create()->findAll(Tool::create('RAND()'))->current();
            $case->setClientId($contact->getId());
            $contact = ContactMap::create()->findAll(Tool::create('RAND()'))->current();
            $case->setOwnerId($contact->getId());

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

            $list = ContactMap::create()->findFiltered(array(
                'type' => Contact::TYPE_STUDENT
            ), \Tk\Db\Tool::create('RAND()', rand(1, 3)));
            foreach ($list as $student) {
                $case->addStudent($student);
            }
            $case->setName($this->createFullName() . ' ' . $this->createSpecies() . ' ' . $this->createBreed());

            //$arr = array_values(ObjectUtil::getClassConstants($case, 'STATUS_'));
            //$selected = $arr[rand(0, count($arr)-1)];
            $case->setStatus(PathCase::STATUS_PENDING);
            $case->setReportStatus(rand(0, 1) ? PathCase::REPORT_STATUS_INTERIM : PathCase::REPORT_STATUS_COMPLETED);

            if (rand(0, 1))
                $case->setZoonotic($this->createStr());

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

            if (rand(0, 1)) {
                $case->setBillable(true);
                $case->setCost(Money::create((float)(rand(10, 1500) . '.' . rand(0, 99))));
            }
            $case->setAfterHours((bool)rand(0, 1));

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

        //$db->exec('DELETE FROM `cassette` WHERE `notes` = \'***\' ');
        $db->exec('TRUNCATE `cassette`');
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
            $cassette->setCost(rand(1, 50) . '.' . rand(0, 99));
            if (rand(0,1)) {
                $cassette->setComments($this->createLipsumHtml(rand(1, 8)));
            }

            $cassette->setNotes('***');
            $cassette->save();
        }

        //$db->exec('DELETE FROM `request` WHERE `notes` = \'***\' ');
        $db->exec('TRUNCATE `request`');
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
            /** @var Contact $contact */
            $contact = ContactMap::create()->findAll(Tool::create('RAND()'))->current();
            $request->setClientId($contact->getId());

            $request->setQty(rand(1, $cassette->getQty()));
            $request->setCost($service->getCost()->getAmount()*$request->getQty());
            if (rand(0,1)) {
                $request->setComments($this->createLipsumHtml(rand(1, 8)));
            }

            $request->setNotes('***');
            $request->save();
        }


    }

}
