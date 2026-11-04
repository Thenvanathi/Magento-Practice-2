<?php
declare(strict_types=1);

namespace Codilar\Employee\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Employee extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('codilar_employee', 'employee_id');
    }
}
