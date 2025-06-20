<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\cleanup;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Save extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		ini_set('memory_limit', "1024M");
		error_reporting(0);
        $data = $this->getRequest()->getPostValue();
		$postData = $data;
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')
			->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
		// echo '<pre>';print_r($postData);exit;

		$startDateTime = strtotime($postData['start_date'].' 00:00:00');
		$endDateTime = strtotime($postData['end_date'].' 23:59:59');
		$startYear = date('Y', $startDateTime);
		$startMonth = date('m', $startDateTime);
		$startDate = date('d', $startDateTime);
		$endYear = date('Y', $endDateTime);
		$endMonth = date('m', $endDateTime);
		$endDate = date('d', $endDateTime);

		// Clean up quote items table
		if($this->getRequest()->getParam('quote_items') == 'true'){
			$quoteItems = Mage::getModel('sales/quote_item')
				->getCollection()
				->addFieldToFilter('updated_at', array('gteq' => "$startYear-$startMonth-$startDate 00:00:00"))
				->addFieldToFilter('updated_at', array('lteq' => "$endYear-$endMonth-$endDate 23:59:59"));
		}
		
		// Clean up saved designs table
/*
		if($this->getRequest()->getParam('prod_design_saved') == 'true'){
	        $items = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
				->addFieldToFilter('is_ordered', '0')
				->addFieldToFilter('save_id', array('lteq' => $this->getRequest()->getParam('prod_design_saved_number')));
			foreach($items as $item){
				$item->delete();
			}
		}
*/

