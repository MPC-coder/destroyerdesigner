<?php
namespace Laurensmedia\Productdesigner\Model\ResourceModel;

class Imagecategories extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('druk_img_category', 'id');
    }
}
?>