<?php
namespace App\Db;

use App\Config;
use App\Db\Traits\AnimalTypeTrait;
use App\Db\Traits\ClientTrait;
use App\Db\Traits\OwnerTrait;
use App\Db\Traits\PathologistTrait;
use App\Db\Traits\StorageTrait;
use Bs\Db\Status;
use Bs\Db\Traits\StatusTrait;
use Bs\Db\Traits\TimestampTrait;
use Bs\Db\Traits\UserTrait;
use Bs\Db\UserIface;
use Tk\Db\Tool;
use Tk\Money;
use Uni\Db\Traits\InstitutionTrait;
use Uni\Db\User;

/**
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class PathCase extends \Tk\Db\Map\Model implements \Tk\ValidInterface, \Bs\Db\FileIface
{
    use TimestampTrait;
    use InstitutionTrait;
    use ClientTrait;
    use OwnerTrait;
    use StorageTrait;
    use StatusTrait;
    use UserTrait;
    use PathologistTrait;
    use AnimalTypeTrait;

    // TODO: Check these status's against the actual workflow.
    const STATUS_PENDING                = 'pending';            // Submitted ???
    const STATUS_HOLD                   = 'hold';               // Awaiting review???
    const STATUS_FROZEN_STORAGE         = 'frozenStorage';      //
    const STATUS_EXAMINED               = 'examined';           //
    const STATUS_REPORTED               = 'reported';           //
    const STATUS_COMPLETED              = 'completed';          //
    const STATUS_CANCELLED              = 'cancelled';          // case cancelled

    // Used for the reminder mailTemplateEvent
    const REMINDER_STATUS_DISPOSAL      = 'status.app.pathCase.disposalReminder';
    // Used in the mail_sent table `type` field
    const REMINDER_SENT_TYPE            = 'reminder';

    const TYPE_BIOPSY                   = 'biopsy';
    const TYPE_NECROPSY                 = 'necropsy';

    // TODO: Go ovber these types with stakeholder's b4 release
    const SUBMISSION_INTERNAL_DIAG      = 'internalDiagnostic';
    const SUBMISSION_EXTERNAL_DIAG      = 'externalDiagnostic';
    const SUBMISSION_RESEARCH           = 'research';
    const SUBMISSION_TEACHING           = 'teaching';
    const SUBMISSION_OTHER              = 'other';

    // Report Status
    const REPORT_STATUS_INTERIM         = 'interim';            //
    const REPORT_STATUS_COMPLETED       = 'completed';          //

    // Report Status
    const ACCOUNT_STATUS_PENDING        = 'pending';           //
    const ACCOUNT_STATUS_INVOICED       = 'invoiced';          //
    const ACCOUNT_STATUS_UVET_INVOICED  = 'uvetInvoiced';      //
    const ACCOUNT_STATUS_CANCELLED      = 'cancelled';         //

    // After Care Options
    const AC_GENERAL                    = 'general';
    const AC_CREMATION                  = 'cremation';
    const AC_INCINERATION               = 'incineration';

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;


    /**
     * Staff who created this case
     *
     * @var int
     */
    public $userId = 0;

    /**
     * Submitting Client (the billable client)
     * @var int
     */
    public $clientId = 0;

    /**
     * Animal Owner, (the owner client id)
     * Generally the same as client ID, so pre populate in new cases when this is 0
     * @var int
     */
    public $ownerId = 0;

    /**
     * userId of the pathologist user
     * @var int
     */
    public $pathologistId = 0;


    /**
     * Staff who last edited the secondOpinion text box
     *
     * @var int
     */
    public $soUserId = 0;

    /**
     * @var string
     * @deprecated
     */
    public $resident = '';

    /**
     * Staff only notes
     * @var string
     * @deprecated Not in use for now
     */
    public $name = '';

    /**
     * Pathology Number
     * @var string
     */
    public $pathologyId = '';

    /**
     * BIOPSY, NECROPSY
     * @var string
     */
    public $type = '';

    /**
     * direct client/external vet/Inernal vet/researcher/ Other - Specify
     * @var string
     */
    public $submissionType = '';

    /**
     * Has a submission form been received
     * Flagged to true manually by user when the form is received.
     * @var bool
     */
    public $submissionReceived = false;

    /**
     * Date the case arrived/seen (Generally the same as the created but editable)
     * @var \DateTime
     */
    public $arrival = null;

    /**
     * Pending/frozen storage/examined/reported/awaiting review (if applicable)/completed
     * @var string
     */
    public $status = 'pending';

    /**
     *
     * @var string
     */
    public $reportStatus = 'interim';

    /**
     * @var bool
     */
    public $billable = false;

    /**
     *
     * @var string
     */
    public $accountStatus = '';

    /**
     * This should be a cost for the case billed to the clientId
     * @var Money
     * @deprecated Use PathCase::getInvoiceTotal()
     */
    public $cost = null;


    /**
     * @var bool
     */
    public $afterHours = false;

    /**
     * A description of any risks with the animal
     * @var string
     */
    public $zoonotic = '';

    /**
     * If true then alert user of this info when viewing the case
     * @var bool
     */
    public $zoonoticAlert = false;

    /**
     * Any issues the staff should be alerted to when dealing with this animal
     * @var string
     */
    public $issue = '';

    /**
     * If true then alert user of this info when viewing the case
     * @var bool
     */
    public $issueAlert = false;


    /**
     * Samples count for Biopsies only
     * @var int
     */
    public $bioSamples = 1;

    /**
     * Notes for Biopsies only
     * @var string
     */
    public $bioNotes = '';


    /**
     * Is in fact the animal count (Generally 1 but could be a heard of 10 cattle for example.)
     * @var int
     */
    public $specimenCount = 1;

    /**
     * @var string
     */
    public $ownerName = '';

    /**
     * @var string
     */
    public $animalName = '';

    /**
     * @var int
     */
    public $animalTypeId = 0;

    /**
     * @var string
     */
    public $species = '';

    /**
     * @var string
     */
    public $breed = '';

    /**
     * @var string
     */
    public $sex = '';

    /**
     * @var bool
     */
    public $desexed = false;

    /**
     * @var string
     */
    public $patientNumber = '';

    /**
     * @var string
     */
    public $microchip = '';

    /**
     * @var string
     */
    public $origin = '';

    /**
     * @var string
     */
    public $colour = '';

    /**
     * @var string
     */
    public $weight = '';

    /**
     * @var string
     */
    public $size = '';

    /**
     * Date of birth
     * @var \DateTime
     */
    public $dob = null;

    /**
     * Date and time of death
     * @var \DateTime
     */
    public $dod = null;

    /**
     * @var bool
     */
    public $euthanised = false;

    /**
     * @var string
     */
    public $euthanisedMethod = '';

    /**
     * after care type: General Disposal/cremation/Internal incineration
     * @var string
     */
    public $acType = '';

    /**
     * after care Date to wait until processing animal
     * @var \DateTime
     */
    public $acHold = null;

    /**
     * The current location of the animal (cleared when disposal is completed)
     * @var int
     */
    public $storageId = 0;

    /**
     * @var bool
     */
    public $studentReport = false;

    /**
     * @var \DateTime
     */
    public $disposal = null;

    /**
     * @var string
     */
    public $collectedSamples = '';

    /**
     * @var string
     */
    public $clinicalHistory = '';

    /**
     * @var string
     */
    public $grossPathology = '';

    /**
     * @var string
     */
    public $grossMorphologicalDiagnosis = '';

    /**
     * @var string
     */
    public $histopathology = '';

    /**
     * @var string
     */
    public $ancillaryTesting = '';

    /**
     * @var string
     */
    public $morphologicalDiagnosis = '';

    /**
     * (required) case NOT saved if blank
     * @var string
     */
    public $causeOfDeath = '';

    /**
     * Any notes added after report submitted
     * @var string
     */
    public $addendum = '';

    /**
     * @var string
     */
    public $secondOpinion = '';

    /**
     * public comments
     * @var string
     */
    public $comments = '';

    /**
     * Staff only notes
     * @var string
     */
    public $notes = '';

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;

    /**
     * @var UserIface
     */
    private $_soUser = null;


    /**
     * PathCase
     */
    public function __construct()
    {
        $this->_TimestampTrait();
        $this->setInstitutionId($this->getConfig()->getInstitutionId());
        if ($this->getConfig()->getAuthUser())
            $this->setUserId($this->getConfig()->getAuthUser()->getId());

        $this->arrival = new \DateTime();
        $this->cost = Money::create(0);
    }

    /**
     * @return string[]
     */
    public function __sleep()
    {
        $arr = ['id', 'institutionId', 'userId', 'clientId', 'ownerId', 'soUserId', 'pathologistId', 'resident',
            'name', 'pathologyId', 'type', 'submissionType', 'submissionReceived', 'arrival', 'status',
            'reportStatus', 'billable', 'accountStatus', 'cost', 'afterHours', 'zoonotic', 'zoonoticAlert',
            'issue', 'issueAlert', 'bioSamples', 'bioNotes', 'specimenCount', 'ownerName', 'animalName', 'animalTypeId',
            'species', 'breed', 'sex', 'desexed', 'patientNumber', 'microchip', 'origin', 'colour', 'weight',
            'dob', 'dod', 'euthanised', 'euthanisedMethod', 'acType', 'acHold', 'storageId', 'studentReport',
            'disposal', 'collectedSamples', 'clinicalHistory', 'grossPathology', 'grossMorphologicalDiagnosis',
            'histopathology', 'ancillaryTesting', 'morphologicalDiagnosis', 'causeOfDeath', 'secondOpinion', 'addendum',
            'comments', 'notes', 'modified', 'created'];
        return $arr;
    }

    /**
     * Return the institution setting inst.pathCase.owner.name.only
     * If true then use the pathCase::ownerName otherwise use a contact object for the owner.
     */
    public static function useOwnerObject(): bool
    {
        $inst = Config::getInstance()->getInstitution();
        if (!$inst) return false;
        return !$inst->getData()->get(\App\Controller\Institution\Edit::INSTITUTION_OWNER_NAME_ONLY, false);
    }


    /**
     * @return int
     * @throws \Exception
     */
    public function insert()
    {
        if (!$this->getPathologyId()) {
            $this->setPathologyId($this->getVolatilePathologyId());
        }
        return parent::insert();
    }

    /**
     * @param Tool|null $tool
     * @return File[]|\Tk\Db\Map\ArrayObject
     * @throws \Exception
     */
    public function getFileList(string $label = '', ?\Tk\Db\Tool $tool = null)
    {
        $filter = ['model' => $this];
        if ($label) $filter['label'] = $label;
        $list = FileMap::create()->findFiltered($filter, $tool);
        return $list;
    }

    /**
     * @param Tool|null $tool
     * @return File[]|\Tk\Db\Map\ArrayObject
     * @throws \Exception
     */
    public function getSelectedFileList(string $label = '', ?\Tk\Db\Tool $tool = null)
    {
        $filter = ['model' => $this, 'selected' => true];
        if ($label) $filter['label'] = $label;
        $list = FileMap::create()->findFiltered($filter, $tool);
        return $list;
    }

    /**
     * @param Tool|null $tool
     * @return File[]|\Tk\Db\Map\ArrayObject
     * @throws \Exception
     * @deprecated Use getFileList()
     */
    public function getFiles(Tool $tool = null)
    {
        return $this->getFileList('', $tool);
    }

    /**
     * @param Tool|null $tool
     * @return File[]|\Tk\Db\Map\ArrayObject
     * @throws \Exception
     */
    public function getPdfFiles(Tool $tool = null)
    {
        $list = FileMap::create()->findFiltered(array('model' => $this, 'active' => true), $tool);
        return $list;
    }

    /**
     * Get the Case root file folder, all content related to the case must be stored in here
     *
     * @return string
     * @throws \Exception
     */
    public function getDataPath()
    {
        // TODO: we need to implement this to reduce folder list sizes, but first write a script to:
        //       - move all existing files in data folder (use path_case.created year
        //       - update any file paths in the DB file, path_case content fields
        //

//        $path = sprintf( '/pathCase/%s/%s', $this->getCreated('Y'), $this->getVolatileId());
//        if ($this->getInstitution()) {
//            $path = sprintf('%s%s', $this->getInstitution()->getDataPath(), $path);
//        }
//        return $path;


        if ($this->getInstitution()) {
            return sprintf('%s/pathCase/%s', $this->getInstitution()->getDataPath(), $this->getVolatileId());
        }
        return sprintf('pathCase/%s', $this->getVolatileId());
    }


    public function getReportPdfFilename(): string
    {
        $animalName = '';
        if ($this->getAnimalName()) {
            $animalName = '_' . preg_replace('/[^a-z0-9-]/i', '', $this->getAnimalName());
        }
        $ownerName = '';
        if ($this->getPatientNumber()) {
            $ownerName = '_' . preg_replace('/[^a-z0-9-]/i', '', $this->getPatientNumber());
        } elseif ($this->getOwnerName()) {
            $ownerName = '_' . preg_replace('/[^a-z0-9-]/i', '', $this->getOwnerName());
        }
        $interimStatus = '';
        if ($this->getReportStatus() == PathCase::REPORT_STATUS_INTERIM)
            $interimStatus = '_' . PathCase::REPORT_STATUS_INTERIM;
        return sprintf('PathologyResults_%s%s%s%s.pdf',
            $this->getPathologyId(),
            $animalName,
            $ownerName,
            $interimStatus
        );
    }

    public function getCasePdfFilename(): string
    {
        $animalName = '';
        if ($this->getAnimalName()) {
            $animalName = '_' . preg_replace('/[^a-z0-9-]/i', '', $this->getAnimalName());
        }
        $ownerName = '';
        if ($this->getPatientNumber()) {
            $ownerName = '_' . preg_replace('/[^a-z0-9-]/i', '', $this->getPatientNumber());
        } elseif ($this->getOwnerName()) {
            $ownerName = '_' . preg_replace('/[^a-z0-9-]/i', '', $this->getOwnerName());
        }
        $interimStatus = '';
        if ($this->getReportStatus() == PathCase::REPORT_STATUS_INTERIM)
            $interimStatus = '_' . PathCase::REPORT_STATUS_INTERIM;
        return sprintf('PathologyCase_%s%s%s%s.pdf',
            $this->getPathologyId(),
            $animalName,
            $ownerName,
            $interimStatus
        );
    }

    public function getInvoiceTotal(): Money
    {
        $value = 0;
        $items = InvoiceItemMap::create()->findFiltered(['pathCaseId' => $this->getVolatileId()]);
        foreach ($items as $item) {
            $value += $item->getTotal()->getAmount();
        }
        return Money::create($value);
    }

    /**
     * @return int
     */
    public function getSoUserId(): int
    {
        return $this->soUserId;
    }

    /**
     * @param int|UserIface $soUserId
     * @return PathCase
     */
    public function setSoUserId($soUserId): PathCase
    {
        if ($soUserId instanceof UserIface) $soUserId = $soUserId->getId();
        $this->soUserId = $soUserId;
        return $this;
    }

    /**
     * @return UserIface|\Tk\Db\Map\Model|\Tk\Db\ModelInterface|null
     * @throws \Exception
     */
    public function getSoUser()
    {
        if (!$this->_soUser)
            $this->_soUser = $this->getConfig()->getUserMapper()->find($this->getSoUserId());
        return $this->_soUser;
    }


    /**
     * @param string $pathologyId
     * @return PathCase
     */
    public function setPathologyId($pathologyId) : PathCase
    {
        $this->pathologyId = $pathologyId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPathologyId() : string
    {
        return $this->pathologyId;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getVolatilePathologyId() : string
    {
        if (!$this->getInstitutionId())
            throw new \Tk\Exception('No institutionId set.');
        if ($this->getPathologyId())
            return $this->getPathologyId();

        $y = date('y');
        $str = '1-'.$y;
        $last = PathCaseMap::create()->findFiltered(array(
            'institutionId' => $this->getInstitutionId()
        ), Tool::create(' b.path_year DESC, b.path_no DESC', 1))->current();
        if ($last) {
            $pidArr = explode('-', $last->getPathologyId());
            $i = 1;
            do {
                if (count($pidArr) >= 2 && (int)$pidArr[1] && $y == (int)$pidArr[1]) {
                    $str = sprintf('%d-%s', (int)$pidArr[0] + $i, $y);
                }
                $found = PathCaseMap::create()->findFiltered(array(
                    'institutionId' => $this->getInstitutionId(),
                    'pathologyId' => $str
                ), Tool::create('', 1))->current();
                $i++;
            } while ($found);
        }
        return $str;
    }

    /**
     * @return bool
     */
    public function isBillable(): bool
    {
        return $this->billable;
    }

    /**
     * @param bool $billable
     * @return PathCase
     */
    public function setBillable(bool $billable): PathCase
    {
        $this->billable = $billable;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountStatus(): string
    {
        return $this->accountStatus;
    }

    /**
     * @param string $accountStatus
     * @return PathCase
     */
    public function setAccountStatus(string $accountStatus): PathCase
    {
        $this->accountStatus = $accountStatus;
        return $this;
    }

    /**
     * @param Money|float $cost
     * @return PathCase
     * @deprecated Use PathCase::getInvoiceTotal()
     */
    public function setCost($cost) : PathCase
    {
        if (!is_object($cost)) {
            $cost = Money::create((float)$cost);
        }
        $this->cost = $cost;
        return $this;
    }

    /**
     * @return Money
     * @deprecated Use PathCase::getInvoiceTotal()
     */
    public function getCost() : Money
    {
        return $this->cost;
    }

    /**
     * @return bool
     */
    public function isAfterHours(): bool
    {
        return $this->afterHours;
    }

    /**
     * @param bool $afterHours
     * @return PathCase
     */
    public function setAfterHours(bool $afterHours): PathCase
    {
        $this->afterHours = $afterHours;
        return $this;
    }

    /**
     * @param string $type
     * @return PathCase
     */
    public function setType($type) : PathCase
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() : ?string
    {
        return $this->type;
    }

    /**
     * @param string $submissionType
     * @return PathCase
     */
    public function setSubmissionType($submissionType) : PathCase
    {
        $this->submissionType = $submissionType;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubmissionType() : string
    {
        return $this->submissionType;
    }

    /**
     * @return bool
     */
    public function isSubmissionReceived(): bool
    {
        return $this->submissionReceived;
    }

    /**
     * @param bool $submissionReceived
     * @return PathCase
     */
    public function setSubmissionReceived(bool $submissionReceived): PathCase
    {
        $this->submissionReceived = $submissionReceived;
        return $this;
    }

    /**
     * @param null|string $format
     * @return \DateTime|string
     */
    public function getArrival($format = null)
    {
        if ($format && $this->arrival)
            return $this->arrival->format($format);
        return $this->arrival;
    }

    /**
     * @param \DateTime $arrival
     * @return PathCase
     */
    public function setArrival(\DateTime $arrival): PathCase
    {
        $this->arrival = $arrival;
        return $this;
    }

    /**
     * @param string $zoonotic
     * @return PathCase
     */
    public function setZoonotic($zoonotic) : PathCase
    {
        $this->zoonotic = $zoonotic;
        return $this;
    }

    /**
     * @return string
     */
    public function getZoonotic() : string
    {
        return $this->zoonotic;
    }

    /**
     * @return bool
     */
    public function isZoonoticAlert(): bool
    {
        return $this->zoonoticAlert;
    }

    /**
     * @param bool $zoonoticAlert
     * @return PathCase
     */
    public function setZoonoticAlert(bool $zoonoticAlert): PathCase
    {
        $this->zoonoticAlert = $zoonoticAlert;
        return $this;
    }

    /**
     * @return string
     */
    public function getIssue(): string
    {
        return $this->issue;
    }

    /**
     * @param string $issue
     * @return PathCase
     */
    public function setIssue(string $issue): PathCase
    {
        $this->issue = $issue;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIssueAlert(): bool
    {
        return $this->issueAlert;
    }

    /**
     * @param bool $issueAlert
     * @return PathCase
     */
    public function setIssueAlert(bool $issueAlert): PathCase
    {
        $this->issueAlert = $issueAlert;
        return $this;
    }

    /**
     * @return int
     */
    public function getBioSamples()
    {
        return $this->bioSamples;
    }

    /**
     * @param int $bioSamples
     * @return PathCase
     */
    public function setBioSamples($bioSamples): PathCase
    {
        $this->bioSamples = $bioSamples;
        return $this;
    }

    /**
     * @return string
     */
    public function getBioNotes(): string
    {
        return $this->bioNotes;
    }

    /**
     * @param string $bioNotes
     * @return PathCase
     */
    public function setBioNotes(string $bioNotes): PathCase
    {
        $this->bioNotes = $bioNotes;
        return $this;
    }

    /**
     * @param int $specimenCount
     * @return PathCase
     */
    public function setSpecimenCount($specimenCount) : PathCase
    {
        $this->specimenCount = $specimenCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getSpecimenCount() : int
    {
        return $this->specimenCount;
    }

    /**
     * @param string $animalName
     * @return PathCase
     */
    public function setAnimalName($animalName) : PathCase
    {
        $this->animalName = $animalName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAnimalName() : string
    {
        return $this->animalName;
    }

    /**
     * @param string $species
     * @return PathCase
     */
    public function setSpecies($species) : PathCase
    {
        $this->species = $species;
        return $this;
    }

    /**
     * @return string
     */
    public function getSpecies() : string
    {
        return $this->species;
    }

    /**
     * @param string $breed
     * @return PathCase
     */
    public function setBreed($breed) : PathCase
    {
        $this->breed = $breed;
        return $this;
    }

    /**
     * @return string
     */
    public function getBreed() : string
    {
        return $this->breed;
    }

    /**
     * @param string $sex
     * @return PathCase
     */
    public function setSex($sex) : PathCase
    {
        $this->sex = $sex;
        return $this;
    }

    /**
     * @return string
     */
    public function getSex() : string
    {
        return $this->sex;
    }

    /**
     * @param bool $desexed
     * @return PathCase
     */
    public function setDesexed($desexed) : PathCase
    {
        $this->desexed = $desexed;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDesexed() : bool
    {
        return $this->desexed;
    }

    /**
     * @param string $patientNumber
     * @return PathCase
     */
    public function setPatientNumber($patientNumber) : PathCase
    {
        $this->patientNumber = $patientNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getPatientNumber() : string
    {
        return $this->patientNumber;
    }

    /**
     * @param string $microchip
     * @return PathCase
     */
    public function setMicrochip($microchip) : PathCase
    {
        $this->microchip = $microchip;
        return $this;
    }

    /**
     * @return string
     */
    public function getMicrochip() : string
    {
        return $this->microchip;
    }

    public function getOwnerName(): string
    {
        return $this->ownerName;
    }

    public function setOwnerName(string $ownerName) : PathCase
    {
        $this->ownerName = $ownerName;
        return $this;
    }

    /**
     * @param string $origin
     * @return PathCase
     */
    public function setOrigin($origin) : PathCase
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin() : string
    {
        return $this->origin;
    }

    /**
     * @return string
     */
    public function getColour(): string
    {
        return $this->colour;
    }

    /**
     * @param string $colour
     * @return PathCase
     */
    public function setColour(string $colour): PathCase
    {
        $this->colour = $colour;
        return $this;
    }

    /**
     * @param string $weight
     * @return PathCase
     */
    public function setWeight($weight) : PathCase
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return string
     */
    public function getWeight() : string
    {
        return $this->weight;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $size
     * @return PathCase
     */
    public function setSize($size): PathCase
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param \DateTime $dob
     * @return PathCase
     */
    public function setDob($dob) : PathCase
    {
        $this->dob = $dob;
        return $this;
    }

    /**
     * @param null|string $format   If supplied then a string of the formatted date is returned
     * @return \DateTime|string
     */
    public function getDob($format = null)
    {
        if ($format && $this->dob)
            return $this->dob->format($format);
        return $this->dob;
    }

    /**
     * @param \DateTime $dod
     * @return PathCase
     */
    public function setDod($dod) : PathCase
    {
        $this->dod = $dod;
        return $this;
    }

    /**
     * @param null|string $format   If supplied then a string of the formatted date is returned
     * @return \DateTime|string
     */
    public function getDod($format = null)
    {
        if ($format && $this->dod)
            return $this->dod->format($format);
        return $this->dod;
    }

    /**
     * return the age years component
     * @return int
     */
    public function getAge()
    {
        $age = 0;
        if ($this->getDob()) {
            if (property_exists($this, 'age')) {
                $age = $this->age;
            } else {
                $dod = \Tk\Date::create();
                if ($this->getDod())
                    $dod = $this->getDod();
                $age = $this->getDob()->diff($dod)->y;
            }
        }
        return $age;
    }

    /**
     * return the age months component excluding the years
     * @return int
     */
    public function getAgeMonths()
    {
        $age_m = 0;
        if ($this->getDob()) {
            if (property_exists($this, 'age_m')) {
                $age_m = $this->age_m;
            } else {
                $dod = \Tk\Date::create();
                if ($this->getDod())
                    $dod = $this->getDod();
                $age_m = $this->getDob()->diff($dod)->m;
            }
        }
        return $age_m;
    }


    /**
     * @param bool $euthanised
     * @return PathCase
     */
    public function setEuthanised($euthanised) : PathCase
    {
        $this->euthanised = $euthanised;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEuthanised() : bool
    {
        return $this->euthanised;
    }

    /**
     * @param string $euthanisedMethod
     * @return PathCase
     */
    public function setEuthanisedMethod($euthanisedMethod) : PathCase
    {
        $this->euthanisedMethod = $euthanisedMethod;
        return $this;
    }

    /**
     * @return string
     */
    public function getEuthanisedMethod() : string
    {
        return $this->euthanisedMethod;
    }

    /**
     * @param string $acType
     * @return PathCase
     */
    public function setAcType($acType) : PathCase
    {
        $this->acType = $acType;
        return $this;
    }

    /**
     * @return string
     */
    public function getAcType() : string
    {
        return $this->acType;
    }

    /**
     * @param \DateTime $acHold
     * @return PathCase
     */
    public function setAcHold($acHold) : PathCase
    {
        $this->acHold = $acHold;
        return $this;
    }

    /**
     * @param null|string $format   If supplied then a string of the formatted date is returned
     * @return \DateTime|string
     */
    public function getAcHold($format = null)
    {
        if ($format && $this->acHold)
            return $this->acHold->format($format);
        return $this->acHold;
    }

    /**
     * @param \DateTime $disposal
     * @return PathCase
     */
    public function setDisposal($disposal) : PathCase
    {
        $this->disposal = $disposal;
        return $this;
    }

    /**
     * @param null|string $format   If supplied then a string of the formatted date is returned
     * @return \DateTime|string
     */
    public function getDisposal($format = null)
    {
        if ($format && $this->disposal)
            return $this->disposal->format($format);
        return $this->disposal;
    }

    /**
     * @return string
     */
    public function getResident(): string
    {
        return $this->resident;
    }

    /**
     * @param string $resident
     * @return PathCase
     */
    public function setResident(string $resident): PathCase
    {
        $this->resident = $resident;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStudentReport(): bool
    {
        return $this->studentReport;
    }

    /**
     * @param bool $studentReport
     * @return PathCase
     */
    public function setStudentReport(bool $studentReport): PathCase
    {
        $this->studentReport = $studentReport;
        return $this;
    }

    /**
     * @param null|\Tk\Db\Tool $tool
     * @return array|\Tk\Db\Map\ArrayObject|Student[]
     * @throws \Exception
     */
    public function getStudentList($tool = null)
    {
        return StudentMap::create()->findFiltered([
            'pathCaseId' => $this->getVolatileId()
        ], $tool);
    }

    /**
     * @param int|Contact $contactId
     * @return PathCase
     */
    public function addStudent($contactId): PathCase
    {
        if ($contactId instanceof Contact) $contactId = $contactId->getVolatileId();
        PathCaseMap::create()->addContact($this->getVolatileId(), $contactId);
        return $this;
    }

    /**
     * @return string
     */
    public function getReportStatus(): string
    {
        return $this->reportStatus;
    }

    /**
     * @param string $reportStatus
     * @return PathCase
     */
    public function setReportStatus(string $reportStatus): PathCase
    {
        $this->reportStatus = $reportStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollectedSamples(): string
    {
        return $this->collectedSamples;
    }

    /**
     * @param string $collectedSamples
     * @return PathCase
     */
    public function setCollectedSamples(string $collectedSamples): PathCase
    {
        $this->collectedSamples = $collectedSamples;
        return $this;
    }

    /**
     * @param string $clinicalHistory
     * @return PathCase
     */
    public function setClinicalHistory($clinicalHistory) : PathCase
    {
        $this->clinicalHistory = $clinicalHistory;
        return $this;
    }

    /**
     * @return string
     */
    public function getClinicalHistory() : string
    {
        return $this->clinicalHistory;
    }

    /**
     * @param string $grossPathology
     * @return PathCase
     */
    public function setGrossPathology($grossPathology) : PathCase
    {
        $this->grossPathology = $grossPathology;
        return $this;
    }

    /**
     * @return string
     */
    public function getGrossPathology() : string
    {
        return $this->grossPathology;
    }

    /**
     * @param string $grossMorphologicalDiagnosis
     * @return PathCase
     */
    public function setGrossMorphologicalDiagnosis($grossMorphologicalDiagnosis) : PathCase
    {
        $this->grossMorphologicalDiagnosis = $grossMorphologicalDiagnosis;
        return $this;
    }

    /**
     * @return string
     */
    public function getGrossMorphologicalDiagnosis() : string
    {
        return $this->grossMorphologicalDiagnosis;
    }

    /**
     * @param string $histopathology
     * @return PathCase
     */
    public function setHistopathology($histopathology) : PathCase
    {
        $this->histopathology = $histopathology;
        return $this;
    }

    /**
     * @return string
     */
    public function getHistopathology() : string
    {
        return $this->histopathology;
    }

    /**
     * @param string $ancillaryTesting
     * @return PathCase
     */
    public function setAncillaryTesting($ancillaryTesting) : PathCase
    {
        $this->ancillaryTesting = $ancillaryTesting;
        return $this;
    }

    /**
     * @return string
     */
    public function getAncillaryTesting() : string
    {
        return $this->ancillaryTesting;
    }

    /**
     * @param string $morphologicalDiagnosis
     * @return PathCase
     */
    public function setMorphologicalDiagnosis($morphologicalDiagnosis) : PathCase
    {
        $this->morphologicalDiagnosis = $morphologicalDiagnosis;
        return $this;
    }

    /**
     * @return string
     */
    public function getMorphologicalDiagnosis() : string
    {
        return $this->morphologicalDiagnosis;
    }

    /**
     * @param string $causeOfDeath
     * @return PathCase
     */
    public function setCauseOfDeath($causeOfDeath) : PathCase
    {
        $this->causeOfDeath = $causeOfDeath;
        return $this;
    }

    /**
     * @return string
     */
    public function getCauseOfDeath() : string
    {
        return $this->causeOfDeath;
    }

    /**
     * @return string
     */
    public function getSecondOpinion()
    {
        return $this->secondOpinion;
    }

    /**
     * @param string $secondOpinion
     * @return PathCase
     */
    public function setSecondOpinion($secondOpinion): PathCase
    {
        $this->secondOpinion = $secondOpinion;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddendum(): string
    {
        return $this->addendum;
    }

    /**
     * @param string $addendum
     * @return PathCase
     */
    public function setAddendum(string $addendum): PathCase
    {
        $this->addendum = $addendum;
        return $this;
    }

    /**
     * @param string $comments
     * @return PathCase
     */
    public function setComments($comments) : PathCase
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return string
     */
    public function getComments() : string
    {
        return $this->comments;
    }

    /**
     * @param string $notes
     * @return PathCase
     */
    public function setNotes($notes) : PathCase
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotes() : string
    {
        return $this->notes;
    }

    /**
     * Is this case editable by this user
     * IE: Only users flagged as CASE_FULL_EDIT's can edit a case after it is completed
     *
     * @param User $user
     */
    public function isEditable(User $user)
    {
        if (!$user->hasPermission(Permission::CASE_FULL_EDIT) && $this->hasStatus(self::STATUS_COMPLETED)) {
            return false;
        }
        return true;
    }

    /**
     * Return true if this case has been invoiced
     *
     * @return bool
     */
    public function isBilled()
    {
        if (!$this->isBillable()) return true;      // Do this so that non-billable cases are marked readonly
        return ($this->getAccountStatus() == self::ACCOUNT_STATUS_INVOICED || $this->getAccountStatus() == self::ACCOUNT_STATUS_UVET_INVOICED);
    }

    /**
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->institutionId) {
            $errors['institutionId'] = 'Invalid value: institutionId';
        }

        if (!$this->pathologyId) {
            $errors['pathologyId'] = 'Invalid value: pathologyId';
        } else {
            $found = PathCaseMap::create()->findFiltered(array(
                'institutionId' => $this->getInstitutionId(),
                'pathologyId' => $this->pathologyId
            ), Tool::create('', 1))->current();
            if ($found && $found->getId() != $this->getId()) {
                $errors['pathologyId'] = 'Case already exists with pathologyId: ' . $this->getPathologyId();
            }
        }
        if (!$this->clientId) {
            $errors['clientId'] = 'Invalid value: clientId';
        }
        if ($this->isBillable() && !$this->getAccountStatus()) {
            $errors['accountStatus'] = 'Please enter a valid account status';
        }

        if (!$this->type) {
            $errors['type'] = 'Invalid value: type';
        }

        if (!$this->submissionType) {
            $errors['submissionType'] = 'Invalid value: submissionType';
        }

        if (!$this->status) {
            $errors['status'] = 'Invalid value: status';
        }

        return $errors;
    }

    public function hasStatusChanged(Status $status)
    {
        $prevStatusName = $status->getPreviousName();
        switch ($status->getName()) {
            case PathCase::STATUS_PENDING:
                if (!$prevStatusName || PathCase::STATUS_HOLD == $prevStatusName)
                    return true;
                break;
            case PathCase::STATUS_EXAMINED:
                if (!$prevStatusName || PathCase::STATUS_PENDING == $prevStatusName || PathCase::STATUS_HOLD == $prevStatusName)
                    return true;
                break;
            case PathCase::STATUS_REPORTED:
                if (PathCase::STATUS_EXAMINED == $prevStatusName || PathCase::STATUS_PENDING == $prevStatusName )
                    return true;
                break;
            case PathCase::STATUS_COMPLETED:
                if (PathCase::STATUS_PENDING == $prevStatusName || PathCase::STATUS_REPORTED == $prevStatusName || PathCase::STATUS_EXAMINED == $prevStatusName)
                    return true;
                break;
            case PathCase::STATUS_HOLD:
            case PathCase::STATUS_FROZEN_STORAGE:
            case Request::STATUS_CANCELLED:
                return true;
        }
        return false;
    }
}
