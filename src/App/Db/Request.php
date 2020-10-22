<?php
namespace App\Db;

use App\Db\Traits\CassetteTrait;
use App\Db\Traits\ClientTrait;
use App\Db\Traits\PathCaseTrait;
use App\Db\Traits\ServiceTrait;
use Bs\Db\Status;
use Bs\Db\Traits\StatusTrait;
use Bs\Db\Traits\TimestampTrait;
use Tk\Money;

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
    //const STATUS_PROCESSING         = 'processing';
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
    public $qty = 1;

    /**
     * The total cost based on qty requested + the service cost
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
     * Request
     */
    public function __construct()
    {
        $this->_TimestampTrait();
        $this->cost = Money::create(0);
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
     * @param Money|float $cost
     * @return Request
     */
    public function setCost($cost) : Request
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

        if ((int)$this->qty < 1) {
            $errors['qty'] = 'Invalid value: qty';
        }

//        if (!$this->cost) {
//            $errors['cost'] = 'Invalid value: cost';
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
//            case Request::STATUS_PROCESSING:
//                if (!$prevStatusName || Request::STATUS_PENDING == $prevStatusName)
//                    return true;
//                break;
            case Request::STATUS_COMPLETED:
//                if (!$prevStatusName || Request::STATUS_PENDING == $prevStatusName || Request::STATUS_PROCESSING == $prevStatusName)
                if (!$prevStatusName || Request::STATUS_PENDING == $prevStatusName)
                    return true;
                break;
            case Request::STATUS_CANCELLED:
                return true;
        }
        return false;
    }
}
