<?php

namespace Laurensmedia\Productdesigner\Block\Adminhtml\Products\Edit\Tab;

/**
 * Products edit form sizes tab
 */
class Stores extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Laurensmedia\Productdesigner\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Laurensmedia\Productdesigner\Model\Status $status,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }

	private function getStoreData(){
		$storeManagerDataList = $this->_storeManager->getStores();
		$options = array();
		
		$options[] = ['label' => 'Default setup', 'value' => 0];
		foreach ($storeManagerDataList as $key => $value) {
			$options[] = ['label' => $value['name'].' - '.$value['code'], 'value' => $key];
		}
		return $options;
	}

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Laurensmedia\Productdesigner\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('products');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Store switch')]);

		$stores = $this->getStoreData();
						
        $fieldset->addField(
            'store_switch',
            'select',
            [
                'label' => __('Store switch'),
                'title' => __('Store switch'),
                'name' => 'store_switch',
                'values' => $stores,
                'disabled' => $isElementDisabled,
                'onchange' => "if(confirm('Are you sure you want to switch stores? Please note that changes will be lost.')){ document.location = '".$this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true])."store/'+jQuery(this).val(); }"
            ]
        );		

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }
        if($this->getRequest()->getParam('store') > 0){
			$model->setData('store_switch', $this->getRequest()->getParam('store'));
		}

        $form->setValues($model->getData());
        $this->setForm($form);
		
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Store switch');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Store switch');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
}
