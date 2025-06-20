<?php

namespace Laurensmedia\Productdesigner\Block\Adminhtml\Templates\Edit\Tab;

/**
 * Templates edit form main tab
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
        $model = $this->_coreRegistry->registry('templates');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

						

        $fieldset->addField(
            'title',
            'text',
            [
                'label' => __('Title'),
                'title' => __('Title'),
                'name' => 'title',
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'product_id',
            'select',
            [
                'label' => __('Product'),
                'title' => __('Product'),
                'name' => 'product_id',
                'options' => \Laurensmedia\Productdesigner\Block\Adminhtml\Templates\Grid::getOptionArray14(),
                'disabled' => $isElementDisabled
            ]
        );
						
        $fieldset->addField(
            'autoload',
            'select',
            [
                'label' => __('Load this template by default?'),
                'title' => __('Load this template by default?'),
                'name' => 'autoload',
                'options' => \Laurensmedia\Productdesigner\Block\Adminhtml\Templates\Grid::getOptionArray13(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'template_preview',
            'image',
            array(
                'name' => 'template_preview',
                'label' => __('Preview image'),
                'title' => __('Preview image'),
                'note' => 'Allow image type: jpg',
           )
        );
						
						

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        } else {
	        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$sideId = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Templatedata\Collection')
				->addFieldToFilter('template_id', $model->getId())
				->getFirstItem()
				->getId();
            $saveLocation = 'productdesigner/templates/'.$sideId.'.jpg';
			$model->setData('template_preview', $saveLocation);
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
