<?php
namespace Codilar\AfterPlugin\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

/**
 * Plugin: Appends a suffix to the returned name of products whose SKU starts with "COD-".
 */
class FeaturedProductName
{
    private const SKU_PREFIX = 'COD-';
    private const NAME_SUFFIX = ' - Codilar Featured';

    /**
     * @param State $appState
     */
    public function __construct(
        private readonly State $appState
    ) {
    }

    /**
     * Runs after Product::getName() and receives its return value.
     *
     * Execution flow:
     *  1. Any code calls $product->getName() (storefront, API, ...).
     *  2. The original getName() runs and returns the name stored on the product.
     *  3. Magento passes that value here as $result.
     *  4. SKU starts with "COD-": the suffix is appended to $result.
     *     Otherwise $result is returned unchanged.
     *  5. Only the returned value changes. The product data and the database are never touched.
     *
     * @param Produc
     * t $subject
     * @param string|null $result Name returned by the original getName()
     * @return string|null
     */
    public function afterGetName(Product $subject, $result)
    {
        if ($result === null || $this->isAdminArea()) {
            return $result;
        }

        $sku = (string)$subject->getSku();
        if (str_starts_with($sku, self::SKU_PREFIX) && !str_ends_with($result, self::NAME_SUFFIX)) {
            return $result . self::NAME_SUFFIX;
        }

        return $result;
    }

    /**
     * The admin form re-submits getName() values on save, so the suffix is not applied there,
     * otherwise it could end up stored in the database.
     *
     * @return bool
     */
    private function isAdminArea(): bool
    {
        try {
            return $this->appState->getAreaCode() === Area::AREA_ADMINHTML;
        } catch (LocalizedException $e) {
            // Area not set (e.g. CLI script): treat as non-admin.
            return false;
        }
    }
}
