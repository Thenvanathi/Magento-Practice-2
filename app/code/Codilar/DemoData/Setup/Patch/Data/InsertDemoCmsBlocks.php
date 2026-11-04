<?php
declare(strict_types=1);

namespace Codilar\DemoData\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Study example: inserts demo rows straight into the `cms_block` table
 * (and links them to all store views through `cms_block_store`).
 *
 * Rows already present (matched by identifier) are skipped, so re-running is safe.
 */
class InsertDemoCmsBlocks implements DataPatchInterface
{
    private const ALL_STORE_VIEWS = 0;

    private const DEMO_BLOCKS = [
        [
            'identifier' => 'demo_welcome_banner',
            'title' => 'Demo Welcome Banner',
            'content' => '<h2>Welcome to our store!</h2><p>Free shipping on orders over $50.</p>',
        ],
        [
            'identifier' => 'demo_footer_note',
            'title' => 'Demo Footer Note',
            'content' => '<p>Demo data inserted by a data patch.</p>',
        ],
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $blockTable = $this->moduleDataSetup->getTable('cms_block');
        $blockStoreTable = $this->moduleDataSetup->getTable('cms_block_store');

        $this->moduleDataSetup->startSetup();

        foreach (self::DEMO_BLOCKS as $block) {
            $exists = $connection->fetchOne(
                $connection->select()
                    ->from($blockTable, 'block_id')
                    ->where('identifier = ?', $block['identifier'])
            );
            if ($exists) {
                continue;
            }

            // block_id is auto-increment, creation/update time have DB defaults.
            $connection->insert($blockTable, $block + ['is_active' => 1]);

            // A block is only visible once it is linked to a store (0 = all store views).
            $connection->insert($blockStoreTable, [
                'block_id' => (int)$connection->lastInsertId($blockTable),
                'store_id' => self::ALL_STORE_VIEWS,
            ]);
        }

        $this->moduleDataSetup->endSetup();

        return $this;
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
