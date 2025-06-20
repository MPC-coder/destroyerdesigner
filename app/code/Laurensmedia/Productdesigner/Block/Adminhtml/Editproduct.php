<?php

namespace Laurensmedia\Productdesigner\Block\Adminhtml;

class Editproduct extends \Magento\Framework\View\Element\Template
{
    
    protected $_template = 'products/tabs/colors.phtml';
    
    protected $request;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->request = $request;
    }
    
    /**
     * get form key
     *
     * @return string
     */
    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }
    
    public function getProductId(){
        return $this->request->getParam('id');
    }
    
    public function getStoreId(){
        return $this->request->getParam('store') ?: 0;
    }
    
}