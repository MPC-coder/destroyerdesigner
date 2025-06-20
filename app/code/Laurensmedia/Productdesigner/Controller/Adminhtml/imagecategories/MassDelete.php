<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Imagecategories;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Laurensmedia\Productdesigner\Model\ImagecategoriesFactory;
use Exception;

/**
 * Class MassDelete
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Laurensmedia_Productdesigner::imagecategories_delete';

    /**
     * @var ImagecategoriesFactory
     */
    protected ImagecategoriesFactory $imagecategoriesFactory;

    /**
     * @param Context $context
     * @param ImagecategoriesFactory $imagecategoriesFactory
     */
    public function __construct(
        Context $context,
        ImagecategoriesFactory $imagecategoriesFactory
    ) {
        $this->imagecategoriesFactory = $imagecategoriesFactory;
        parent::__construct($context);
    }

    /**
     * Execute mass delete action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $itemIds = $this->getRequest()->getParam('imagecategories');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                $itemsDeleted = 0;
                foreach ($itemIds as $itemId) {
                    $model = $this->imagecategoriesFactory->create();
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