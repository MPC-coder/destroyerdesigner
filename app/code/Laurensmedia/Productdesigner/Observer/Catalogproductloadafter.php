<?php
namespace Laurensmedia\Productdesigner\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class Catalogproductloadafter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;
     
    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->_request = $request;
    }
    
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		
        if ($this->_request->getFullActionName() == 'productdesigner_index_addtocart') { //checking when product is adding to cart

	        $data = $this->_request->getParam('cart');
	        $connectId = $this->_request->getParam('connect_id');
	        $options = $this->_request->getParam('options');
	        $isUpdate = $this->_request->getParam('isupdatequoteitem');
		
	        // assuming you are posting your custom form values in an array called extra_options...
	        if (!empty($data))
	        {
	            $product = $observer->getProduct();
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$block = $objectManager->create('Laurensmedia\Productdesigner\Block\Index');
				$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
				$baseUrl = $storeManager->getStore()->getBaseUrl();
				$mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

		        $product_id = $data['productid'];
				$printingTech = $product->getAttributeText('technology');
				$firstSideGrayScale = '';
				$secondSideGrayScale = '';

		        $finalPrice = $data['finalprice'];
		        $number = $data['number'] - 1;
		        $color = isset($data['color']) ? $data['color'] : '';
		        $druktype = isset($data['druktype']) ? $data['druktype'] : '';
		        $bevestiging = isset($data['bevestiging']) ? $data['bevestiging'] : '';
		        $sizes = isset($data['sizes']) ? $data['sizes'] : '';
		        $sizeshtml = "";
		        if ($sizes != "") {
		            foreach ($sizes as $size) {
		                $sizeshtml .= $size['name'].": ".$size['amount']."x, ";
		            }
		        }
		        $svgdata = array();
		        $jsondata = array();
		        $colorimages = array();
		        $width = array();
		        $height = array();
		        $x = array();
		        $y = array();
		        $labels = array();
		        $html = '';
		        $counter = 1;
		        for($i=0; $i<=$number; $i++) {
		            $label = $data[$i]['label'];
		            $labels[] = $label;
		            $svgdata[$label] = $data[$i]['svg'];
		            $jsondata[$label] = $data[$i]['json'];
		            $colorimages[$label] = $data[$i]['image'];
		            $width[$label] = $data[$i]['width'];
		            $height[$label] = $data[$i]['height'];
		            $x[$label] = $data[$i]['x'];
		            $y[$label] = $data[$i]['y'];
					$rand = rand(1, 99999);

	                $previewwidth = $width[$label] / 5.67;
	                $previewheight = $height[$label] / 5.67;
	                $previewx = $x[$label] / 5.67;
	                $previewy = $y[$label] / 5.67;

					if($counter == 1){
						$grayscale = $firstSideGrayScale;
					} else {
						$grayscale = $secondSideGrayScale;
					}

					$savedImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
						->addFieldToFilter('product_id', $product_id)
						->addFieldToFilter('label', $label)
						->addFieldToFilter('connect_id', $connectId['connect_id'])
						->setPageSize(1)
						->setCurPage(1)
						->load()
						->getFirstItem();
					$imageUrl = $mediaUrl.'productdesigner/png_export/'.$savedImage['png'];
					$label = str_replace(' ', '_', $label);

					$html .= '<div class="pd-preview-item" style="float:left;width:205px;height:230px;margin-right:2px;">';
					$html .= '<div class="pd-preview-holder" style="float:left;width:205px;height:205px;margin-right:2px;">';
					$html .= '<img src="'.$imageUrl.'" width="205" height="205" style="position:absolute;margin-top:1px;z-index:999;'.$grayscale.'" />';
					$html .= '</div>';
					$html .= '<p>'.$label.'</p>';
					$html .= '</div>';
					$counter++;
		        }
	
	            // add to the additional options array
	            $additionalOptions = array();
	            
	            if ($additionalOption = $product->getCustomOption('additional_options'))
	            {
	                $additionalOptions = (array) json_decode($additionalOption->getValue(), true);
	            }

	            $additionalOptions[] = array(
	            	'label' => __("Design"),
	            	'custom_view' => 'productdesigner',
	            	'value' => $html,
	            	'full_view' => $html
	            );
	            
	            // add the additional options array with the option code additional_options
	            $observer->getProduct()
	                ->addCustomOption('additional_options', json_encode($additionalOptions));
	        }
		}
	}
}