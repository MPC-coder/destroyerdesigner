<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\products;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Laurensmedia_Productdesigner::products';

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Laurensmedia_Productdesigner::products');
        $resultPage->addBreadcrumb(__('Laurensmedia'), __('Laurensmedia'));
        $resultPage->addBreadcrumb(__('Manage item'), __('Manage Products'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Products'));

        return $resultPage;
    }
}