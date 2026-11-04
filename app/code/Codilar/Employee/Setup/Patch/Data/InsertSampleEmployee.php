<?php
declare(strict_types=1);

namespace Codilar\Employee\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InsertSampleEmployee implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $connection->insertOnDuplicate(
            $this->moduleDataSetup->getTable('codilar_employee'),
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@codilar.com',
                'department' => 'Engineering',
            ],
            ['first_name', 'last_name', 'department']
        );

        $connection->endSetup();

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
