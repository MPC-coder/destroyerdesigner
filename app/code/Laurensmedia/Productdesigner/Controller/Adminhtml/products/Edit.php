<?php

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\products;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_storeManager = $storeManager;
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
        $resultPage->setActiveMenu('Laurensmedia_Productdesigner::Products')
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

        $model = $this->_objectManager->create('\Magento\Catalog\Model\Product');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
			$name = $model->getName();
        }

        $model = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')->addFieldToFilter('product_id', $id);
        if($this->getRequest()->getParam('store') > 0){
			$model->addFieldToFilter('store_id', $this->getRequest()->getParam('store'));
		} else {
			$model->addFieldToFilter('store_id', array('null' => true));
		}
        $model = $model->getFirstItem();

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('products', $model);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->setActiveMenu('Laurensmedia_Productdesigner::products');
        $resultPage->addBreadcrumb(__('Laurensmedia'), __('Laurensmedia'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Item') : __('New Item'),
            $id ? __('Edit Item') : __('New Item')
        );
        
        $title = __('New Item');
        if($id > 0){
	        $title = __('Edit Item').' '.$id.' : '.$name;
	        if($this->getRequest()->getParam('store') > 0){
				$title .= ' (currently editing for store '.$this->getStoreData()[$this->getRequest()->getParam('store')].')';
			}
        }
        
        $resultPage->getConfig()->getTitle()->prepend($title);
        //$resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Item'));

        return $resultPage;
    }

	private function getStoreData(){
		$storeManagerDataList = $this->_storeManager->getStores();
		$options = array();
		
		$options[0] = 'Default setup';
		foreach ($storeManagerDataList as $key => $value) {
			$options[$key] = $value['name'];
		}
		return $options;
	}
}