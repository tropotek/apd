<?php
namespace App\Db;

use Bs\Db\Traits\TimestampTrait;
use Uni\Db\Traits\InstitutionTrait;

/**
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Address extends \Tk\Db\Map\Model implements \Tk\ValidInterface
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
     * @var string
     */
    public $number = '';

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
    public $address = '';

    /**
     * @var float
     */
    public $mapZoom = 14;

    /**
     * @var float
     */
    public $mapLng = 0;

    /**
     * @var float
     */
    public $mapLat = 0;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * Address
     */
    public function __construct()
    {
        $this->_TimestampTrait();

    }

    /**
     * @param string $number
     * @return Address
     */
    public function setNumber($number) : Address
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumber() : string
    {
        return $this->number;
    }

    /**
     * @param string $street
     * @return Address
     */
    public function setStreet($street) : Address
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
     * @return Address
     */
    public function setCity($city) : Address
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
     * @return Address
     */
    public function setCountry($country) : Address
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
     * @return Address
     */
    public function setState($state) : Address
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
     * @return Address
     */
    public function setPostcode($postcode) : Address
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
     * @param string $address
     * @return Address
     */
    public function setAddress($address) : Address
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress() : string
    {
        return $this->address;
    }

    /**
     * @param float $mapZoom
     * @return Address
     */
    public function setMapZoom($mapZoom) : Address
    {
        $this->mapZoom = $mapZoom;
        return $this;
    }

    /**
     * @return float
     */
    public function getMapZoom() : float
    {
        return $this->mapZoom;
    }

    /**
     * @param float $mapLng
     * @return Address
     */
    public function setMapLng($mapLng) : Address
    {
        $this->mapLng = $mapLng;
        return $this;
    }

    /**
     * @return float
     */
    public function getMapLng() : float
    {
        return $this->mapLng;
    }

    /**
     * @param float $mapLat
     * @return Address
     */
    public function setMapLat($mapLat) : Address
    {
        $this->mapLat = $mapLat;
        return $this;
    }

    /**
     * @return float
     */
    public function getMapLat() : float
    {
        return $this->mapLat;
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

        if (!$this->number) {
            $errors['number'] = 'Invalid value: number';
        }

        if (!$this->street) {
            $errors['street'] = 'Invalid value: street';
        }

        if (!$this->city) {
            $errors['city'] = 'Invalid value: city';
        }

        if (!$this->country) {
            $errors['country'] = 'Invalid value: country';
        }

        if (!$this->state) {
            $errors['state'] = 'Invalid value: state';
        }

        if (!$this->postcode) {
            $errors['postcode'] = 'Invalid value: postcode';
        }

        if (!$this->address) {
            $errors['address'] = 'Invalid value: address';
        }

        if (!$this->mapZoom) {
            $errors['mapZoom'] = 'Invalid value: mapZoom';
        }

        if (!$this->mapLng) {
            $errors['mapLng'] = 'Invalid value: mapLng';
        }

        if (!$this->mapLat) {
            $errors['mapLat'] = 'Invalid value: mapLat';
        }

        return $errors;
    }

}
