<?php
namespace Codilar\PriceValidation\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Plugin: Rejects products with a zero or negative price before they are saved.
 */
class ProductPriceValidation
{
    /**
     * Runs before ProductRepositoryInterface::save(), with the same arguments.
     *
     * Execution flow:
     *  1. Caller invokes ProductRepositoryInterface::save($product, $saveOptions).
     *  2. Magento runs this method first.
     *  3. Invalid price: a LocalizedException is thrown, the original save() never runs
     *     and nothing is written to the database.
     *  4. Valid price: the arguments are left unchanged and the original save() runs.
     *
     * Used for two targets with the same shape: ProductRepositoryInterface::save($product, $saveOptions)
     * (REST, GraphQL, code) and ResourceModel\Product::save($product) (admin form, $product->save()).
     *
     * @param ProductRepositoryInterface|\Magento\Catalog\Model\ResourceModel\Product $subject
     * @param ProductInterface $product
     * @param bool $saveOptions
     * @return array|null
     * @throws LocalizedException
     */
    public function beforeSave(
        $subject,
        ProductInterface $product,
        $saveOptions = false
    ): ?array {
        $price = $product->getPrice();

        // No price on the product (e.g. configurable or dynamic-price bundle): nothing to validate.
        if ($price === null || $price === '') {
            return null;
        }

        if ((float)$price <= 0) {
            throw new LocalizedException(
                __('Product price must be greater than zero. "%1" was given.', $price)
            );
        }

        // Returning null leaves the original arguments untouched.
        return null;
    }
}
