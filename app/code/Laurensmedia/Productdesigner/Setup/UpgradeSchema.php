<?php
namespace Laurensmedia\Productdesigner\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context){
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
		    $eavTable = $installer->getTable('quote_item');
		
		    $columns = [
		        'productdesigner_data' => [
		            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		            'nullable' => true,
		            'comment' => 'productdesigner_data',
		        ],
		    ];
		
		    $connection = $installer->getConnection();
		    foreach ($columns as $name => $definition) {
		        $connection->addColumn($eavTable, $name, $definition);
		    }
		    
        }
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
		    $eavTable = $installer->getTable('sales_order_item');
		
		    $columns = [
		        'productdesigner_data' => [
		            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
		            'nullable' => true,
		            'comment' => 'productdesigner_data',
		        ],
		    ];
		
		    $connection = $installer->getConnection();
		    foreach ($columns as $name => $definition) {
		        $connection->addColumn($eavTable, $name, $definition);
		    }
		}
        $installer->endSetup();
    }
}