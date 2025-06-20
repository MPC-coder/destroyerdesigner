<?php

namespace Laurensmedia\Productdesigner\Block\Adminhtml\Groups\Edit\Tab;

/**
 * Groups edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        /* @var $model \Laurensmedia\Productdesigner\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('groups');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

		
        $fieldset->addField(
            'code',
            'text',
            [
                'name' => 'code',
                'label' => __('Name'),
                'title' => __('Name'),
				'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'display_header',
            'select',
            [
                'name' => 'display_header',
                'label' => __('Display header in Product Designer?'),
                'title' => __('Display header in Product Designer?'),
				'required' => true,
                'disabled' => $isElementDisabled,
                'options' => array(
	                1 => __("Yes"),
	                0 => __("No"),
                )
            ]
        );
										
        $fieldset->addField(
            'store_ids',
            'multiselect',
            [
                'label' => __('Stores'),
                'title' => __('Stores'),
                'name' => 'store_ids',
				'required' => true,
                'values' => \Laurensmedia\Productdesigner\Block\Adminhtml\Groups\Grid::getValueArray2(),
                'disabled' => $isElementDisabled
            ]
        );
											
        $fieldset->addField(
            'fonts',
            'multiselect',
            [
                'label' => __('Font Families'),
                'title' => __('Font Families'),
                'name' => 'fonts',
				'required' => true,
                'values' => \Laurensmedia\Productdesigner\Block\Adminhtml\Groups\Grid::getValueArray3(),
                'disabled' => $isElementDisabled
            ]
        );
											
        $fieldset->addField(
            'image_categories',
            'multiselect',
            [
                'label' => __('Image categories'),
                'title' => __('Image categories'),
                'name' => 'image_categories',
				'required' => true,
                'values' => \Laurensmedia\Productdesigner\Block\Adminhtml\Groups\Grid::getValueArray4(),
                'disabled' => $isElementDisabled
            ]
        );
											
        $fieldset->addField(
            'colors',
            'multiselect',
            [
                'label' => __('Text colors'),
                'title' => __('Text colors'),
                'name' => 'colors',
				'required' => true,
                'values' => \Laurensmedia\Productdesigner\Block\Adminhtml\Groups\Grid::getValueArray5(),
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
