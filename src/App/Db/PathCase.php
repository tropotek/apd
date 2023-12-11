<?php
namespace App\Db;

use App\Db\Traits\AnimalTypeTrait;
use App\Db\Traits\CompanyTrait;
use App\Db\Traits\OwnerTrait;
use App\Db\Traits\PathologistTrait;
use App\Db\Traits\StorageTrait;
use Bs\Db\Status;
use Bs\Db\Traits\StatusTrait;
use Bs\Db\Traits\TimestampTrait;
use Bs\Db\Traits\UserTrait;
use Bs\Db\UserIface;
use Tk\Db\Map\ArrayObject;
use Tk\Db\Tool;
use Tk\Money;
use Uni\Db\Traits\InstitutionTrait;
use Uni\Db\User;
use Uni\Db\UserMap;


class PathCase extends \Tk\Db\Map\Model implements \Tk\ValidInterface, \Bs\Db\FileIface
{
    use TimestampTrait;
    use InstitutionTrait;
    use CompanyTrait;
    use StorageTrait;
    use StatusTrait;
    use UserTrait;
    use PathologistTrait;
    use AnimalTypeTrait;


    const STATUS_PENDING                = 'pending';            // Submitted ???
    const STATUS_HOLD                   = 'hold';               // Awaiting review???
    const STATUS_FROZEN_STORAGE         = 'frozenStorage';      //
    const STATUS_EXAMINED               = 'examined';           //
    const STATUS_REPORTED               = 'reported';           //
    const STATUS_COMPLETED              = 'completed';          //
    const STATUS_CANCELLED              = 'cancelled';          // case cancelled


    // Used for the reminder mailTemplateEvent
    const REMINDER_STATUS_DISPOSAL         = 'status.app.pathCase.disposalReminder';
    const REMINDER_NECROPSY_COMPLETE_CASE  = 'status.app.pathCase.necropsyCompleteCase';
    const REMINDER_BIOPSY_COMPLETE_REPORT  = 'status.app.pathCase.biopsyCompleteReport';

    const TYPE_BIOPSY                   = 'biopsy';
    const TYPE_NECROPSY                 = 'necropsy';

    const SUBMISSION_INTERNAL_DIAG      = 'internalDiagnostic';
    const SUBMISSION_EXTERNAL_DIAG      = 'externalDiagnostic';
    const SUBMISSION_RESEARCH           = 'research';
    const SUBMISSION_TEACHING           = 'teaching';
    const SUBMISSION_OTHER              = 'other';

    // Report Status
    const REPORT_STATUS_INTERIM         = 'interim';
    const REPORT_STATUS_COMPLETED       = 'completed';

    // Report Status
    const ACCOUNT_STATUS_PENDING        = 'pending';
    const ACCOUNT_STATUS_INVOICED       = 'invoiced';
    const ACCOUNT_STATUS_UVET_INVOICED  = 'uvetInvoiced';
    const ACCOUNT_STATUS_CANCELLED      = 'cancelled';

    const ACCOUNT_STATUS_LIST = [
        'Pending'       => self::ACCOUNT_STATUS_PENDING,
        'Invoiced'      => self::ACCOUNT_STATUS_INVOICED,
        'uVet Invoiced' => self::ACCOUNT_STATUS_UVET_INVOICED,
        'Cancelled'     => self::ACCOUNT_STATUS_CANCELLED,
    ];

    // After Care Options
    const DISPOSAL_METHOD_GENERAL        = 'general';
    const DISPOSAL_METHOD_CREMATION      = 'cremation';
    const DISPOSAL_METHOD_INCINERATION   = 'incineration';

    const DISPOSAL_METHOD_LIST = [
        'General'       => self::DISPOSAL_METHOD_GENERAL,
        'Cremation'     => self::DISPOSAL_METHOD_CREMATION,
        'Incineration'  => self::DISPOSAL_METHOD_INCINERATION,
    ];


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
     * @var int
     */
    public $userId = 0;

    /**
     * Submitting Client (the billable client)
     * @var null|int
     */
    public $companyId = null;

    /**
     * userId of the pathologist user
     * @var int
     */
    public $pathologistId = 0;

