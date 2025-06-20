<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Plugin\Magento\Quote\Model\Quote\Item;

class AbstractItem
{

	public function afterCheckData(
		\Magento\Quote\Model\Quote\Item\AbstractItem $subject,
		$result
	) {
		if($subject->getHasError()){
			$messages = $subject->getMessage(false);
			if(in_array((string)__('Item qty declaration error'), $messages)){
				$subject->setHasError(false);
				$subject->removeMessageByText(__('Item qty declaration error'));
			}
		}
		return $subject;
	}
}