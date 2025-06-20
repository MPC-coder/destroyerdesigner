<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Bulkexport;

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

	/**
	 * Save action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute()
	{
		$data = $this->getRequest()->getParams();
		$postData = $data;
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
		$mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$store = $storeManager->getStore($postData['store']);
		$baseUrl = $storeManager->getStore()->getBaseUrl();
		$mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$items = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Bulkexport\Collection')
			->addFieldToFilter('finished', '1')
			->addFieldToFilter('store_id', $postData['store'])
			->addFieldToFilter('pdf_file_'.$postData['type'], array('neq' => null));

		$types = array('engraving', 'sublimation', 'printing', 'wood');

		$files = array();
		foreach($items as $item){
			$file = $item->getData('pdf_file_'.$postData['type']);
			if($file != ''){
				$files[] = $file;
			}
		}
		
		$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')
			->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
			->getAbsolutePath();
		
		if(count($files) == 1){
			//Download PDF
			$file = $files[0];
			$fileName = basename($file);
			header("Content-type:application/pdf");
			header("Content-Disposition:attachment;filename=".$fileName);
			readfile($mediaDirectory.'productdesigner/order_export/'.$file);
			return;
		}

		if(empty($files)){
			echo 'No files to export';return;
		} else {
			// Create ZIP file
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')
				->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::LIB_INTERNAL);
			$dir = $mediaDirectory->getAbsolutePath('');
			$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')
				->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
				->getAbsolutePath();
			$files = array_unique($files);
			
			require_once($dir.'zip/Zip.php');
			$zipLocation = $mediaDirectory.'productdesigner/tmp/'.date('Y-m-d').'-'.$postData['type'].'-'.$store->getName().'.zip';
			if(file_exists($zipLocation)){
				unlink($zipLocation);
			}
			$zip = new \Zip();
			$zip->zip_start($zipLocation);
			foreach ($files as $file) {
				$file_location = $mediaDirectory.'productdesigner/order_export/'.$file;
				$zip->zip_add($file_location, '/'.basename($zipLocation).'/'.basename($file));
			}
			$zip->zip_end();
			
			// $zipLocation = $mediaDirectory.'productdesigner/tmp/'.date('Y-m-d').'-'.$postData['type'].'-'.$store->getName().'.zip';
			// $zip = new \ZipArchive();
			// $zip->open($zipLocation, \ZipArchive::CREATE);
			// foreach ($files as $file) {
			// 	$file_location = $mediaDirectory.'productdesigner/order_export/'.$file;
			// 	$zip->addFile($file_location, '/'.basename($zipLocation).'/'.basename($file));
			// }
			// $zip->close();
			
			header('Content-Type: application/zip');
			header('Content-disposition: attachment; filename='.basename($zipLocation));
			header('Content-Length: ' . filesize($zipLocation));
			readfile($zipLocation);
	
			unlink($zipLocation);
			return;
		}
	}
}