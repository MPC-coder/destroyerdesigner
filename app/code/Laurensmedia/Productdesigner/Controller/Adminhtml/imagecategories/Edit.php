<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\imagecategories;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Laurensmedia\Productdesigner\Model\ImagecategoriesFactory;
use Magento\Backend\Model\Session;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected Registry $_coreRegistry;

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var ImagecategoriesFactory
     */
    protected ImagecategoriesFactory $imageCategoriesFactory;

    /**
     * @var Session
     */
    protected Session $backendSession;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param ImagecategoriesFactory $imagecategoriesFactory
     * @param Session $backendSession
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        ImagecategoriesFactory $imagecategoriesFactory = null,
        Session $backendSession = null
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->imageCategoriesFactory = $imagecategoriesFactory ?: 
            $context->getObjectManager()->create(ImagecategoriesFactory::class);
        $this->backendSession = $backendSession ?: 
            $context->getObjectManager()->get(Session::class);
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Laurensmedia_Productdesigner::Imagecategories')
            ->addBreadcrumb(__('Laurensmedia Productdesigner'), __('Laurensmedia Productdesigner'))
            ->addBreadcrumb(__('Manage Item'), __('Manage Item'));
        return $resultPage;
    }

    /**
     * Edit Item
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->imageCategoriesFactory->create();

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
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
        $this->_coreRegistry->register('imagecategories', $model);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->setActiveMenu('Laurensmedia_Productdesigner::imagecategories');
        $resultPage->addBreadcrumb(__('Laurensmedia'), __('Laurensmedia'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Item') : __('New Item'),
            $id ? __('Edit Item') : __('New Item')
        );
        $resultPage->getConfig()->getTitle()->prepend($id ? __('Edit Item').' '.$model->getId().' : '.$model->getLabel() : __('New Item'));
        //$resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Item'));
        return $resultPage;
    }
}