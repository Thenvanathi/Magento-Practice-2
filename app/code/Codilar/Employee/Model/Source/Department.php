<?php
declare(strict_types=1);

namespace Codilar\Employee\Model\Source;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\OptionSourceInterface;

class Department implements OptionSourceInterface
{
    public function __construct(
        private readonly ResourceConnection $resource
    ) {
    }

    public function toOptionArray(): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName('codilar_employee'), 'department')
            ->where('department IS NOT NULL')
            ->where('department != ?', '')
            ->distinct()
            ->order('department ASC');

        $options = [];
        foreach ($connection->fetchCol($select) as $department) {
            $options[] = ['value' => $department, 'label' => $department];
        }

        return $options;
    }
}
