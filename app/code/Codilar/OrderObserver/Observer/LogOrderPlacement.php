<?php
namespace Codilar\OrderObserver\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Observer: records a reference to every order placed through checkout.
 *
 * Event: checkout_submit_all_after, dispatched at the end of QuoteManagement::placeOrder()
 * once the order has been submitted and saved. Payload: 'order' and 'quote'.
 */
class LogOrderPlacement implements ObserverInterface
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
     *  1. Customer places an order; QuoteManagement::placeOrder() submits and saves it.
     *  2. Magento dispatches checkout_submit_all_after with the order and quote.
     *  3. This method checks the payload really holds a saved order, then logs references only.
     *
     * "Order placed" does not mean "paid": for online payment methods the order can still be
     * awaiting payment, so the state and status are logged as facts and nothing is assumed.
     * No customer, address or payment data is logged.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getData('order');

        // Ignore the event if the payload is not a saved order.
        if (!$order instanceof OrderInterface || !$order->getEntityId()) {
            return;
        }

        // Logging must never break checkout.
        try {
            $this->logger->info('Order placed', [
                'entity_id' => (int)$order->getEntityId(),
                'increment_id' => (string)$order->getIncrementId(),
                'created_at' => $order->getCreatedAt(),
                'state' => $order->getState(),
                'status' => $order->getStatus(),
            ]);
        } catch (\Throwable $e) {
            // Intentionally swallowed: a log failure m`ust not stop the customer's order.
        }
    }
}
