<?php


namespace Laurensmedia\Advancedshipping\Model\Config\Source;

class LmAdvancedshippingSettingsDisplaytype implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
		return array(
			array('value' => 'all', 'label' => 'Show all available methods'),
			array('value' => 'lowest', 'label' => 'Show method with lowest shipping costs only'),
			array('value' => 'highest', 'label' => 'Show method with highest shipping costs only'),
		);
    }

    public function toArray()
    {
		return array(
			array('value' => 'all', 'label' => 'Show all available methods'),
			array('value' => 'lowest', 'label' => 'Show method with lowest shipping costs only'),
			array('value' => 'highest', 'label' => 'Show method with highest shipping costs only'),
		);
    }
}
