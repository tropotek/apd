<?php
namespace App\Db;

use App\Db\Traits\PathCaseTrait;
use App\Db\Traits\StorageTrait;
use Bs\Db\Traits\TimestampTrait;
use Tk\Db\Tool;

/**
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Cassette extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use TimestampTrait;
    use StorageTrait;
    use PathCaseTrait;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $pathCaseId = 0;

    /**
     * @var int
     */
    public $storageId = 0;

    /**
     * @var string
     */
    public $container = '';

    /**
     * @var string
     */
    public $number = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var int
     */
    public $qty = 0;

    /**
     * @var float
     */
    public $price = 0;

    /**
     * @var string
     */
    public $comments = '';

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
     * Cassette
     */
    public function __construct()
    {
        $this->_TimestampTrait();

    }

    /**
     * @param string $container
     * @return Cassette
     */
    public function setContainer($container) : Cassette
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @return string
     */
    public function getContainer() : string
    {
        return $this->container;
    }

    /**
     * @param string $number
     * @return Cassette
     */
    public function setNumber($number) : Cassette
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
     * @param string $name
     * @return Cassette
     */
    public function setName($name) : Cassette
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
     * @param int $qty
     * @return Cassette
     */
    public function setQty($qty) : Cassette
    {
        $this->qty = $qty;
        return $this;
    }

    /**
     * @return int
     */
    public function getQty() : int
    {
        return $this->qty;
    }

    /**
     * @param float $price
     * @return Cassette
     */
    public function setPrice($price) : Cassette
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
     * @return Cassette
     */
    public function setComments($comments) : Cassette
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
     * @return Cassette
     */
    public function setNotes($notes) : Cassette
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
     * @param int $pathCaseId
     * @return int
     * @throws \Exception
     */
    static public function getNextNumber($pathCaseId)
    {
        /** @var Cassette $cassette */
        $cassette = CassetteMap::create()->findFiltered(array('pathCaseId' => $pathCaseId), Tool::create('number DESC'))->current();
        if ($cassette)
            return (int)$cassette->getNumber() + 1;
        return 1;
    }

    /**
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->pathCaseId) {
            $errors['pathCaseId'] = 'Invalid value: pathCaseId';
        }

        if (!$this->storageId) {
            $errors['storageId'] = 'Invalid value: storageId';
        }

        if (!$this->container) {
            $errors['container'] = 'Invalid value: container';
        }

        if (!$this->number) {
            $errors['number'] = 'Invalid value: number';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->qty) {
            $errors['qty'] = 'Invalid value: qty';
        }

        if (!$this->price) {
            $errors['price'] = 'Invalid value: price';
        }

        return $errors;
    }

}
