<?php
namespace Laurensmedia\Advancedshipping\Controller\Adminhtml\matrix;

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
        
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Laurensmedia\Advancedshipping\Model\Matrix');

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }
			
      			$data['store_ids']= $data['store_ids'] ? implode(',',$data['store_ids']) : '';
      			$data['customer_group_ids']= $data['customer_group_ids'] ? implode(',',$data['customer_group_ids']) : '';
      			$data['product_ids']= isset($data['product_ids']) ? implode(',',$data['product_ids']) : '';
      			$data['shipping_groups']= $data['shipping_groups'] ? implode(',',$data['shipping_groups']) : '';
      
      			$data['shipping_code'] = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', '_', urldecode(html_entity_decode(strip_tags(str_replace(' ', '_', strtolower($data['shipping_description']))))))));
      
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Matrix has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Matrix.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}