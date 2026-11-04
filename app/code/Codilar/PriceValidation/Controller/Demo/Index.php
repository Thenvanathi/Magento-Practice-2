<?php
namespace Codilar\PriceValidation\Controller\Demo;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

/**
 * Demo page: /pricevalidation/demo?price=<value>
 *
 * Tries to save a throwaway product through the product repository so the
 * ProductPriceValidation plugin can be seen working from a browser.
 * Developer mode only. A product that saves successfully is deleted again.
 */
class Index implements HttpGetActionInterface
{
    private const DEMO_SKU = 'codilar-price-validation-demo';

    public function __construct(
        private readonly RequestInterface $request,
        private readonly RawFactory $rawFactory,
        private readonly ProductInterfaceFactory $productFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly State $appState,
        private readonly Registry $registry
    ) {
    }

    public function execute()
    {
        $result = $this->rawFactory->create();
        $result->setHeader('Content-Type', 'text/plain; charset=utf-8');

        // Never expose a product-creating URL outside developer mode.
        if ($this->appState->getMode() !== State::MODE_DEVELOPER) {
            return $result->setContents('Demo page is available in developer mode only.');
        }

        $price = (string)$this->request->getParam('price', '');
        if ($price === '' || !is_numeric($price)) {
            return $result->setContents("Usage: /pricevalidation/demo?price=10  (try 10, 0 and -5)");
        }

        $product = $this->productFactory->create();
        $product->setSku(self::DEMO_SKU)
            ->setName('Price Validation Demo')
            ->setTypeId('simple')
            ->setAttributeSetId(4)
            ->setStatus(2) // disabled, so it never shows in the storefront
            ->setVisibility(1)
            ->setPrice((float)$price);

        try {
            // The plugin's beforeSave() runs first; it throws here for price <= 0.
            $saved = $this->productRepository->save($product);
        } catch (LocalizedException $e) {
            return $result->setContents("BLOCKED by plugin: " . $e->getMessage());
        }

        // Valid price: save went through. Remove the throwaway product again
        // (deleting outside the admin area needs the secure-area flag).
        $this->registry->register('isSecureArea', true, true);
        try {
            $this->productRepository->delete($saved);
        } finally {
            $this->registry->unregister('isSecureArea');
        }

        return $result->setContents(
            "PASS: price {$price} is valid. save() continued normally (demo product deleted again)."
        );
    }
}
