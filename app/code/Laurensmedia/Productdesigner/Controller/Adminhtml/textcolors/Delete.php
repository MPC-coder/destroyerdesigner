<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Textcolors;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Laurensmedia\Productdesigner\Model\TextcolorsFactory;
use Exception;

class Delete extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Laurensmedia_Productdesigner::textcolors_delete';

    /**
     * @var TextcolorsFactory
     */
    protected TextcolorsFactory $textcolorsFactory;

    /**
     * @param Context $context
     * @param TextcolorsFactory $textcolorsFactory
     */
    public function __construct(
        Context $context,
        TextcolorsFactory $textcolorsFactory
    ) {
        $this->textcolorsFactory = $textcolorsFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $id = $this->getRequest()->getParam('id');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
                $model = $this->textcolorsFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('The item has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find an item to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}