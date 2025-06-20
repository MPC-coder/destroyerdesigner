<?php

namespace Laurensmedia\Productdesigner\Block\Adminhtml\Bulkexport\Edit\Tab;

/**
 * Bulkexport edit form main tab
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
        $model = $this->_coreRegistry->registry('bulkexport');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		// Choose orders to export
		$orderIds = array(
			array('label' => 'All orders', 'value' => 'all'),
			array('label' => 'Only processing orders', 'value' => 'processing')
		);

		$orders = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\Collection')
			->addAttributeToSelect('increment_id')
			->addAttributeToSelect('entity_id');
		
		foreach($orders->getData() as $orderItem){
			$orderIds[] = array('label' => $orderItem['increment_id'], 'value' => $orderItem['entity_id']);
		}

		$fieldset->addField('order_ids', 'multiselect',
			array(
				'label' => __('Order IDs'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'order_ids',
				'values' => $orderIds
			)
		);
		
		
		
		// Choose products to export
		$productIds = array(array('label' => 'All products', 'value' => 'all'));
		$products = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
			->addAttributeToSelect(array('bedrukbaar', 'product_id', 'name'))
			->addAttributeToFilter('bedrukbaar', '1')
			->load();
		foreach($products as $product){
			$productIds[] = array(
				'label' => $product->getName().' ('.$product->getId().')',
				'value' => $product->getId()
			);
		}
		$fieldset->addField('product_ids', 'multiselect',
			array(
				'label' => __('Product IDs'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'product_ids',
				'values' => $productIds
			)
		);
		// Export multiple products on one page, every product on a new page or every product in a new PDF?
		$exportCombiningOptions = array(
			array('label' => 'Print multiple products on the same page', 'value' => 'multiple_on_page'),
			array('label' => 'Print every product side on a new page', 'value' => 'new_page'),
			array('label' => 'Print every product side on a new PDF', 'value' => 'new_pdf'),
			array('label' => 'Print every order on a new line + add order number in pdf output', 'value' => 'new_line'),
			array('label' => 'Print every order on a new line + add order number in pdf output and add summary', 'value' => 'new_line_with_summary')
		);
		$fieldset->addField('export_combining', 'select',
			array(
				'label' => __('How to arrange the PDF?'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'export_combining',
				'values' => $exportCombiningOptions
			)
		);
		
		// If design is empty, print previous design in stead?
		$emptyDesignOptions = array(
			array('label' => 'Leave out of export', 'value' => 'do_not_print'),
			array('label' => 'Print a product side which is designed in stead', 'value' => 'print_other_side')
		);
		$fieldset->addField('empty_design', 'select',
			array(
				'label' => __('What if a product side is not designed?'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'empty_design',
				'values' => $emptyDesignOptions
			)
		);
		
		// Set PDF size
		$fieldset->addField('pdf_width', 'text',
			array(
				'label' => __('PDF width in mm'),
				'class' => 'entry',
				'required' => false,
				'name' => 'pdf_width',
				'after_element_html' => '<small>Leave empty to adjust PDF dimensions to product output dimensions. Every product side will then be printed on a new page or PDF, depending on your PDF arrange setting.</small>'
			)
		);
		$fieldset->addField('pdf_height', 'text',
			array(
				'label' => __('PDF height in mm'),
				'class' => 'entry',
				'required' => false,
				'name' => 'pdf_height',
				'after_element_html' => '<small>Leave empty to adjust PDF dimensions to product output dimensions. Every product side will then be printed on a new page or PDF, depending on your PDF arrange setting.</small>'
			)
		);
		
		// Set PDF margin
		$fieldset->addField('pdf_margin_vertical', 'text',
			array(
				'label' => __('PDF margin (top and bottom)'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'pdf_margin_vertical',
				'after_element_html' => '<small>This is the border margin in mm of the PDF. The design will be printed between these borders.</small>',
				'value' => '0'
			)
		);
		$fieldset->addField('pdf_margin_horizontal', 'text',
			array(
				'label' => __('PDF margin (left and right)'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'pdf_margin_horizontal',
				'after_element_html' => '<small>This is the border margin in mm of the PDF. The design will be printed between these borders.</small>',
				'value' => '0'
			)
		);
		
		// Set product margin on PDF (margin between items)
		$fieldset->addField('pdf_margin_items_vertical', 'text',
			array(
				'label' => __('PDF margin between items (top and bottom)'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'pdf_margin_items_vertical',
				'after_element_html' => '<small>This is the vertical margin in mm between items one page. Only applicable if PDF arrangement is set to "Print multiple products on the same page".</small>',
				'value' => '0'
			)
		);
		$fieldset->addField('pdf_margin_items_horizontal', 'text',
			array(
				'label' => __('PDF margin between items (left and right)'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'pdf_margin_items_horizontal',
				'after_element_html' => '<small>This is the horizontal margin in mm between items one page. Only applicable if PDF arrangement is set to "Print multiple products on the same page".</small>',
				'value' => '0'
			)
		);
		
		// Define date range
		$dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
		$timeFormat = $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT);
		$fieldset->addField('start_date', 'date', 
			array(
				'label' => __('Start date'),
				'name' => 'start_date',
				'after_element_html' => '<small><br />Define a start date. Only orders placed on and after this date will be exported.</small>',
				'format' => $dateFormat
			)
        );
		$fieldset->addField('end_date', 'date', 
			array(
				'label' => __('End date'),
				'name' => 'end_date',
				'after_element_html' => '<small><br />Define a end date. Only orders placed on and after this date will be exported.</small>',
				'format' => $dateFormat
			)
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
