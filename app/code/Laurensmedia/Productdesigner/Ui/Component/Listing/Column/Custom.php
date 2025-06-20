<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Ui\Component\Listing\Column;
 
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResourceModel;
use Magento\Sales\Model\Order;
 
class Custom extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;
    
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteria;
    
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $orderCollectionFactory;
    
    /**
     * @var OrderResourceModel
     */
    protected OrderResourceModel $orderResourceModel;
    
    /**
     * @var Order
     */
    protected Order $order;
 
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $criteria
     * @param CollectionFactory $orderCollectionFactory
     * @param OrderResourceModel $orderResourceModel
     * @param Order $order
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        CollectionFactory $orderCollectionFactory,
        OrderResourceModel $orderResourceModel,
        Order $order,
        array $components = [],
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteria = $criteria;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderResourceModel = $orderResourceModel;
        $this->order = $order;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!isset($item['custom_info_labels']) || $item['custom_info_labels'] == '') {
                    $order = $this->order->load($item["entity_id"]);
                    if ($order->getCustomInfoLabels() != '') {
                        $item[$this->getData('name')] = $order->getCustomInfoLabels();
                        continue;
                    }
                    
                    $shippingMethod = $order->getShippingDescription() ?? '';
                    
                    $labels = [];
                    if (strpos($shippingMethod, 'Colissimo') !== false) {
                        $labels[] = '<span class="custom-label shipping-label" style="background:green; color: white;">Colissimo</span>';
                    } elseif (strpos($shippingMethod, 'Chronopost') !== false) {
                        $labels[] = '<span class="custom-label shipping-label" style="background:blue; color: white;">Chronopost</span>';
                    } elseif (strpos($shippingMethod, 'lettre_suivie') !== false) {
                        $labels[] = '<span class="custom-label shipping-label">Lettre suivie</span>';
                    }
                    
                    $orderItems = $order->getAllItems();
                    foreach ($orderItems as $orderItem) {
                        $addHtml = '<span class="custom-label product-options-label">' . $orderItem->getName();
                        $options = $orderItem->getProductOptions();
                        if (isset($options['options'])) {
                            $options = $options['options'];
                            $addHtml .= '<ul>';
                            foreach ($options as $option) {
                                $addHtml .= '<li><span>' . $option['label'] . '</span>: ' . $option['value'] . '</li>';
                            }
                            $addHtml .= '</ul>';
                        }
                        $addHtml .= '</span>';
                        $labels[] = $addHtml;
                    }
                    
                    $labelHtml = implode('', $labels);
                    
                    $item[$this->getData('name')] = $labelHtml;
                    
                    $order->setCustomInfoLabels($labelHtml);
                    $order->save();
                }
            }
        }
        return $dataSource;
    }
}