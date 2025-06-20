<?php

namespace Laurensmedia\Productdesigner\Model\ResourceModel\Sizes;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Laurensmedia\Productdesigner\Model\Sizes', 'Laurensmedia\Productdesigner\Model\ResourceModel\Sizes');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>