<?php
declare(strict_types=1);

namespace Codilar\Employee\Model;

use Codilar\Employee\Model\ResourceModel\Employee as EmployeeResource;
use Magento\Framework\Model\AbstractModel;

class Employee extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(EmployeeResource::class);
    }
}
