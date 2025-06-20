<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Textcolors;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Laurensmedia\Productdesigner\Model\TextcolorsFactory;
use Exception;

class MassDelete extends Action implements HttpPostActionInterface
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
     * Delete textcolors action
     *
     * @return ResultInterface
     * @throws Exception
     */
    public function execute(): ResultInterface
    {
        $itemIds = $this->getRequest()->getParam('textcolors');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $model = $this->textcolorsFactory->create();
                    $model->load($itemId);
                    $model->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($itemIds))
                );
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect->setPath('productdesigner/*/index');
    }
}