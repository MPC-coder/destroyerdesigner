<?php
namespace Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Laurensmedia\Advancedshipping\Model\matrixFactory
     */
    protected $_matrixFactory;

    /**
     * @var \Laurensmedia\Advancedshipping\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Laurensmedia\Advancedshipping\Model\matrixFactory $matrixFactory
     * @param \Laurensmedia\Advancedshipping\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Laurensmedia\Advancedshipping\Model\MatrixFactory $MatrixFactory,
        \Laurensmedia\Advancedshipping\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_matrixFactory = $MatrixFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_matrixFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

				$this->addColumn(
					'store_ids',
					[
						'header' => __('Stores'),
						'index' => 'store_ids',
					]
				);
				
				
				
				$this->addColumn(
					'customer_group_ids',
					[
						'header' => __('Customer Groups'),
						'index' => 'customer_group_ids',
					]
				);
				
				
				
				$this->addColumn(
					'shipping_groups',
					[
						'header' => __('Shipping Groups'),
						'index' => 'shipping_groups',
					]
				);
						
						
				$this->addColumn(
					'country',
					[
						'header' => __('Country'),
						'index' => 'country',
					]
				);
				
				$this->addColumn(
					'city',
					[
						'header' => __('City'),
						'index' => 'city',
					]
				);
				
				$this->addColumn(
					'zip_from',
					[
						'header' => __('ZIP from'),
						'index' => 'zip_from',
					]
				);
				
				$this->addColumn(
					'zip_to',
					[
						'header' => __('ZIP to'),
						'index' => 'zip_to',
					]
				);
				
				$this->addColumn(
					'totalitems_from',
					[
						'header' => __('Minimal number of items in cart'),
						'index' => 'totalitems_from',
					]
				);
				
				$this->addColumn(
					'totalitems_to',
					[
						'header' => __('Maximum number of items in cart'),
						'index' => 'totalitems_to',
					]
				);
				
				$this->addColumn(
					'items_from',
					[
						'header' => __('Minimal qty in cart for this product'),
						'index' => 'items_from',
					]
				);
				
				$this->addColumn(
					'items_to',
					[
						'header' => __('Maximum qty in cart for this product'),
						'index' => 'items_to',
					]
				);
				
				$this->addColumn(
					'weight_from',
					[
						'header' => __('Weight from'),
						'index' => 'weight_from',
					]
				);
				
				$this->addColumn(
					'weight_to',
					[
						'header' => __('Weight to'),
						'index' => 'weight_to',
					]
				);
				
				$this->addColumn(
					'subtotal_from',
					[
						'header' => __('Subtotal from'),
						'index' => 'subtotal_from',
					]
				);
				
				$this->addColumn(
					'subtotal_to',
					[
						'header' => __('Subtotal to'),
						'index' => 'subtotal_to',
					]
				);
				
				$this->addColumn(
					'shipping_costs',
					[
						'header' => __('Shipping costs'),
						'index' => 'shipping_costs',
					]
				);
				
				$this->addColumn(
					'shipping_description',
					[
						'header' => __('Shipping method title'),
						'index' => 'shipping_description',
					]
				);
				
						
						$this->addColumn(
							'stop_processing',
							[
								'header' => __('Stop further processing'),
								'index' => 'stop_processing',
								'type' => 'options',
								'options' => \Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getOptionArray18()
							]
						);
						
						
						
						$this->addColumn(
							'stop_other_shippinggroups',
							[
								'header' => __('Remove shipping costs for products not in this shipping group(s)'),
								'index' => 'stop_other_shippinggroups',
								'type' => 'options',
								'options' => \Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getOptionArray19()
							]
						);
						
						


		
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('advancedshipping/*/exportCsv', ['_current' => true]),__('CSV'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('Laurensmedia_Advancedshipping::matrix/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('matrix');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('advancedshipping/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('advancedshipping/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('advancedshipping/*/index', ['_current' => true]);
    }

    /**
     * @param \Laurensmedia\Advancedshipping\Model\matrix|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'advancedshipping/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray0()
		{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	        $stores = $objectManager->create('\Magento\Store\Model\StoreRepository')->getList();
	        $storeList = array();
	        foreach ($stores as $store) {
	            $websiteId = $store["website_id"];
	            $storeId = $store["store_id"];
	            $storeName = $store["name"];
	            $storeList[$storeId] = $storeName;
	        }
	        return($storeList);
		}
		static public function getValueArray0()
		{
            $data_array=array();
			foreach(\Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getOptionArray0() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		
		static public function getOptionArray1()
		{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$customerGroups = $objectManager->get('\Magento\Customer\Model\ResourceModel\Group\Collection')->toOptionArray();
			
	        $customerGroupList = array();
	        foreach ($customerGroups as $customerGroup) {
	            $groupId = $customerGroup['value'];
	            $customerGroupName = $customerGroup['label'];
	            $customerGroupList[$groupId] = $customerGroupName;
	        }
	        return($customerGroupList);
		}
		static public function getValueArray1()
		{
            $data_array=array();
			foreach(\Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getOptionArray1() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);
		}
		
		static public function getOptionArray2()
		{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$shippingGroups = $objectManager->get('\Magento\Eav\Api\AttributeRepositoryInterface')->get(\Magento\Catalog\Model\Product::ENTITY, 'lm_shipping_groups');
			$shippingGroups = $shippingGroups->getSource()->getAllOptions();
			
	        $shippingGroupList = array();
	        foreach ($shippingGroups as $shippingGroup) {
	            $groupId = $shippingGroup['value'];
	            if(!$groupId > 0){
		            continue;
	            }
	            $shippingGroupName = $shippingGroup['label'];
	            $shippingGroupList[$groupId] = $shippingGroupName;
	        }
	        return($shippingGroupList);
		}
		static public function getValueArray2()
		{
            $data_array=array();
			foreach(\Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getOptionArray2() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}

		static public function getOptionArray3()
		{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$countries = $objectManager->create('\Magento\Directory\Model\ResourceModel\Country\Collection')->toOptionArray();
			
	        $countryList = array();
	        foreach ($countries as $country) {
	            $groupId = $country['value'];
	            $countryName = $country['label'];
	            $countryList[$groupId] = $countryName;
	        }
	        return($countryList);
		}
		static public function getValueArray3()
		{
            $data_array=array();
			foreach(\Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getOptionArray3() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		
		static public function getOptionArray18()
		{
            $data_array=array(); 
			$data_array[0]='No';
			$data_array[1]='Yes';
            return($data_array);
		}
		static public function getValueArray18()
		{
            $data_array=array();
			foreach(\Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getOptionArray18() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		
		static public function getOptionArray19()
		{
            $data_array=array(); 
			$data_array[0]='No';
			$data_array[1]='Yes';
            return($data_array);
		}
		static public function getValueArray19()
		{
            $data_array=array();
			foreach(\Laurensmedia\Advancedshipping\Block\Adminhtml\Matrix\Grid::getOptionArray19() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		

}