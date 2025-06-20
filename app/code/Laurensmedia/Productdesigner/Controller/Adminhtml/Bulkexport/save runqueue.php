<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Bulkexport;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Runqueue extends \Magento\Backend\App\Action
{
    /**
     * @var \Laurensmedia\Productdesigner\Helper\Tcpdfhelper
     */
    private $tcpdfHelper = null;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Get Tcpdf Helper
     *
     * @return \Laurensmedia\Productdesigner\Helper\Tcpdfhelper
     */
    protected function getTcpdfHelper()
    {
        if ($this->tcpdfHelper === null) {
            $this->tcpdfHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Laurensmedia\Productdesigner\Helper\Tcpdfhelper::class);
        }
        return $this->tcpdfHelper;
    }

	/**
	 * Save action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute()
	{
		ini_set('memory_limit', "2048M");
		error_reporting(0);
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
		$mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseUrl = $storeManager->getStore()->getBaseUrl();
		$mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		// Set defaults
		$pdfPath = $mediaPath.'productdesigner/order_export/'.date('Y').'/'.date('m').'/';
		if(!file_exists($pdfPath) && !is_dir($pdfPath)){
			mkdir($pdfPath, 0777, true);
		}
		
		// Load first item to be processed
		$firstItem = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Bulkexport\Collection')
			->addFieldToFilter('finished', 0)
			->setPageSize(1)
			->setCurPage(1)
			->load()
			->getFirstItem();
			
		// Load item properties
		$exportCombining = $firstItem->getExportCombining();
		$emptyDesign = $firstItem->getEmptyDesign();
		$pdfWidth = $firstItem->getPdfWidth();
		$pdfHeight = $firstItem->getPdfHeight();
		$pdfMarginVertical = $firstItem->getPdfMarginVertical();
		$pdfMarginHorizontal = $firstItem->getPdfMarginHorizontal();
		$pdfMarginItemsVertical = $firstItem->getPdfMarginItemsVertical();
		$pdfMarginItemsHorizontal = $firstItem->getPdfMarginItemsHorizontal();

		// Get all items in case of multiple product sides on one page or PDF
		$allItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Bulkexport\Collection')
			->addFieldToFilter('export_combining', $exportCombining)
			->addFieldToFilter('empty_design', $emptyDesign)
			->addFieldToFilter('pdf_margin_vertical', $pdfMarginVertical)
			->addFieldToFilter('pdf_margin_horizontal', $pdfMarginHorizontal)
			->addFieldToFilter('pdf_margin_items_vertical', $pdfMarginItemsVertical)
			->addFieldToFilter('pdf_margin_items_horizontal', $pdfMarginItemsHorizontal);
		if($pdfWidth > 0 && $pdfHeight > 0){
			$allItems = $allItems
				->addFieldToFilter('pdf_width', $pdfWidth)
				->addFieldToFilter('pdf_height', $pdfHeight);
		}
		
		$firstOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getFirstItem()->getOrderId())->getRealOrderId();
		$lastOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getLastItem()->getOrderId())->getRealOrderId();
		
		$random = rand(0, 999999);
		$random = $firstOrderId.'-'.$lastOrderId;
		$pdfFile = $pdfPath.$random.'.pdf';
		$dbFile = date('Y').'/'.date('m').'/'.$random.'.pdf';
		
		// Initialize PDF creator
		$layout = $this->_view->getLayout();
		$block = $layout->createBlock('Laurensmedia\Productdesigner\Block\Index');
		$helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Laurensmedia\Productdesigner\Helper\Tcpdfhelper');

		// create new PDF document
		$pdf = $helper->getPdfObject($block->get_base_dir(''));
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		
		// Paging
		$curXPos = 0;
		$curYPos = 0;
		$maxHeight = 0;
		$needNewPage = true;
		if($exportCombining == 'multiple_on_page'){
			foreach($allItems as $item){
				$prevItem = '';
				// Load saved items
				$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
				$mediaUrl = $storeManager->getStore($orderItem->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
				$qty = $orderItem->getQtyOrdered();
				$orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
				$connectId = $orderItemOptions['connect_id']['connect_id'];
				if($connectId > 0){
					$savedItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
						->addFieldToFilter('connect_id', $connectId)
						->setPageSize(4)
						->setCurPage(1)
						->load();
					// Print each product side on PDF
					for($i=0; $i<$qty; $i++){
						foreach($savedItems as $productSide){
							$file = $productSide->getSvgfile();
							$file = $productSide->getSvg();
							$fileName = basename($file);
							$outputFileName = 'output_'.$fileName;
							$file = str_replace($fileName, $outputFileName, $file);
							//echo $file.'<br />';continue;
							
							// Check if SVG file contains any elements
							//$handle = fopen($mediaPath.'productdesigner/savesvg/'.$file, 'r');
							//$fileContents = fread($handle, filesize($mediaPath.'productdesigner/savesvg/'.$file));
							$handle = fopen($mediaPath.'productdesigner/svg/'.$file, 'r');
							$fileContents = fread($handle, filesize($mediaPath.'productdesigner/svg/'.$file));
							fclose($handle);

							if(strpos($fileContents, 'image') == false && strpos($fileContents, 'path') == false){
								if($emptyDesign == 'do_not_print' || $prevItem == ''){
									continue;
								} elseif($emptyDesign == 'print_other_side'){
									$productSide = $prevItem;
									$file = $productSide->getSvgfile();
									$file = $productSide->getSvg();
									$fileName = basename($file);
									$outputFileName = 'output_'.$fileName;
									$file = str_replace($fileName, $outputFileName, $file);
								}
							} else {
								$prevItem = $productSide;
							}
							
							if(!$pdfWidth > 0 || !$pdfHeight > 0){
								$pdfWidth = $productSide->getOutputwidth();
								$pdfHeight = $productSide->getOutputheight();
							}
							$outputWidth = $productSide->getOutputwidth();
							$outputHeight = $productSide->getOutputheight();
							if($outputHeight > $maxHeight){
								$maxHeight = $outputHeight;
							}
							
							// Set PDF border margin
							if($curXPos == 0){
								$curXPos = $pdfMarginHorizontal;
							}
							if($curYPos == 0){
								$curYPos = $pdfMarginVertical;
							}
							if(($curXPos + $outputWidth + $pdfMarginHorizontal) > $pdfWidth){
								$curXPos = $pdfMarginHorizontal;
								$curYPos = $curYPos + $maxHeight + $pdfMarginItemsHorizontal;
								$maxHeight = 0;
							}
							if(($curYPos + $outputHeight) > $pdfHeight){
								$needNewPage = true;
							}
							
							if($needNewPage == true){
								// Define page orientation
								if($pdfWidth < $pdfHeight){
									$orientation = 'P';
								} else {
									$orientation = 'L';
								}
								$pdf->AddPage($orientation, array($pdfWidth, $pdfHeight));
								$pdf->setPageOrientation($orientation, false, 0);
								$needNewPage = false;
								$curXPos = $pdfMarginHorizontal;
								$curYPos = $pdfMarginVertical;
							}

							$fileContents = file_get_contents($mediaPath.'productdesigner/svg/'.$file);
							$fileContents = '@'.str_replace($mediaUrl, $mediaPath.'', $fileContents);
							$pdf->ImageSVG(
								//$file=$mediaPath.'productdesigner/savesvg/'.$file,
								$file=$fileContents,
								$x=$curXPos,
								$y=$curYPos,
								$w=$outputWidth,
								$h=$outputHeight,
								$link='',
								$align='',
								$palign='',
								$border=0,
								$fitonpage=false
							);
							$curXPos = $curXPos + $outputWidth + $pdfMarginItemsHorizontal;
						}
					}
				}

				// Save item as processed
				$objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport')
					->load($item->getId())
					->setData(array('finished' => 1, 'pdf_file' => $dbFile))
					->setId($item->getId())
					->save();
			}
		} elseif($exportCombining == 'new_page'){
			foreach($allItems as $item){
				$prevItem = '';
				// Load saved items
				$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
				$mediaUrl = $storeManager->getStore($orderItem->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
				$qty = $orderItem->getQtyOrdered();
				$orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
				$connectId = $orderItemOptions['connect_id']['connect_id'];
				if($connectId > 0){
					$savedItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
						->addFieldToFilter('connect_id', $connectId)
						->setPageSize(4)
						->setCurPage(1)
						->load();
					for($i=0; $i<$qty; $i++){
						// Print each product side on PDF
						foreach($savedItems as $productSide){
							$file = $productSide->getSvgfile();
							$file = $productSide->getSvg();
							$fileName = basename($file);
							$outputFileName = 'output_'.$fileName;
							$file = str_replace($fileName, $outputFileName, $file);
							
							// Check if SVG file contains any elements
							//$handle = fopen($mediaPath.'productdesigner/savesvg/'.$file, 'r');
							//$fileContents = fread($handle, filesize($mediaPath.'productdesigner/savesvg/'.$file));
							$handle = fopen($mediaPath.'productdesigner/svg/'.$file, 'r');
							$fileContents = fread($handle, filesize($mediaPath.'productdesigner/svg/'.$file));
							fclose($handle);
							if(strpos($fileContents, 'image') == false && strpos($fileContents, 'path') == false){
								if($emptyDesign == 'do_not_print' || $prevItem == ''){
									continue;
								} elseif($emptyDesign == 'print_other_side'){
									$productSide = $prevItem;
									$file = $productSide->getSvgfile();
									$file = $productSide->getSvg();
									$fileName = basename($file);
									$outputFileName = 'output_'.$fileName;
									$file = str_replace($fileName, $outputFileName, $file);
								}
							} else {
								$prevItem = $productSide;	
							}
							
							$pdfWidth = $productSide->getOutputwidth() + (2 * $pdfMarginHorizontal);
							$pdfHeight = $productSide->getOutputheight() + (2 * $pdfMarginVertical);
							$outputWidth = $productSide->getOutputwidth();
							$outputHeight = $productSide->getOutputheight();
							
							if($needNewPage == true){
								// Define page orientation
								if($pdfWidth < $pdfHeight){
									$orientation = 'P';
								} else {
									$orientation = 'L';
								}
								$pdf->AddPage($orientation, array($pdfWidth, $pdfHeight));
								$pdf->setPageOrientation($orientation, false, 0);
								$curXPos = $pdfMarginHorizontal;
								$curYPos = $pdfMarginVertical;
							}
							
							$fileContents = file_get_contents($mediaPath.'productdesigner/svg/'.$file);
							$fileContents = '@'.str_replace($mediaUrl, $mediaPath.'', $fileContents);
							$pdf->ImageSVG(
								//$file=$mediaPath.'productdesigner/savesvg/'.$file,
								$file=$fileContents,
								$x=$curXPos,
								$y=$curYPos,
								$w=$outputWidth,
								$h=$outputHeight,
								$link='',
								$align='',
								$palign='',
								$border=0,
								$fitonpage=false
							);
						}
					}
				}
				// Save item as processed
				$objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport')
					->load($item->getId())
					->setData(array('finished' => 1, 'pdf_file' => $dbFile))
					->setId($item->getId())
					->save();
			}
		} elseif($exportCombining == 'new_pdf'){
			$item = $firstItem;
			// Load saved items
			$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
			$mediaUrl = $storeManager->getStore($orderItem->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
			$qty = $orderItem->getQtyOrdered();
			$orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
			$connectId = $orderItemOptions['connect_id']['connect_id'];
			if($connectId > 0){
				$savedItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
					->addFieldToFilter('connect_id', $connectId)
					->setPageSize(4)
					->setCurPage(1)
					->load();
				for($i=0; $i<$qty; $i++){
					// Print each product side on PDF
					foreach($savedItems as $productSide){
						$file = $productSide->getSvgfile();
						$file = $productSide->getSvg();
						$fileName = basename($file);
						$outputFileName = 'output_'.$fileName;
						$file = str_replace($fileName, $outputFileName, $file);
						
						$pdfWidth = $productSide->getOutputwidth() + (2 * $pdfMarginHorizontal);
						$pdfHeight = $productSide->getOutputheight() + (2 * $pdfMarginVertical);
						$outputWidth = $productSide->getOutputwidth();
						$outputHeight = $productSide->getOutputheight();
						
						if($needNewPage == true){
							// Define page orientation
							if($pdfWidth < $pdfHeight){
								$orientation = 'P';
							} else {
								$orientation = 'L';
							}
							$pdf->AddPage($orientation, array($pdfWidth, $pdfHeight));
							$pdf->setPageOrientation($orientation, false, 0);
							$curXPos = $pdfMarginHorizontal;
							$curYPos = $pdfMarginVertical;
						}
						
						$fileContents = file_get_contents($mediaPath.'productdesigner/svg/'.$file);
						$fileContents = '@'.str_replace($mediaUrl, $mediaPath.'', $fileContents);
						$pdf->ImageSVG(
							//$file=$mediaPath.'productdesigner/savesvg/'.$file,
							$file=$fileContents,
							$x=$curXPos,
							$y=$curYPos,
							$w=$outputWidth,
							$h=$outputHeight,
							$link='',
							$align='',
							$palign='',
							$border=0,
							$fitonpage=false
						);
					}
				}
			}
			// Save item as processed
			$objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport')
				->load($item->getId())
				->setData(array('finished' => 1, 'pdf_file' => $dbFile))
				->setId($item->getId())
				->save();
			
		} elseif($exportCombining == 'new_line'){
			$lastOrderId = '';
			$needNewLine = false;
			$curXPos = 0;
			$curYPos = 0;			
			
			foreach($allItems as $item){
				$prevItem = '';
				// Load saved items
				$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
				$mediaUrl = $storeManager->getStore($orderItem->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
				$orderId = $orderItem->getOrderId();
				if($orderId != $lastOrderId){
					// New line
					$needNewLine = true;
				}
				if($lastOrderId == ''){
					$curYPos = $pdfMarginVertical + 16;
				}
				$lastOrderId = $orderId;
				$realOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($orderId)->getRealOrderId();
				$qty = $orderItem->getQtyOrdered();
				$orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
				$connectId = $orderItemOptions['connect_id']['connect_id'];
				if($connectId > 0){
					$savedItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
						->addFieldToFilter('connect_id', $connectId)
						->setPageSize(4)
						->setCurPage(1)
						->load();
					// Print each product side on PDF
					for($i=0; $i<$qty; $i++){
						foreach($savedItems as $productSide){
							$file = $productSide->getSvgfile();
							$file = $productSide->getSvg();
							$fileName = basename($file);
							$outputFileName = 'output_'.$fileName;
							$file = str_replace($fileName, $outputFileName, $file);
/* 							echo $file.'<br />';continue; */
							
							// Check if SVG file contains any elements
							//$handle = fopen($mediaPath.'productdesigner/savesvg/'.$file, 'r');
							//$fileContents = fread($handle, filesize($mediaPath.'productdesigner/savesvg/'.$file));
							$handle = fopen($mediaPath.'productdesigner/svg/'.$file, 'r');
							$fileContents = fread($handle, filesize($mediaPath.'productdesigner/svg/'.$file));
							$fileContents = str_replace($mediaUrl, $mediaPath.'', $fileContents);
							fclose($handle);

							if(strpos($fileContents, 'image') == false && strpos($fileContents, 'path') == false){
								if($emptyDesign == 'do_not_print' || $prevItem == ''){
									continue;
								} elseif($emptyDesign == 'print_other_side'){
									$productSide = $prevItem;
									$file = $productSide->getSvgfile();
									$file = $productSide->getSvg();
									$fileName = basename($file);
									$outputFileName = 'output_'.$fileName;
									$file = str_replace($fileName, $outputFileName, $file);
								}
							} else {
								$prevItem = $productSide;
							}
							
							if(!$pdfWidth > 0 || !$pdfHeight > 0){
								$pdfWidth = $productSide->getOutputwidth();
								$pdfHeight = $productSide->getOutputheight();
							}
							$outputWidth = $productSide->getOutputwidth();
							$outputHeight = $productSide->getOutputheight();
							if($outputHeight > $maxHeight){
								$maxHeight = $outputHeight;
							}
							
							// Set PDF border margin
							if($curXPos == 0){
								$curXPos = $pdfMarginHorizontal;
							}
							if($curYPos == 0){
								$curYPos = $pdfMarginVertical;
							}
							if(($curXPos + $outputWidth + $pdfMarginHorizontal) > $pdfWidth || $needNewLine == true){
								$curXPos = $pdfMarginHorizontal;
								$curYPos = $curYPos + $maxHeight + $pdfMarginItemsHorizontal;
								$maxHeight = 0;
							}
							
							if($needNewLine == true){
								$curYPos = $curYPos + 8;
							}
							
							if(($curYPos + $outputHeight) > $pdfHeight){
								$needNewPage = true;
							}
							
							if($needNewPage == true){
								// Define page orientation
								if($pdfWidth < $pdfHeight){
									$orientation = 'P';
								} else {
									$orientation = 'L';
								}
								$pdf->AddPage($orientation, array($pdfWidth, $pdfHeight));
								$pdf->setPageOrientation($orientation, false, 0);
								$needNewPage = false;
								$curXPos = $pdfMarginHorizontal;
								$curYPos = $pdfMarginVertical + 8;
							}
							if($needNewLine == true){
								$pdf->Text(
									$x=$curXPos,
									$y=($curYPos - 8),
									$txt=$realOrderId
								);
							}
							$needNewLine = false;
							$fileContents = file_get_contents($mediaPath.'productdesigner/svg/'.$file);
							$fileContents = '@'.str_replace($mediaUrl, $mediaPath.'', $fileContents);
							$pdf->ImageSVG(
								//$file=$mediaPath.'productdesigner/savesvg/'.$file,
								//$file=$mediaPath.'productdesigner/svg/'.$file,
								$file=$fileContents,
								$x=$curXPos,
								$y=$curYPos,
								$w=$outputWidth,
								$h=$outputHeight,
								$link='',
								$align='',
								$palign='',
								$border=0,
								$fitonpage=false
							);
							$curXPos = $curXPos + $outputWidth + $pdfMarginItemsHorizontal;
						}
					}
				}

				// Save item as processed
				$objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport')
					->load($item->getId())
					->setData(array('finished' => 1, 'pdf_file' => $dbFile))
					->setId($item->getId())
					->save();
			}
		} elseif($exportCombining == 'new_line_with_summary'){
			
			$firstOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getFirstItem()->getOrderId())->getRealOrderId();
			$lastOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getLastItem()->getOrderId())->getRealOrderId();
			$lastOrderIdOrig = $lastOrderId;
			
			$types = array('engraving', 'sublimation', 'printing', 'wood');
			$stores = $storeManager->getStores();
			foreach($types as $type){
				foreach($stores as $store){
					$random = rand(0, 999999);
					$random = $store->getStoreId().'-'.$firstOrderId.'-'.$lastOrderIdOrig.'-'.$type;
					$pdfFile = $pdfPath.$random.'.pdf';
					$dbFile = date('Y').'/'.date('m').'/'.$random.'.pdf';
					
					// Initialize PDF creator
					$layout = $this->_view->getLayout();
					$block = $layout->createBlock('Laurensmedia\Productdesigner\Block\Index');
					$helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Laurensmedia\Productdesigner\Helper\Tcpdfhelper');
			
					// create new PDF document
					$pdf = $helper->getPdfObject($block->get_base_dir(''));
					$pdf->setPrintHeader(false);
					$pdf->setPrintFooter(false);
					
					$lastOrderId = '';
					$needNewLine = false;
					$curXPos = 0;
					$curYPos = 0;
					
					// Introduction text
					$pdf->SetFont('helvetica', '', 8, '', 'false');
					$allOrderIds = $allItems->getColumnValues('order_id');
					$firstOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allOrderIds[0])->getRealOrderId();
					$lastOrderId = $objectManager->create('Magento\Sales\Model\Order')->load(end($allOrderIds))->getRealOrderId();
					if($pdfWidth < $pdfHeight){
						$orientation = 'P';
					} else {
						$orientation = 'L';
					}
					$pdf->AddPage($orientation, array($pdfWidth, $pdfHeight));
					$pdf->setPageOrientation($orientation, false, 0);
					$needNewPage = false;
					$curXPos = $pdfMarginHorizontal;
					$curYPos = $pdfMarginVertical;
					$pdf->Text(
						$x=$curXPos,
						$y=$curYPos,
						$txt='BULK :: '.$firstOrderId.'-'.$lastOrderId.'.pdf'
					);
					$curYPos += 4;
					$pdf->Text(
						$x=$curXPos,
						$y=$curYPos,
						$txt='Printing type: '.$type
					);
					$curYPos += 4;
					$pdf->Text(
						$x=$curXPos,
						$y=$curYPos,
						$txt='for this bulk, you need:'
					);
					$curYPos += 4;
					$orderItemIds = $allItems->getColumnValues('order_item_id');
					$orderItemsArray = array();
					foreach($orderItemIds as $orderItemId){
						$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($orderItemId);
						$mediaUrl = $storeManager->getStore($orderItem->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
						
						$product = $orderItem->getProduct();
						$technologyAttribute = $product->getResource()->getAttribute('technology');
$productPrintingTypes = $technologyAttribute ? array_filter(explode(',', str_replace(' ', '', $technologyAttribute->getFrontend()->getValue($product)))) : [];
						if(!in_array($type, $productPrintingTypes)){
							continue;
						}
						if($store->getStoreId() != $orderItem->getStoreId()){
							continue;
						}
						
						$productSku = $orderItem->getSku();
						$qty = (int)$orderItem->getQtyOrdered();
						if(isset($orderItemsArray[$productSku]) && !empty($orderItemsArray[$productSku])){
							$qty += $orderItemsArray[$productSku]['qty'];
						}
						$orderItemsArray[$productSku] = array(
							'sku' => $productSku,
							'qty' => $qty
						);
					}
					
					foreach($orderItemsArray as $orderItem){
						$pdf->Text(
							$x=$curXPos,
							$y=$curYPos,
							$txt=$orderItem['sku'].' x '.$orderItem['qty']
						);
						$curYPos += 4;
					}			
		/*
					foreach($orderItemIds as $orderItemId){
						$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($orderItemId);
						$productSku = $orderItem->getSku();
						$qty = (int)$orderItem->getQtyOrdered();
						$pdf->Text(
							$x=$curXPos,
							$y=$curYPos,
							$txt=$productSku.' x '.$qty
						);
						$curYPos += 3;
					}
		*/
					$curYPos = $curYPos - $pdfMarginVertical;
					$curYPos = $curYPos - 25;
					
					$orderData = [];
					// Récupérer le store courant pour comparaison
					$store = $storeManager->getStore();

					foreach ($allItems as $item) {
						// Charger l'order item via l'ObjectManager (à privilégier via DI en production)
						$orderItem = $objectManager->create(\Magento\Sales\Model\Order\Item::class)
							->load($item->getOrderItemId());
						
						// Récupérer l'ID du store de cet order item
						$orderItemStoreId = $orderItem->getStoreId();
						
						// Récupérer l'URL média pour le store concerné (si besoin ultérieur)
						$mediaUrl = $storeManager->getStore($orderItemStoreId)
							->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
						
						$product = $orderItem->getProduct();
						
						// Récupérer l'attribut "technology" du produit et s'assurer qu'il existe
						$technologyAttribute = $product->getResource()->getAttribute('technology');
						if (!$technologyAttribute) {
							continue;
						}
						$technologyValue = $technologyAttribute->getFrontend()->getValue($product);
						
						// Nettoyer la chaîne et obtenir un tableau des types, en éliminant les valeurs vides
						$productPrintingTypes = array_filter(explode(',', str_replace(' ', '', $technologyValue)));
						
						// Si le type souhaité n'est pas présent, on passe à l'élément suivant
						if (!in_array($type, $productPrintingTypes)) {
							continue;
						}
						
						// Vérifier que l'order item appartient bien au store courant
						if ($store->getStoreId() != $orderItemStoreId) {
							continue;
						}
						
						$orderId = $orderItem->getOrderId();
						$qtyOrdered = (int)$orderItem->getQtyOrdered();
						
						// Si le produit est déjà présent pour cet ordre, additionner la quantité
						if (isset($orderData[$orderId][$orderItem->getSku()])) {
							// Format attendu : "SKU x quantité"
							$existingEntry = $orderData[$orderId][$orderItem->getSku()];
							$parts = explode(' x ', $existingEntry);
							$existingQty = isset($parts[1]) ? floatval(trim($parts[1])) : 0;
							$qtyOrdered += $existingQty;
						}
						
						// Enregistrer le résultat au format "SKU x quantité"
						$orderData[$orderId][$orderItem->getSku()] = $orderItem->getSku() . ' x ' . $qtyOrdered;
					}
					
					$lastOrderId = '';			
					foreach($allItems as $item){
						$prevItem = '';
						// Load saved items
						$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
						$mediaUrl = $storeManager->getStore($orderItem->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
						
						$product = $orderItem->getProduct();
						$technologyAttribute = $product->getResource()->getAttribute('technology');
						$productPrintingTypes = $technologyAttribute 
							? array_filter(explode(',', str_replace(' ', '', $technologyAttribute->getFrontend()->getValue($product))))
							: [];
						if(!in_array($type, $productPrintingTypes)){
							continue;
						}
						if($store->getStoreId() != $orderItem->getStoreId()){
							continue;
						}
						
						$_resource = $objectManager->create('Magento\Catalog\Model\Product')->getResource();
						$enableRotate = $_resource->getAttributeRawValue($orderItem->getProductId(),  'pd_rotate_in_export', $storeManager->getStore());
						$orderId = $orderItem->getOrderId();
						if($orderId != $lastOrderId){
							// New line
							$needNewLine = true;
						}
						$needNewLine = true;
						if($lastOrderId == ''){
							//$curYPos = $pdfMarginVertical + 16;
						}
						$lastOrderId = $orderId;
						$lastOrder = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
						$realOrderId = $lastOrder->getRealOrderId();
						$shippingData = $lastOrder->getShippingAddress()->getData();
						$city = $shippingData['city'];
						$city = $shippingData['lastname'];
						
						$qty = $orderItem->getQtyOrdered();
						if(floatval($qty) < 1 && $orderItem->getQtyInvoiced() > 0){
							$qty = $orderItem->getQtyInvoiced();
						}
						$orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
						$connectId = $orderItemOptions['connect_id']['connect_id'];
						
						if($connectId > 0){
							$savedItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
								->addFieldToFilter('connect_id', $connectId)
								->setPageSize(4)
								->setCurPage(1)
								->load();
								
							if(in_array('sublimation', $productPrintingTypes) && in_array('engraving', $productPrintingTypes)){
								if($type == 'sublimation'){
									$savedItems = array($savedItems->getFirstItem());
								} elseif($type == 'engraving'){
									$savedItems->setPageSize(1)->setCurPage(2);
									if(count($savedItems) < 1){
										$savedItems = array();
									}
								}
							}
								
							// Print each product side on PDF
							$skuCounter = 0;
							for($i=0; $i<$qty; $i++){
								$skuCounter ++;
								
								$amountOfSides = count($savedItems);
								$sidesTotalWidth = 0;
								foreach($savedItems as $productSide){
									$file = $productSide->getSvgfile();
									$file = $productSide->getSvg();
									$overlayImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
										->addFieldToFilter('product_id', $productSide->getProductId())
										->addFieldToFilter('label', $productSide->getLabel())
										->addFieldToFilter('store_id', $productSide->getStoreId())
										->setPageSize(1)
										->setCurPage(1)
										->load()
										->getFirstItem();
									if(empty($overlayImage->getData())){
										$overlayImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
											->addFieldToFilter('product_id', $productSide->getProductId())
											->addFieldToFilter('label', $productSide->getLabel())
											->addFieldToFilter('store_id', array('null' => true))
											->setPageSize(1)
											->setCurPage(1)
											->load()
											->getFirstItem();
									}
									$overlayImage = $overlayImage->getPdfoverlayimage();
									$overlayImagePath = '';
									if($overlayImage != ''){
										$overlayImagePath = $mediaPath.'productdesigner/overlayimgs/'.$overlayImage;
									}
									$fileName = basename($file);
									$outputFileName = 'output_'.$fileName;
									$file = str_replace($fileName, $outputFileName, $file);

									
									// Check if SVG file contains any elements
									//$handle = fopen($mediaPath.'productdesigner/savesvg/'.$file, 'r');
									//$fileContents = fread($handle, filesize($mediaPath.'productdesigner/savesvg/'.$file));
                  
									//$handle = fopen($mediaPath.'productdesigner/svg/'.$file, 'r');
									//$fileContents = fread($handle, filesize($mediaPath.'productdesigner/svg/'.$file));
									$filePath = $mediaPath.'productdesigner/svg/'.$file;
                  if (file_exists($filePath)) {
                      $fileContents = file_get_contents($filePath);
                  } else {
                      // G�rer l'erreur - par exemple, cr�er un SVG vide ou sauter cet �l�ment
                      $fileContents = '';
                      error_log("Fichier introuvable: " . $filePath);
                  }

									
									$fileContents = str_replace($mediaUrl, $mediaPath.'', $fileContents);
									fclose($handle);
									
									$hasDesign = true;
									if(strpos($fileContents, 'overlayimgs') == false && strpos($fileContents, 'color_img') == false && strpos($fileContents, 'image') == false && strpos($fileContents, 'path') == false){
										$hasDesign = false;
									} elseif(strpos($fileContents, 'color_img') == false && strpos($fileContents, 'overlayimgs') != false && substr_count($fileContents, 'image') <= 1 && strpos($fileContents, 'text') == false){
										$hasDesign = false;
									} elseif(strpos($fileContents, 'overlayimgs') == false && strpos($fileContents, 'color_img') != false && substr_count($fileContents, 'image') <= 1 && strpos($fileContents, 'text') == false){
										$hasDesign = false;
									} elseif(strpos($fileContents, 'overlayimgs') != false && strpos($fileContents, 'color_img') != false && substr_count($fileContents, 'image') <= 2 && strpos($fileContents, 'text') == false){
										$hasDesign = false;
									}
		
									if((strpos($fileContents, 'image') == false && strpos($fileContents, 'path') == false) || !$hasDesign){
										if($emptyDesign == 'do_not_print' || $prevItem == ''){
											continue;
										} elseif($emptyDesign == 'print_other_side'){
											$productSide = $prevItem;
											$file = $productSide->getSvgfile();
											$file = $productSide->getSvg();
											$fileName = basename($file);
											$outputFileName = 'output_'.$fileName;
											$file = str_replace($fileName, $outputFileName, $file);
										}
									} else {
										$prevItem = $productSide;
									}
									
									if(!$pdfWidth > 0 || !$pdfHeight > 0){
										$pdfWidth = $productSide->getOutputwidth();
										$pdfHeight = $productSide->getOutputheight();
									}
									$outputWidth = $productSide->getOutputwidth();
									$outputHeight = $productSide->getOutputheight();
									if($outputHeight > $maxHeight){
										$maxHeight = $outputHeight;
									}
									
									$origOutputWidth = $outputWidth;
									$origOutputHeight = $outputHeight;
		//							if($origOutputWidth > $origOutputHeight){
									if($enableRotate == '1'){
										$outputWidth = $origOutputHeight;
										$outputHeight = $origOutputWidth;
										$maxHeight = $origOutputWidth;
									}
									$sidesTotalWidth += $outputWidth + $pdfMarginItemsHorizontal;
									
									// Set PDF border margin
									if($curXPos == 0){
										$curXPos = $pdfMarginHorizontal;
									}
									if($curYPos == 0){
										$curYPos = $pdfMarginVertical;
									}
									$origMaxHeight = $maxHeight;
									$neededNewLine = false;
									if(($curXPos + $outputWidth + $pdfMarginHorizontal) > $pdfWidth || $needNewLine == true){
										$curXPos = $pdfMarginHorizontal;
										$curYPos = $curYPos + $maxHeight + $pdfMarginItemsHorizontal + 2;
										$maxHeight = 0;
										$skuCounter = 0;
										$neededNewLine = true;
									}
									
									if($needNewLine == true){
										$curYPos = $curYPos + 8;
									}
									
									if(($curYPos + $outputHeight) > $pdfHeight){
										$needNewPage = true;
									}
									
									if($needNewPage == true){
										// Define page orientation
										if($pdfWidth < $pdfHeight){
											$orientation = 'P';
										} else {
											$orientation = 'L';
										}
										$pdf->AddPage($orientation, array($pdfWidth, $pdfHeight));
										$pdf->setPageOrientation($orientation, false, 0);
										$needNewPage = false;
										$curXPos = $pdfMarginHorizontal;
										$curYPos = $pdfMarginVertical + 8;
									}
									if($needNewLine == true){
										$orderProducts = implode(', ', $orderData[$orderId]);
										$pdf->Text(
											$x=$curXPos,
											$y=($curYPos - 8),
											$txt=$realOrderId.' - '.$city.' - '.$orderProducts
										);
									}
									$needNewLine = false;
									
                  
									// Convert to paths with inkscape  
									/*$pathInfo = pathinfo($mediaPath.'productdesigner/svg/'.$file);
									$fileName = $pathInfo['filename'];
									$extension = $pathInfo['extension'];
									$dirName = $pathInfo['dirname'];
									$saveFileLocation = $dirName.'/'.$fileName.'-inkscape-before.'.$extension;
                  */




                   /*
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
	// 								$fileContents = str_replace('"Melinda"', '"Melinda"', $fileContents);
	// 								$fileContents = str_replace('"Monotype Corsiva"', '"Monotype Corsiva"', $fileContents);
									$fileContents = str_replace('"Not just Groovy"', '"Not Just Groovy"', $fileContents);
									$fileContents = str_replace('"Old English"', '"Old English Text MT"', $fileContents);
	// 								$fileContents = str_replace('"Pristina"', '"Pristina"', $fileContents);
									$fileContents = str_replace('"Script"', '"Script MT Bold"', $fileContents);
	// 								$fileContents = str_replace('"Ubuntu"', '"Ubuntu"', $fileContents);
	// 								$fileContents = str_replace('"Verdana"', '"Verdana"', $fileContents);
									$fileContents = str_replace('"Phitradesign Ink"', '"phitradesign INK"', $fileContents);
									$fileContents = str_replace('"Viksi Script"', '"Viksi Script"', $fileContents);
                  */
									/*
									file_put_contents($saveFileLocation, $fileContents);
									$saveFileLocationAfter = $dirName.'/'.$fileName.'-inkscape-after.'.$extension;
									exec("inkscape ".$saveFileLocation." --export-plain-svg=".$saveFileLocationAfter." --export-text-to-path");
									unlink($saveFileLocation);
									$file = str_replace($fileName.'.'.$extension, $fileName.'-inkscape-after.'.$extension, $file);
									*/

 /*
// S'assurer qu'on utilise bien .svg
$extension = 'svg';
$saveFileLocation = $dirName . '/' . $fileName . '.' . $extension;
$saveFileLocationAfter = $dirName . '/' . $fileName . '-inkscape-after.' . $extension;

// Sauvegarder le contenu SVG d'origine
file_put_contents($saveFileLocation, $fileContents);

// Commande Inkscape
$command = sprintf(
    'inkscape "%s" --export-filename="%s" --export-plain-svg --export-text-to-path',
    $saveFileLocation,
    $saveFileLocationAfter
);

// Ex�cution
exec($command . ' 2>&1', $output, $exitCode);

// Debug
if ($exitCode !== 0) {
    error_log("[Inkscape ERROR] Code: $exitCode");
    error_log("[CMD] $command");
    error_log("[OUT] " . implode("\n", $output));
} else {
    // Supprimer l�original si conversion OK
    unlink($saveFileLocation);

    // Remplacer dans $file le nom
    $file = str_replace(
        $fileName . '.' . $extension,
        $fileName . '-inkscape-after.' . $extension,
        $file
    );
}
  */
  //$this->tcpdfHelper->prepareSvgForTcpdf($fileContents);
  
/*  
$fileContents = $this->getTcpdfHelper()->prepareSvgForTcpdf($fileContents);
$file_prepared = $mediaDirectory.'productdesigner/tmp/'.$fileName.'-prepared.'.$extension;
file_put_contents($file_prepared, $fileContents);
$file = str_replace($fileName.'.'.$extension, $fileName.'-prepared.'.$extension, $file);  
*/
// Mise � jour du nom de fichier vectoris�
//$file = str_replace($fileName . '.' . $extension, $fileName . '-inkscape-after.' . $extension, $file);

// Convert to paths with SVGFont
$pathInfo = pathinfo($mediaPath.'productdesigner/svg/'.$file);
$fileName = $pathInfo['filename'];
$extension = $pathInfo['extension'];
$dirName = $pathInfo['dirname'];

// 1. Charger le contenu SVG actuel
$svgFilePath = $dirName . '/' . $fileName . '.' . $extension;
$fileContents = file_get_contents($svgFilePath);

// 2. Traiter le SVG avec SVGFont
$fileContents = $this->getTcpdfHelper()->prepareSvgForTcpdf($fileContents);

// 3. Sauvegarder dans le M�ME r�pertoire que l'original (pas dans /tmp/)
$svgOutputPath = $dirName . '/' . $fileName . '-svgfont.' . $extension;
file_put_contents($svgOutputPath, $fileContents);

// 4. Mettre � jour la r�f�rence du fichier
$file = str_replace($fileName . '.' . $extension, $fileName . '-svgfont.' . $extension, $file);

// 5. IMPORTANT: D�bogage - sauvegarde d'une copie dans /tmp/ pour v�rification
$debugPath = $mediaPath . 'productdesigner/tmp/' . $fileName . '-debug.' . $extension;
file_put_contents($debugPath, $fileContents);


									if($overlayImagePath != '' && file_exists($overlayImagePath)){
										// Create overlay image with cut-out output part
										if(function_exists('imagecrop')){
	/*
											$pathInfo = pathinfo($overlayImagePath);
											$fileName = $pathInfo['filename'];
											$extension = $pathInfo['extension'];
											$dirName = $pathInfo['dirname'];
											$saveFileLocation = $dirName.'/'.$fileName.'-cropped.'.$extension;
											if(!file_exists($saveFileLocation)){
												if(strtolower($extension) == 'jpg' || strtolower($extension) == 'jpeg'){
													$im = imagecreatefromjpeg($overlayImagePath);
												} elseif(strtolower($extension) == 'png'){
													$im = imagecreatefrompng($overlayImagePath);
												}
			
												$to_crop_array = array(
													'x' => $productSide['output_x1'],
													'y' => $productSide['output_y1'],
													'width' => $productSide['output_x2'] - $productSide['output_x1'],
													'height'=> $productSide['output_y2'] - $productSide['output_y1']
												);
												$thumb_im = imagecrop($im, $to_crop_array);
												imagepng($thumb_im, $saveFileLocation);
												
												// Recolor the image to white colors
												//$this->recolorimage($saveFileLocation, $saveFileLocation, 'ffffff');
											}
											$overlayImagePath = $saveFileLocation;
	*/
										} else {
											$overlayImagePath = '';
										}
									}
									
									// Get rotate attribute
									if($enableRotate == '1'){
		//							if($origOutputWidth > $origOutputHeight){
										// Rotate items
										$pdf->StartTransform();
										if($neededNewLine == true){
											$curXPos += $outputHeight;
										}
										$pdf->Rotate(90, $curXPos, $curYPos+$outputHeight);
										$fileContents = file_get_contents($mediaPath.'productdesigner/svg/'.$file);
										
										$outerWidth = $productSide->getData('x2') - $productSide->getData('x1');
										//$svgWidth = $this->get_string_between($fileContents, 'width="', '"');
                    $svgWidth = (float)$this->get_string_between($fileContents, 'width="', '"');
										$ratio = $svgWidth / $outerWidth;
	
										$scaleFactor = ($svgWidth) / (410);
										$ratio = $ratio * $scaleFactor;
										
										if(!$productSide->getOrigOutputX1() > 0){
											$productSide->setOrigOutputX1($productSide->getData('output_x1'));
											$productSide->setOrigOutputX2($productSide->getData('output_x2'));
											$productSide->setOrigOutputY1($productSide->getData('output_y1'));
											$productSide->setOrigOutputY2($productSide->getData('output_y2'));
										}
										
										$productSide->setOutputX1($productSide->getOrigOutputX1() * $ratio);
										$productSide->setOutputX2($productSide->getOrigOutputX2() * $ratio);
										$productSide->setOutputY1($productSide->getOrigOutputY1() * $ratio);
										$productSide->setOutputY2($productSide->getOrigOutputY2() * $ratio);
										$viewBox = ($productSide->getData('output_x1') - 4).' '.($productSide->getData('output_y1') - 4).' '.($productSide->getData('output_x2') - $productSide->getData('output_x1')).' '.($productSide->getData('output_y2') - $productSide->getData('output_y1'));
	
										$viewBoxX1 = ($productSide->getData('output_x1') * $scaleFactor) + 2;
										$viewBoxY1 = ($productSide->getData('output_y1') * $scaleFactor) + 2;
										$viewBoxWidth = ($productSide->getData('output_x2') * $scaleFactor) - ($productSide->getData('output_x1') * $scaleFactor);
										$viewBoxHeight = ($productSide->getData('output_y2') * $scaleFactor) - ($productSide->getData('output_y1') * $scaleFactor);
										$viewBox = $viewBoxX1.' '.$viewBoxY1.' '.$viewBoxWidth.' '.$viewBoxHeight;
	
										$fileContents = $this->replace_between($fileContents, 'viewBox="', '"', $viewBox);
										$fileContents = $this->replace_between($fileContents, 'height="', '"', $svgWidth);
										
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
										
										//$fileContents = '@'.str_replace($mediaUrl, $mediaPath.'', $fileContents);
                    $fileContents = '@'.str_replace($mediaUrl, $mediaPath, $fileContents);
$fileContents = str_replace('+', '', $fileContents);
                    
										$pdf->ImageSVG(
											//$file=$mediaPath.'productdesigner/savesvg/'.$file,
											$file=$fileContents,
											$x=$curXPos,
											$y=$curYPos,
											$w=$origOutputWidth,
											$h=$origOutputHeight,
											$link='',
											$align='',
											$palign='',
											$border=0,
											$fitonpage=false
										);
										if($overlayImagePath != ''){
											$pdf->Image(
												$file=$overlayImagePath,
												$x=$curXPos,
												$y=$curYPos,
												$w=$origOutputWidth,
												$h=$origOutputHeight
											);
										}
										
										// Cutout SVG
										$cutoutImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
											->addFieldToFilter('product_id', $productSide->getProductId())
											->addFieldToFilter('label', $productSide->getLabel())
											->addFieldToFilter('store_id', $productSide->getStoreId())
											->setPageSize(1)
											->setCurPage(1)
											->load()
											->getFirstItem();
										if(empty($cutoutImage->getData())){
											$cutoutImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
												->addFieldToFilter('product_id', $productSide->getProductId())
												->addFieldToFilter('label', $productSide->getLabel())
												->addFieldToFilter('store_id', array('null' => true))
												->setPageSize(1)
												->setCurPage(1)
												->load()
												->getFirstItem();
										}
										$cutoutImage = $cutoutImage->getCutoutsvg();
										$cutoutImagePath = '';
										if($cutoutImage != ''){
											$cutoutImagePath = $mediaPath.'productdesigner/cutoutsvg/'.$cutoutImage;
										}
										if($cutoutImagePath != ''){
											$pdf->ImageSVG($file=$cutoutImagePath, $x=$curXPos, $y=$curYPos, $w=$origOutputWidth, $h=$origOutputHeight, $link='', $align='', $palign='', $border=0, $fitonpage=false);
										}
										
										$pdf->StopTransform();
										$curXPos = $curXPos + $origOutputHeight + $pdfMarginItemsHorizontal;
	} else {
    $filePath = $mediaPath . 'productdesigner/svg/' . $file;
    $fileContents = file_get_contents($filePath);

    $x1 = (float) $productSide->getData('x1');
    $x2 = (float) $productSide->getData('x2');
    $outerWidth = $x2 - $x1;

    $svgWidthRaw = $this->get_string_between($fileContents, 'width="', '"');
    $svgWidth = (float) preg_replace('/[^\d.]/', '', $svgWidthRaw);

    if ($outerWidth > 0 && $svgWidth > 0) {
        $ratio = $svgWidth / $outerWidth;
        $scaleFactor = $svgWidth / 410;
    } else {
        $ratio = 1.0;
        $scaleFactor = 1.0;
    }

    $viewBoxX1 = ($productSide->getData('output_x1') * $scaleFactor) + 2;
    $viewBoxY1 = ($productSide->getData('output_y1') * $scaleFactor) + 2;
    $viewBoxWidth = ($productSide->getData('output_x2') * $scaleFactor) - ($productSide->getData('output_x1') * $scaleFactor);
    $viewBoxHeight = ($productSide->getData('output_y2') * $scaleFactor) - ($productSide->getData('output_y1') * $scaleFactor);
    $viewBox = $viewBoxX1 . ' ' . $viewBoxY1 . ' ' . $viewBoxWidth . ' ' . $viewBoxHeight;

    $fileContents = $this->replace_between($fileContents, 'viewBox="', '"', $viewBox);
    $fileContents = $this->replace_between($fileContents, 'height="', '"', $svgWidth);

    $doc = new \DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadXML($fileContents);

    $images = $doc->getElementsByTagName('image');
    foreach ($images as $image) {
        $attr = $image->getAttributeNode('xlink:href');
        if (!$attr) {
            continue;
        }
        $url = $attr->value;
        if (str_contains($url, 'overlayimgs') || str_contains($url, 'color_img')) {
            $image->parentNode->removeChild($image);
        }
    }

    $fileContents = $doc->saveXML();

    // 2e passe
    $doc = new \DOMDocument();
    $doc->loadXML($fileContents);
    $images = $doc->getElementsByTagName('image');
    foreach ($images as $image) {
        $attr = $image->getAttributeNode('xlink:href');
        if (!$attr) {
            continue;
        }
        $url = $attr->value;
        if (str_contains($url, 'overlayimgs') || str_contains($url, 'color_img')) {
            $image->parentNode->removeChild($image);
        }
    }

    $fileContents = $doc->saveXML();

    //$fileContents = '@' . str_replace($mediaUrl, $mediaPath, $fileContents);
    $fileContents = '@'.str_replace($mediaUrl, $mediaPath, $fileContents);
$fileContents = str_replace('+', '', $fileContents);
    
    
    $pdf->ImageSVG(
        file: $fileContents,
        x: $curXPos,
        y: $curYPos,
        w: $outputWidth,
        h: $outputHeight,
        link: '',
        align: '',
        palign: '',
        border: 0,
        fitonpage: false
    );

    if ($overlayImagePath !== '') {
        $pdf->Image(
            file: $overlayImagePath,
            x: $curXPos,
            y: $curYPos,
            w: $outputWidth,
            h: $outputHeight
        );
    }

    // Gestion du cutout
    $cutoutImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
        ->addFieldToFilter('product_id', $productSide->getProductId())
        ->addFieldToFilter('label', $productSide->getLabel())
        ->addFieldToFilter('store_id', $productSide->getStoreId())
        ->setPageSize(1)
        ->setCurPage(1)
        ->load()
        ->getFirstItem();

    if (empty($cutoutImage->getData())) {
        $cutoutImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
            ->addFieldToFilter('product_id', $productSide->getProductId())
            ->addFieldToFilter('label', $productSide->getLabel())
            ->addFieldToFilter('store_id', ['null' => true])
            ->setPageSize(1)
            ->setCurPage(1)
            ->load()
            ->getFirstItem();
    }

    $cutoutImage = $cutoutImage->getCutoutsvg();
    if (!empty($cutoutImage)) {
        $cutoutImagePath = $mediaPath . 'productdesigner/cutoutsvg/' . $cutoutImage;
        $pdf->ImageSVG(
            file: $cutoutImagePath,
            x: $curXPos,
            y: $curYPos,
            w: $outputWidth,
            h: $outputHeight,
            link: '',
            align: '',
            palign: '',
            border: 0,
            fitonpage: false
        );
    }

    $curXPos += $outputWidth + $pdfMarginItemsHorizontal;
}

									
								}
								// Show sku below item
								$xPos = ($sidesTotalWidth * $skuCounter) + ($sidesTotalWidth - strlen($orderItem->getSku())) / 2;
								$pdf->Text(
									$x=$xPos,
									$y=($curYPos + $outputHeight + 1),
									$txt=$orderItem->getSku()
								);
								$lineStartPos = ($sidesTotalWidth * $skuCounter) + $pdfMarginHorizontal;
								$lineEndPos = ($sidesTotalWidth * $skuCounter) + ($sidesTotalWidth - strlen($orderItem->getSku())) / 2;
								$pdf->Line($lineStartPos, ($curYPos + $outputHeight + 2), $lineEndPos, ($curYPos + $outputHeight + 2));
								
								$diff = $lineEndPos - $lineStartPos;
								$lineStartPos = $lineEndPos + (strlen($orderItem->getSku()) * 2);
								$lineEndPos = $lineStartPos + $diff;
								$pdf->Line($lineStartPos, ($curYPos + $outputHeight + 2), $lineEndPos, ($curYPos + $outputHeight + 2));
							}
						}
						
						$saveData = array(
							'finished' => 1,
							'pdf_file_'.$type => $dbFile,
							'store_id' => $orderItem->getStoreId()
						);
		
						// Save item as processed
						$objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport')
							->load($item->getId())
							->setData($saveData)
							->setId($item->getId())
							->save();
					}
					if(count($allItems) > 0 && count($orderData) > 0){
						 $pdf->Output($pdfFile, 'F');
					 }
				}
			}
		} elseif($exportCombining == 'wood_board'){
			
			$firstOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getFirstItem()->getOrderId())->getRealOrderId();
			$lastOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getLastItem()->getOrderId())->getRealOrderId();
			$lastOrderIdOrig = $lastOrderId;
			
			$types = array('wood');
			foreach($types as $type){
				$random = rand(0, 999999);
				$random = $firstOrderId.'-'.$lastOrderIdOrig.'-'.$type;
				$pdfFile = $pdfPath.$random.'.pdf';
				$dbFile = date('Y').'/'.date('m').'/'.$random.'.pdf';
				
				// Initialize PDF creator
				$layout = $this->_view->getLayout();
				$block = $layout->createBlock('Laurensmedia\Productdesigner\Block\Index');
				$helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Laurensmedia\Productdesigner\Helper\Tcpdfhelper');
		
				// create new PDF document
				$pdf = $helper->getPdfObject($block->get_base_dir(''));
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
				
				$lastOrderId = '';
				$needNewLine = false;
				$curXPos = 0;
				$curYPos = 0;
				
				// Introduction text
				$allOrderIds = $allItems->getColumnValues('order_id');
				$firstOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allOrderIds[0])->getRealOrderId();
				$lastOrderId = $objectManager->create('Magento\Sales\Model\Order')->load(end($allOrderIds))->getRealOrderId();
				if($pdfWidth < $pdfHeight){
					$orientation = 'P';
				} else {
					$orientation = 'L';
				}
				$pdf->AddPage($orientation, array($pdfWidth, $pdfHeight));
				$pdf->setPageOrientation($orientation, false, 0);
				$needNewPage = false;
				$curXPos = $pdfMarginHorizontal;
				$curYPos = $pdfMarginVertical;

				$orderItemIds = $allItems->getColumnValues('order_item_id');
				$orderItemsArray = [];
				foreach ($orderItemIds as $orderItemId) {
					// Charger l'order item (note : il est préférable d'utiliser DI plutôt que l'ObjectManager)
					$orderItem = $objectManager->create(\Magento\Sales\Model\Order\Item::class)->load($orderItemId);
				
					// Récupérer le produit associé
					$product = $orderItem->getProduct();
				
					// Récupérer l'attribut "technology" du produit
					$technologyAttribute = $product->getResource()->getAttribute('technology');
					if (!$technologyAttribute) {
						// Si l'attribut n'existe pas, on passe à l'élément suivant
						continue;
					}
				
					// Obtenir la valeur de l'attribut et supprimer les espaces pour obtenir une chaîne CSV propre
					$technologyValue = $technologyAttribute->getFrontend()->getValue($product);
					$productPrintingTypes = explode(',', str_replace(' ', '', $technologyValue));
				
					// Vérifier si le type souhaité est présent dans le tableau
					if (!in_array($type, $productPrintingTypes)) {
						continue;
					}
				
					// Ici, le type est présent et vous pouvez continuer le traitement de l'order item
				}	
				
				$orderData = [];