    /**
     * Staff who last edited the secondOpinion text box
     * @var int
     */
    public $soUserId = 0;

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
     * direct client/external vet/Internal vet/researcher/ Other - Specify
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
     * @var null|\DateTime
     */
    public $arrival = null;

    /**
     * Case services completed on (Necropsies, histologies)
     * @var null|\DateTime
     */
    public $servicesCompletedOn = null;

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
     * @var null|string
     */
    public $accountStatus = null;

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
     * @var null|string
     */
    public $issue = null;

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
     * @var null|\DateTime
     */
    public $dob = null;

    /**
     * Date and time of death
     * @var null|\DateTime
     */
    public $dod = null;

    /**
     * @var bool
     */
    public $euthanised = false;

    /**
     * @var null|string
     */
    public $euthanisedMethod = null;

    /**
     * after care type: General Disposal/cremation/Internal incineration
     * @var null|string
     */
    public $disposeMethod = '';

    /**
     * after care Date to wait until processing animal
     * @var null|\DateTime
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
     * @var null|\DateTime
     */
    public $disposeOn = null;

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
     * @var null|int
     */
    public $reviewedById = null;

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
    public $modified;

    /**
     * @var \DateTime
     */
    public $created;

    /**
     * @var UserIface|null
     */
    private $_soUser = null;



    public function __construct()
    {
        $this->_TimestampTrait();
        $this->setInstitutionId($this->getConfig()->getInstitutionId());
        if ($this->getConfig()->getAuthUser())
            $this->setUserId($this->getConfig()->getAuthUser()->getId());
        $this->arrival = new \DateTime();
    }

    /**
     * @return string[]
     */
    public function __sleep()
    {
        $arr = ['id', 'institutionId', 'userId', 'companyId', 'soUserId', 'pathologistId',
            'pathologyId', 'type', 'submissionType', 'submissionReceived', 'arrival', 'status',
            'reportStatus', 'billable', 'accountStatus', 'afterHours', 'zoonotic', 'zoonoticAlert',
            'issue', 'issueAlert', 'bioSamples', 'bioNotes', 'specimenCount', 'ownerName', 'animalName', 'animalTypeId',
            'species', 'breed', 'sex', 'desexed', 'patientNumber', 'microchip', 'origin', 'colour', 'weight',
            'dob', 'dod', 'euthanised', 'euthanisedMethod', 'disposeMethod', 'acHold', 'storageId', 'studentReport',
            'disposeOn', 'collectedSamples', 'clinicalHistory', 'grossPathology', 'grossMorphologicalDiagnosis',
            'histopathology', 'ancillaryTesting', 'morphologicalDiagnosis', 'causeOfDeath', 'secondOpinion', 'addendum',
            'comments', 'notes', 'modified', 'created'];
        return $arr;
    }

    public function insert(): int
    {
        if (!$this->getPathologyId()) {
            $this->setPathologyId($this->getVolatilePathologyId());
        }
        return parent::insert();
    }

    public function getFileList(string $label = '', ?\Tk\Db\Tool $tool = null): ArrayObject
    {
        $filter = ['model' => $this];
        if ($label) $filter['label'] = $label;
        return FileMap::create()->findFiltered($filter, $tool);
    }

    public function getSelectedFileList(string $label = '', ?\Tk\Db\Tool $tool = null): ArrayObject
    {
        $filter = ['model' => $this, 'selected' => true];
        if ($label) $filter['label'] = $label;
        return FileMap::create()->findFiltered($filter, $tool);
    }

    public function getPdfFiles(?Tool $tool = null): ArrayObject
    {
        return FileMap::create()->findFiltered(array('model' => $this, 'active' => true), $tool);
    }

    /**
     * Get the Case root file folder, all content related to the case must be stored in here
     */
    public function getDataPath(): string
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

    public function getSoUserId(): int
    {
        return $this->soUserId;
    }

    /**
     * @param int|UserIface $soUserId
     */
    public function setSoUserId($soUserId): PathCase
    {
        if ($soUserId instanceof UserIface) $soUserId = $soUserId->getId();
        $this->soUserId = $soUserId;
        return $this;
    }

    /**
     * @return UserIface|\Tk\Db\Map\Model|\Tk\Db\ModelInterface|null
     */
    public function getSoUser()
    {
        if (!$this->_soUser)
            $this->_soUser = $this->getConfig()->getUserMapper()->find($this->getSoUserId());
        return $this->_soUser;
    }

