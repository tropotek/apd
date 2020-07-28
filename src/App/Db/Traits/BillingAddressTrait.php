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
trait BillingAddressTrait
{

    /**
     * @var Address
     */
    private $_billingAddress = null;


    /**
     * @return int
     */
    public function getBillingAddressId()
    {
        return $this->billingAddressId;
    }

    /**
     * @param int $addressId
     * @return $this
     */
    public function setBillingAddressId($addressId)
    {
        $this->billingAddressId = (int)$addressId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Address|null
     */
    public function getBillingAddress()
    {
        if (!$this->_billingAddress) {
            try {
                $this->_billingAddress = AddressMap::create()->find($this->getBillingAddressId());
            } catch (Exception $e) {
            }
        }
        return $this->_billingAddress;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateBillingAddressId($errors = [])
    {
        if (!$this->getBillingAddressId()) {
            $errors['billingAddressId'] = 'Invalid value: billingAddressId';
        }
        return $errors;
    }

}