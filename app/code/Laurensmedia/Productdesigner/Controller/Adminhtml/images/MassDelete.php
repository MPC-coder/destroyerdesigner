<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Images;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Laurensmedia\Productdesigner\Model\ImagesFactory;
use Exception;

/**
 * Class MassDelete
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Laurensmedia_Productdesigner::images_delete';

    /**
     * @var ImagesFactory
     */
    protected ImagesFactory $imagesFactory;

    /**
     * @param Context $context
     * @param ImagesFactory $imagesFactory
     */
    public function __construct(
        Context $context,
        ImagesFactory $imagesFactory
    ) {
        $this->imagesFactory = $imagesFactory;
        parent::__construct($context);
    }

    /**
     * Execute mass delete action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $itemIds = $this->getRequest()->getParam('images');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                $itemsDeleted = 0;
                foreach ($itemIds as $itemId) {
                    $model = $this->imagesFactory->create();
                    $model->load($itemId);
                    $model->delete();
                    $itemsDeleted++;
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', $itemsDeleted)
                );
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        
        return $resultRedirect->setPath('productdesigner/*/index');
    }
}