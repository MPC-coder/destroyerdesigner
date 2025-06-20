<?php

namespace Laurensmedia\Productdesigner\Block\Adminhtml\Images\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Store\Model\System\Store;
use Laurensmedia\Productdesigner\Model\Status;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Block\Template\Context;

/**
 * Images edit form main tab
 */
class Main extends Generic implements TabInterface
{
    /**
     * @var System\Store
     */
    protected $_systemStore;

    /**
     * @var Status
     */
    protected $_status;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param System\Store $systemStore
     * @param Status $status
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Laurensmedia\Productdesigner\Model\BlogPosts $model */
        $model = $this->_coreRegistry->registry('images');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'label',
            'text',
            [
                'name'     => 'label',
                'label'    => __('Label'),
                'title'    => __('Label'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'image',
            'image',
            [
                'name'     => 'image',
                'label'    => __('Image (jpg,png,gif,svg)'),
                'title'    => __('Image (jpg,png,gif,svg)'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'scale_factor',
            'text',
            [
                'name'     => 'scale_factor',
                'label'    => __('Scale factor (percentage)'),
                'title'    => __('Scale factor (percentage)'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        // Conversion de la donnée CSV en tableau, en s'assurant qu'elle est une chaîne et en filtrant les valeurs vides.
        $categoriesCsv = (string)$model->getData('categorie');
        $categoriesArray = array_filter(explode(',', $categoriesCsv));
        $model->addData(['categories' => $categoriesArray]);

        $fieldset->addField(
            'categories',
            'multiselect',
            [
                'label'    => __('Categories'),
                'title'    => __('Categories'),
                'name'     => 'categories',
                'values'   => \Laurensmedia\Productdesigner\Block\Adminhtml\Images\Grid::getValueArray3(),
                'disabled' => $isElementDisabled
            ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }
        $model->setData('image', 'productdesigner_images/' . $model->getData('url'));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * Determine if tab can be shown.
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Determine if tab is hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action.
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    /**
     * Retrieve target option array.
     *
     * @return array
     */
    public function getTargetOptionArray()
    {
        return [
            '_self'  => __('Self'),
            '_blank' => __('New Page'),
        ];
    }
}
