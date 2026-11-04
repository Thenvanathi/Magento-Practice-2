<?php
namespace Codilar\AroundPlugin\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Plugin: replaces Magento's generic out-of-stock error with a friendlier one.
 *
 * Why "around" is needed (before/after cannot do this):
 *  - The error is thrown INSIDE the original addProduct(). A before plugin runs too early to see it.
 *  - An after plugin never runs when the original throws.
 *  - Only around can call $proceed() inside try/catch and throw a different exception instead.
 */
class FriendlyStockMessage
{
    /** Stock errors raised by Magento that we replace. */
    private const STOCK_ERRORS = [
        'Not enough items for sale',
        'The requested qty is not available',
    ];

    /**
     * Execution flow:
     *  1. $proceed() runs the original addProduct().
     *  2. No exception: return its result untouched.
     *  3. Stock exception: throw a new, friendlier LocalizedException (original kept as "previous").
     *  4. Any other exception: rethrow it unchanged.
     *
     * @param Cart $subject
     * @param callable $proceed Original addProduct() call
     * @param int|string|Product $productInfo
     * @param DataObject|int|array|null $requestInfo
     * @return Cart
     * @throws LocalizedException
     */
    public function aroundAddProduct(Cart $subject, callable $proceed, $productInfo, $requestInfo = null)
    {
        try {
            return $proceed($productInfo, $requestInfo);
        } catch (LocalizedException $e) {
            if (!$this->isStockError($e->getMessage())) {
                throw $e;
            }

            $name = $productInfo instanceof Product ? (string)$productInfo->getName() : '';
            $message = $name !== ''
                ? __('Sorry, we do not have that many "%1" in stock. Please reduce the quantity and try again.', $name)
                : __('Sorry, we do not have that many in stock. Please reduce the quantity and try again.');

            throw new LocalizedException($message, $e);
        }
    }

    /**
     * Same replacement for quantity changes in the cart page / mini-cart
     * (Cart::updateItems), which does not go through addProduct().
     *
     * @param Cart $subject
     * @param callable $proceed Original updateItems() call
     * @param array $data Item id => ['qty' => ...]
     * @return Cart
     * @throws LocalizedException
     */
    public function aroundUpdateItems(Cart $subject, callable $proceed, $data)
    {
        try {
            return $proceed($data);
        } catch (LocalizedException $e) {
            if (!$this->isStockError($e->getMessage())) {
                throw $e;
            }

            // Name the product when exactly one cart line is being changed.
            $name = '';
            if (is_array($data) && count($data) === 1) {
                $item = $subject->getQuote()->getItemById((int)array_key_first($data));
                $name = $item ? (string)$item->getName() : '';
            }
            $message = $name !== ''
                ? __('Sorry, we do not have that many "%1" in stock. Please reduce the quantity and try again.', $name)
                : __('Sorry, we do not have that many in stock. Please reduce the quantity and try again.');

            throw new LocalizedException($message, $e);
        }
    }

    /**
     * @param string $message
     * @return bool
     */
    private function isStockError(string $message): bool
    {
        foreach (self::STOCK_ERRORS as $error) {
            if (str_contains($message, $error)) {
                return true;
            }
        }

        return false;
    }
}
