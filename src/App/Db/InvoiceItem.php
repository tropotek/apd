<?php
namespace App\Db;

use App\Db\Traits\PathCaseTrait;
use Bs\Db\Traits\TimestampTrait;
use Tk\Money;

/**
 * @author Mick Mifsud
 * @created 2021-01-27
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class InvoiceItem extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use TimestampTrait;
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
     * @var string
     */
    public $code = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var float
     */
    public $qty = 1;

    /**
     * @var Money
     */
    public $price = 0;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * InvoiceItem
     */
    public function __construct()
    {
        $this->_TimestampTrait();
        $this->price = new Money(0);

    }

    /**
     * @param string $description
     * @param \Tk\Money $price
     * @param float $qty
     * @param string $code
     * @return InvoiceItem
     */
    public static function create($description, $price, $qty = 1, $code = '')
    {
        $obj = new static();
        $obj->setDescription($description);
        $obj->setPrice($price);
        $obj->setQty($qty);
        $obj->setCode($code);
        return $obj;
    }

    /**
     * @param string $code
     * @return InvoiceItem
     */
    public function setCode($code) : InvoiceItem
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode() : string
    {
        return $this->code;
    }

    /**
     * @param string $description
     * @return InvoiceItem
     */
    public function setDescription($description) : InvoiceItem
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @param float $qty
     * @return InvoiceItem
     */
    public function setQty($qty) : InvoiceItem
    {
        $this->qty = $qty;
        return $this;
    }

    /**
     * @return float
     */
    public function getQty() : float
    {
        return $this->qty;
    }

    /**
     * @return \Tk\Money
     */
    public function getPrice(): \Tk\Money
    {
        return $this->price;
    }

    /**
     * @param \Tk\Money $price
     * @return InvoiceItem
     */
    public function setPrice(\Tk\Money $price): InvoiceItem
    {
        $this->price = $price;
        return $this;
    }


    /**
     * @return \Tk\Money
     */
    public function getTotal()
    {
        return $this->price->multiply($this->qty);
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

//        if (!$this->code) {
//            $errors['code'] = 'Invalid value: code';
//        }

        // TODO: are we going to allow negative or 0 value items??? Should this be configurable?
        if (!$this->price || $this->price->getAmount() <= 0) {
            $errors['price'] = 'Invalid value: price';
        }

        if (!$this->qty) {
            $errors['qty'] = 'Invalid value: qty';
        }

        return $errors;
    }

}
