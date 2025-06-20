<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Laurensmedia\Laposte\Controller\Adminhtml\Bulkgenerate;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Intermediate extends \Magento\Backend\App\Action
{

	protected $resultPageFactory;

	/**
	 * Constructor
	 *
	 * @param \Magento\Backend\App\Action\Context  $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		CollectionFactory $collectionFactory
	) {
		$this->collectionFactory = $collectionFactory;
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct($context);
	}

	/**
	 * Execute view action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute()
	{
		$pdf = $this->getRequest()->getParam('pdf');
		$orderIds = explode(',', $this->getRequest()->getParam('orders'));
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$mediaPath = $objectManager->create('\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
		$mediaUrl = $objectManager->create('\Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$directory = $mediaPath.'lapostepdf/';
		$filePath = $directory.$pdf.'.pdf';
		$fileUrl = $mediaUrl.'lapostepdf/'.$pdf.'.pdf';
		
		$page = $this->resultPageFactory->create();
		$block = $page->getLayout()->getBlock('bulkgenerate.intermediate');
		$block->setData('pdf_url', $fileUrl);
		$block->setData('order_ids', $orderIds);
		
		$collection = $this->collectionFactory->create()->addFieldToFilter('entity_id', $orderIds);
		$block->setData('order_collection', $collection);
		
		return $page;
	}
}
