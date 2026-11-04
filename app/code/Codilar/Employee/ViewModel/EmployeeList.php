<?php
declare(strict_types=1);

namespace Codilar\Employee\ViewModel;

use Codilar\Employee\Model\Config;
use Codilar\Employee\Model\ResourceModel\Employee\CollectionFactory;
use Codilar\Employee\Model\Source\Department;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class EmployeeList implements ArgumentInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly CollectionFactory $collectionFactory,
        private readonly Department $departmentSource,
        private readonly Config $config
    ) {
    }

    public function getPageTitle(): string
    {
        return $this->config->getPageTitle() ?: (string)__('Our Team');
    }

    public function getSearch(): string
    {
        return trim((string)$this->request->getParam('q', ''));
    }

    public function getSelectedDepartment(): string
    {
        return trim((string)$this->request->getParam('department', ''));
    }

    public function getDepartments(): array
    {
        return array_column($this->departmentSource->toOptionArray(), 'value');
    }

    public function getEmployees(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->setOrder('first_name', 'ASC');

        if ($this->getSelectedDepartment() !== '') {
            $collection->addFieldToFilter('department', $this->getSelectedDepartment());
        }

        if ($this->getSearch() !== '') {
            $like = ['like' => '%' . $this->getSearch() . '%'];
            $collection->addFieldToFilter(['first_name', 'last_name', 'department'], [$like, $like, $like]);
        }

        return $collection->getItems();
    }
}
