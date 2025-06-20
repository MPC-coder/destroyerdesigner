<?php

namespace Laurensmedia\Productdesigner\Block\Adminhtml\Cleanup\Edit\Tab;

/**
 * Imagecategories edit form main tab
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

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

		$dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
		$timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT);
		$fieldset->addField('start_date', 'date', 
			array(
				'label' => __('Start date'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'start_date',
				'after_element_html' => '<small><br />Define a start date. Only images placed on and after this date will be deleted.</small>',
				'format' => $dateFormat,
			)
        );
		
		$fieldset->addField('end_date', 'date', 
			array(
				'label' => __('End date'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'end_date',
				'after_element_html' => '<small><br />Define a start date. Only images placed on and before this date will be deleted.</small>',
				'format' => $dateFormat,
			)
        );
        
/*
		$fieldset->addField('quote_items', 'checkbox', 
			array(
				'label' => __('Clean up quote_items tables'),
				'class' => 'entry',
				'required' => false,
				'name' => 'quote_items',
				'value' => 'true'
			)
        );
*/
        
		$fieldset->addField('prod_design_saved', 'checkbox', 
			array(
				'label' => __('Clean up prod_design_saved table'),
				'class' => 'entry',
				'required' => false,
				'name' => 'prod_design_saved',
				'value' => 'true'
			)
        );

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
        
		$fieldset->addField('prod_design_saved_number', 'text', 
			array(
				'label' => __('Clean up prod_design_saved table till this number'),
				'class' => 'entry',
				'required' => false,
				'name' => 'prod_design_saved_number',
				'value' => $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')->setOrder('save_id', 'desc')->setPageSize(1)->getLastItem()->getSaveId()
			)
        );
        
		$fieldset->addField('note', 'note', 
			array(
				'label' => __('Note'),
				'text' => '<b>Only images not attached to orders will be deleted. Please backup your files before continuing. This action can not be undone.</b>'
			)
        );											

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
