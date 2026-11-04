<?php
declare(strict_types=1);

namespace Codilar\Employee\Controller\Adminhtml\Employee;

use Codilar\Employee\Model\EmployeeFactory;
use Codilar\Employee\Model\ResourceModel\Employee as EmployeeResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\AlreadyExistsException;

class Save extends Action implements HttpPostActionInterface
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
        $redirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $redirect->setPath('*/*/');
        }

        $id = (int)($data['employee_id'] ?? 0);
        $employee = $this->employeeFactory->create();
        if ($id) {
            $this->employeeResource->load($employee, $id);
            if (!$employee->getId()) {
                $this->messageManager->addErrorMessage(__('This employee no longer exists.'));

                return $redirect->setPath('*/*/');
            }
        }

        $employee->setData('first_name', trim((string)($data['first_name'] ?? '')));
        $employee->setData('last_name', trim((string)($data['last_name'] ?? '')));
        $employee->setData('email', trim((string)($data['email'] ?? '')));
        $employee->setData('department', trim((string)($data['department'] ?? '')) ?: null);

        try {
            $this->employeeResource->save($employee);
            $this->messageManager->addSuccessMessage(__('Employee saved.'));

            if ($this->getRequest()->getParam('back')) {
                return $redirect->setPath('*/*/edit', ['employee_id' => $employee->getId()]);
            }

            return $redirect->setPath('*/*/');
        } catch (AlreadyExistsException) {
            $this->messageManager->addErrorMessage(__('An employee with this email already exists.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $redirect->setPath('*/*/edit', $id ? ['employee_id' => $id] : []);
    }
}
