<?php
declare(strict_types=1);

namespace Codilar\StoreSetup\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteFactory;

/**
 * Creates the store structure: 2 stores, each with 2 store views, under the existing website.
 *
 *   Website "base" (existing)
 *   |-- Store "main_website_store" (existing, root category "Women's Store")
 *   |     |-- store view "default"  (existing)
 *   |     `-- store view "main_ta"  (Tamil)
 *   `-- Store "mens_store" (new, new root category "Men's Store")
 *         |-- store view "mens_en"  (English)
 *         `-- store view "mens_ta"  (Tamil)
 *
 * Every step first looks for an existing entity by its code, so running the patch on a
 * database that already has some of these entities does not create duplicates.
 */
class CreateStoresAndStoreViews implements DataPatchInterface
{
    private const WEBSITE_CODE = 'base';

    // Store group code => [name, root category name, store views (code => name)].
    private const STRUCTURE = [
        'main_website_store' => [
            'name' => 'Main Website Store',
            'root_category' => "Women's Store",
            'views' => ['default' => 'Default Store View', 'main_ta' => 'Tamil'],
        ],
        'mens_store' => [
            'name' => "Men's Store",
            'root_category' => "Men's Store",
            'views' => ['mens_en' => 'English', 'mens_ta' => 'Tamil'],
        ],
    ];

    public function __construct(
        private readonly WebsiteFactory $websiteFactory,
        private readonly WebsiteResource $websiteResource,
        private readonly GroupFactory $groupFactory,
        private readonly GroupResource $groupResource,
        private readonly StoreFactory $storeFactory,
        private readonly StoreResource $storeResource,
        private readonly CategoryFactory $categoryFactory,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly CategoryResource $categoryResource,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function apply(): self
    {
        $websiteId = $this->getWebsiteId();

        foreach (self::STRUCTURE as $groupCode => $definition) {
            $rootCategoryId = $this->getOrCreateRootCategory($definition['root_category']);
            $group = $this->getOrCreateGroup($groupCode, $definition['name'], $websiteId, $rootCategoryId);

            $sortOrder = 0;
            foreach ($definition['views'] as $viewCode => $viewName) {
                $this->createStoreViewIfMissing($viewCode, $viewName, $websiteId, (int)$group->getId(), $sortOrder++);
            }

            $this->ensureDefaultStoreView($group, array_key_first($definition['views']));
        }

        // Make the running process aware of the new stores.
        $this->storeManager->reinitStores();

        return $this;
    }

    private function getWebsiteId(): int
    {
        $website = $this->websiteFactory->create();
        $this->websiteResource->load($website, self::WEBSITE_CODE, 'code');
        if (!$website->getId()) {
            throw new \RuntimeException(sprintf('Website "%s" does not exist.', self::WEBSITE_CODE));
        }

        return (int)$website->getId();
    }

    /**
     * A root category is a level-1 category (child of the invisible tree root, id 1).
     */
    private function getOrCreateRootCategory(string $name): int
    {
        $existing = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('level', 1)
            ->addAttributeToFilter('name', $name)
            ->getFirstItem();
        if ($existing->getId()) {
            return (int)$existing->getId();
        }

        $category = $this->categoryFactory->create();
        $category->setName($name)
            ->setParentId(Category::TREE_ROOT_ID)
            ->setPath((string)Category::TREE_ROOT_ID)
            ->setIsActive(true)
            ->setIncludeInMenu(true)
            ->setStoreId(0);

        $category = $this->categoryRepository->save($category);

        // The saved path must be "<tree root>/<own id>" (for example 1/14), like the existing
        // root category 1/2. When it is not, the category tree cannot place it correctly.
        $expectedPath = Category::TREE_ROOT_ID . '/' . $category->getId();
        if ($category->getPath() !== $expectedPath) {
            $category->setPath($expectedPath);
            $this->categoryResource->save($category);
        }

        return (int)$category->getId();
    }

    private function getOrCreateGroup(string $code, string $name, int $websiteId, int $rootCategoryId)
    {
        $group = $this->groupFactory->create();
        $this->groupResource->load($group, $code, 'code');
        if ($group->getId()) {
            return $group;
        }

        $group->setCode($code)
            ->setName($name)
            ->setWebsiteId($websiteId)
            ->setRootCategoryId($rootCategoryId);
        $this->groupResource->save($group);

        return $group;
    }

    private function createStoreViewIfMissing(
        string $code,
        string $name,
        int $websiteId,
        int $groupId,
        int $sortOrder
    ): void {
        $store = $this->storeFactory->create();
        $this->storeResource->load($store, $code, 'code');
        if ($store->getId()) {
            return;
        }

        $store->setCode($code)
            ->setName($name)
            ->setWebsiteId($websiteId)
            ->setGroupId($groupId)
            ->setSortOrder($sortOrder)
            ->setIsActive(1);
        $this->storeResource->save($store);
    }

    /**
     * A store (group) must have a default store view. Set it only when none is set.
     */
    private function ensureDefaultStoreView($group, string $viewCode): void
    {
        if ((int)$group->getDefaultStoreId() > 0) {
            return;
        }

        $store = $this->storeFactory->create();
        $this->storeResource->load($store, $viewCode, 'code');
        $group->setDefaultStoreId((int)$store->getId());
        $this->groupResource->save($group);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
