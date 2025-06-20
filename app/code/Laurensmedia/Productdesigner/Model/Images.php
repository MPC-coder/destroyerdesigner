<?php
namespace Laurensmedia\Productdesigner\Model;

class Images extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Laurensmedia\Productdesigner\Model\ResourceModel\Images');
    }
}
?>