/*
		$orderImages = array();
        $svgFiles = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
			->addFieldToFilter('is_ordered', 1)
			->getColumnValues('svg');
		foreach($svgFiles as $svgFile){
			$svgFileLocation = $mediaDirectory->getAbsolutePath().'productdesigner/svg/'.$svgFile;
			$handle = fopen($svgFileLocation, 'r');
			$svgContent = fread($handle, filesize($svgFileLocation));
			fclose($handle);
			
			// Look for images in SVG file
			$svgObject = simplexml_load_file($svgFileLocation);
			if($svgObject && !empty($svgObject)){
				foreach($svgObject->children() as $svgElement){
					if($svgElement->getName() == 'image'){
						$imgSrc = $svgElement->attributes('xlink', true)->href;
						$orderImages[] = (string)$imgSrc[0];
					} elseif($svgElement->getName() == 'g'){
						foreach($svgElement->children() as $svgSubElement){
							if($svgSubElement->getName() == 'image'){
								$imgSrc = $svgSubElement->attributes('xlink', true)->href;
								$orderImages[] = (string)$imgSrc[0];
							}
						}
					}
				}
			}
		}
*/

		$orderItems = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\Item\Collection')
			->addFieldToSelect('productdesigner_data')
			->addFieldToSelect('item_id')
			->addFieldToSelect('order_id')
			->addFieldToFilter('productdesigner_data', array('like' => '%connect_id%'));
		$orderImages = array();
		$orderItemConnectIds = array();
		foreach($orderItems as $orderItem){
			$itemJson = json_decode($orderItem->getProductdesignerData(), true);
			$connectId = '';
			if(isset($itemJson['connect_id'])){
				$connectId = $itemJson['connect_id']['connect_id'];
			}
			if($connectId == ''){
				continue;
			}
			$orderItemConnectIds[] = $connectId;
			
			
		}
		
		// Delete uploaded files
		if($this->getRequest()->getParam('clean_uploads') == 'true'){
			$count = 0;
			$dir = $mediaDirectory->getAbsolutePath().'productdesigner_uploads/';
			$yearDirs = scandir($dir);
			foreach($yearDirs as $yearDir){
				if($yearDir == '.' || $yearDir == '..' || !is_dir($dir.$yearDir)){
					continue;
				}
				if(strlen($yearDir) != 4 || $yearDir < $startYear || $yearDir > $endYear){
					continue;
				}
				$monthDirs = scandir($dir.$yearDir.'/');
				foreach($monthDirs as $monthDir){
					if(strlen($monthDir) != 2 || $monthDir == '.' || $monthDir == '..' || !is_dir($dir.$yearDir.'/'.$monthDir)){
						continue;
					}
					if($yearDir == $startYear){
						if($monthDir < $startMonth){
							continue;
						}
					} elseif($yearDir == $endYear){
						if($monthDir > $endMonth){
							continue;
						}
					}
					
					// Get connect ids for same year/month
					$savedItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
						->addFieldToSelect('connect_id')
						->addFieldToSelect('svg')
						->addFieldToFilter('svg', array('like' => '%'.$yearDir.'/'.$monthDir.'%'));
					$connectIds = array();
					foreach($savedItems as $savedItem){
						$connectIds[] = $savedItem->getConnectId();
					}
					$connectIds = array_unique($connectIds);
					
					$dateDirs = scandir($dir.$yearDir.'/'.$monthDir.'/');
					foreach($dateDirs as $dateDir){
						if(strlen($dateDir) != 2 || $dateDir == '.' || $dateDir == '..' || !is_dir($dir.$yearDir.'/'.$monthDir.'/'.$dateDir)){
							continue;
						}
						if($yearDir == $startYear && $monthDir == $startMonth){
							if($dateDir < $startDate){
								continue;
							}
						} elseif($yearDir == $endYear && $monthDir == $endMonth){
							if($dateDir > $endDate){
								continue;
							}
						}
						
						$files = scandir($dir.$yearDir.'/'.$monthDir.'/'.$dateDir.'/');
						foreach($files as $fileName){
							if(!is_dir($dir.$yearDir.'/'.$monthDir.'/'.$dateDir.'/'.$fileName)){
								$filePath = $mediaDirectory->getAbsolutePath().'productdesigner_uploads/'.$yearDir.'/'.$monthDir.'/'.$dateDir.'/'.$fileName;
								
								$isConnectedToOrder = false;
								foreach($connectIds as $connectId){
									$orderItem = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\Item\Collection')
										->addFieldToSelect('productdesigner_data')
										->addFieldToSelect('item_id')
										->addFieldToSelect('order_id')
										->addFieldToFilter('productdesigner_data', array('like' => '%'.$connectId.'%'))
										->setPageSize(1)
										->getFirstItem();
									if($orderItem->getId() > 0){
										// Check for file name in saved file
										foreach($savedItems as $savedItem){
											if($savedItem->getConnectId() == $connectId){
												$svgFileLocation = $mediaDirectory->getAbsolutePath().'productdesigner/svg/'.$savedItem->getSvg();
												if(file_exists($svgFileLocation)){
													$fileContents = file_get_contents($svgFileLocation);
													if(strpos($fileContents, $fileName) !== false){
														$isConnectedToOrder = true;
													}
												}
											}
										}
									}
								}
								if(!$isConnectedToOrder){
									$deletePath = $mediaDirectory->getAbsolutePath().'productdesigner_uploads/'.$yearDir.'/'.$monthDir.'/'.$dateDir.'/'.$fileName;
									$dateCreated = filectime($deletePath);
									if($dateCreated < $startDateTime || $dateCreated > $endDateTime){
										echo "Out of date range ".$filePath.'<br />';
										continue;
									}
									unlink($mediaDirectory->getAbsolutePath().'productdesigner_uploads/'.$yearDir.'/'.$monthDir.'/'.$dateDir.'/'.$fileName);
									//unlink($mediaDirectory->getAbsolutePath().'productdesigner_uploads/thumbs/'.$yearDir.'/'.$monthDir.'/'.$dateDir.'/'.$fileName);
									echo "Deleted ".$filePath.'<br />';
									$count++;
								} else {
									echo "Exists ".$filePath.'<br />';
								}
								
							}
						}
					}
				}
			}
			
			$this->messageManager->addSuccess(__('Successfully deleted '.$count.' images'));
		}
		
		
		// Delete SVG files
		if($this->getRequest()->getParam('clean_svg') == 'true'){
			$count = 0;
			$svgFiles = array();
			$svgFileNames = array();
			$dir = $mediaDirectory->getAbsolutePath().'productdesigner/svg/';
			$yearDirs = scandir($dir);
			foreach($yearDirs as $yearDir){
				if($yearDir == '.' || $yearDir == '..' || !is_dir($dir.$yearDir)){
					continue;
				}
				$monthDirs = scandir($dir.$yearDir.'/');
				foreach($monthDirs as $monthDir){
					if($monthDir == '.' || $monthDir == '..' || !is_dir($dir.$yearDir.'/'.$monthDir)){
						continue;
					}
	
					$files = scandir($dir.$yearDir.'/'.$monthDir.'/');
					foreach($files as $fileName){
						if(!is_dir($dir.$yearDir.'/'.$monthDir.'/'.$fileName)){
							if(strpos($fileName, 'output_') !== false){
								continue;
							}
							$filePath = $mediaDirectory->getAbsolutePath().'productdesigner/svg/'.$yearDir.'/'.$monthDir.'/'.$fileName;
							$svgFiles[] = $filePath;
							$svgFileNames[] = $yearDir.'/'.$monthDir.'/'.$fileName;
						}
					}
				}
			}
			
	        $savedItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
	        	->addFieldToSelect('connect_id')
	        	->addFieldToSelect('svg')
				->addFieldToFilter('svg', array('in' => $svgFileNames));
			$connectIds = array();
			foreach($savedItems as $item){
				$svgFileName = $item->getSvg();
				$connectIds[$svgFileName] = $item->getConnectId();
			}
	
			foreach($svgFileNames as $fileName){
				$connectId = $connectIds[$fileName];
				if($connectId != '' && !in_array($connectId, $orderItemConnectIds)){
					$dateCreated = filectime($dir.$fileName);
					if($dateCreated < $startDateTime || $dateCreated > $endDateTime){
						echo "Out of date range ".$dir.$fileName.'<br />';
						continue;
					}
					// Unlink svg file
					if(file_exists($dir.$fileName)){
						unlink($dir.$fileName);
						$count++;
					}
					$baseName = basename($fileName);
					$outputFile = $dir.str_replace($baseName, 'output_'.$baseName, $fileName);
					if(file_exists($outputFile)){
						unlink($outputFile);
						$count++;
					}
				}
			}
			
			$this->messageManager->addSuccess(__('Successfully deleted '.$count.' svg files'));
		}




		// Delete PNG files
		if($this->getRequest()->getParam('clean_png') == 'true'){
			$count = 0;
			$pngFiles = array();
			$pngFileNames = array();
			$dir = $mediaDirectory->getAbsolutePath().'productdesigner/png_export/';
			$yearDirs = scandir($dir);
			foreach($yearDirs as $yearDir){
				if($yearDir == '.' || $yearDir == '..' || !is_dir($dir.$yearDir)){
					continue;
				}
				$monthDirs = scandir($dir.$yearDir.'/');
				foreach($monthDirs as $monthDir){
					if($monthDir == '.' || $monthDir == '..' || !is_dir($dir.$yearDir.'/'.$monthDir)){
						continue;
					}
	
					$files = scandir($dir.$yearDir.'/'.$monthDir.'/');
					foreach($files as $fileName){
						if(!is_dir($dir.$yearDir.'/'.$monthDir.'/'.$fileName)){
							$filePath = $mediaDirectory->getAbsolutePath().'productdesigner/png_export/'.$yearDir.'/'.$monthDir.'/'.$fileName;
							$pngFiles[] = $filePath;
							$pngFileNames[] = $yearDir.'/'.$monthDir.'/'.$fileName;
						}
					}
				}
			}
			
	        $savedItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
	        	->addFieldToSelect('connect_id')
	        	->addFieldToSelect('png')
				->addFieldToFilter('png', array('in' => $pngFileNames));
			$connectIds = array();
			foreach($savedItems as $item){
				$pngFileName = $item->getPng();
				$connectIds[$pngFileName] = $item->getConnectId();
			}
	
			foreach($pngFileNames as $fileName){
				$connectId = $connectIds[$fileName];
				if($connectId != '' && !in_array($connectId, $orderItemConnectIds)){
					$dateCreated = filectime($dir.$fileName);
					if($dateCreated < $startDateTime || $dateCreated > $endDateTime){
						echo "Out of date range ".$dir.$fileName.'<br />';
						continue;
					}
					// Unlink png file
					if(file_exists($dir.$fileName)){
						unlink($dir.$fileName);
						$count++;
					}
				}
			}
			
			$this->messageManager->addSuccess(__('Successfully deleted '.$count.' png files'));
		}




		// Delete JSON files
		if($this->getRequest()->getParam('clean_json') == 'true'){
			$count = 0;
			$jsonFiles = array();
			$jsonFileNames = array();
			$dir = $mediaDirectory->getAbsolutePath().'productdesigner/json/';
			$yearDirs = scandir($dir);
			foreach($yearDirs as $yearDir){
				if($yearDir == '.' || $yearDir == '..' || !is_dir($dir.$yearDir)){
					continue;
				}
				$monthDirs = scandir($dir.$yearDir.'/');
				foreach($monthDirs as $monthDir){
					if($monthDir == '.' || $monthDir == '..' || !is_dir($dir.$yearDir.'/'.$monthDir)){
						continue;
					}
	
					$files = scandir($dir.$yearDir.'/'.$monthDir.'/');
					foreach($files as $fileName){
						if(!is_dir($dir.$yearDir.'/'.$monthDir.'/'.$fileName)){
							$filePath = $mediaDirectory->getAbsolutePath().'productdesigner/json/'.$yearDir.'/'.$monthDir.'/'.$fileName;
							$jsonFiles[] = $filePath;
							$jsonFileNames[] = $yearDir.'/'.$monthDir.'/'.$fileName;
						}
					}
				}
			}
			
	        $savedItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
	        	->addFieldToSelect('connect_id')
	        	->addFieldToSelect('json')
				->addFieldToFilter('json', array('in' => $jsonFileNames));
			$connectIds = array();
			foreach($savedItems as $item){
				$jsonFileName = $item->getJson();
				$connectIds[$jsonFileName] = $item->getConnectId();
			}
	
			foreach($jsonFileNames as $fileName){
				$connectId = $connectIds[$fileName];
				if($connectId != '' && !in_array($connectId, $orderItemConnectIds)){
					$dateCreated = filectime($dir.$fileName);
					if($dateCreated < $startDateTime || $dateCreated > $endDateTime){
						echo "Out of date range ".$dir.$fileName.'<br />';
						continue;
					}
					// Unlink json file
					if(file_exists($dir.$fileName)){
						unlink($dir.$fileName);
						$count++;
					}
				}
			}
			
			$this->messageManager->addSuccess(__('Successfully deleted '.$count.' json files'));
		}
		
		// DELETE BULK PDF FILES
		if($this->getRequest()->getParam('clean_bulk_pdf') == 'true'){
			$count = 0;
			$dir = $mediaDirectory->getAbsolutePath().'productdesigner/order_export/';
			exec('rm -rf '.$dir.'/*');
		}
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/edit');
    }
}