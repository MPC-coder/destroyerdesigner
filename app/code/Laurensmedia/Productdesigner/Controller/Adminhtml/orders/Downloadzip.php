<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Downloadzip extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context)
    {
        parent::__construct($context);
    }

    public function execute()
    {
		$params = $this->getRequest()->getParams();
		$orderId = $params['order_id'];
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$libDirectory = $objectManager->get('Magento\Framework\Filesystem')
			->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::LIB_INTERNAL);
		$dir = $libDirectory->getAbsolutePath('');
		require_once($dir.'zip/Zip.php');
		$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')
			->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
			->getAbsolutePath();
		$order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
		if($order){
			$files = array();
			foreach($order->getAllItems() as $orderItem){
				$itemData = json_decode($orderItem->getProductdesignerData(), true);
				if(isset($itemData['connect_id']) && isset($itemData['connect_id']['connect_id'])){
					$connectId = $itemData['connect_id']['connect_id'];
					$files[] = $this->generatePdf($connectId, $order, $orderItem);
				}
			}
			
			$zipLocation = $mediaDirectory.'productdesigner/tmp/'.$order->getIncrementId().'.zip';
			if(file_exists($zipLocation)){
				unlink($zipLocation);
			}
			
			$zip = new \Zip();
			$zip->zip_start($zipLocation);
			foreach ($files as $file) {
				$file_location = $mediaDirectory.'productdesigner/order_export/'.$file;
				$zip->zip_add($file, $order->getIncrementId().'/'.basename($file));
			}
			$zip->zip_end();
			
			// $zip = new \ZipArchive();
			// $zip->open($zipLocation, \ZipArchive::CREATE);
			// foreach ($files as $file) {
			// 	$zip->addFile($file, $order->getIncrementId().'/'.basename($file));
			// }
			// $zip->close();
			
			foreach($files as $file){
				unlink($file);
			}
			
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename='.basename($zipLocation));
			header('Content-Length: ' . filesize($zipLocation));
			readfile($zipLocation);

			unlink($zipLocation);
			return;
		}
	}
	
	public function generatePdf($connectId, $order, $orderItem){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseUrl = $storeManager->getStore()->getBaseUrl();
		$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')
			->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
			->getAbsolutePath();
		$mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

		$layout = $this->_view->getLayout();
		$block = $layout->createBlock('Laurensmedia\Productdesigner\Block\Index');
		$helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Laurensmedia\Productdesigner\Helper\Tcpdfhelper');
		
		$id = $orderItem->getId();
		$incrementId = $order->getIncrementId();
		$sku = $orderItem->getSku();
		$saveObjects = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
			->addFieldToFilter('connect_id', $connectId);
		
		$width = $saveObjects->getFirstItem()->getOutputwidth();
		$height = $saveObjects->getFirstItem()->getOutputheight();

		if($width == 0 || $height == 0){
			$width = $saveObjects->getFirstItem()->getData('x2') - $saveObjects->getFirstItem()->getData('x1');
			$height = $saveObjects->getFirstItem()->getData('y2') - $saveObjects->getFirstItem()->getData('y1');
		}

		if($width <= $height){
			$orientation = 'P';
		} else {
			$orientation = 'L';
		}
		
		// create new PDF document
		$pdf = $helper->getPdfObject($block->get_base_dir(''));
		
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Laurens Schuitemaker');
		$pdf->SetTitle('Label Designer Output');
		$pdf->SetSubject('Order');
		$pdf->SetKeywords('Label Designer, Laurens Media');
		
		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		
		$pdf->setVisibility('all');

		$pdf->AddPage($orientation, array($width, $height));
		
		$pdf->setPageOrientation($orientation, false, 0);
		
		$count = 0;
		foreach($saveObjects as $saveObject){
			$count++;
			
			$pdf->startLayer($saveObject->getLabel(), true, true, false);
			
			// Add both sides
			$droparea = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
				->addFieldToFilter('product_id', $saveObject->getProductId())
				->addFieldToFilter('label', $saveObject->getLabel())
				->addFieldToFilter('store_id', $saveObject->getStoreId())
				->getFirstItem();
			if(empty($droparea->getData())){
				$droparea = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
					->addFieldToFilter('product_id', $saveObject->getProductId())
					->addFieldToFilter('label', $saveObject->getLabel())
					->addFieldToFilter('store_id', array('null' => true))
					->getFirstItem();
			}
	
			$file_location = $mediaDirectory.'productdesigner/svg/'.$saveObject->getSvg();
			
			$fileContents = file_get_contents($file_location);
			
			$outerWidth = $saveObject->getData('x2') - $saveObject->getData('x1');
			$svgWidth = $this->get_string_between($fileContents, 'width="', '"');
			$ratio = $svgWidth / $outerWidth;
	
			$scaleFactor = ($svgWidth) / (410);
			$viewBoxX1 = ($saveObject->getData('output_x1') * $scaleFactor) + 2;
			$viewBoxY1 = ($saveObject->getData('output_y1') * $scaleFactor) + 2;
			$viewBoxWidth = ($saveObject->getData('output_x2') * $scaleFactor) - ($saveObject->getData('output_x1') * $scaleFactor);
			$viewBoxHeight = ($saveObject->getData('output_y2') * $scaleFactor) - ($saveObject->getData('output_y1') * $scaleFactor);
			$viewBox = $viewBoxX1.' '.$viewBoxY1.' '.$viewBoxWidth.' '.$viewBoxHeight;
			$fileContents = $this->replace_between($fileContents, 'viewBox="', '"', $viewBox);
			$fileContents = $this->replace_between($fileContents, 'height="', '"', $svgWidth);
			$fileContents = str_replace('&nbsp;', ' ', $fileContents);
			$fileContents = str_replace('"=""', '', $fileContents);	
		
			$doc = new \DOMDocument();
			$doc->loadXML($fileContents);
			$images = $doc->getElementsByTagName('image');
			foreach($images as $image){
				if(!$image->getAttributeNode('xlink:href')){ continue; }
				$url = $image->getAttributeNode('xlink:href')->value;
				if(strpos($url, 'overlayimgs') !== false){
					$image->parentNode->removeChild($image);
				}
				if(strpos($url, 'color_img') !== false){
					$image->parentNode->removeChild($image);
				}
			}
			
			$fileContents = $doc->saveXML();
			$doc = new \DOMDocument();
			$doc->loadXML($fileContents);
			$images = $doc->getElementsByTagName('image');
			foreach($images as $image){
				if(!$image->getAttributeNode('xlink:href')){ continue; }
				$url = $image->getAttributeNode('xlink:href')->value;
				if(strpos($url, 'overlayimgs') !== false){
					$image->parentNode->removeChild($image);
				}
				if(strpos($url, 'color_img') !== false){
					$image->parentNode->removeChild($image);
				}
			}
			$fileContents = $doc->saveXML();
			
			$fileContents = str_replace('  ', '&#160;&#160;', $fileContents);
			
			$fileContents = str_replace('"Amatic"', '"Amatic Bold"', $fileContents);
	// 								$fileContents = str_replace('"Arial"', '"Arial"', $fileContents);
			$fileContents = str_replace('"Baroque"', '"Baroque Script"', $fileContents);
			$fileContents = str_replace('"Baskerville"', '"Libre Baskerville"', $fileContents);
			$fileContents = str_replace('"Bauhaus"', '"Bauhaus"', $fileContents);
			$fileContents = str_replace('"Birds of Paradise"', '"Birds of Paradise  Personal use"', $fileContents);
	// 								$fileContents = str_replace('"Bridgnorth"', '"Bridgnorth"', $fileContents);
			$fileContents = str_replace('"Comic Sans"', '"Comic Sans MS"', $fileContents);
			$fileContents = str_replace('"Cooper"', '"Cooper Black"', $fileContents);
			$fileContents = str_replace('"Dancing"', '"Dancing Script OT"', $fileContents);
			$fileContents = str_replace('"Duepuntozero"', '"Duepuntozero"', $fileContents);
			$fileContents = str_replace('"Edwardian"', '"Edwardian Script ITC"', $fileContents);
			$fileContents = str_replace('"FreestyleScript"', '"Freestyle Script"', $fileContents);
	// 								$fileContents = str_replace('"Gentilis"', '"Gentilis"', $fileContents);
			$fileContents = str_replace('"Harrington"', '"Harrington (Plain):001.001"', $fileContents);
			$fileContents = str_replace('"KentuckyFriedChicken"', '"KentuckyFriedChickenFont"', $fileContents);
			$fileContents = str_replace('"Lucida Handwriting"', '"QK Marisa"', $fileContents);
	// 		$fileContents = str_replace('"Melinda"', '"Melinda"', $fileContents);
	// 								$fileContents = str_replace('"Monotype Corsiva"', '"Monotype Corsiva"', $fileContents);
			$fileContents = str_replace('"Not just Groovy"', '"Not Just Groovy"', $fileContents);
			$fileContents = str_replace('"Old English"', '"Old English Text MT"', $fileContents);
	// 								$fileContents = str_replace('"Pristina"', '"Pristina"', $fileContents);
			$fileContents = str_replace('"Script"', '"Script MT Bold"', $fileContents);
	// 								$fileContents = str_replace('"Ubuntu"', '"Ubuntu"', $fileContents);
	// 								$fileContents = str_replace('"Verdana"', '"Verdana"', $fileContents);
			$fileContents = str_replace('"Phitradesign Ink"', '"phitradesign INK"', $fileContents);
			$fileContents = str_replace('"Viksi Script"', '"Viksi Script"', $fileContents);
			
	//		$fileContents = str_replace('FreestyleScript', 'FreestyleScript-Regular', $fileContents);
	/*
			$fileContents = str_replace('Monotype Corsiva', 'MonotypeCorsiva', $fileContents);
			$fileContents = str_replace('Lucida Handwriting', 'LucidaHandwriting-Italic', $fileContents);
	*/
			
	// 		echo $fileContents;exit;
	
			$saveFileLocation = $block->get_media_dir('').'productdesigner/tmp/tmp.php';
			file_put_contents($saveFileLocation, $fileContents);
			exec("inkscape ".$saveFileLocation." --export-plain-svg=".$saveFileLocation." --export-text-to-path");
			$fileContents = file_get_contents($saveFileLocation);
			unlink($saveFileLocation);
			
			$fileContents = '@'.str_replace($block->get_media_url(), $block->get_media_dir(''), $fileContents);
			$fileContents = str_replace('+', '', $fileContents);
			$pdf->ImageSVG($file=$fileContents, $x=0, $y=0, $w=$width, $h=$height, $link='', $align='', $palign='', $border=0, $fitonpage=false);

			$pdf->endLayer();
			
			// If side 1, also add cutout
			if($droparea->getCutoutsvg() != '' && $count == 1){
				$pdf->startLayer('Cutout', true, true, false);
				$file_location = $mediaDirectory.'productdesigner/cutoutsvg/'.$droparea->getCutoutsvg();
				$pdf->ImageSVG($file=$file_location, $x=0, $y=0, $w=$width, $h=$height, $link='', $align='', $palign='', $border=0, $fitonpage=false);
				$pdf->endLayer();
			}
			
		}
		$randomnumber = rand(0, 99999);
		$fileLocation = $mediaDirectory.'productdesigner/tmp/'.$orderItem->getSku().'-'.$randomnumber.'.pdf';
		$pdf->Output($fileLocation, 'F');
		return $fileLocation;
    }

	public function replace_between($str, $needle_start, $needle_end, $replacement) {
	    $pos = strpos($str, $needle_start);
	    $start = $pos === false ? 0 : $pos + strlen($needle_start);
	
	    $pos = strpos($str, $needle_end, $start);
	    $end = $pos === false ? strlen($str) : $pos;
	
	    return substr_replace($str, $replacement, $start, $end - $start);
	}
	
	public function get_string_between($string, $start, $end){
	    $string = ' ' . $string;
	    $ini = strpos($string, $start);
	    if ($ini == 0) return '';
	    $ini += strlen($start);
	    $len = strpos($string, $end, $ini) - $ini;
	    return substr($string, $ini, $len);
	}
	
}