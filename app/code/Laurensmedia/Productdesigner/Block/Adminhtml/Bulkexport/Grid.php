<?php
namespace Laurensmedia\Productdesigner\Block\Adminhtml\Bulkexport;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Laurensmedia\Productdesigner\Model\bulkexportFactory
     */
    protected $_bulkexportFactory;

    /**
     * @var \Laurensmedia\Productdesigner\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Laurensmedia\Productdesigner\Model\bulkexportFactory $BulkexportFactory
     * @param \Laurensmedia\Productdesigner\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Laurensmedia\Productdesigner\Model\BulkexportFactory $BulkexportFactory,
        \Laurensmedia\Productdesigner\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_bulkexportFactory = $BulkexportFactory;
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
        $collection = $this->_bulkexportFactory->create()->getCollection();
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
			'order_id',
			[
				'header' => __('Order ID'),
				'index' => 'order_id',
			]
		);
		
		$this->addColumn(
			'product_id',
			[
				'header' => __('Product ID'),
				'index' => 'product_id',
			]
		);

		$this->addColumn(
			'export_combining',
			[
				'header' => __('Export arrangement'),
				'index' => 'export_combining',
			]
		);

		$this->addColumn(
			'empty_design',
			[
				'header' => __('Empty product sides'),
				'index' => 'empty_design',
			]
		);

		$this->addColumn(
			'pdf_width',
			[
				'header' => __('PDF width'),
				'index' => 'pdf_width',
			]
		);

		$this->addColumn(
			'pdf_height',
			[
				'header' => __('PDF height'),
				'index' => 'pdf_height',
			]
		);

		$this->addColumn(
			'pdf_margin_vertical',
			[
				'header' => __('PDF margin (top/bottom)'),
				'index' => 'pdf_margin_vertical',
			]
		);

		$this->addColumn(
			'pdf_margin_horizontal',
			[
				'header' => __('PDF margin (left/right)'),
				'index' => 'pdf_margin_horizontal',
			]
		);

		$this->addColumn(
			'pdf_margin_items_vertical',
			[
				'header' => __('PDF item margin (vertical)'),
				'index' => 'pdf_margin_items_vertical',
			]
		);

		$this->addColumn(
			'pdf_margin_items_horizontal',
			[
				'header' => __('PDF item margin (horizontal)'),
				'index' => 'pdf_margin_items_horizontal',
			]
		);

		$this->addColumn(
			'finished',
			[
				'header' => __('Processed'),
				'index' => 'finished',
			]
		);

        $this->addColumn(
            'delete',
            [
                'header' => __('Remove'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Remove'),
                        'url' => [
                            'base' => '*/*/delete'
                        ],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
		
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
     * @param \Laurensmedia\Productdesigner\Model\bulkexport|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		return false;
    }

		

}