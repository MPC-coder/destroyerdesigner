<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Laurensmedia\Laposte\Api\Data;

interface CodesInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const CODE = 'code';
    const ORDER_ID = 'order_id';
    const CODES_ID = 'codes_id';
    const ID = 'id';

    /**
     * Get codes_id
     * @return string|null
     */
    public function getCodesId();

    /**
     * Set codes_id
     * @param string $codesId
     * @return \Laurensmedia\Laposte\Api\Data\CodesInterface
     */
    public function setCodesId($codesId);

    /**
     * Get code
     * @return string|null
     */
    public function getCode();

    /**
     * Set code
     * @param string $code
     * @return \Laurensmedia\Laposte\Api\Data\CodesInterface
     */
    public function setCode($code);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Laurensmedia\Laposte\Api\Data\CodesExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Laurensmedia\Laposte\Api\Data\CodesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Laurensmedia\Laposte\Api\Data\CodesExtensionInterface $extensionAttributes
    );

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \Laurensmedia\Laposte\Api\Data\CodesInterface
     */
    public function setOrderId($orderId);

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Laurensmedia\Laposte\Api\Data\CodesInterface
     */
    public function setId($id);
}

