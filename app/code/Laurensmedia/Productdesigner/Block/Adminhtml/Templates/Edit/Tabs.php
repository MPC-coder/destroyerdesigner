<?php
namespace Laurensmedia\Productdesigner\Block\Adminhtml\Templates\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('templates_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Templates Information'));
    }
}