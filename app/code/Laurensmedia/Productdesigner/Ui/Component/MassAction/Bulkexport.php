<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Ui\Component\MassAction;

use Magento\Framework\Option\ArrayInterface;
use JsonSerializable;

class Bulkexport extends OptionsAbstract implements JsonSerializable
{
    /**
     * Get options
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        if (empty($this->options)) {
            $this->getMatchingOptions();
            $this->options = array_values($this->options);
        }

        return $this->options;
    }
}

