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
class Service extends \Tk\Db\Map\Model implements \Tk\ValidInterface
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
    public $name = '';

    /**
     * This should be a cost per service
     * @var float
     */
    public $price = 0;

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
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * Service
     */
    public function __construct()
    {
        $this->_TimestampTrait();
        $this->institutionId = $this->getConfig()->getInstitutionId();
    }

    /**
     * @param string $name
     * @return Service
     */
    public function setName($name) : Service
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
     * @param float $price
     * @return Service
     */
    public function setPrice($price) : Service
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice() : float
    {
        return $this->price;
    }

    /**
     * @param string $comments
     * @return Service
     */
    public function setComments($comments) : Service
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return string
     */
    public function getComments() : string
    {
        return $this->comments;
    }

    /**
     * @param string $notes
     * @return Service
     */
    public function setNotes($notes) : Service
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

        if (!$this->price) {
            $errors['price'] = 'Invalid value: price';
        }

        return $errors;
    }

}
