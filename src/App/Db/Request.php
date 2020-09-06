<?php
namespace App\Db;

use App\Db\Traits\CassetteTrait;
use App\Db\Traits\ClientTrait;
use App\Db\Traits\PathCaseTrait;
use App\Db\Traits\ServiceTrait;
use Bs\Db\Status;
use Bs\Db\Traits\StatusTrait;
use Bs\Db\Traits\TimestampTrait;

/**
 * @author Mick Mifsud
 * @created 2020-07-30
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Request extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use TimestampTrait;
    use PathCaseTrait;
    use CassetteTrait;
    use ServiceTrait;
    use ClientTrait;
    use StatusTrait;

    const STATUS_PENDING            = 'pending';
    const STATUS_PROCESSING         = 'processing';
    const STATUS_COMPLETED          = 'completed';
    const STATUS_CANCELLED          = 'cancelled';

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
    public $cassetteId = 0;

    /**
     * @var int
     */
    public $serviceId = 0;

    /**
     * The client requesting the samples (NOT sure if this could be staff, client, etc)
     * @var int
     */
    public $clientId = 0;

    /**
     * @var string
     */
    public $status = 'pending';

    /**
     * Quantity of samples requested (check available tissue.qty on submit)
     * @var int
     */
    public $qty = 0;

    /**
     * The total cost based on qty requested + the service cost
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
     * Request
     */
    public function __construct()
    {
        $this->_TimestampTrait();

    }

    public function insert()
    {
        if ($this->getCassette()) {
            $this->getCassette()->addRequest($this);
            $this->getCassette()->save();
        }
        return parent::insert();
    }

    public function delete()
    {
        if ($this->getCassette()) {
            $this->getCassette()->removeRequest($this);
            $this->getCassette()->save();
        }
        return parent::delete();
    }

    /**
     * @param int $qty
     * @return Request
     */
    public function setQty($qty) : Request
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
     * @return Request
     */
    public function setPrice($price) : Request
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
     * @return Request
     */
    public function setComments($comments) : Request
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
     * @return Request
     */
    public function setNotes($notes) : Request
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

        if (!$this->pathCaseId) {
            $errors['pathCaseId'] = 'Invalid value: pathCaseId';
        }

        if (!$this->cassetteId) {
            $errors['cassetteId'] = 'Invalid value: cassetteId';
        }

        if (!$this->serviceId) {
            $errors['serviceId'] = 'Invalid value: serviceId';
        }

        if (!$this->clientId) {
            $errors['clientId'] = 'Invalid value: clientId';
        }

        if (!$this->qty || $this->qty < 1 || $this->qty > $this->getCassette()->getQty()) {
            $errors['qty'] = 'Invalid value: qty';
        }

//        if (!$this->price) {
//            $errors['price'] = 'Invalid value: price';
//        }

        return $errors;
    }

    public function hasStatusChanged(Status $status)
    {
        $prevStatusName = $status->getPreviousName();
        switch ($status->getName()) {
            case Request::STATUS_PENDING:
                if (!$prevStatusName)
                    return true;
                break;
            case Request::STATUS_PROCESSING:
                if (!$prevStatusName || Request::STATUS_PENDING == $prevStatusName)
                    return true;
                break;
            case Request::STATUS_COMPLETED:
                if (!$prevStatusName || Request::STATUS_PENDING == $prevStatusName || Request::STATUS_PROCESSING == $prevStatusName)
                    return true;
                break;
            case Request::STATUS_CANCELLED:
                return true;
        }
        return false;
    }
}
