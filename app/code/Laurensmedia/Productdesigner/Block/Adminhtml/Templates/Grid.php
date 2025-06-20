<?php
namespace Laurensmedia\Productdesigner\Block\Adminhtml\Templates;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Laurensmedia\Productdesigner\Model\templatesFactory
     */
    protected $_templatesFactory;

    /**
     * @var \Laurensmedia\Productdesigner\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Laurensmedia\Productdesigner\Model\templatesFactory $templatesFactory
     * @param \Laurensmedia\Productdesigner\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Laurensmedia\Productdesigner\Model\TemplatesFactory $TemplatesFactory,
        \Laurensmedia\Productdesigner\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_templatesFactory = $TemplatesFactory;
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
        $collection = $this->_templatesFactory->create()->getCollection();
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
            'title',
            [
                'header' => __('Title'),
                'type' => 'text',
                'index' => 'title'
            ]
        );


		$this->addColumn(
			'product_id',
			[
				'header' => __('Product'),
				'index' => 'product_id',
				'type' => 'options',
				'options' => \Laurensmedia\Productdesigner\Block\Adminhtml\Templates\Grid::getOptionArray14()
			]
		);
		
						
		$this->addColumn(
			'autoload',
			[
				'header' => __('Load this template by default?'),
				'index' => 'autoload',
				'type' => 'options',
				'options' => \Laurensmedia\Productdesigner\Block\Adminhtml\Templates\Grid::getOptionArray13()
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
     * @param \Laurensmedia\Productdesigner\Model\templates|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'productdesigner/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray13()
		{
            $data_array=array(); 
			$data_array[0]='No';
			$data_array[1]='Yes';
            return($data_array);
		}
		static public function getValueArray13()
		{
            $data_array=array();
			foreach(\Laurensmedia\Productdesigner\Block\Adminhtml\Templates\Grid::getOptionArray13() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}

		static public function getOptionArray14()
		{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	        $collection = $objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\Collection')
				->addAttributeToSelect('name')
				->load();
            $data_array=array(); 
			foreach($collection as $product){
				$data_array[$product->getId()] = $product->getName().' ('.$product->getSku().')';
			}
            return($data_array);
		}
		static public function getValueArray14()
		{
            $data_array=array();
			foreach(\Laurensmedia\Productdesigner\Block\Adminhtml\Templates\Grid::getOptionArray14() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		

}