    public function setPathologyId(?string $pathologyId) : PathCase
    {
        $this->pathologyId = $pathologyId;
        return $this;
    }

    public function getPathologyId() : string
    {
        return $this->pathologyId;
    }

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

    public function isBillable(): bool
    {
        return $this->billable;
    }

    public function setBillable(bool $billable): PathCase
    {
        $this->billable = $billable;
        return $this;
    }

    public function getAccountStatus(): string
    {
        return $this->accountStatus;
    }

    public function setAccountStatus(string $accountStatus): PathCase
    {
        $this->accountStatus = $accountStatus;
        return $this;
    }

    public function isAfterHours(): bool
    {
        return $this->afterHours;
    }

    public function setAfterHours(bool $afterHours): PathCase
    {
        $this->afterHours = $afterHours;
        return $this;
    }

    public function setType(?string $type) : PathCase
    {
        $this->type = $type;
        return $this;
    }

    public function getType() : ?string
    {
        return $this->type;
    }

    public function setSubmissionType(?string $submissionType) : PathCase
    {
        $this->submissionType = $submissionType;
        return $this;
    }

    public function getSubmissionType() : string
    {
        return $this->submissionType;
    }

    public function isSubmissionReceived(): bool
    {
        return $this->submissionReceived;
    }

    public function setSubmissionReceived(bool $submissionReceived): PathCase
    {
        $this->submissionReceived = $submissionReceived;
        return $this;
    }

    /**
     * @return \DateTime|string
     */
    public function getArrival(?string $format = null)
    {
        if ($format && $this->arrival)
            return $this->arrival->format($format);
        return $this->arrival;
    }

    public function setArrival(\DateTime $arrival): PathCase
    {
        $this->arrival = $arrival;
        return $this;
    }

    /**
     * @return \DateTime|string
     */
    public function getServicesCompletedOn(?string $format = null): ?\DateTime
    {
        if ($format && $this->servicesCompletedOn)
            return $this->servicesCompletedOn->format($format);
        return $this->servicesCompletedOn;
    }

    public function setServicesCompletedOn(?\DateTime $servicesCompletedOn): PathCase
    {
        $this->servicesCompletedOn = $servicesCompletedOn;
        return $this;
    }

    public function setZoonotic(?string $zoonotic) : PathCase
    {
        $this->zoonotic = $zoonotic;
        return $this;
    }

    public function getZoonotic() : string
    {
        return $this->zoonotic;
    }

    public function isZoonoticAlert(): bool
    {
        return $this->zoonoticAlert;
    }

    public function setZoonoticAlert(bool $zoonoticAlert): PathCase
    {
        $this->zoonoticAlert = $zoonoticAlert;
        return $this;
    }

    public function getIssue(): string
    {
        return $this->issue;
    }

    public function setIssue(?string $issue): PathCase
    {
        $this->issue = $issue;
        return $this;
    }

    public function isIssueAlert(): bool
    {
        return $this->issueAlert;
    }

    public function setIssueAlert(bool $issueAlert): PathCase
    {
        $this->issueAlert = $issueAlert;
        return $this;
    }

    public function getBioSamples(): int
    {
        return $this->bioSamples;
    }

    public function setBioSamples(?int $bioSamples): PathCase
    {
        $this->bioSamples = $bioSamples;
        return $this;
    }

    public function getBioNotes(): string
    {
        return $this->bioNotes;
    }

    public function setBioNotes(?string $bioNotes): PathCase
    {
        $this->bioNotes = $bioNotes;
        return $this;
    }

    public function setSpecimenCount(?int $specimenCount) : PathCase
    {
        $this->specimenCount = $specimenCount;
        return $this;
    }

    public function getSpecimenCount() : int
    {
        return $this->specimenCount;
    }

    public function setAnimalName(?string $animalName) : PathCase
    {
        $this->animalName = $animalName;
        return $this;
    }

    public function getAnimalName() : string
    {
        return $this->animalName;
    }

    public function setSpecies(?string $species) : PathCase
    {
        $this->species = $species;
        return $this;
    }

    public function getSpecies() : string
    {
        return $this->species;
    }

    public function setBreed(?string $breed) : PathCase
    {
        $this->breed = $breed;
        return $this;
    }

