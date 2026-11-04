<?php
declare(strict_types=1);

namespace Codilar\Employee\Block\Adminhtml\Employee\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        if (!$this->getEmployeeId()) {
            return [];
        }

        $url = $this->getUrl('*/*/delete', ['employee_id' => $this->getEmployeeId()]);

        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'on_click' => sprintf(
                "deleteConfirm('%s', '%s')",
                __('Are you sure you want to delete this employee?'),
                $url
            ),
            'sort_order' => 20,
        ];
    }
}
