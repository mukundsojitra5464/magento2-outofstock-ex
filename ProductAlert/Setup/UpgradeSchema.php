<?php

namespace Agile\ProductAlert\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;

use Magento\Framework\Setup\ModuleContextInterface;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements  UpgradeSchemaInterface

{

    public function upgrade(SchemaSetupInterface $setup,ModuleContextInterface $context){

            $setup->startSetup();

            // Get module table

            $tableName = $setup->getTable('product_alert_stock');

            // Check if the table already exists

            if ($setup->getConnection()->isTableExists($tableName) == true) {

            // Declare data

            $columns = [

            'email' => [

            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,

            'nullable' => true,

            'comment' => 'Email',

            ],

            ];

            $connection = $setup->getConnection();

            foreach ($columns as $name => $definition) {

            $connection->addColumn($tableName, $name, $definition);

            }

            
            }

            $setup->endSetup();
    }

}