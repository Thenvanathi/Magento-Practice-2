<?php
declare(strict_types=1);

namespace Codilar\Homepage\ViewModel;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Data for the homepage template. Categories are looked up by name inside the current store's own
 * root category, so a store view without those categories simply shows no tiles (no broken links).
 */
class Homepage implements ArgumentInterface
{
    private const BEAUTY_CATEGORIES = ['makeup', 'skincare', 'hair care'];
    private const EXTRA_CATEGORIES = ['bags', 'footwear', 'jewelry & accessories'];
    private const ROUTINE = [
        'Cleanse' => 'SKIN-CLEANSER-001',
        'Treat' => 'SKIN-SERUM-001',
        'Protect' => 'SKIN-SUNSCREEN-001',
    ];
    private const PRODUCTS_PER_CATEGORY = 4;

    /** @var Category[]|null lowercase name => category */
    private ?array $categories = null;

    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly Visibility $productVisibility,
        private readonly ImageHelper $imageHelper,
        private readonly PriceCurrencyInterface $priceCurrency
    ) {
    }

    /**
     * @return array<int, array{key: string, name: string, url: string, products: array}>
     */
    public function getBeautySections(): array
    {
        $sections = [];
        foreach (self::BEAUTY_CATEGORIES as $name) {
            $category = $this->getCategories()[$name] ?? null;
            if (!$category) {
                continue;
            }
            $sections[] = [
                'key' => trim((string)preg_replace('/[^a-z0-9]+/', '-', $name), '-'),
                'name' => (string)$category->getName(),
                'url' => (string)$category->getUrl(),
                'products' => $this->loadCategoryProducts($category),
            ];
        }

        return $sections;
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    public function getExtraCategories(): array
    {
        $extra = [];
        foreach (self::EXTRA_CATEGORIES as $name) {
            $category = $this->getCategories()[$name] ?? null;
            if ($category) {
                $extra[] = ['name' => (string)$category->getName(), 'url' => (string)$category->getUrl()];
            }
        }

        return $extra;
    }

    /**
     * @return array<int, array{step: string, product: array}>
     */
    public function getRoutine(): array
    {
        $collection = $this->createProductCollection();
        $collection->addAttributeToFilter('sku', ['in' => array_values(self::ROUTINE)]);

        $bySku = [];
        foreach ($collection as $product) {
            $bySku[$product->getSku()] = $this->toArray($product);
        }

        $routine = [];
        foreach (self::ROUTINE as $step => $sku) {
            if (isset($bySku[$sku])) {
                $routine[] = ['step' => $step, 'product' => $bySku[$sku]];
            }
        }

        return $routine;
    }

    /**
     * @return Category[]
     */
    private function getCategories(): array
    {
        if ($this->categories !== null) {
            return $this->categories;
        }

        $store = $this->storeManager->getStore();
        $collection = $this->categoryCollectionFactory->create();
        $collection->setStoreId((int)$store->getId())
            ->addAttributeToSelect(['name', 'url_key', 'url_path'])
            ->addIsActiveFilter()
            ->addFieldToFilter('path', ['like' => '1/' . $store->getRootCategoryId() . '/%'])
            ->addUrlRewriteToResult();

        $this->categories = [];
        foreach ($collection as $category) {
            $this->categories[mb_strtolower((string)$category->getName())] ??= $category;
        }

        return $this->categories;
    }

    private function loadCategoryProducts(Category $category): array
    {
        $collection = $this->createProductCollection();
        $collection->addCategoryFilter($category)
            ->setOrder('entity_id', 'DESC')
            ->setPageSize(self::PRODUCTS_PER_CATEGORY);

        return array_map([$this, 'toArray'], array_values($collection->getItems()));
    }

    private function createProductCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'price', 'small_image', 'url_key'])
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds())
            ->addStoreFilter()
            ->addUrlRewrite();

        return $collection;
    }

    private function toArray(Product $product): array
    {
        return [
            'name' => (string)$product->getName(),
            'url' => $product->getProductUrl(),
            'price' => $this->priceCurrency->format((float)$product->getFinalPrice(), false),
            'image' => $this->imageHelper->init($product, 'category_page_grid')->getUrl(),
        ];
    }
}
