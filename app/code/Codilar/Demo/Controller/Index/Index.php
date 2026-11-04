<?php
namespace Codilar\Demo\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Controller: Handles GET requests to /demo/index/index.
 */
class Index implements HttpGetActionInterface
{
    /**
     * PageFactory creates a Magento frontend page result.
     *
     * @param PageFactory $pageFactory
     */
    public function __construct(
        private readonly PageFactory $pageFactory
    ) {
    }

    /**
     * Execute the controller action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Ask Magento to render a page using layout XML and templates.
        return $this->pageFactory->create();
    }
}
