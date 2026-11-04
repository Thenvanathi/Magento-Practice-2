<?php
declare(strict_types=1);

namespace Codilar\Employee\Controller\Adminhtml\Employee;

use Codilar\Employee\Model\EmployeeFactory;
use Codilar\Employee\Model\ResourceModel\Employee as EmployeeResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Delete extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_Employee::employees';

    public function __construct(
        Context $context,
        private readonly EmployeeFactory $employeeFactory,
        private readonly EmployeeResource $employeeResource
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $id = (int)$this->getRequest()->getParam('employee_id');
        try {
            $employee = $this->employeeFactory->create();
            $this->employeeResource->load($employee, $id);
            if (!$employee->getId()) {
                throw new \InvalidArgumentException((string)__('This employee no longer exists.'));
            }
            $this->employeeResource->delete($employee);
            $this->messageManager->addSuccessMessage(__('Employee deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
