<?php
declare(strict_types=1);

namespace Codilar\Employee\Model\Employee;

use Codilar\Employee\Model\ResourceModel\Employee\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        $items = [];
        foreach ($this->collection->getItems() as $employee) {
            $items[$employee->getId()] = $employee->getData();
        }

        return $items;
    }
}
