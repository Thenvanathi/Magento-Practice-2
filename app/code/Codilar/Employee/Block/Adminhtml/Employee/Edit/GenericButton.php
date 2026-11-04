<?php
declare(strict_types=1);

namespace Codilar\Employee\Block\Adminhtml\Employee\Edit;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class GenericButton
{
    public function __construct(
        protected readonly UrlInterface $urlBuilder,
        protected readonly RequestInterface $request
    ) {
    }

    public function getEmployeeId(): int
    {
        return (int)$this->request->getParam('employee_id');
    }

    public function getUrl(string $route, array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
