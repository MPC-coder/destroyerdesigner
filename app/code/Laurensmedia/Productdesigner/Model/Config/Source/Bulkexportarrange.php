<?php

namespace Laurensmedia\Productdesigner\Model\Config\Source;

class Bulkexportarrange implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'multiple_on_page' => 'Print multiple products on the same page',
            'new_page' => 'Print every product side on a new page',
            'new_pdf' => 'Print every product side on a new PDF',
            'new_line' => 'Print every order on a new line + add order number in pdf output',
            'new_line_with_summary' => 'Print every order on a new line + add order number in pdf output and add summary',
        ];
    }
}