<?php
namespace Laurensmedia\Laposte\Controller\Adminhtml\Bulkgenerate;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\Filesystem\DirectoryList;

class Addfromgrid extends AbstractMassAction
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::delete';

    /**
     * @var OrderRepository
     */
    protected $orderRepository;
	
	protected $codesCollectionFactory;
	
	protected $codesModel;
	
	protected $_filesystem;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderRepository $orderRepository,
		\Laurensmedia\Laposte\Model\ResourceModel\Codes\CollectionFactory $codesCollectionFactory,
		\Laurensmedia\Laposte\Model\ResourceModel\Codes $codesModel,
		\Magento\Framework\Filesystem $filesystem
    )
    {
        parent::__construct($context, $filter);

        $this->collectionFactory = $collectionFactory;
        $this->orderRepository   = $orderRepository;
		$this->codesCollectionFactory = $codesCollectionFactory;
		$this->codesModel = $codesModel;
		$this->_filesystem = $filesystem;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
		$collection = $this->filter->getCollection($this->collectionFactory->create());
		try {
			$alreadyAssignedCount = 0;
			$assignedCount = 0;
			$pdf = null;
			$orderIds = array();
			foreach($collection as $order){
				$orderIds[] = $order->getId();
				// Check if already has shipping label
				$assignedCode = $this->codesCollectionFactory->create()->addFieldToFilter('order_id', $order->getId())->getFirstItem();
				if($assignedCode->getId() > 0){
					$alreadyAssignedCount++;
					$pdf = $this->addItemToPdf($order, $assignedCode->getCode(), $pdf);
				} else {
					// Assign order id to shipping code
					$blankCodeObject = $this->codesCollectionFactory->create()->addFieldToFilter('order_id', array('null' => true))->getFirstItem();
					if($blankCodeObject->getId() > 0){
						$laPosteCode = $blankCodeObject->getCode();
						$blankCodeObject->setOrderId($order->getId())->save();
						
						// Now generated PDF
						$pdf = $this->addItemToPdf($order, $laPosteCode, $pdf);
						
						$assignedCount++;
					}
				}
			}
			
			$mediaPath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
			$directory = $mediaPath.'lapostepdf/';
			if(!file_exists($directory)){
				mkdir($directory);
			}
			
			$randomnumber = rand(0, 99999999);
			$pdf->Output($directory.$randomnumber.'.pdf', 'F');
			
			if($assignedCount > 0){
				$this->messageManager->addSuccess(__('A total of %1 record(s) were assigned.', $assignedCount));
			}
			if($alreadyAssignedCount > 0){
				$this->messageManager->addError(__('A total of %1 record(s) were already assigned.', $alreadyAssignedCount));
			}
		} catch (\Exception $e) {
			$this->messageManager->addError(__($e->getMessage()));
		}
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $resultRedirect = $objectManager->get('Magento\Framework\Controller\Result\RedirectFactory')->create();
	    $resultRedirect->setPath('laposte/bulkgenerate/intermediate', array('pdf' => $randomnumber, 'orders' => implode(',', $orderIds)));
	    return $resultRedirect;
    }
	
	private function addItemToPdf($order, $laPosteCode, $pdf = null){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		// $order = $objectManager->get('Magento\Sales\Model\Order')->load($this->getRequest()->getParam('order_id'));
		$layout = $this->_view->getLayout();
		$block = $layout->createBlock('Laurensmedia\Productdesigner\Block\Index');
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseUrl = $storeManager->getStore()->getBaseUrl();
		$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')
			->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
			->getAbsolutePath();
		$mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$allowerdContries = $objectManager->get('Magento\Directory\Model\AllowedCountries')->getAllowedCountries();
		$countryFactory = $objectManager->get('\Magento\Directory\Model\CountryFactory');
		$countries = array();
		foreach($allowerdContries as $countryCode){
			if($countryCode){
				$data = $countryFactory->create()->loadByCode($countryCode);
				$countries[$countryCode] = $data->getName();
			}
		}

		$helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Laurensmedia\Productdesigner\Helper\Tcpdfhelper');
		
		$orderItemNames = array();
		foreach($order->getAllItems() as $item){
			$orderItemNames[] = $item->getName();
		}
		
		$width = 150;
		$height = 100;
		if($width <= $height){
			$orientation = 'P';
		} else {
			$orientation = 'L';
		}
		
		// create new PDF document
		if($pdf == null){
			$pdf = $helper->getPdfObject($block->get_base_dir(''));
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->AddPage($orientation, array($width, $height));
			$pdf->setPageOrientation($orientation, false, 0);
			$pdf->SetFont ('helvetica', '', $pdf->pixelsToUnits('30'), '', 'default', true );
		} else {
			$pdf->AddPage($orientation, array($width, $height));
			$pdf->setPageOrientation($orientation, false, 0);
		}
		
/*
		// Product information
		$pdf->Rect(($width - 120), 0, (120 - 74), 40, 'F', array(), array(232,244,255));
		$html = implode("\n", $orderItemNames);
		$pdf->MultiCell((120 - 74), 38, $html, 0, 'L', 0, 1, ($width - 120), 2, true, 0, false, true, 38);
*/
		
		// Porto
		$pdf->Rect(($width - 74), 0, 74, 40, 'F', array(), array(221,221,221));
		// $portoCode = 'L 023 199 XXX X';
		$pdf->MultiCell(74, 38, $laPosteCode, 0, 'C', 0, 1, ($width - 74), 2, true, 0, false, true, 38);
		
		// Address
		$addressData = $order->getShippingAddress()->getData();
		$address = $addressData['firstname'].' '.$addressData['lastname']."\n";
		$address .= $addressData['street']."\n";
		$address .= $addressData['postcode'].' '.$addressData['city']."\n";
		$address .= $countries[$addressData['country_id']];
// 			echo '<pre>';print_r($order->getShippingAddress()->getData());exit;
		$pdf->Rect(($width - 140), ($height - 52), (140 - 15), 40, 'F', array(), array(221,221,221));
		$pdf->MultiCell((140 - 15), 38, $address, 0, 'C', 0, 1, ($width - 140), ($height - 50), true, 0, false, true, 38);

		// Store information
		$pdf->Rect(0, 0, 40, 40, 'F', array(), array(232,244,255));
		$storeInformation = $objectManager->create('\Magento\Store\Model\Information');
		$storeInfo = $storeInformation->getStoreInformationObject($order->getStore());
// 			echo '<pre>';print_r($storeInfo->getData());exit;
		$html = $storeInfo->getName()."\n";
		$html .= $storeInfo->getData('street_line1')."\n";
		$html .= $storeInfo->getData('postcode').' '.$storeInfo->getData('city')."\n";
		$html .= $storeInfo->getData('country');
		$pdf->MultiCell(38, 38, $html, 0, 'L', 0, 1, 2, 2, true, 0, false, true, 38);
		
		return $pdf;
	}
}