<?php
declare(strict_types=1);

namespace Codilar\Employee\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'codilar_employee/general/enabled';
    private const XML_PATH_PAGE_TITLE = 'codilar_employee/general/page_title';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getPageTitle(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_PAGE_TITLE, ScopeInterface::SCOPE_STORE);
    }
}
