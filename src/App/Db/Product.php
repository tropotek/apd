<?php
namespace App\Db;

use Bs\Db\Traits\TimestampTrait;
use Tk\Money;
use Uni\Db\Traits\InstitutionTrait;

/**
 * @author Mick Mifsud
 * @created 2022-12-10
 * @link http://tropotek.com.au/
 * @license Copyright 2022 Tropotek
 */
class Product extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use InstitutionTrait;
    use TimestampTrait;

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
     * @var string
     */
    public $code = '';

    /**
     * @var Money
     */
    public $price = null;

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * Product
     */
    public function __construct()
    {
        $this->_TimestampTrait();
        $this->setInstitutionId($this->getConfig()->getInstitutionId());
        $this->price = Money::create(0);

    }

    /**
     * @param string $name
     * @return Product
     */
    public function setName(string $name) : Product
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
     * @param Money $price
     * @return Product
     */
    public function setPrice(Money $price) : Product
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return Money
     */
    public function getPrice() : Money
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Product
     */
    public function setCode(string $code): Product
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param string $description
     * @return Product
     */
    public function setDescription(string $description) : Product
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

        if ($this->price->getAmount() < 0) {
            $errors['price'] = 'Invalid value: price';
        }

        return $errors;
    }

}
