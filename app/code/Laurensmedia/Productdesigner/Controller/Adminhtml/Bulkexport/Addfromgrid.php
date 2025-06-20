<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Bulkexport;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Laurensmedia\Productdesigner\Model\BulkexportFactory;
use Laurensmedia\Productdesigner\Block\Index;

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

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var BulkexportFactory
     */
    protected $bulkexportFactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderRepository $orderRepository
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param BulkexportFactory $bulkexportFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderRepository $orderRepository,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        BulkexportFactory $bulkexportFactory
    ) {
        parent::__construct($context, $filter);

        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->bulkexportFactory = $bulkexportFactory;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        // Obtenir le baseUrl et mediaUrl
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        // Obtenir les informations de configuration
        $exportCombining = $this->scopeConfig->getValue(
            'lm_productdesigner/lm_pd_bulkexport/lm_pd_bulkexport_arrange',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $emptyDesign = $this->scopeConfig->getValue(
            'lm_productdesigner/lm_pd_bulkexport/lm_pd_bulkexport_emptyside',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $pdfWidth = $this->scopeConfig->getValue(
            'lm_productdesigner/lm_pd_bulkexport/lm_pd_bulkexport_width', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $pdfHeight = $this->scopeConfig->getValue(
            'lm_productdesigner/lm_pd_bulkexport/lm_pd_bulkexport_height',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $pdfVerticalMargin = $this->scopeConfig->getValue(
            'lm_productdesigner/lm_pd_bulkexport/lm_pd_bulkexport_verticalmargin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $pdfHorizontalMargin = $this->scopeConfig->getValue(
            'lm_productdesigner/lm_pd_bulkexport/lm_pd_bulkexport_horizontalmargin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $pdfVerticalSpacing = $this->scopeConfig->getValue(
            'lm_productdesigner/lm_pd_bulkexport/lm_pd_bulkexport_verticalmargin_between',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $pdfHorizontalSpacing = $this->scopeConfig->getValue(
            'lm_productdesigner/lm_pd_bulkexport/lm_pd_bulkexport_horizontalmargin_between',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        // Récupérer les éléments de commande à traiter
        $printOrderItems = [];
        
        foreach ($collection->getItems() as $order) {
            $orderItems = $order->getAllItems();
            
            foreach ($orderItems as $orderItem) {
                // Check if we're processing wood items
                if ($this->getRequest()->getParam('iswood') === 'true') {
                    $pdfWidth = 600;
                    $pdfHeight = 300;
                    $pdfVerticalMargin = 0;
                    $pdfHorizontalMargin = 0;
                    $exportCombining = 'wood_board';
                    $pdfVerticalSpacing = 1;
                    $pdfHorizontalSpacing = 1;
                }
                
                $orderItemProductId = $orderItem->getProductId();
                $printOrderItems[] = [
                    'order_id' => $order->getId(),
                    'product_id' => $orderItemProductId,
                    'order_item_id' => $orderItem->getId(),
                    'export_combining' => $exportCombining,
                    'empty_design' => $emptyDesign,
                    'pdf_width' => $pdfWidth,
                    'pdf_height' => $pdfHeight,
                    'pdf_margin_vertical' => $pdfVerticalMargin,
                    'pdf_margin_horizontal' => $pdfHorizontalMargin,
                    'pdf_margin_items_vertical' => $pdfVerticalSpacing,
                    'pdf_margin_items_horizontal' => $pdfHorizontalSpacing
                ];
            }
        }
        
        // Enregistrer chaque élément dans la table bulkexport
        foreach ($printOrderItems as $item) {
            $bulkExport = $this->bulkexportFactory->create();
            $bulkExport->addData($item)->save();
        }
        
        // Rediriger vers la liste des exports
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('productdesigner/bulkexport');
        
        return $resultRedirect;
    }
}