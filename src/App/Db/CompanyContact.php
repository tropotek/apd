<?php
namespace App\Db;

use App\Db\Traits\CompanyTrait;
use Bs\Db\Traits\TimestampTrait;

class CompanyContact extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use TimestampTrait;
    use CompanyTrait;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $companyId = 0;

    // TODO: remove
    public $contact_id = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $phone = '';

    // TODO: remove
    public $del = false;

    /**
     * @var \DateTime|null
     */
    public $modified = null;

    /**
     * @var \DateTime|null
     */
    public $created = null;


    public function __construct()
    {
        $this->_TimestampTrait();
    }

    public function getName(): string
    {
        return trim($this->name);
    }

    public function setName(string $name): CompanyContact
    {
        $this->name = $name;
        return $this;
    }

    public function setEmail(string $email) : CompanyContact
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function setPhone(string $phone) : CompanyContact
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPhone() : string
    {
        return $this->phone;
    }

    public static function getSelectList(int $companyId): array
    {
        if (!$companyId) return [];
        $list = CompanyContactMap::create()->findFiltered(
            compact('companyId'),
            \Tk\Db\Tool::create('name')
        );
        $arr = [];
        foreach ($list as $item) {
            $label = $item->getName();
            if ($item->getEmail()) {
                $label .= sprintf(' - [%s]', $item->getEmail());
            }
            $arr[$label] = $item->getId();
        }
        return $arr;
    }

    public function validate(): array
    {
        $errors = [];

        if (!$this->getName()) {
            $errors['name'] = 'Please enter at a name for this contact.';
        }

        if ($this->getEmail() && !filter_var($this->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid value: email';
        }

        // find existing contact with same type and same first and last name (case in-sensitive search)
        if (!$this->getId()) {
            if ($this->getName()) {
                $found = CompanyContactMap::create()->findFiltered([
                    'companyId' => $this->getCompanyId(),
                    'name' => $this->getName(),
                    'exclude' => $this->getVolatileId()
                ]);
                if ($found->count()) {
                    $errors['name'] = 'A Contact with this name already exists for this company.';
                }
            }

            if ($this->getEmail()) {
                $found = CompanyContactMap::create()->findFiltered([
                    'companyId' => $this->getCompanyId(),
                    'email' => $this->getEmail(),
                    'exclude' => $this->getVolatileId()
                ]);
                if ($found->count()) {
                    $errors['email'] = 'A Contact with this email already exists for this company.';
                }
            }
        }

        return $errors;
    }

}
