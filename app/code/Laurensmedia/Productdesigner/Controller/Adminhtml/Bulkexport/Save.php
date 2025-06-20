<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Bulkexport;

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
        $data = $this->getRequest()->getPostValue();
        $postData = $data;
        
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport');

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }
	            
			$orderIds = $postData['order_ids'];
			$productIds = $postData['product_ids'];
			if($postData['start_date'] != '' && $postData['end_date'] != ''){
				$startDateTime = strtotime(str_replace('/', '-', $postData['start_date']));
				$endDateTime = strtotime(str_replace('/', '-', $postData['end_date']));
				$startDate = date('Y-m-d H:i:s', $startDateTime);
				$endDate = date('Y-m-d H:i:s', $endDateTime);
				//$startDate = DateTime::createFromFormat('d/m/Y', $postData['start_date'])->format('Y-m-d H:i:s');
				//$endDate = DateTime::createFromFormat('d/m/Y', $postData['end_date'])->format('Y-m-d H:i:s');
			}
			
			// Fetch order items to be printed
			$printOrderItems = array();
			
			// Get orders in date range
			if(!in_array('processing', $orderIds)){
				$orders = $this->_objectManager->create('Magento\Sales\Model\ResourceModel\Order\Collection');
			} else {
				$orders = $this->_objectManager->create('Magento\Sales\Model\ResourceModel\Order\Collection')
					->addFieldToFilter('status', 'processing');
			}
			if(!in_array('all', $orderIds) && !in_array('processing', $orderIds)){
				$orders = $orders->addFieldToFilter('entity_id', $orderIds);
			}
			if($postData['start_date'] != '' && $postData['end_date'] != ''){
				$orders = $orders->addAttributeToFilter('created_at', array('from'=>$startDate, 'to'=>$endDate));
			}
			foreach($orders as $order){
				$orderItems = $order->getAllItems();
				foreach($orderItems as $orderItem){
					$orderItemProductId = $orderItem->getProductId();
					if(in_array($orderItemProductId, $productIds) || in_array('all', $productIds)){
						$printOrderItems[] = array(
							'order_id' => $order->getId(),
							'product_id' => $orderItemProductId,
							'order_item_id' => $orderItem->getId(),
							'export_combining' => $postData['export_combining'],
							'empty_design' => $postData['empty_design'],
							'pdf_width' => $postData['pdf_width'],
							'pdf_height' => $postData['pdf_height'],
							'pdf_margin_vertical' => $postData['pdf_margin_vertical'],
							'pdf_margin_horizontal' => $postData['pdf_margin_horizontal'],
							'pdf_margin_items_vertical' => $postData['pdf_margin_items_vertical'],
							'pdf_margin_items_horizontal' => $postData['pdf_margin_items_horizontal']
						);
					}
				}
			}
			//foreach(Mage::getModel('shirt/exportqueue')->getCollection() as $item){
				//$item->delete()->save();
			//}
			foreach($printOrderItems as $item){
	            $model = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport');
				$model->setData($item);
	            try {
	                $model->save();
	            } catch (\Magento\Framework\Exception\LocalizedException $e) {
	                $this->messageManager->addError($e->getMessage());
	            } catch (\RuntimeException $e) {
	                $this->messageManager->addError($e->getMessage());
	            } catch (\Exception $e) {
	                $this->messageManager->addException($e, __('Something went wrong while saving the Bulkexport.'));
	            }
			}

            $this->messageManager->addSuccess(__('The Bulkexport has been saved.'));
            $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }
}