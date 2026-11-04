<?php
declare(strict_types=1);

namespace Codilar\Employee\Controller\Adminhtml\Employee;

use Codilar\Employee\Model\EmployeeFactory;
use Codilar\Employee\Model\ResourceModel\Employee as EmployeeResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_Employee::employees';

    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly EmployeeFactory $employeeFactory,
        private readonly EmployeeResource $employeeResource
    ) {
        parent::__construct($context);
    }

    public function execute(): Page|Redirect
    {
        $id = (int)$this->getRequest()->getParam('employee_id');
        if ($id) {
            $employee = $this->employeeFactory->create();
            $this->employeeResource->load($employee, $id);
            if (!$employee->getId()) {
                $this->messageManager->addErrorMessage(__('This employee no longer exists.'));

                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $page = $this->pageFactory->create();
        $page->setActiveMenu('Codilar_Employee::employees');
        $page->getConfig()->getTitle()->prepend($id ? __('Edit Employee') : __('Add Employee'));

        return $page;
    }
}
