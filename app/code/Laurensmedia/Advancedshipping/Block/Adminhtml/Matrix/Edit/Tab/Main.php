<?php

namespace Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Edit\Tab;

/**
 * Matrix edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Laurensmedia\Advancedshipping\Model\Status
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
        \Laurensmedia\Advancedshipping\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Laurensmedia\Advancedshipping\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('matrix');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

						
						
        $fieldset->addField(
            'store_ids',
            'multiselect',
            [
                'label' => __('Stores'),
                'title' => __('Stores'),
                'name' => 'store_ids[]',
                'values' => \Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getValueArray0(),
                'disabled' => $isElementDisabled
            ]
        );
						
										
						
        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            [
                'label' => __('Customer Groups'),
                'title' => __('Customer Groups'),
                'name' => 'customer_group_ids[]',
                'values' => \Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getValueArray1(),
                'disabled' => $isElementDisabled
            ]
        );
						
										
						
        $fieldset->addField(
            'shipping_groups',
            'multiselect',
            [
                'label' => __('Shipping Groups'),
                'title' => __('Shipping Groups'),
                'name' => 'shipping_groups[]',
                'values' => \Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getValueArray2(),
                'disabled' => $isElementDisabled
            ]
        );
						
						
        $fieldset->addField(
            'country',
            'select',
            [
                'name' => 'country',
                'label' => __('Country'),
                'title' => __('Country'),
				
                'options' => \Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getOptionArray3(),
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'city',
            'text',
            [
                'name' => 'city',
                'label' => __('City'),
                'title' => __('City'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'zip_from',
            'text',
            [
                'name' => 'zip_from',
                'label' => __('ZIP from'),
                'title' => __('ZIP from'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'zip_to',
            'text',
            [
                'name' => 'zip_to',
                'label' => __('ZIP to'),
                'title' => __('ZIP to'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'totalitems_from',
            'text',
            [
                'name' => 'totalitems_from',
                'label' => __('Minimal number of items in cart'),
                'title' => __('Minimal number of items in cart'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'totalitems_to',
            'text',
            [
                'name' => 'totalitems_to',
                'label' => __('Maximum number of items in cart'),
                'title' => __('Maximum number of items in cart'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'items_from',
            'text',
            [
                'name' => 'items_from',
                'label' => __('Minimal qty in cart for this product'),
                'title' => __('Minimal qty in cart for this product'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'items_to',
            'text',
            [
                'name' => 'items_to',
                'label' => __('Maximum qty in cart for this product'),
                'title' => __('Maximum qty in cart for this product'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'weight_from',
            'text',
            [
                'name' => 'weight_from',
                'label' => __('Weight from'),
                'title' => __('Weight from'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'weight_to',
            'text',
            [
                'name' => 'weight_to',
                'label' => __('Weight to'),
                'title' => __('Weight to'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'subtotal_from',
            'text',
            [
                'name' => 'subtotal_from',
                'label' => __('Subtotal from'),
                'title' => __('Subtotal from'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'subtotal_to',
            'text',
            [
                'name' => 'subtotal_to',
                'label' => __('Subtotal to'),
                'title' => __('Subtotal to'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'shipping_costs',
            'text',
            [
                'name' => 'shipping_costs',
                'label' => __('Shipping costs'),
                'title' => __('Shipping costs'),
				'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'shipping_description',
            'text',
            [
                'name' => 'shipping_description',
                'label' => __('Shipping method title'),
                'title' => __('Shipping method title'),
				
                'disabled' => $isElementDisabled
            ]
        );
									
						
        $fieldset->addField(
            'stop_processing',
            'select',
            [
                'label' => __('Stop further processing'),
                'title' => __('Stop further processing'),
                'name' => 'stop_processing',
				
                'options' => \Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getOptionArray18(),
                'disabled' => $isElementDisabled
            ]
        );
						
										
						
        $fieldset->addField(
            'stop_other_shippinggroups',
            'select',
            [
                'label' => __('Remove shipping costs for products not in this shipping group(s)'),
                'title' => __('Remove shipping costs for products not in this shipping group(s)'),
                'name' => 'stop_other_shippinggroups',
				
                'options' => \Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getOptionArray19(),
                'disabled' => $isElementDisabled
            ]
        );
						
						

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
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
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
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
