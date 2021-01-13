<?php
namespace App\Db;

use Uni\Db\Traits\InstitutionTrait;

/**
 * @author Mick Mifsud
 * @created 2021-01-13
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class AnimalType extends \Tk\Db\Map\Model implements \Tk\ValidInterface
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
     * @var int
     */
    public $parentId = 0;

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
     * @var null|AnimalType
     */
    private $_parent = null;


    /**
     * AnimalType
     */
    public function __construct()
    {
        $this->setInstitutionId($this->getConfig()->getInstitutionId());
        $this->modified = new \DateTime();
        $this->created = new \DateTime();

    }
    
    /**
     * @param int $parentId
     * @return AnimalType
     */
    public function setParentId($parentId) : AnimalType
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @return int
     */
    public function getParentId() : int
    {
        return $this->parentId;
    }

    /**
     * @return AnimalType|\Tk\Db\ModelInterface|null
     * @throws \Exception
     */
    public function getParent()
    {
        if (!$this->_parent)
            $this->_parent = AnimalTypeMap::create()->find($this->parentId);
        return $this->_parent;
    }

    /**
     * @return AnimalType[]|\Tk\Db\Map\ArrayObject
     * @throws \Exception
     */
    public function getChildren()
    {
        return AnimalTypeMap::create()->findFiltered(array('parentId' => $this->getId()));
    }

    /**
     * @param string $name
     * @return AnimalType
     */
    public function setName($name) : AnimalType
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
     * @return AnimalType
     */
    public function setDescription($description) : AnimalType
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
     * @return AnimalType
     */
    public function setModified($modified) : AnimalType
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
     * @return AnimalType
     */
    public function setCreated($created) : AnimalType
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
        $errors = array();

        if (!$this->parentId) {
            $errors['parentId'] = 'Invalid value: parentId';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        return $errors;
    }

}
