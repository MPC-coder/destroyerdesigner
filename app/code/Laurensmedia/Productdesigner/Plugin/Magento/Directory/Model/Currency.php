<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Plugin\Magento\Directory\Model;

class Currency
{

	public function aroundGetOutputFormat(
		\Magento\Directory\Model\Currency $subject,
		\Closure $proceed
	) {
		return '%s €';
	}
}