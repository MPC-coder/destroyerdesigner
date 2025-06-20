<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Laurensmedia\Laposte\Model\Data;

use Laurensmedia\Laposte\Api\Data\CodesInterface;

class Codes extends \Magento\Framework\Api\AbstractExtensibleObject implements CodesInterface
{

    /**
     * Get codes_id
     * @return string|null
     */
    public function getCodesId()
    {
        return $this->_get(self::CODES_ID);
    }

    /**
     * Set codes_id
     * @param string $codesId
     * @return \Laurensmedia\Laposte\Api\Data\CodesInterface
     */
    public function setCodesId($codesId)
    {
        return $this->setData(self::CODES_ID, $codesId);
    }

    /**
     * Get code
     * @return string|null
     */
    public function getCode()
    {
        return $this->_get(self::CODE);
    }

    /**
     * Set code
     * @param string $code
     * @return \Laurensmedia\Laposte\Api\Data\CodesInterface
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Laurensmedia\Laposte\Api\Data\CodesExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Laurensmedia\Laposte\Api\Data\CodesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Laurensmedia\Laposte\Api\Data\CodesExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Set order_id
     * @param string $orderId
     * @return \Laurensmedia\Laposte\Api\Data\CodesInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get id
     * @return string|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set id
     * @param string $id
     * @return \Laurensmedia\Laposte\Api\Data\CodesInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }
}

