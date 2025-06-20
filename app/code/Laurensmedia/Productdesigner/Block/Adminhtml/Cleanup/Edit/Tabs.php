<?php
namespace Laurensmedia\Productdesigner\Block\Adminhtml\Cleanup\Edit;

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
        $this->setId('cleanup_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Cleanup Information'));
    }
}