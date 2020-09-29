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
class Client extends \Tk\Db\Map\Model implements \Tk\ValidInterface
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
        $this->_TimestampTrait();
        $this->institutionId = $this->getConfig()->getInstitutionId();
    }

    /**
     * @param string $uid
     * @return Client
     */
    public function setUid($uid) : Client
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
     * @param string $accountCode
     * @return Client
     */
    public function setAccountCode($accountCode) : Client
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
     * @return Client
     */
    public function setName($name) : Client
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
     * @return Client
     */
    public function setEmail($email) : Client
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
     * @return Client
     */
    public function setPhone($phone) : Client
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
     * @return Client
     */
    public function setFax($fax) : Client
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
     * @return Client
     */
    public function setStreet($street) : Client
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
     * @return Client
     */
    public function setCity($city) : Client
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
     * @return Client
     */
    public function setCountry($country) : Client
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
     * @return Client
     */
    public function setState($state) : Client
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
     * @return Client
     */
    public function setPostcode($postcode) : Client
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
     * @return Client
     */
    public function setNotes($notes) : Client
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
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->institutionId) {
            $errors['institutionId'] = 'Invalid value: institutionId';
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
