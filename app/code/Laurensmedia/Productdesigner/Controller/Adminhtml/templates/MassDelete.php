<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\templates;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Laurensmedia\Productdesigner\Model\TemplatesFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassDelete
 */
class MassDelete extends Action
{
    /**
     * @var TemplatesFactory
     */
    protected $templatesFactory;

    /**
     * @param Context $context
     * @param TemplatesFactory $templatesFactory
     */
    public function __construct(
        Context $context,
        TemplatesFactory $templatesFactory
    ) {
        parent::__construct($context);
        $this->templatesFactory = $templatesFactory;
    }

    /**
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $itemIds = $this->getRequest()->getParam('templates');
        if (!is_array($itemIds) || empty($itemIds)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                foreach ($itemIds as $itemId) {
                    $template = $this->templatesFactory->create()->load($itemId);
                    $template->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($itemIds))
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while deleting the items.'));
            }
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('productdesigner/*/index');
    }
}