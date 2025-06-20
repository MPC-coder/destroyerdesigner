<?php

namespace Laurensmedia\Advancedshipping\Model\Carrier;
 
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
 
class Advancedshipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'advancedshipping';
 
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }
 
    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['advancedshipping' => $this->getConfigData('name')];
    }
 
    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
 
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        
		$storeId = $request->getStoreId();
        $customer = $objectManager->get('\Magento\Customer\Model\Session')->getCustomer();
		$customerGroupId = $customer->getGroupId();
		
		$backendQuote = $objectManager->get('\Magento\Backend\Model\Session\Quote')->getQuote();

		$address = $objectManager->get('\Magento\Checkout\Model\Session')->getQuote()->getShippingAddress();
		if($backendQuote){
			$address = $backendQuote->getShippingAddress();
		}
		$country = $address->getCountryId();
		if($country == ''){
			$country = 'FR';
		}
		if($address->getRegionId() != ''){
			$state = $address->getRegionId();
		} else {
			$state = $address->getRegion();
		}
		$city = $address->getCity();
		if($city == ''){
			$city = '*';
		}
		$zipCode = $address->getPostcode();
		if($zipCode == ''){
			$zipCode = '*';
		}

		$itemsInCart = 0;
		$totalWeight = 0;
		$subtotal = $objectManager->get('\Magento\Checkout\Model\Session')->getQuote()->getSubtotal();
		if($subtotal == null){
			if($backendQuote){
				$subtotal = $backendQuote->getSubtotal();
			}
		}

		if ($request->getAllItems()) {
			foreach ($request->getAllItems() as $item) {
				if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
					continue;
				}
				if($item->getQty() > 0){
					$itemsInCart += $item->getQty();
					$totalWeight += $item->getQty() * $item->getWeight() * 1000;
				}
			}
		}
		
		$shippingMethods = array();
		$matrixItems = $objectManager->get('Laurensmedia\Advancedshipping\Model\ResourceModel\Matrix\Collection')
			->setOrder('shipping_costs', 'ASC')
			->addFieldToFilter('store_ids', array('finset' => $storeId))
			->addFieldToFilter('customer_group_ids', array('finset' => $customerGroupId))
			//->addFieldToFilter('shipping_groups', array('finset' => $shippingGroup))
			->addFieldToFilter('country', array(null, '', '*', $country))
			->addFieldToFilter('city', array(null, '', '*', $city))
			->addFieldToFilter('zip_from', array(array('lteq' => $zipCode), null, '', '*'))
			->addFieldToFilter('zip_to', array(array('gteq' => $zipCode), null, '', '*'))
			->addFieldToFilter('totalitems_from', array(array('lteq' => $itemsInCart), null, '', '*'))
			->addFieldToFilter('totalitems_to', array(array('gteq' => $itemsInCart), null, '', '*'))
			->addFieldToFilter('weight_from', array(array('lteq' => $totalWeight), null, '', '*'))
			->addFieldToFilter('weight_to', array(array('gteq' => $totalWeight), null, '', '*'))
			->addFieldToFilter('subtotal_from', array(array('lteq' => $subtotal), null, '', '*'))
			->addFieldToFilter('subtotal_to', array(array('gteq' => $subtotal), null, '', '*'));
			
		foreach($matrixItems as $matrixItem){
			// Add price when there are already other items in the cart with this shipping method applied to
			$price = $matrixItem->getShippingCosts();
			$method = $this->_rateMethodFactory->create();
			$method->setCarrier($this->_code);
			$method->setCarrierTitle($this->getConfigData('title'));
			$method->setMethod($matrixItem->getShippingCode());
			$method->setMethodTitle($matrixItem->getShippingDescription());
				
			$method->setPrice($price);
			$method->setCost($price);
			$shippingMethods[$matrixItem->getShippingCode()] = array(
					'method' => $method,
					'price' => $price,
					'stop_progress' => $matrixItem->getStopProcessing(),
					'stop_other_groups' => $matrixItem->getStopOtherShippinggroups(),
					'shipping_groups' => $matrixItem->getShippingGroups(),
				);
		}
		foreach($shippingMethods as $method){
			$result->append($method['method']);
		}
		return $result;
    }
}