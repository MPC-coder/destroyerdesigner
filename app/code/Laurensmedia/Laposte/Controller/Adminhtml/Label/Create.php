<?php

namespace Laurensmedia\Laposte\Controller\Adminhtml\Label;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Create extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPagee;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
	    if($this->getRequest()->getParam('order_id') > 0){
	        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	        $order = $objectManager->get('Magento\Sales\Model\Order')->load($this->getRequest()->getParam('order_id'));
			$layout = $this->_view->getLayout();
			$block = $layout->createBlock('Laurensmedia\Productdesigner\Block\Index');
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
			$baseUrl = $storeManager->getStore()->getBaseUrl();
			$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')
				->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
				->getAbsolutePath();
			$mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
			$allowerdContries = $objectManager->get('Magento\Directory\Model\AllowedCountries')->getAllowedCountries();
			$countryFactory = $objectManager->get('\Magento\Directory\Model\CountryFactory');
			$countries = array();
			foreach($allowerdContries as $countryCode){
				if($countryCode){
					$data = $countryFactory->create()->loadByCode($countryCode);
					$countries[$countryCode] = $data->getName();
				}
			}

			$helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Laurensmedia\Productdesigner\Helper\Tcpdfhelper');
			
	        $orderItemNames = array();
	        foreach($order->getAllItems() as $item){
		        $orderItemNames[] = $item->getName();
	        }
	        
	        $width = 150;
	        $height = 100;
			if($width <= $height){
				$orientation = 'P';
			} else {
				$orientation = 'L';
			}
			
			// create new PDF document
			$pdf = $helper->getPdfObject($block->get_base_dir(''));
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->AddPage($orientation, array($width, $height));
			$pdf->setPageOrientation($orientation, false, 0);
			$pdf->SetFont ('helvetica', '', $pdf->pixelsToUnits('30'), '', 'default', true );
			
/*
			// Product information
			$pdf->Rect(($width - 120), 0, (120 - 74), 40, 'F', array(), array(232,244,255));
			$html = implode("\n", $orderItemNames);
			$pdf->MultiCell((120 - 74), 38, $html, 0, 'L', 0, 1, ($width - 120), 2, true, 0, false, true, 38);
*/
			
			// Porto
			$pdf->Rect(($width - 74), 0, 74, 40, 'F', array(), array(221,221,221));
			$portoCode = 'L 023 199 XXX X';
			$pdf->MultiCell(74, 38, $portoCode, 0, 'C', 0, 1, ($width - 74), 2, true, 0, false, true, 38);
			
			// Address
			$addressData = $order->getShippingAddress()->getData();
			$address = $addressData['firstname'].' '.$addressData['lastname']."\n";
			$address .= $addressData['street']."\n";
			$address .= $addressData['postcode'].' '.$addressData['city']."\n";
			$address .= $countries[$addressData['country_id']];
// 			echo '<pre>';print_r($order->getShippingAddress()->getData());exit;
			$pdf->Rect(($width - 140), ($height - 52), (140 - 15), 40, 'F', array(), array(221,221,221));
			$pdf->MultiCell((140 - 15), 38, $address, 0, 'C', 0, 1, ($width - 140), ($height - 50), true, 0, false, true, 38);

			// Store information
			$pdf->Rect(0, 0, 40, 40, 'F', array(), array(232,244,255));
			$storeInformation = $objectManager->create('\Magento\Store\Model\Information');
			$storeInfo = $storeInformation->getStoreInformationObject($order->getStore());
// 			echo '<pre>';print_r($storeInfo->getData());exit;
			$html = $storeInfo->getName()."\n";
			$html .= $storeInfo->getData('street_line1')."\n";
			$html .= $storeInfo->getData('postcode').' '.$storeInfo->getData('city')."\n";
			$html .= $storeInfo->getData('country');
			$pdf->MultiCell(38, 38, $html, 0, 'L', 0, 1, 2, 2, true, 0, false, true, 38);

			$randomnumber = rand(0, 99999);
			$pdf->Output($randomnumber.'.pdf', 'I');
			return;
	    }
    }
}