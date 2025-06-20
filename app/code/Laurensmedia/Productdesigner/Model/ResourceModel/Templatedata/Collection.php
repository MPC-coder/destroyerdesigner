<?php

namespace Laurensmedia\Productdesigner\Model\ResourceModel\Templatedata;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Laurensmedia\Productdesigner\Model\Templatedata', 'Laurensmedia\Productdesigner\Model\ResourceModel\Templatedata');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>