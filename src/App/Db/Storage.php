<?php
namespace App\Db;

use App\Db\Traits\AddressTrait;
use Bs\Db\Traits\TimestampTrait;
use Uni\Db\Traits\InstitutionTrait;

/**
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Storage extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use TimestampTrait;
    use InstitutionTrait;
    use AddressTrait;

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
    public $addressId = 0;

    /**
     * @var string
     */
    public $uid = '';

    /**
     * @var string
     */
    public $name = '';

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
     * Storage
     */
    public function __construct()
    {
        $this->_TimestampTrait();

    }

    /**
     * @param string $uid
     * @return Storage
     */
    public function setUid($uid) : Storage
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
     * @return Storage
     */
    public function setName($name) : Storage
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
     * @param float $mapZoom
     * @return Storage
     */
    public function setMapZoom($mapZoom) : Storage
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
     * @return Storage
     */
    public function setMapLng($mapLng) : Storage
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
     * @return Storage
     */
    public function setMapLat($mapLat) : Storage
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
     * @param string $notes
     * @return Storage
     */
    public function setNotes($notes) : Storage
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

        if (!$this->addressId) {
            $errors['addressId'] = 'Invalid value: addressId';
        }

        if (!$this->uid) {
            $errors['uid'] = 'Invalid value: uid';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
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
