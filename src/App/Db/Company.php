<?php
namespace App\Db;

use Bs\Db\Traits\TimestampTrait;
use Uni\Db\Traits\InstitutionTrait;

class Company extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use TimestampTrait;
    use InstitutionTrait;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * Client account code or their accounts' dep. account code for invoicing
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

    public function setAccountCode(string $accountCode) : Company
    {
        $this->accountCode = $accountCode;
        return $this;
    }

    public function getAccountCode() : string
    {
        return $this->accountCode;
    }

    public function getName(): string
    {
        return trim($this->name);
    }

    public function setName(string $name): Company
    {
        $this->name = $name;
        return $this;
    }

    public function setEmail(string $email) : Company
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function setPhone(string $phone) : Company
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPhone() : string
    {
        return $this->phone;
    }

    public function setFax(string $fax) : Company
    {
        $this->fax = $fax;
        return $this;
    }

    public function getFax() : string
    {
        return $this->fax;
    }

    public function setStreet(string $street) : Company
    {
        $this->street = $street;
        return $this;
    }

    public function getStreet() : string
    {
        return $this->street;
    }

    public function setCity(string $city) : Company
    {
        $this->city = $city;
        return $this;
    }

    public function getCity() : string
    {
        return $this->city;
    }

    public function setCountry(string $country) : Company
    {
        $this->country = $country;
        return $this;
    }

    public function getCountry() : string
    {
        return $this->country;
    }

    public function setState(string $state) : Company
    {
        $this->state = $state;
        return $this;
    }

    public function getState() : string
    {
        return $this->state;
    }

    public function setPostcode(string $postcode) : Company
    {
        $this->postcode = $postcode;
        return $this;
    }

    public function getPostcode() : string
    {
        return $this->postcode;
    }

    public function getAddress()
    {
        $str = '';
        if ($this->getStreet())
            $str .= $this->getStreet() . ', ';
        if ($this->getCity())
            $str .= $this->getCity() . ', ';
        if ($this->getPostcode())
            $str .= $this->getPostcode() . ', ';
        if ($this->getState())
            $str .= $this->getState() . ', ';
        if ($this->getCountry())
            $str .= $this->getCountry() . ', ';
        return rtrim($str, ', ');
    }

    public function setNotes(string $notes) : Company
    {
        $this->notes = $notes;
        return $this;
    }

    public function getNotes() : string
    {
        return $this->notes;
    }

    public function getSelectTitle(): string
    {
        $str = $this->getName();
        if (trim($this->getEmail())) {
            $str .= ' [' . $this->getEmail() . ']';
        }
        return $str;
    }

    public static function getSelectList(): array
    {
        $filter = array(
            'institutionId' => \App\Config::getInstance()->getInstitutionId()
        );
        $list = CompanyMap::create()->findFiltered($filter, \Tk\Db\Tool::create('name'));
        $arr = [];
        foreach ($list as $item) {
            $label = $item->getName();
            if (trim($item->getEmail())) {
                $label .= ' [' . $item->getEmail() . ']';
            }
            $arr[$label] = $item->getId();
        }
        return $arr;
    }


    public function validate(): array
    {
        $errors = [];

        if (!$this->getName()) {
            $errors['name'] = 'Please enter at a name for this company.';
        }

        if ($this->getEmail() && !filter_var($this->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid value: email';
        }

        // find existing contact with same type and same first and last name (case in-sensitive search)
        if (!$this->getId()) {
            if ($this->getName()) {
                $found = CompanyMap::create()->findFiltered([
                    'institutionId' => $this->getInstitutionId(),
                    'name' => $this->getName(),
                    'exclude' => $this->getVolatileId()
                ]);
                if ($found->count()) {
                    $errors['name'] = 'A record with this name already exists.';
                }
            }

            if ($this->getEmail()) {
                $found = CompanyMap::create()->findFiltered([
                    'institutionId' => $this->getInstitutionId(),
                    'email' => $this->getEmail(),
                    'exclude' => $this->getVolatileId()
                ]);
                if ($found->count()) {
                    $errors['email'] = 'A Company with this email already exists.';
                }
            }
        }

        return $errors;
    }

}
