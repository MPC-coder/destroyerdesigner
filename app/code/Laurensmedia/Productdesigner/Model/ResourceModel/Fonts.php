<?php
namespace Laurensmedia\Productdesigner\Model\ResourceModel;

class Fonts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('druk_fonts_library', 'id_fonts');
    }
}
?>