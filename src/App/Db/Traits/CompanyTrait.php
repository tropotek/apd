<?php

namespace App\Db\Traits;

use App\Db\Company;
use App\Db\CompanyMap;
use Exception;

trait CompanyTrait
{

    /**
     * @var Company
     */
    private $_company = null;


    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function setCompanyId(int $companyId): self
    {
        $this->companyId = $companyId;
        return $this;
    }

    public function getCompany(): ?Company
    {
        if (!$this->_company) {
            $this->_company = CompanyMap::create()->find($this->getCompanyId());
        }
        return $this->_company;
    }

    public function validateCompanyId(array $errors = []): array
    {
        if (!$this->getCompanyId()) {
            $errors['companyId'] = 'Invalid value: companyId';
        }
        return $errors;
    }

}