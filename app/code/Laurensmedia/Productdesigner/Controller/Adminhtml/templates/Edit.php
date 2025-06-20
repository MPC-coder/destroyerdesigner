<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Templates;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Laurensmedia\Productdesigner\Model\TemplatesFactory;

class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Laurensmedia_Productdesigner::templates_edit';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected Registry $coreRegistry;

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var TemplatesFactory
     */
    protected TemplatesFactory $templatesFactory;

    /**
     * @var Session
     */
    protected Session $backendSession;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param TemplatesFactory $templatesFactory
     * @param Session $backendSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        TemplatesFactory $templatesFactory,
        Session $backendSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $registry;
        $this->templatesFactory = $templatesFactory;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return Page
     */
    protected function initAction(): Page
    {
        // load layout, set active menu and breadcrumbs
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Laurensmedia_Productdesigner::templates')
            ->addBreadcrumb(__('Laurensmedia Productdesigner'), __('Laurensmedia Productdesigner'))
            ->addBreadcrumb(__('Manage Template'), __('Manage Template'));
        return $resultPage;
    }

    /**
     * Edit Template
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->templatesFactory->create();

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This template no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->coreRegistry->register('templates', $model);

        // 5. Build edit form
        /** @var Page $resultPage */
        $resultPage = $this->initAction();
        $resultPage->setActiveMenu('Laurensmedia_Productdesigner::templates');
        $resultPage->addBreadcrumb(
            $id ? __('Edit Template') : __('New Template'),
            $id ? __('Edit Template') : __('New Template')
        );
        $resultPage->getConfig()->getTitle()->prepend($id ? __('Edit Template') : __('New Template'));

        return $resultPage;
    }
}