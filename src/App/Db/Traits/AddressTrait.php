<?php

namespace App\Db\Traits;

use App\Db\Address;
use App\Db\AddressMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait AddressTrait
{

    /**
     * @var Address
     */
    private $_address = null;


    /**
     * @return int
     */
    public function getAddressId()
    {
        return $this->addressId;
    }

    /**
     * @param int $addressId
     * @return $this
     */
    public function setAddressId($addressId)
    {
        $this->addressId = (int)$addressId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Address|null
     */
    public function getAddress()
    {
        if (!$this->_address) {
            try {
                $this->_address = AddressMap::create()->find($this->getAddressId());
            } catch (Exception $e) {
            }
        }
        return $this->_address;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateAddressId($errors = [])
    {
        if (!$this->getAddressId()) {
            $errors['addressId'] = 'Invalid value: addressId';
        }
        return $errors;
    }

}