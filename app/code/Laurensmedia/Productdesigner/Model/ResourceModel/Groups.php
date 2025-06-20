<?php
namespace Laurensmedia\Productdesigner\Model\ResourceModel;

class Groups extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('prod_design_groups', 'id');
    }
}
?>