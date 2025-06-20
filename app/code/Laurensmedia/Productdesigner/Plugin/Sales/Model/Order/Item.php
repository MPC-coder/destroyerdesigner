<?php
namespace Laurensmedia\Productdesigner\Plugin\Sales\Model\Order;
 
use Magento\Sales\Model\Order\Item as OrderItem;
 
class Item
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
    public function aroundGetProductOptions(
        OrderItem $subject,
        \Closure $proceed
    ) {
        // Get Order Item
        $orderItemOptions = $proceed();
        
        if($subject->getProductdesignerData() != ''){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$requestInterface = $objectManager->get('Magento\Framework\App\RequestInterface');
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
			$baseUrl = $storeManager->getStore()->getBaseUrl();
			$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')
				->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
			$mediaUrl = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
			
	        $usedLabels = array();
	        if(isset($orderItemOptions['additional_options'])){
		        foreach($orderItemOptions['additional_options'] as $index => $option){
					if(strpos($option['label'], 'Prévisualiser') !== false){
				        unset($orderItemOptions['additional_options'][$index]);
				        continue;
			        }
			        $usedLabels[] = $option['label'];
		        }
		    }

	        $data = json_decode($subject->getProductdesignerData(), true);
	        $usedLabels = array();
	        if(!empty($data['labels'])){
		        foreach($data['labels'] as $label){
					$saveObject = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
						->addFieldToFilter('connect_id', $data['connect_id'])
						->addFieldToFilter('label', $label)
						->getFirstItem();

			        $orderItemOptions['additional_options'][] = array(
				        'label' => __("Preview").' '.$label,
				        'custom_view' => 'productdesigner',
				        'value' => '<img src="'.$mediaUrl.'productdesigner/png_export/'.$saveObject->getPng().'" style="width: 200px;height:auto;" />'
			        );

					if($requestInterface->getFullActionName() == 'sales_order_view'){
				        $orderItemOptions['additional_options'][] = array(
					        'label' => __("Download PDF").' '.$label,
					        'custom_view' => 'productdesigner',
					        'value' => '<a href="'.$baseUrl.'/productdesigner/index/generatepdf/id/'.$saveObject->getId().'/order/'.$subject->getOrder()->getIncrementId().'/sku/'.urlencode($subject->getSku()).'/disableproductimage/true" target="_blank">Download</a>'
				        );
				        $orderItemOptions['additional_options'][] = array(
					        'label' => __("Download SVG").' '.$label,
					        'custom_view' => 'productdesigner',
					        'value' => '<a href="'.$baseUrl.'/productdesigner/index/generatepdf/id/'.$saveObject->getId().'/order/'.$subject->getOrder()->getIncrementId().'/sku/'.urlencode($subject->getSku()).'/outputsvg/true" target="_blank">Download</a>'
				        );
				    }
		        }
	        }
// 	        echo '<pre>';print_r($data);exit;
	    }
 
        return $orderItemOptions;
    }
}