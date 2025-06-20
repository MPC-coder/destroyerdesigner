<?php
namespace Laurensmedia\Productdesigner\Block\Adminhtml\Images;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Laurensmedia\Productdesigner\Model\imagesFactory
     */
    protected $_imagesFactory;

    /**
     * @var \Laurensmedia\Productdesigner\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Laurensmedia\Productdesigner\Model\imagesFactory $imagesFactory
     * @param \Laurensmedia\Productdesigner\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Laurensmedia\Productdesigner\Model\ImagesFactory $ImagesFactory,
        \Laurensmedia\Productdesigner\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_imagesFactory = $ImagesFactory;
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
        $collection = $this->_imagesFactory->create()->getCollection();
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
					'label',
					[
						'header' => __('Label'),
						'index' => 'label',
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
     * @param \Laurensmedia\Productdesigner\Model\images|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'productdesigner/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	static public function getOptionArray3()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $images = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Imagecategories\Collection')->load();
        $data_array = array();
        foreach($images as $image){
			$imageId = $image->getId();
			$data_array[$imageId] = $image->getLabel();
        }
        return $data_array;
	}
	static public function getValueArray3()
	{
        $data_array=array();
		foreach(\Laurensmedia\Productdesigner\Block\Adminhtml\Images\Grid::getOptionArray3() as $k=>$v){
           $data_array[]=array('value'=>$k,'label'=>$v);		
		}
        return($data_array);

	}

	

}