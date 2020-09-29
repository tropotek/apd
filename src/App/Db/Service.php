<?php
namespace App\Db;

use Bs\Db\Traits\TimestampTrait;
use Tk\Money;
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
     * @var Money
     */
    public $cost = null;

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
        $this->cost = Money::create(0);
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
     * @param Money|float $cost
     * @return Service
     */
    public function setCost($cost) : Service
    {
        if (!is_object($cost)) {
            $cost = Money::create((float)$cost);
        }
        $this->cost = $cost;
        return $this;
    }

    /**
     * @return Money
     */
    public function getCost() : Money
    {
        return $this->cost;
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

//        if (!$this->cost) {
//            $errors['cost'] = 'Invalid value: cost';
//        }

        return $errors;
    }

}
