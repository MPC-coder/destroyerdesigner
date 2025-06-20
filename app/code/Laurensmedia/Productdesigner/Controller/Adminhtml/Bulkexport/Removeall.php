<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Bulkexport;

class Removeall extends \Magento\Backend\App\Action
{
    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            // init model and delete
            $model = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport');
			$connection = $model->getCollection()->getConnection();
			$tableName = $model->getCollection()->getMainTable();
			$connection->truncateTable($tableName);
            // display success message
            $this->messageManager->addSuccess(__('The items have been deleted.'));
            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            // display error message
            $this->messageManager->addError($e->getMessage());
            // go back to edit form
            return $resultRedirect->setPath('*/*/');
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a item to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}