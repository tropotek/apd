<?php
namespace App\Db;

use Uni\Db\Traits\InstitutionTrait;

/**
 * @author Mick Mifsud
 * @created 2021-01-13
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class Test extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
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
     * Test
     */
    public function __construct()
    {
        $this->setInstitutionId($this->getConfig()->getInstitutionId());
        $this->modified = new \DateTime();
        $this->created = new \DateTime();

    }


    /**
     * @param string $name
     * @return Test
     */
    public function setName($name) : Test
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
     * @param string $description
     * @return Test
     */
    public function setDescription($description) : Test
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
     * @param \DateTime $modified
     * @return Test
     */
    public function setModified($modified) : Test
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getModified() : \DateTime
    {
        return $this->modified;
    }

    /**
     * @param \DateTime $created
     * @return Test
     */
    public function setCreated($created) : Test
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated() : \DateTime
    {
        return $this->created;
    }

    /**
     * @return array
     */
    public function validate()
    {
        $errors = [];

        if (!$this->institutionId) {
            $errors['institutionId'] = 'Invalid value: institutionId';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        return $errors;
    }

}
