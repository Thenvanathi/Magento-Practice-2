<?php
declare(strict_types=1);

namespace Codilar\Employee\Model\ResourceModel\Employee;

use Codilar\Employee\Model\Employee;
use Codilar\Employee\Model\ResourceModel\Employee as EmployeeResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'employee_id';

    protected function _construct(): void
    {
        $this->_init(Employee::class, EmployeeResource::class);
    }
}
