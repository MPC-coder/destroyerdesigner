<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Templates;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Laurensmedia\Productdesigner\Model\TemplatesFactory;
use Exception;

class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Laurensmedia_Productdesigner::templates_delete';

    /**
     * @var TemplatesFactory
     */
    protected TemplatesFactory $templatesFactory;

    /**
     * @param Context $context
     * @param TemplatesFactory $templatesFactory
     */
    public function __construct(
        Context $context,
        TemplatesFactory $templatesFactory
    ) {
        $this->templatesFactory = $templatesFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($id) {
            try {
                // init model and delete
                $model = $this->templatesFactory->create();
                $model->load($id);
                $model->delete();
                
                // display success message
                $this->messageManager->addSuccessMessage(__('The template has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a template to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}