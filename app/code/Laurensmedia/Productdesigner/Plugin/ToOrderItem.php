<?php
namespace Laurensmedia\Productdesigner\Plugin;
 
use Magento\Quote\Model\Quote\Item\ToOrderItem as QuoteToOrderItem;
 
class ToOrderItem
{
    /**
     * aroundConvert
     *
     * @param QuoteToOrderItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param array $data
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    public function aroundConvert(
        QuoteToOrderItem $subject,
        \Closure $proceed,
        $item,
        $data = []
    ) {
        // Get Order Item
        $orderItem = $proceed($item, $data);
        // Get Quote Item's additional Options
        $additionalOptions = $item->getOptionByCode('additional_options');
 
        // Check if there is any additional options in Quote Item
        if ($additionalOptions && is_array($additionalOptions) && count($additionalOptions) > 0) {
            // Get Order Item's other options
            $options = $orderItem->getProductOptions();
            // Set additional options to Order Item
            $options['additional_options'] = json_decode($additionalOptions->getValue(), true);
            $orderItem->setProductOptions($options);
        }
        
        $productDesignerData = $item->getProductdesignerData();
        if($productDesignerData != ''){
            $orderItem->setProductdesignerData($productDesignerData);
        }
 
        return $orderItem;
    }
}