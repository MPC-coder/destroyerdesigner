<?php
namespace Laurensmedia\Productdesigner\Block\Adminhtml\Groups;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Laurensmedia\Productdesigner\Model\groupsFactory
     */
    protected $_groupsFactory;

    /**
     * @var \Laurensmedia\Productdesigner\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Laurensmedia\Productdesigner\Model\groupsFactory $groupsFactory
     * @param \Laurensmedia\Productdesigner\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Laurensmedia\Productdesigner\Model\GroupsFactory $GroupsFactory,
        \Laurensmedia\Productdesigner\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_groupsFactory = $GroupsFactory;
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
        $collection = $this->_groupsFactory->create()->getCollection();
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
					'code',
					[
						'header' => __('Name'),
						'index' => 'code',
					]
				);
				
				$this->addColumn(
					'display_header',
					[
						'header' => __('Display header in Product Designer?'),
						'index' => 'display_header',
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
		

		

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('productdesigner/*/index', ['_current' => true]);
    }

    /**
     * @param \Laurensmedia\Productdesigner\Model\groups|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'productdesigner/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray2()
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
		static public function getValueArray2()
		{
            $data_array=array();
			foreach(\Laurensmedia\Productdesigner\Block\Adminhtml\Groups\Grid::getOptionArray2() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		
		static public function getOptionArray3()
		{
	        $data_array=array();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$fonts = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Fonts\Collection')
				->load();
			foreach($fonts as $font){
				$fontId = $font->getId();
				$data_array[$fontId] = $font->getName();
			}
	        return($data_array);
		}
		static public function getValueArray3()
		{
            $data_array=array();
			foreach(\Laurensmedia\Productdesigner\Block\Adminhtml\Groups\Grid::getOptionArray3() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		
		static public function getOptionArray4()
		{
	        $data_array=array();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$categories = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Imagecategories\Collection')
				->load();
			foreach($categories as $category){
				$categoryId = $category->getId();
				$data_array[$categoryId] = $category->getLabel();
			}
	        return($data_array);
		}
		static public function getValueArray4()
		{
            $data_array=array();
			foreach(\Laurensmedia\Productdesigner\Block\Adminhtml\Groups\Grid::getOptionArray4() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		
		static public function getOptionArray5()
		{
	        $data_array=array();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$colors = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Textcolors\Collection')
				->load();
			foreach($colors as $color){
				$colorId = $color->getId();
				$data_array[$colorId] = $color->getName();
			}
	        return($data_array);
		}
		static public function getValueArray5()
		{
            $data_array=array();
			foreach(\Laurensmedia\Productdesigner\Block\Adminhtml\Groups\Grid::getOptionArray5() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		

}