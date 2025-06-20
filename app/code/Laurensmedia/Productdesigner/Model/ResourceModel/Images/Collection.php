<?php

namespace Laurensmedia\Productdesigner\Model\ResourceModel\Images;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Laurensmedia\Productdesigner\Model\Images', 'Laurensmedia\Productdesigner\Model\ResourceModel\Images');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>