foreach ($allItems as $item) {
    // Récupérer l'order item via l'ObjectManager (préférez DI dans le long terme)
    $orderItem = $objectManager->create(\Magento\Sales\Model\Order\Item::class)
        ->load($item->getOrderItemId());
    
    // Récupérer l'URL média (non utilisée ici, mais disponible si besoin)
    $mediaUrl = $storeManager->getStore($orderItem->getStoreId())
        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    
    $product = $orderItem->getProduct();
    
    // Obtenir la valeur de l'attribut "technology" du produit
    $technologyValue = $product->getResource()
        ->getAttribute('technology')
        ->getFrontend()
        ->getValue($product);
    
    // Supprimer les espaces et découper la chaîne par virgule pour obtenir un tableau
    $productPrintingTypes = explode(',', str_replace(' ', '', $technologyValue));
    
    // Vérifier que le type (la variable $type doit être définie dans votre contexte) est présent
    if (!in_array($type, $productPrintingTypes)) {
        continue;
    }
    
    $orderId = $orderItem->getOrderId();
    $qtyOrdered = (int)$orderItem->getQtyOrdered();
    
    // Si le produit est déjà présent dans $orderData pour cet ordre, additionner la quantité
    if (isset($orderData[$orderId][$orderItem->getSku()])) {
        // Format attendu : "SKU x quantité"
        $existingEntry = $orderData[$orderId][$orderItem->getSku()];
        $parts = explode('x', $existingEntry);
        $existingQty = isset($parts[1]) ? floatval(trim($parts[1])) : 0;
        $qtyOrdered += $existingQty;
    }
    
    // Enregistrer le résultat au format "SKU x quantité"
    $orderData[$orderId][$orderItem->getSku()] = $orderItem->getSku() . ' x ' . $qtyOrdered;
}
				
				// Dot in left top of document
				$pdf->startLayer('repere', true, true, false);
				$pdf->Rect(
					$x=0,
					$y=0,
					$w=1,
					$h=1,
					'F',
					array('color' => array(0, 0, 0))
				);
				$pdf->endLayer();
				
				for($layerCounter=0;$layerCounter<3;$layerCounter++){
					$curXPos = $pdfMarginHorizontal;
					$curYPos = $pdfMarginVertical;
					
					if($layerCounter > 0){
						$pdf->AddPage($orientation, array($pdfWidth, $pdfHeight));
						$pdf->setPageOrientation($orientation, false, 0);
						$pdf->startLayer('repere', true, true, false);
						$pdf->Rect(
							$x=0,
							$y=0,
							$w=1,
							$h=1,
							'F',
							array('color' => array(0, 0, 0))
						);
						$pdf->endLayer();
					}
					
					if($layerCounter == 0){
						$pdf->startLayer('verso', true, true, false);
					} elseif($layerCounter == 1){
						$pdf->startLayer('recto', true, true, false);
					} elseif($layerCounter == 2){
						$pdf->startLayer('decoupe', true, true, false);
					}
					$lastOrderId = '';			
					foreach($allItems as $item){
						$prevItem = '';
						// Load saved items
						$orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
						$mediaUrl = $storeManager->getStore($orderItem->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
						
						$product = $orderItem->getProduct();
						$technologyAttribute = $product->getResource()->getAttribute('technology');
						if (!$technologyAttribute) {
							continue;
						}

						$technologyValue = $technologyAttribute->getFrontend()->getValue($product);
						$productPrintingTypes = array_filter(explode(',', str_replace(' ', '', $technologyValue)));

						if (!in_array($type, $productPrintingTypes)) {
							continue;
						}
						
						$_resource = $objectManager->create('Magento\Catalog\Model\Product')->getResource();
						$enableRotate = $_resource->getAttributeRawValue($orderItem->getProductId(),  'pd_rotate_in_export', $storeManager->getStore());
						$orderId = $orderItem->getOrderId();
						$lastOrderId = $orderId;
						$lastOrder = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
						$realOrderId = $lastOrder->getRealOrderId();
						$shippingData = $lastOrder->getShippingAddress()->getData();
						
						$qty = $orderItem->getQtyOrdered();
						if(floatval($qty) < 1 && $orderItem->getQtyInvoiced() > 0){
							$qty = $orderItem->getQtyInvoiced();
						}
						$orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
						$connectId = $orderItemOptions['connect_id']['connect_id'];
						
						if($connectId > 0){
							$savedItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
								->addFieldToFilter('connect_id', $connectId)
								->setPageSize(4)
								->setCurPage(1)
								->load();
								
							// Print each product side on PDF
							$skuCounter = 0;
							for($i=0; $i<$qty; $i++){
								$skuCounter ++;
								
								$amountOfSides = count($savedItems);
								$sidesTotalWidth = 0;
								$count = 0;
								foreach($savedItems as $productSide){
									$count++;
									if(!$pdfWidth > 0 || !$pdfHeight > 0){
										$pdfWidth = $productSide->getOutputwidth();
										$pdfHeight = $productSide->getOutputheight();
									}
									$outputWidth = $productSide->getOutputwidth();
									$outputHeight = $productSide->getOutputheight();
									if($outputHeight > $maxHeight){
										$maxHeight = $outputHeight;
									}
									
									$origOutputWidth = $outputWidth;
									$origOutputHeight = $outputHeight;
									$sidesTotalWidth += $outputWidth + $pdfMarginItemsHorizontal;

									// Set PDF border margin
									if($curXPos == 0){
										$curXPos = $pdfMarginHorizontal;
									}
									if($curYPos == 0){
										$curYPos = $pdfMarginVertical;
									}
									$origMaxHeight = $maxHeight;
									$neededNewLine = false;
									if(($curXPos + $outputWidth + $pdfMarginHorizontal) > $pdfWidth || $needNewLine == true){
										$curXPos = $pdfMarginHorizontal;
										$curYPos = $curYPos + $maxHeight + $pdfMarginItemsHorizontal + 2;
										$maxHeight = 0;
										$skuCounter = 0;
										$neededNewLine = true;
									}
									
									if($needNewLine == true){
										$curYPos = $curYPos + 8;
									}
									
									if(($curYPos + $outputHeight) > $pdfHeight){
										$needNewPage = true;
									}
									
									if($needNewPage == true){
										// Define page orientation
										if($pdfWidth < $pdfHeight){
											$orientation = 'P';
										} else {
											$orientation = 'L';
										}
										$pdf->AddPage($orientation, array($pdfWidth, $pdfHeight));
										$pdf->setPageOrientation($orientation, false, 0);
										$needNewPage = false;
										$curXPos = $pdfMarginHorizontal;
										$curYPos = $pdfMarginVertical + 8;
									}
									$needNewLine = false;

									if($layerCounter == 0 || $layerCounter == 1){
										$file = $productSide->getSvgfile();
										$file = $productSide->getSvg();
										$fileName = basename($file);
										$outputFileName = 'output_'.$fileName;
										$file = str_replace($fileName, $outputFileName, $file);
										$handle = fopen($mediaPath.'productdesigner/svg/'.$file, 'r');
										$fileContents = fread($handle, filesize($mediaPath.'productdesigner/svg/'.$file));
										$fileContents = str_replace($mediaUrl, $mediaPath.'', $fileContents);
										fclose($handle);
										
										$hasDesign = true;
										if(strpos($fileContents, 'overlayimgs') == false && strpos($fileContents, 'color_img') == false && strpos($fileContents, 'image') == false && strpos($fileContents, 'path') == false){
											$hasDesign = false;
										} elseif(strpos($fileContents, 'color_img') == false && strpos($fileContents, 'overlayimgs') != false && substr_count($fileContents, 'image') <= 1 && strpos($fileContents, 'text') == false){
											$hasDesign = false;
										} elseif(strpos($fileContents, 'overlayimgs') == false && strpos($fileContents, 'color_img') != false && substr_count($fileContents, 'image') <= 1 && strpos($fileContents, 'text') == false){
											$hasDesign = false;
										} elseif(strpos($fileContents, 'overlayimgs') != false && strpos($fileContents, 'color_img') != false && substr_count($fileContents, 'image') <= 2 && strpos($fileContents, 'text') == false){
											$hasDesign = false;
										}
			
										if((strpos($fileContents, 'image') == false && strpos($fileContents, 'path') == false) || !$hasDesign){
											if($emptyDesign == 'do_not_print' || $prevItem == ''){
												continue;
											} elseif($emptyDesign == 'print_other_side'){
												$productSide = $prevItem;
												$file = $productSide->getSvgfile();
												$file = $productSide->getSvg();
												$fileName = basename($file);
												$outputFileName = 'output_'.$fileName;
												$file = str_replace($fileName, $outputFileName, $file);
											}
										} else {
											$prevItem = $productSide;
										}
										
										// Convert to paths with inkscape
									/*	$pathInfo = pathinfo($mediaPath.'productdesigner/svg/'.$file);
										$fileName = $pathInfo['filename'];
										$extension = $pathInfo['extension'];
										$dirName = $pathInfo['dirname'];
										$saveFileLocation = $dirName.'/'.$fileName.'-inkscape-before.'.$extension;
									*/
                  
                  
                  /*	
										$fileContents = str_replace('"Amatic"', '"Amatic Bold"', $fileContents);
										$fileContents = str_replace('"Baroque"', '"Baroque Script"', $fileContents);
										$fileContents = str_replace('"Baskerville"', '"Libre Baskerville"', $fileContents);
										$fileContents = str_replace('"Bauhaus"', '"Bauhaus"', $fileContents);
										$fileContents = str_replace('"Birds of Paradise"', '"Birds of Paradise  Personal use"', $fileContents);
										$fileContents = str_replace('"Comic Sans"', '"Comic Sans MS"', $fileContents);
										$fileContents = str_replace('"Cooper"', '"Cooper Black"', $fileContents);
										$fileContents = str_replace('"Dancing"', '"Dancing Script OT"', $fileContents);
										$fileContents = str_replace('"Duepuntozero"', '"Duepuntozero"', $fileContents);
										$fileContents = str_replace('"Edwardian"', '"Edwardian Script ITC"', $fileContents);
										$fileContents = str_replace('"FreestyleScript"', '"Freestyle Script"', $fileContents);
										$fileContents = str_replace('"Harrington"', '"Harrington (Plain):001.001"', $fileContents);
										$fileContents = str_replace('"KentuckyFriedChicken"', '"KentuckyFriedChickenFont"', $fileContents);
										$fileContents = str_replace('"Lucida Handwriting"', '"QK Marisa"', $fileContents);
										$fileContents = str_replace('"Not just Groovy"', '"Not Just Groovy"', $fileContents);
										$fileContents = str_replace('"Old English"', '"Old English Text MT"', $fileContents);
										$fileContents = str_replace('"Script"', '"Script MT Bold"', $fileContents);
										$fileContents = str_replace('"Phitradesign Ink"', '"phitradesign INK"', $fileContents);
										$fileContents = str_replace('"Viksi Script"', '"Viksi Script"', $fileContents);
                    */
										/*
										file_put_contents($saveFileLocation, $fileContents);
										$saveFileLocationAfter = $dirName.'/'.$fileName.'-inkscape-after.'.$extension;
										exec("inkscape ".$saveFileLocation." --export-plain-svg=".$saveFileLocationAfter." --export-text-to-path");
										unlink($saveFileLocation);
										$file = str_replace($fileName.'.'.$extension, $fileName.'-inkscape-after.'.$extension, $file);
										*/
                    /*
                    // S'assurer qu'on utilise bien .svg
                    $extension = 'svg';
                    $saveFileLocation = $dirName . '/' . $fileName . '.' . $extension;
                    $saveFileLocationAfter = $dirName . '/' . $fileName . '-inkscape-after.' . $extension;

                    // Sauvegarder le contenu SVG d'origine
                    file_put_contents($saveFileLocation, $fileContents);

                    // Commande Inkscape
                    $command = sprintf(
                        'inkscape "%s" --export-filename="%s" --export-plain-svg --export-text-to-path',
                        $saveFileLocation,
                        $saveFileLocationAfter
                    );

                    // Ex�cution
                    exec($command . ' 2>&1', $output, $exitCode);

                    // Debug
                    if ($exitCode !== 0) {
                        error_log("[Inkscape ERROR] Code: $exitCode");
                        error_log("[CMD] $command");
                        error_log("[OUT] " . implode("\n", $output));
                    } else {
                        // Supprimer l�original si conversion OK
                        unlink($saveFileLocation);

                        // Remplacer dans $file le nom
                        $file = str_replace(
                            $fileName . '.' . $extension,
                            $fileName . '-inkscape-after.' . $extension,
                            $file
                        );
                    }
                    */
                   //$this->tcpdfHelper->prepareSvgForTcpdf($fileContents);
/*$fileContents = $this->getTcpdfHelper()->prepareSvgForTcpdf($fileContents);
$file_prepared = $mediaDirectory.'productdesigner/tmp/'.$fileName.'-prepared.'.$extension;
file_put_contents($file_prepared, $fileContents);
$file = str_replace($fileName.'.'.$extension, $fileName.'-prepared.'.$extension, $file);
*/
// Convert to paths with SVGFont
$pathInfo = pathinfo($mediaPath.'productdesigner/svg/'.$file);
$fileName = $pathInfo['filename'];
$extension = $pathInfo['extension'];
$dirName = $pathInfo['dirname'];

// 1. Charger le contenu SVG actuel
$svgFilePath = $dirName . '/' . $fileName . '.' . $extension;
$fileContents = file_get_contents($svgFilePath);

// 2. Traiter le SVG avec SVGFont
$fileContents = $this->getTcpdfHelper()->prepareSvgForTcpdf($fileContents);

// 3. Sauvegarder dans le M�ME r�pertoire que l'original (pas dans /tmp/)
$svgOutputPath = $dirName . '/' . $fileName . '-svgfont.' . $extension;
file_put_contents($svgOutputPath, $fileContents);

// 4. Mettre � jour la r�f�rence du fichier
$file = str_replace($fileName . '.' . $extension, $fileName . '-svgfont.' . $extension, $file);

// 5. IMPORTANT: D�bogage - sauvegarde d'une copie dans /tmp/ pour v�rification
$debugPath = $mediaPath . 'productdesigner/tmp/' . $fileName . '-debug.' . $extension;
file_put_contents($debugPath, $fileContents);
                    // Mise � jour du nom de fichier vectoris�
                    //$file = str_replace($fileName . '.' . $extension, $fileName . '-inkscape-after.' . $extension, $file);

		
										$fileContents = file_get_contents($mediaPath.'productdesigner/svg/'.$file);
										
										$outerWidth = $productSide->getData('x2') - $productSide->getData('x1');
										$svgWidth = $this->get_string_between($fileContents, 'width="', '"');
										$ratio = $svgWidth / $outerWidth;
		
										$scaleFactor = ($svgWidth) / (410);
		
										$viewBoxX1 = ($productSide->getData('output_x1') * $scaleFactor) + 2;
										$viewBoxY1 = ($productSide->getData('output_y1') * $scaleFactor) + 2;
										$viewBoxWidth = ($productSide->getData('output_x2') * $scaleFactor) - ($productSide->getData('output_x1') * $scaleFactor);
										$viewBoxHeight = ($productSide->getData('output_y2') * $scaleFactor) - ($productSide->getData('output_y1') * $scaleFactor);
										$viewBox = $viewBoxX1.' '.$viewBoxY1.' '.$viewBoxWidth.' '.$viewBoxHeight;
										
										$fileContents = $this->replace_between($fileContents, 'viewBox="', '"', $viewBox);
										$fileContents = $this->replace_between($fileContents, 'height="', '"', $svgWidth);
		
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
                    
										//$fileContents = '@'.str_replace($mediaUrl, $mediaPath.'', $fileContents);
										$fileContents = '@'.str_replace($mediaUrl, $mediaPath, $fileContents);
                    $fileContents = str_replace('+', '', $fileContents);

										if(($layerCounter == 0 && $count == 1) || ($layerCounter == 1 && $count == 2)){
											$pdf->ImageSVG(
												//$file=$mediaPath.'productdesigner/savesvg/'.$file,
												$file=$fileContents,
												$x=$curXPos,
												$y=$curYPos,
												$w=$outputWidth,
												$h=$outputHeight,
												$link='',
												$align='',
												$palign='',
												$border=0,
												$fitonpage=false
											);
										}
									}
									
									if($layerCounter == 2 && $count == 2){
										// Cutout SVG
										$cutoutImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
											->addFieldToFilter('product_id', $productSide->getProductId())
											->addFieldToFilter('label', $productSide->getLabel())
											->addFieldToFilter('store_id', $productSide->getStoreId())
											->setPageSize(1)
											->setCurPage(1)
											->load()
											->getFirstItem();
										if(empty($cutoutImage->getData())){
											$cutoutImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
												->addFieldToFilter('product_id', $productSide->getProductId())
												->addFieldToFilter('label', $productSide->getLabel())
												->addFieldToFilter('store_id', array('null' => true))
												->setPageSize(1)
												->setCurPage(1)
												->load()
												->getFirstItem();
										}
										$cutoutImage = $cutoutImage->getCutoutsvg();
										$cutoutImagePath = '';
										if($cutoutImage != ''){
											$cutoutImagePath = $mediaPath.'productdesigner/cutoutsvg/'.$cutoutImage;
										}
										if($cutoutImagePath != ''){
											$pdf->ImageSVG($file=$cutoutImagePath, $x=$curXPos, $y=$curYPos, $w=$outputWidth, $h=$outputHeight, $link='', $align='', $palign='', $border=0, $fitonpage=false);
										}
									}
									if($count == 2){
										$curXPos = $curXPos + $outputWidth + $pdfMarginItemsHorizontal;
									}
								}
							}
						}
						
						$saveData = array(
							'finished' => 1,
							'pdf_file_'.$type => $dbFile,
							'store_id' => $orderItem->getStoreId()
						);
		
						// Save item as processed
						$objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport')
							->load($item->getId())
							->setData($saveData)
							->setId($item->getId())
							->save();
					}
					$pdf->endLayer();
				}
				if(count($allItems) > 0){
					 $pdf->Output($pdfFile, 'F');
				 }
			}
		}

		// Output PDF
		if(count($allItems) > 0){
// 			$pdf->Output($pdfFile, 'F');
			 $pdf->Output($pdfFile, 'I');
		}
	}
	
	function hex2rgb($hex) {
		$hex = str_replace("#", "", $hex);
		
		if(strlen($hex) == 3) {
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
		$rgb = array($r, $g, $b);
		//return implode(",", $rgb); // returns the rgb values separated by commas
		return $rgb; // returns an array with the rgb values
	}
	
	public function recolorimage($oldImagePath, $newImagePath, $newImageColor){
		$imageSize = getimagesize($oldImagePath);
		$width = $imageSize[0];
		$height = $imageSize[1];
		$pathInfo = pathinfo($oldImagePath);
		$ext = $pathInfo['extension'];

		$rgbColor = $this->hex2rgb($newImageColor);

		// Start recoloring image
		if($ext == 'png'){
			$img = imagecreatefrompng($oldImagePath);
		} else {
			return $oldImagePath;
		}
		$transparentImage = imagecreatetruecolor($width, $height);

		$black = imagecolorallocatealpha($img, 0, 0, 0, 127);
		imagefill($transparentImage, 0, 0, $black);
		//$white = imagecolorallocatealpha($img, 255, 10, 255, 0);
		$white = imagecolorallocatealpha($img, $rgbColor[0], $rgbColor[1], $rgbColor[2], 0);

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$color = imagecolorat($img, $x, $y);
				$color = imagecolorsforindex($img, $color);
				if ($color['alpha'] < 127) {
					imagesetpixel($transparentImage, $x, $y, $white);
				} else {
					imagesetpixel($transparentImage, $x, $y, $black);
				}
			}
		}
		ImageColorTransparent($img, $black);
		imageAlphaBlending($transparentImage, true);
		imageSaveAlpha($transparentImage, true);

		ImagePng($transparentImage, $newImagePath);
		ImageDestroy($img);
		ImageDestroy($transparentImage);
		return $newImagePath;
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