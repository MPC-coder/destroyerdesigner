<?php
namespace Laurensmedia\Productdesigner\Block\Adminhtml\Bulkexport\Edit;

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
        $this->setId('bulkexport_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Bulkexport Information'));
    }
}