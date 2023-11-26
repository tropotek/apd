<?php
namespace App\Db;

use Bs\Db\Traits\TimestampTrait;
use Bs\Db\Traits\UserTrait;
use Tk\Mail\Message;
use Uni\Db\Traits\InstitutionTrait;

/**
 */
class Student extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use TimestampTrait;
    use InstitutionTrait;
    use UserTrait;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var \DateTime|null
     */
    public $modified = null;

    /**
     * @var \DateTime|null
     */
    public $created = null;


    public function __construct()
    {
        $this->_TimestampTrait();
        $this->setInstitutionId($this->getConfig()->getInstitutionId());
    }

    public function save()
    {
        parent::save();
    }


    public function setName($name) : Student
    {
        $this->name = $name;
        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setEmail($email) : Student
    {
        // clean email
        [$email, $n] = Message::splitEmail($email);
        $this->email = $email;
        return $this;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public static function getSelectList(): array
    {
        $list = StudentMap::create()->findFiltered(
            [ 'institutionId' => \App\Config::getInstance()->getInstitutionId() ],
            \Tk\Db\Tool::create('name')
        );
        $arr = [];
        foreach ($list as $item) {
            $label = $item->getName();
            if ($item->getEmail()) {
                $label .= sprintf(' - [%s]', $item->getEmail());
            }
            $arr[$label] = $item->getId();
        }
        return $arr;
    }

    public function validate(): array
    {
        $errors = [];

        if (!$this->institutionId) {
            $errors['institutionId'] = 'Invalid value: institutionId';
        }

        if (!$this->getName()) {
            $errors['name'] = 'Please enter a name for this record.';
        }

        // clean email for validation
        [$email, $name] = Message::splitEmail($this->getEmail());
        $this->setEmail($email);
        if ($this->getEmail() && !filter_var($this->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid value: email';
        }

        // find existing contact with same name and email (case in-sensitive search)
        if (!$this->getId()) {
            $found = StudentMap::create()->findFiltered([
                'institutionId' => $this->getInstitutionId(),
                'name' => $this->getName(),
                'exclude' => $this->getId()
            ]);
            if ($found->count()) {
                $errors['name'] = 'A student with this name already exists.';
            }
            if ($this->getEmail()) {
                $found = StudentMap::create()->findFiltered([
                    'institutionId' => $this->getInstitutionId(),
                    'email' => $this->getEmail(),
                    'exclude' => $this->getId()
                ]);
                if ($found->count()) {
                    $errors['email'] = 'A student with this email already exists.';
                }
            }
        }

        return $errors;
    }

}
