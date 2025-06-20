<?php
namespace Laurensmedia\Productdesigner\Ui\DataProvider\Product\Form\Modifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form;
use Magento\Backend\Model\UrlInterface;
class Productdesigner extends AbstractModifier
{
	
	protected $editproductFactory;
	
	protected $backendUrl;
	
	public function __construct(
		\Laurensmedia\Productdesigner\Block\Adminhtml\EditproductFactory $editproductFactory,
		UrlInterface $backendUrl
	) {
		$this->editproductFactory = $editproductFactory;
		$this->backendUrl = $backendUrl;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function modifyData(array $data)
	{
		return $data;
	}
	/**
	 * {@inheritdoc}
	 */
	public function modifyMeta(array $meta)
	{
		$meta = array_replace_recursive(
			$meta,
			[
				'test' => [
					'arguments' => [
						'data' => [
							'config' => [
								'additionalClasses' => 'admin__fieldset-product-productdesigner',
								'label' => __('Productdesigner'),
								'collapsible' => true,
								'componentType' => Form\Fieldset::NAME,
								'dataScope' => self::DATA_SCOPE_PRODUCT,
								'disabled' => false,
								'sortOrder' => $this->getNextGroupSortOrder(
									$meta,
									'search-engine-optimization',
									15
								)
							],
						],
					],
					'children' => $this->getPanelChildren(),
				],
			]
		);
		return $meta;
	}
	protected function getPanelChildren()
	{
		return [
			'productdesigner_tab_content' => $this->getProductDesignerContent()
		];
	}
	protected function getProductDesignerContent()
	{
		$content = '<form action="'.$this->backendUrl->getUrl('productdesigner/products/save/back/true').'" enctype="multipart/form-data" method="post" target="_blank">';
		
		$block = $this->editproductFactory->create();
		
		$content .= '<div class="productdesigner-menu">';
		$content .= $this->addMenuItem('Sides', 'sides', true);
		$content .= $this->addMenuItem('Sizes', 'sizes', false);
		$content .= $this->addMenuItem('Colors', 'colors', false);
		$content .= $this->addMenuItem('Printing quality', 'printing_quality', false);
		$content .= $this->addMenuItem('Product Group', 'product_group', false);
		$content .= '</div>';
		
		$block->setTemplate('products/tabs/catalog_product_edit/actions.phtml');
		$content .= $block->toHtml();
		
		$block->setTemplate('products/tabs/catalog_product_edit/sides.phtml');
		$content .= $this->wrapHtmlPart($block->toHtml(), 'Sides', 'sides', false);
		
		$block->setTemplate('products/tabs/catalog_product_edit/sizes.phtml');
		$content .= $this->wrapHtmlPart($block->toHtml(), 'Sizes', 'sizes');
		
		$block->setTemplate('products/tabs/catalog_product_edit/colors.phtml');
		$content .= $this->wrapHtmlPart($block->toHtml(), 'Colors', 'colors');
		
		$block->setTemplate('products/tabs/catalog_product_edit/printingquality.phtml');
		$content .= $this->wrapHtmlPart($block->toHtml(), 'Printing quality', 'printing_quality');
		
		$block->setTemplate('products/tabs/catalog_product_edit/group.phtml');
		$content .= $this->wrapHtmlPart($block->toHtml(), 'Product Group', 'product_group');
		
		$content .= '</form>';
		
		return [
			'arguments' => [
				'data' => [
					'config' => [
						'content' => $content,
						'formElement' => 'container',
						'componentType' => 'container',
						'label' => false,
						'template' => 'ui/form/components/complex',
					],
				],
			],
			'children' => [],
		];
	}
	
	private function wrapHtmlPart($html, $label, $code, $hidden = true){
		$display = '';
		if($hidden){
			$display = 'display:none;';
		}
		$finalHtml = '<div class="productdesigner-part-wrapper" data-menu_item="'.$code.'" style="'.$display.'">';
		$finalHtml .= '<div class="productdesigner-part-container" >';
		$finalHtml .= $html;
		$finalHtml .= '</div>';
		$finalHtml .= '</div>';
		return $finalHtml;
	}
	
	private function addMenuItem($label, $code, $isActive = false){
		$activeClass = $isActive ? 'active' : '';
		$finalHtml = '<div class="productdesigner-menu-item '.$activeClass.'" data-menu_item="'.$code.'">'.$label.'</div>';
		return $finalHtml;
	}
}