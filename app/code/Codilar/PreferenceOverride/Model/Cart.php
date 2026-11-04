<?php
namespace Codilar\PreferenceOverride\Model;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart as VendorCart;

/**
 * Overrides the vendor Cart class (via preference in di.xml).
 *
 * Business rule for this exercise: a product priced below Rs.100 is added with quantity 2;
 * a product priced Rs.100 or more is added with the quantity requested (normally 1).
 *
 * Only PROTECTED vendor methods are overridden here: _getProduct() and _getProductRequest().
 * A plugin cannot intercept protected methods, a subclass can. Public methods, private methods
 * and the constructor are not touched (private methods cannot be overridden at all).
 */
class Cart extends VendorCart
{
    private const CHEAP_PRODUCT_PRICE_LIMIT = 100;
    private const CHEAP_PRODUCT_MIN_QTY = 2;

    /**
     * Product resolved by the vendor flow, kept so _getProductRequest() can read its price.
     *
     * @var Product|null
     */
    private ?Product $resolvedProduct = null;

    /**
     * Step 1 of the flow: the vendor addProduct() calls this first to load the product.
     * Run the vendor logic, and remember the product it returned.
     *
     * @param int|Product $productInfo
     * @return Product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProduct($productInfo)
    {
        $this->resolvedProduct = parent::_getProduct($productInfo);

        return $this->resolvedProduct;
    }

    /**
     * Step 2 of the flow: the vendor flow calls this to build the request (qty, options).
     * Run the vendor logic, then raise the quantity for products priced below the limit.
     *
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProductRequest($requestInfo)
    {
        $request = parent::_getProductRequest($requestInfo);

        $product = $this->resolvedProduct;
        // The remembered product is used once, so it can never leak into a later call.
        $this->resolvedProduct = null;

        $qty = $request->getQty();
        if ($product !== null
            && is_numeric($qty)
            && (float)$qty < self::CHEAP_PRODUCT_MIN_QTY
            && (float)$product->getFinalPrice() < self::CHEAP_PRODUCT_PRICE_LIMIT
        ) {
            $request->setQty(self::CHEAP_PRODUCT_MIN_QTY);
        }

        return $request;
    }
}
