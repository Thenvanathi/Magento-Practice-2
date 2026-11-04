<?php
namespace Codilar\AroundPlugin\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Framework\DataObject;
use Psr\Log\LoggerInterface;

/**
 * Plugin: times every Cart::addProduct() call and records whether it succeeded or failed.
 *
 * Why "around" is needed (before/after cannot do this):
 *  - The start time is a local variable that must live until the original method ends.
 *    A before plugin and an after plugin cannot share local state.
 *  - If the original method throws (out of stock, invalid qty, blocked SKU...), an after plugin
 *    never runs, so a failure could not be logged. Around can catch it, log it and rethrow.
 */
class AddToCartAudit
{
    /**
     * @param LoggerInterface $logger Dedicated logger, wired in di.xml
     */
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Execution flow:
     *  1. Start the timer.
     *  2. $proceed() runs the original addProduct() (and plugins with a higher sortOrder).
     *  3. Success: log duration and result, return the original result untouched.
     *  4. Failure: log duration and error message, then rethrow the SAME exception so the
     *     storefront behaves exactly as before.
     *
     * @param Cart $subject
     * @param callable $proceed Original addProduct() call
     * @param int|string|Product $productInfo
     * @param DataObject|int|array|null $requestInfo
     * @return Cart
     * @throws \Throwable
     */
    public function aroundAddProduct(Cart $subject, callable $proceed, $productInfo, $requestInfo = null)
    {
        $start = hrtime(true);

        try {
            $result = $proceed($productInfo, $requestInfo);
        } catch (\Throwable $e) {
            $this->log('failed', $start, $productInfo, $requestInfo, $e->getMessage());
            throw $e;
        }

        $this->log('success', $start, $productInfo, $requestInfo);

        return $result;
    }

    /**
     * Same audit for quantity changes in the cart page / mini-cart (Cart::updateItems).
     *
     * @param Cart $subject
     * @param callable $proceed Original updateItems() call
     * @param array $data Item id => ['qty' => ...]
     * @return Cart
     * @throws \Throwable
     */
    public function aroundUpdateItems(Cart $subject, callable $proceed, $data)
    {
        $start = hrtime(true);
        $label = 'UPDATE ' . $this->describeItems($subject, $data);
        $qty = is_array($data) && count($data) === 1 ? (current($data)['qty'] ?? null) : null;

        try {
            $result = $proceed($data);
        } catch (\Throwable $e) {
            $this->log('failed', $start, $label, $qty, $e->getMessage());
            throw $e;
        }

        $this->log('success', $start, $label, $qty);

        return $result;
    }

    /**
     * @param Cart $subject
     * @param mixed $data
     * @return string SKUs of the cart lines being changed
     */
    private function describeItems(Cart $subject, $data): string
    {
        $skus = [];
        foreach (is_array($data) ? array_keys($data) : [] as $itemId) {
            $item = $subject->getQuote()->getItemById((int)$itemId);
            $skus[] = $item ? $item->getSku() : 'item:' . $itemId;
        }

        return implode(',', $skus);
    }

    /**
     * Write one readable audit line, e.g.
     *   SUCCESS | BLOCK-101 | qty 44 | 7.6 ms
     *   FAILED  | BLOCK-101 | qty 400 | 5.9 ms | Not enough items for sale
     * A logging problem must never break the cart.
     *
     * @param string $outcome
     * @param int $start hrtime(true) value taken before the original call
     * @param mixed $productInfo
     * @param mixed $requestInfo
     * @param string|null $error
     * @return void
     */
    private function log(string $outcome, int $start, $productInfo, $requestInfo, ?string $error = null): void
    {
        try {
            $line = sprintf(
                '%s | %s | qty %s | %s ms',
                strtoupper($outcome),
                $this->describeProduct($productInfo),
                $this->getQty($requestInfo) ?? '?',
                round((hrtime(true) - $start) / 1e6, 2)
            );
            if ($error !== null) {
                $line .= ' | ' . $error;
            }
            $this->logger->info($line);
        } catch (\Throwable $e) {
            // Intentionally ignored.
        }
    }

    /**
     * @param mixed $productInfo Product, product id, or a ready-made "UPDATE <sku>" label
     * @return string
     */
    private function describeProduct($productInfo): string
    {
        if ($productInfo instanceof Product) {
            return (string)$productInfo->getSku();
        }

        return is_string($productInfo) && str_starts_with($productInfo, 'UPDATE ')
            ? $productInfo
            : 'id:' . $productInfo;
    }

    /**
     * @param mixed $requestInfo
     * @return float|int|string|null
     */
    private function getQty($requestInfo)
    {
        if ($requestInfo instanceof DataObject) {
            return $requestInfo->getData('qty');
        }
        if (is_array($requestInfo)) {
            return $requestInfo['qty'] ?? null;
        }

        return is_numeric($requestInfo) ? $requestInfo : null;
    }
}
