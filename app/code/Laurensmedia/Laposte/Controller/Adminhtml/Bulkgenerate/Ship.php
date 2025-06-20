<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Laurensmedia\Laposte\Controller\Adminhtml\Bulkgenerate;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Ship extends \Magento\Backend\App\Action
{

	protected $resultPageFactory;
	
	protected $codesCollectionFactory;
	
	protected $trackFactory;
	
	protected $shipOrderService;

	/**
	 * Constructor
	 *
	 * @param \Magento\Backend\App\Action\Context  $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		CollectionFactory $collectionFactory,
		\Laurensmedia\Laposte\Model\ResourceModel\Codes\CollectionFactory $codesCollectionFactory,
		\Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
		\Magento\Sales\Api\ShipOrderInterface $shipOrderService
	) {
		$this->collectionFactory = $collectionFactory;
		$this->resultPageFactory = $resultPageFactory;
		$this->codesCollectionFactory = $codesCollectionFactory;
		$this->trackFactory = $trackFactory;
		$this->shipOrderService = $shipOrderService;
		parent::__construct($context);
	}

	/**
	 * Execute view action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute()
	{
		$orderIds = $this->getRequest()->getParam('order_ids');
		$orderCollection = $this->collectionFactory->create()->addFieldToFilter('entity_id', $orderIds);
		
		foreach($orderCollection as $order){
			try{
				$this->shipOrder($order);
				$this->messageManager->addSuccess(__('Shipped order %1', $order->getIncrementId()));
			} catch(\Exception $e){
				var_dump($e->getMessage());exit;
				$this->messageManager->addError(__('Could not ship order %1', $order->getIncrementId()));
			}
		}
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resultRedirect = $objectManager->get('Magento\Framework\Controller\Result\RedirectFactory')->create();
		$resultRedirect->setPath('sales/order/index');
		return $resultRedirect;
	}
	
	private function shipOrder($order){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$laPosteCodeObject = $this->codesCollectionFactory->create()->addFieldToFilter('order_id', $order->getId())->getFirstItem();
		if(!$laPosteCodeObject->getId() > 0){
			throw new \Magento\Framework\Exception\LocalizedException(
				__("The order does not have a la poste code attached.")
			);
			return false;
		}
		
		try {
			$convertOrder = $objectManager->create('Magento\Sales\Model\Convert\Order');
			
			$data = array(
				'carrier_code' => 'custom',
				'title' => 'La Poste',
				'number' => 'https://www.laposte.fr/outils/suivre-vos-envois?code='.$laPosteCodeObject->getCode()
			);
			
			$track = $this->trackFactory->create()->addData($data);
			
			// Loop through order items
			$shipmentItems = array();
			foreach ($order->getAllItems() as $orderItem) {
				// Check if order item has qty to ship or is virtual
				$qtyToShip = $orderItem->getQtyOrdered() - $orderItem->getQtyShipped();
				if($qtyToShip < 0){
					$qtyToShip = 0;
				}
				if (! $qtyToShip || $orderItem->getIsVirtual()) {
					continue;
				}
				$qtyShipped = $qtyToShip;
			
				// Create shipment item with qty
				$shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
				$shipmentItems[] = $shipmentItem;
			}
			
			$shipmentId = $this->shipOrderService->execute(
				$order->getId(),
				array(),
				true,
				false,
				null,
				array($track)
			);
		} catch (\Exception $e) {
			throw new \Magento\Framework\Exception\LocalizedException(
				__($e->getMessage())
			);
		}
	}
}
