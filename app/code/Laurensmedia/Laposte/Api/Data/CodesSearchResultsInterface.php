<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Laurensmedia\Laposte\Api\Data;

interface CodesSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Codes list.
     * @return \Laurensmedia\Laposte\Api\Data\CodesInterface[]
     */
    public function getItems();

    /**
     * Set code list.
     * @param \Laurensmedia\Laposte\Api\Data\CodesInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

