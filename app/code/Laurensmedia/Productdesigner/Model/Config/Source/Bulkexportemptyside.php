<?php

namespace Laurensmedia\Productdesigner\Model\Config\Source;

class Bulkexportemptyside implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'do_not_print' => 'Leave out of export',
            'print_other_side' => 'Print a product side which is designed in stead'
        ];
    }
}