<?php
namespace App\Db;

use Bs\Db\Traits\TimestampTrait;
use Bs\Db\Traits\UserTrait;
use Uni\Db\Traits\InstitutionTrait;

/**
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Contact extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use TimestampTrait;
    use InstitutionTrait;
    use UserTrait;

    const TYPE_CLIENT               = 'client';
    const TYPE_OWNER                = 'owner';
    const TYPE_STUDENT              = 'student';

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * Used if the client is also a staff member
     * @var int
     */
    public $userId = 0;

    /**
     * Farm Shed id
     * @var string
     */
    public $uid = '';

    /**
     * @var string
     */
    public $type = '';

    /**
     * University account code or their accounts dep. account code for invoicing
     * @var string
     */
    public $accountCode = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $phone = '';

    /**
     * @var string
     */
    public $fax = '';

    /**
     * @var string
     */
    public $street = '';

    /**
     * @var string
     */
    public $city = '';

    /**
     * @var string
     */
    public $country = '';

    /**
     * @var string
     */
    public $state = '';

    /**
     * @var string
     */
    public $postcode = '';

    /**
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
     * Client
     */
    public function __construct()
    {
        $this->setType(self::TYPE_CLIENT);
        $this->_TimestampTrait();
        $this->setInstitutionId($this->getConfig()->getInstitutionId());
    }

    /**
     * @param string $uid
     * @return Contact
     */
    public function setUid($uid) : Contact
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @return string
     */
    public function getUid() : string
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Contact
     */
    public function setType(string $type): Contact
    {
        $this->type = $type;
        return $this;
    }

    /**
     * return the status list for a select field
     * @param null|string $selected
     * @return array
     */
    public static function getTypeList($selected = null)
    {
        $arr = \Tk\Form\Field\Select::arrayToSelectList(\Tk\ObjectUtil::getClassConstants(__CLASS__, 'TYPE'));
        if (is_string($selected)) {
            $arr2 = array();
            foreach ($arr as $k => $v) {
                if ($v == $selected) {
                    $arr2[$k.' (Current)'] = $v;
                } else {
                    $arr2[$k] = $v;
                }
            }
            $arr = $arr2;
        }
        return $arr;
    }

    /**
     * @param string $accountCode
     * @return Contact
     */
    public function setAccountCode($accountCode) : Contact
    {
        $this->accountCode = $accountCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountCode() : string
    {
        return $this->accountCode;
    }

    /**
     * @param string $name
     * @return Contact
     */
    public function setName($name) : Contact
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $email
     * @return Contact
     */
    public function setEmail($email) : Contact
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail() : string
    {
        return $this->email;
    }

    /**
     * @param string $phone
     * @return Contact
     */
    public function setPhone($phone) : Contact
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone() : string
    {
        return $this->phone;
    }

    /**
     * @param string $fax
     * @return Contact
     */
    public function setFax($fax) : Contact
    {
        $this->fax = $fax;
        return $this;
    }

    /**
     * @return string
     */
    public function getFax() : string
    {
        return $this->fax;
    }

    /**
     * @param string $street
     * @return Contact
     */
    public function setStreet($street) : Contact
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet() : string
    {
        return $this->street;
    }

    /**
     * @param string $city
     * @return Contact
     */
    public function setCity($city) : Contact
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity() : string
    {
        return $this->city;
    }

    /**
     * @param string $country
     * @return Contact
     */
    public function setCountry($country) : Contact
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry() : string
    {
        return $this->country;
    }

    /**
     * @param string $state
     * @return Contact
     */
    public function setState($state) : Contact
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getState() : string
    {
        return $this->state;
    }

    /**
     * @param string $postcode
     * @return Contact
     */
    public function setPostcode($postcode) : Contact
    {
        $this->postcode = $postcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode() : string
    {
        return $this->postcode;
    }


    /**
     * @param string $notes
     * @return Contact
     */
    public function setNotes($notes) : Contact
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
     * @return string
     */
    public function getTitle()
    {
        return $this->getName() . ' (' . $this->getEmail() . ')';
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getSelectList($type = '')
    {
        $filter = array(
            'institutionId' => \App\Config::getInstance()->getInstitutionId()
        );
        if ($type) {
            $filter['type'] = $type;
        }
        $list = ContactMap::create()->findFiltered($filter, \Tk\Db\Tool::create('name'));
        $arr = array();
        foreach ($list as $item) {
            $arr[$item->getTitle()] = $item->getId();
        }
        return $arr;
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

        if (!$this->getType()) {
            $errors['type'] = 'Invalid value: type';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->email || $this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid value: email';
        }


        return $errors;
    }

}