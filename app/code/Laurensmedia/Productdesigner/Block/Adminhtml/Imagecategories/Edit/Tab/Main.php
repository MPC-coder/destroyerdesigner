<?php

namespace Laurensmedia\Productdesigner\Block\Adminhtml\Imagecategories\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Store\Model\System\Store;
use Laurensmedia\Productdesigner\Model\Status;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Backend\Block\Template\Context;

/**
 * Imagecategories edit form main tab
 */
class Main extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var Status
     */
    protected $_status;

    /**
     * Constructor.
     *
     * @param Context       $context
     * @param Registry      $registry
     * @param FormFactory   $formFactory
     * @param Store         $systemStore
     * @param Status        $status
     * @param array         $data
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
        $model = $this->_coreRegistry->registry('imagecategories');

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
                'name' => 'label',
                'label' => __('Category name'),
                'title' => __('Category name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_background',
            'select',
            [
                'label' => __('Use as background pattern'),
                'title' => __('Use as background pattern'),
                'name' => 'is_background',
                'options' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_frame',
            'select',
            [
                'label' => __('Use as frame image'),
                'title' => __('Use as frame image'),
                'name' => 'is_frame',
                'options' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'disabled' => $isElementDisabled
            ]
        );
        
        $fieldset->addField(
            'scale_factor',
            'text',
            [
                'name' => 'scale_factor',
                'label' => __('Scale factor (percentage)'),
                'title' => __('Scale factor (percentage)'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        // Récupérer les images liées à cette catégorie via la collection.
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $images = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Images\Collection')->load();
        $finalImages = [];
        if ($model->getId()) {
            foreach ($images as $image) {
                $categories = explode(',', $image->getCategorie());
                if (in_array($model->getId(), $categories)) {
                    $finalImages[] = $image->getId();
                }
            }
        }
        $model->addData(['images' => $finalImages]);
	    
        $fieldset->addField(
            'images',
            'multiselect',
            [
                'label' => __('Images'),
                'title' => __('Images'),
                'name' => 'images',
                'values' => \Laurensmedia\Productdesigner\Block\Adminhtml\Imagecategories\Grid::getValueArray3(),
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
     * Check permission for a given resource.
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
            '_self' => __('Self'),
            '_blank' => __('New Page')
        ];
    }
}
