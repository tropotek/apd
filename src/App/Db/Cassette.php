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
     * Storage location if available
     * @var int
     */
    public $storageId = 0;

    /**
     * TODO: Not sure if we will be using this or storage_id or both???
     * @var string
     */
    public $container = '';

    /**
     * Generally just increments by 1 for each group in a case
     * @var string
     */
    public $number = '';

    /**
     * Usually the tissue type name
     * @var string
     */
    public $name = '';

    /**
     * Quantity of samples available
     * @var int
     */
    public $qty = 0;

    /**
     * price per sample ???
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
     * Cassette
     */
    public function __construct()
    {
        $this->_TimestampTrait();

    }

    /**
     * @param Request $request
     * @return $this
     * @throws \Tk\Exception
     */
    public function addRequest(Request $request)
    {
        if (!$this->getId() || !$this->getPathCaseId())
            throw new \Tk\Exception('Save Cassette first');
        if ($this->getQty() < $request->getQty())
            throw new \Tk\Exception('Not enough Quantity available in cassette');
        if (!$request->getCassetteId())
            $request->setCassetteId($this->getId());
        if (!$request->getPathCaseId())
            $request->setPathCaseId($this->getPathCaseId());
        $this->setQty($this->getQty() - $request->getQty());
        return $this;
    }

    /**
     * @param Request $request
     * @return $this
     * @throws \Tk\Exception
     */
    public function removeRequest(Request $request)
    {
        if (!$this->getId() || !$this->getPathCaseId())
            throw new \Tk\Exception('Save Cassette first.');
        //if ($request->getStatus() != Request::STATUS_COMPLETED && $request->getStatus() != Request::STATUS_CANCELLED) {
            $this->setQty($this->getQty() + $request->getQty());
        //}
        return $this;
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
     * @param int|PathCase $pathCaseId
     * @return int
     * @throws \Exception
     */
    static public function getNextNumber($pathCaseId)
    {
        if ($pathCaseId instanceof \Tk\Db\ModelInterface) $pathCaseId = $pathCaseId->getId();
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

//        if (!$this->storageId) {
//            $errors['storageId'] = 'Invalid value: storageId';
//        }

//        if (!$this->container) {
//            $errors['container'] = 'Invalid value: container';
//        }

        if (!$this->number) {
            $errors['number'] = 'Invalid value: number';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

//        if (!$this->qty) {
//            $errors['qty'] = 'Invalid value: qty';
//        }

//        if (!$this->price) {
//            $errors['price'] = 'Invalid value: price';
//        }

        return $errors;
    }

}
