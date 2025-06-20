<?php

namespace Laurensmedia\Advancedshipping\Model\ResourceModel\Matrix;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Laurensmedia\Advancedshipping\Model\Matrix', 'Laurensmedia\Advancedshipping\Model\ResourceModel\Matrix');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>