<?php
namespace Codilar\AfterPlugin\Controller\Demo;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Demo page: /afterplugin/demo?sku=<sku>
 *
 * Shows getName() (after plugin applied) next to the raw stored value.
 */
class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly RawFactory $rawFactory,
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function execute()
    {
        $result = $this->rawFactory->create();
        $result->setHeader('Content-Type', 'text/plain; charset=utf-8');

        $sku = (string)$this->request->getParam('sku', '');
        if ($sku === '') {
            return $result->setContents('Usage: /afterplugin/demo?sku=COD-101');
        }

        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            return $result->setContents("No product with SKU {$sku}");
        }

        return $result->setContents(
            "SKU:                 {$product->getSku()}\n"
            . "getName() (plugin):  {$product->getName()}\n"
            . "Stored name (raw):   {$product->getData('name')}\n"
        );
    }
}
