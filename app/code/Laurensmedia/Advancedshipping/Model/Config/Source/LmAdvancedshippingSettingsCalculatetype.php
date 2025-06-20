<?php


namespace Laurensmedia\Advancedshipping\Model\Config\Source;

class LmAdvancedshippingSettingsCalculatetype implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
		return array(
			array('value' => 'all', 'label' => 'Count every item as seperate package'),
			array('value' => 'once', 'label' => 'Count every seperate product only once'),
			array('value' => 'highestonly', 'label' => 'Count the item with the highest shipping price only'),
		);
    }

    public function toArray()
    {
		return array(
			array('value' => 'all', 'label' => 'Count every item as seperate package'),
			array('value' => 'once', 'label' => 'Count every seperate product only once'),
			array('value' => 'highestonly', 'label' => 'Count the item with the highest shipping price only'),
		);
    }
}
