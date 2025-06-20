<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Laurensmedia\Laposte\Controller\Adminhtml\Codes;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('codes_id');
            $saveItems = array();
            if($id > 0){
                if(isset($data['end_code'])){
                    unset($data['end_code']);
                }
                $saveItems[] = $data;
            } else {
                $count = 0;
                for($i=$data['code'];$i<=$data['end_code'];$i++){
                    $tmpData = $data;
                    unset($tmpData['end_code']);
                    $tmpData['code'] = $i;
                    $saveItems[] = $tmpData;
                    $count++;
                    if($count > 100){
                        break;
                    }
                }
            }
        
            try {
                foreach($saveItems as $saveItem){
                    $model = $this->_objectManager->create(\Laurensmedia\Laposte\Model\Codes::class)->load($id);
                    if (!$model->getId() && $id) {
                        $this->messageManager->addErrorMessage(__('This Codes no longer exists.'));
                        return $resultRedirect->setPath('*/*/');
                    }
                    $model->setData($saveItem);
                    $model->save();
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Codes.'));
            }
            
            $this->messageManager->addSuccessMessage(__('You saved the Codes.'));
            $this->dataPersistor->clear('laurensmedia_laposte_codes');
            
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['codes_id' => $model->getId()]);
            }
            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }
}

