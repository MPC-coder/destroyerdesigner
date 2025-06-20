<?php

namespace Laurensmedia\Productdesigner\Model\ResourceModel\Printingquality;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Laurensmedia\Productdesigner\Model\Printingquality', 'Laurensmedia\Productdesigner\Model\ResourceModel\Printingquality');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>