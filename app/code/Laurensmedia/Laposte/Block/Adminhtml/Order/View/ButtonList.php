<?php
namespace Laurensmedia\Laposte\Block\Adminhtml\Order\View;

class ButtonList extends \Magento\Backend\Block\Widget\Button\ButtonList
{
   public function __construct(\Magento\Backend\Block\Widget\Button\ItemFactory $itemFactory)
   {
       parent::__construct($itemFactory);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $urlManager = $objectManager->get('Magento\Framework\UrlInterface');
		$request = $objectManager->get('Magento\Framework\App\Request\Http'); 
		if($request->getParam('order_id') > 0){
			$this->add('lapostebutton', [
				'label' => __('Download La Poste PDF'),
				'onclick' => 'setLocation(\'' . $urlManager->getUrl('laposte/label/create', array('order_id' => $request->getParam('order_id'))) . '\')',
			]);
			$this->add('downloadzipbutton', [
				'label' => __('Download designer ZIP engraving'),
				'onclick' => 'setLocation(\'' . $urlManager->getUrl('productdesigner/orders/downloadzip', array('order_id' => $request->getParam('order_id'))) . '\')',
			]);
		}
   }
}