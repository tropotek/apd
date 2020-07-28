<?php
namespace App\Db;

use App\Db\Traits\AddressTrait;
use App\Db\Traits\BillingAddressTrait;
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
    use AddressTrait;
    use BillingAddressTrait;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var string
     */
    public $uid = '';

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
    public $billingEmail = '';

    /**
     * @var string
     */
    public $phone = '';

    /**
     * @var string
     */
    public $fax = '';

    /**
     * @var int
     */
    public $addressId = 0;

    /**
     * @var int
     */
    public $billingAddressId = 0;

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
     * @param string $billingEmail
     * @return Client
     */
    public function setBillingEmail($billingEmail) : Client
    {
        $this->billingEmail = $billingEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingEmail() : string
    {
        return $this->billingEmail;
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

        if (!$this->userId) {
            $errors['userId'] = 'Invalid value: userId';
        }

        if (!$this->uid) {
            $errors['uid'] = 'Invalid value: uid';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->email) {
            $errors['email'] = 'Invalid value: email';
        }

        if (!$this->billingEmail) {
            $errors['billingEmail'] = 'Invalid value: billingEmail';
        }

        if (!$this->phone) {
            $errors['phone'] = 'Invalid value: phone';
        }

        if (!$this->fax) {
            $errors['fax'] = 'Invalid value: fax';
        }

        if (!$this->addressId) {
            $errors['addressId'] = 'Invalid value: addressId';
        }

        if (!$this->billingAddressId) {
            $errors['billingAddressId'] = 'Invalid value: billingAddressId';
        }

        return $errors;
    }

}
