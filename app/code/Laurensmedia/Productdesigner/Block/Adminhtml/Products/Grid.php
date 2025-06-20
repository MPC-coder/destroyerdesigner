<?php
namespace Laurensmedia\Productdesigner\Block\Adminhtml\Products;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Laurensmedia\Productdesigner\Model\productsFactory
     */
    protected $_productsFactory;

    /**
     * @var \Laurensmedia\Productdesigner\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Laurensmedia\Productdesigner\Model\productsFactory $productsFactory
     * @param \Laurensmedia\Productdesigner\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Laurensmedia\Productdesigner\Model\ProductsFactory $ProductsFactory,
        \Laurensmedia\Productdesigner\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_productsFactory = $ProductsFactory;
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
/*
        $collection = $this->_productsFactory->create()->getCollection();
        $this->setCollection($collection);
*/

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection')
			->addAttributeToSelect('*')
			->addFieldToFilter('bedrukbaar', array('bedrukbaar'=> true));
			// ->load();
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
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
						
		$this->addColumn(
			'sku',
			[
				'header' => __('Product sku'),
				'index' => 'sku',
				'type' => 'text',
				//'options' => \Laurensmedia\Productdesigner\Block\Adminhtml\Products\Grid::getOptionArray0()
			]
		);


		$this->addColumn(
			'name',
			[
				'header' => __('Product name'),
				'index' => 'name',
				'type' => 'text',
				//'options' => \Laurensmedia\Productdesigner\Block\Adminhtml\Products\Grid::getOptionArray0()
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
     * @param \Laurensmedia\Productdesigner\Model\products|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'productdesigner/*/edit',
            ['id' => $row->getId()]
        );
		
    }


	static public function getOptionArray0()
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
	static public function getValueArray0()
	{
        $data_array=array();
		foreach(\Laurensmedia\Productdesigner\Block\Adminhtml\Products\Grid::getOptionArray0() as $k=>$v){
           $data_array[]=array('value'=>$k,'label'=>$v);		
		}
        return($data_array);

	}



	static public function getOptionArray2()
	{
        $data_array=array();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$groups = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Groups\Collection')
			->load();
		foreach($groups as $group){
			$groupId = $group->getId();
			$data_array[$groupId] = $group->getCode();
		}
        return($data_array);
	}
	static public function getValueArray2()
	{
        $data_array=array();
		foreach(\Laurensmedia\Productdesigner\Block\Adminhtml\Products\Grid::getOptionArray2() as $k=>$v){
           $data_array[]=array('value'=>$k,'label'=>$v);
		}
        return($data_array);

	}
		

}