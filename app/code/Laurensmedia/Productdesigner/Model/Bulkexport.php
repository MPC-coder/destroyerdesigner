<?php

namespace Laurensmedia\Productdesigner\Model;

use Magento\Framework\Model\AbstractModel;

class Bulkexport extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Laurensmedia\Productdesigner\Model\ResourceModel\Bulkexport');
    }
}
