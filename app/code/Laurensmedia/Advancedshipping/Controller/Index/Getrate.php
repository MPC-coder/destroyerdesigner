<?php
namespace Laurensmedia\Advancedshipping\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class Getrate extends Action
{

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
 
    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;
 
 
    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, JsonFactory $resultJsonFactory)
    {
 
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
 
        parent::__construct($context);
    }
    
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		
		$post = $this->getRequest()->getParams();
		$qty = $post['qty'];
		$product_id = $post['product_id'];
		$data = array(
			'qty' => $qty,
			'product_id' => $product_id
		);
		$shipping_costs = $objectManager->create('Laurensmedia\Advancedshipping\Model\Rates')->get_rate($data);
		echo $shipping_costs;
		return $shipping_costs;
		
		$html = '';
        $result->setData(array('html' => $html));
        return $result;
    }
}