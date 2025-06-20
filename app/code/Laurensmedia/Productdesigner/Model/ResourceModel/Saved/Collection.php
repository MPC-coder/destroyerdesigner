<?php

namespace Laurensmedia\Productdesigner\Model\ResourceModel\Saved;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Laurensmedia\Productdesigner\Model\Saved', 'Laurensmedia\Productdesigner\Model\ResourceModel\Saved');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>