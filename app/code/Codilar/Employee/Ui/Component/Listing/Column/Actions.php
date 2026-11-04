<?php
declare(strict_types=1);

namespace Codilar\Employee\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $id = $item['employee_id'];
            $name = $item['first_name'] . ' ' . $item['last_name'];
            $item[$this->getData('name')] = [
                'edit' => [
                    'href' => $this->urlBuilder->getUrl('codilar_employee/employee/edit', ['employee_id' => $id]),
                    'label' => __('Edit'),
                ],
                'delete' => [
                    'href' => $this->urlBuilder->getUrl('codilar_employee/employee/delete', ['employee_id' => $id]),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete employee'),
                        'message' => __('Are you sure you want to delete "%1"?', $name),
                    ],
                ],
            ];
        }

        return $dataSource;
    }
}
