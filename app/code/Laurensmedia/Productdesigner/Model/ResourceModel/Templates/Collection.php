<?php

namespace Laurensmedia\Productdesigner\Model\ResourceModel\Templates;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Laurensmedia\Productdesigner\Model\Templates', 'Laurensmedia\Productdesigner\Model\ResourceModel\Templates');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>