    public function getBreed() : string
    {
        return $this->breed;
    }

    public function setSex(?string $sex) : PathCase
    {
        $this->sex = $sex;
        return $this;
    }

    public function getSex() : string
    {
        return $this->sex;
    }

    public function setDesexed(bool $desexed) : PathCase
    {
        $this->desexed = $desexed;
        return $this;
    }

    public function isDesexed() : bool
    {
        return $this->desexed;
    }

    public function setPatientNumber(?string $patientNumber) : PathCase
    {
        $this->patientNumber = $patientNumber;
        return $this;
    }

    public function getPatientNumber() : string
    {
        return $this->patientNumber;
    }

    public function setMicrochip(?string $microchip) : PathCase
    {
        $this->microchip = $microchip;
        return $this;
    }

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

    public function setOrigin(?string $origin) : PathCase
    {
        $this->origin = $origin;
        return $this;
    }

    public function getOrigin() : string
    {
        return $this->origin;
    }

    public function getColour(): string
    {
        return $this->colour;
    }

    public function setColour(string $colour): PathCase
    {
        $this->colour = $colour;
        return $this;
    }

    public function setWeight(?string $weight) : PathCase
    {
        $this->weight = $weight;
        return $this;
    }

    public function getWeight() : string
    {
        return $this->weight;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(?string $size): PathCase
    {
        $this->size = $size;
        return $this;
    }

    public function setDob(?\DateTime $dob) : PathCase
    {
        $this->dob = $dob;
        return $this;
    }

    /**
     * @return \DateTime|string
     */
    public function getDob(?string $format = null)
    {
        if ($format && $this->dob)
            return $this->dob->format($format);
        return $this->dob;
    }

    public function setDod(?\DateTime $dod) : PathCase
    {
        $this->dod = $dod;
        return $this;
    }

    /**
     * @return \DateTime|string
     */
    public function getDod(?string $format = null)
    {
        if ($format && $this->dod)
            return $this->dod->format($format);
        return $this->dod;
    }

    /**
     * return the age years
     */
    public function getAge(): int
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
     * return the age months excluding the years
     */
    public function getAgeMonths(): int
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

    public function setEuthanised(?string $euthanised) : PathCase
    {
        $this->euthanised = $euthanised;
        return $this;
    }

    public function isEuthanised() : bool
    {
        return $this->euthanised;
    }

    public function setEuthanisedMethod(?string $euthanisedMethod) : PathCase
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

    public function setDisposeMethod(?string $disposeMethod) : PathCase
    {
        $this->disposeMethod = $disposeMethod;
        return $this;
    }

    public function getDisposeMethod() : string
    {
        return $this->disposeMethod;
    }

    public function setAcHold(?\DateTime $acHold) : PathCase
    {
        $this->acHold = $acHold;
        return $this;
    }

    /**
     * @return \DateTime|string
     */
    public function getAcHold(?string $format = null)
    {
        if ($format && $this->acHold)
            return $this->acHold->format($format);
        return $this->acHold;
    }

    public function setDisposeOn(?\DateTime $disposeOn) : PathCase
    {
        $this->disposeOn = $disposeOn;
        return $this;
    }

    /**
     * @return \DateTime|string
     */
    public function getDisposeOn(?string $format = null)
    {
        if ($format && $this->disposeOn)
            return $this->disposeOn->format($format);
        return $this->disposeOn;
    }

    public function isStudentReport(): bool
    {
        return $this->studentReport;
    }

    public function setStudentReport(bool $studentReport): PathCase
    {
        $this->studentReport = $studentReport;
        return $this;
    }

    /**
     * @return Student[]
     */
    public function getStudentList(?\Tk\Db\Tool $tool = null): ArrayObject
    {
        return StudentMap::create()->findFiltered([
            'pathCaseId' => $this->getVolatileId()
        ], $tool);
    }

    /**
     * @return CompanyContact[]
     */
    public function getContactList(?\Tk\Db\Tool $tool = null): ArrayObject
    {
        $filter = [
            'pathCaseId' => $this->getVolatileId()
        ];
        if ($this->getCompanyId()) {
            $filter['companyId'] = $this->getCompanyId();
        }
        return CompanyContactMap::create()->findFiltered($filter, $tool);
    }

    public function getReportStatus(): string
    {
        return $this->reportStatus;
    }

    public function setReportStatus(string $reportStatus): PathCase
    {
        $this->reportStatus = $reportStatus;
        return $this;
    }

    public function getCollectedSamples(): string
    {
        return $this->collectedSamples;
    }

    public function setCollectedSamples(string $collectedSamples): PathCase
    {
        $this->collectedSamples = $collectedSamples;
        return $this;
    }

    public function setClinicalHistory(?string $clinicalHistory) : PathCase
    {
        $this->clinicalHistory = $clinicalHistory;
        return $this;
    }

    public function getClinicalHistory() : string
    {
        return $this->clinicalHistory;
    }

    public function setGrossPathology(?string $grossPathology) : PathCase
    {
        $this->grossPathology = $grossPathology;
        return $this;
    }

    public function getGrossPathology() : string
    {
        return $this->grossPathology;
    }

    public function setGrossMorphologicalDiagnosis(?string $grossMorphologicalDiagnosis) : PathCase
    {
        $this->grossMorphologicalDiagnosis = $grossMorphologicalDiagnosis;
        return $this;
    }

    public function getGrossMorphologicalDiagnosis() : string
    {
        return $this->grossMorphologicalDiagnosis;
    }

    public function setHistopathology(?string $histopathology) : PathCase
    {
        $this->histopathology = $histopathology;
        return $this;
    }

    public function getHistopathology() : string
    {
        return $this->histopathology;
    }

    public function setAncillaryTesting(?string $ancillaryTesting) : PathCase
    {
        $this->ancillaryTesting = $ancillaryTesting;
        return $this;
    }

    public function getAncillaryTesting() : string
    {
        return $this->ancillaryTesting;
    }

    public function setMorphologicalDiagnosis(?string $morphologicalDiagnosis) : PathCase
    {
        $this->morphologicalDiagnosis = $morphologicalDiagnosis;
        return $this;
    }

    public function getMorphologicalDiagnosis() : string
    {
        return $this->morphologicalDiagnosis;
    }

    public function setCauseOfDeath(?string $causeOfDeath) : PathCase
    {
        $this->causeOfDeath = $causeOfDeath;
        return $this;
    }

    public function getCauseOfDeath() : string
    {
        return $this->causeOfDeath;
    }

    public function getSecondOpinion(): string
    {
        return $this->secondOpinion;
    }

    public function setSecondOpinion(?string $secondOpinion): PathCase
    {
        $this->secondOpinion = $secondOpinion;
        return $this;
    }

    public function getAddendum(): string
    {
        return $this->addendum;
    }

    public function setAddendum(?string $addendum): PathCase
    {
        $this->addendum = $addendum;
        return $this;
    }

    public function getReviewer(): ?User
    {
        /** @var User $user */
        $user = UserMap::create()->find($this->getReviewedById());
        return $user;
    }

    public function getReviewedById(): ?int
    {
        return $this->reviewedById;
    }

    public function setReviewedById(?int $reviewedById): PathCase
    {
        $this->reviewedById = $reviewedById;
        return $this;
    }

    public function setComments(?string $comments) : PathCase
    {
        $this->comments = $comments;
        return $this;
    }

    public function getComments() : string
    {
        return $this->comments;
    }

    public function setNotes(?string $notes) : PathCase
    {
        $this->notes = $notes;
        return $this;
    }

    public function getNotes() : string
    {
        return $this->notes;
    }

    /**
     * Is this case editable by this user
     * IE: Only users flagged as CASE_FULL_EDIT's can edit a case after it is completed
     */
    public function isEditable(User $user): bool
    {
        if (!$user->hasPermission(Permission::CASE_FULL_EDIT) && $this->hasStatus(self::STATUS_COMPLETED)) {
            return false;
        }
        return true;
    }

    /**
     * Return true if this case has been invoiced
     */
    public function isBilled(): bool
    {
        if (!$this->isBillable()) return true;      // Do this so that non-billable cases are marked readonly
        return ($this->getAccountStatus() == self::ACCOUNT_STATUS_INVOICED || $this->getAccountStatus() == self::ACCOUNT_STATUS_UVET_INVOICED);
    }

    public function validate(): array
    {
        $errors = [];

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

    public function hasStatusChanged(Status $status): bool
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
