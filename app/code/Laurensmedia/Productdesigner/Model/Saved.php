<?php
namespace Laurensmedia\Productdesigner\Model;

class Saved extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Laurensmedia\Productdesigner\Model\ResourceModel\Saved');
    }
}
?>