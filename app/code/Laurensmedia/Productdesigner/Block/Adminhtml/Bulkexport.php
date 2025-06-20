<?php

namespace Laurensmedia\Productdesigner\Block\Adminhtml;

class Bulkexport extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'bulkexport/bulkexport.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Widget\Context $context,array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {

		
        $addButtonProps = [
            'id' => 'add_new',
            'label' => __('Add New'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        $removeButtonProps = [
            'id' => 'remove_all',
            'label' => __('Remove all'),
            'class' => 'delete',
            'button_class' => '',
            'onclick' => "setLocation('" . $this->_getRemoveAllUrl() . "')"
        ];
        $this->buttonList->add('remove_all', $removeButtonProps);
		

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Laurensmedia\Productdesigner\Block\Adminhtml\Bulkexport\Grid', 'laurensmedia.bulkexport.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     *
     *
     * @return array
     */
    protected function _getAddButtonOptions()
    {

        $splitButtonOptions[] = [
            'label' => __('Add New'),
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];

        return $splitButtonOptions;
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'productdesigner/*/new'
        );
    }

    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getRemoveAllUrl()
    {
        return $this->getUrl(
            'productdesigner/*/removeall'
        );
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

}