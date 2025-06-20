<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class Workspace extends Action
{
    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $data = [
            'workspace' => $this->getRequest()->getParam('id'),
            'color' => $this->getRequest()->getParam('colorimage'),
        ];

        $block = $resultPage->getLayout()
            ->createBlock('Laurensmedia\Productdesigner\Block\Designer')
            ->setTemplate('Laurensmedia_Productdesigner::workspace.phtml')
            ->setData('data', $data)
            ->toHtml();
        $result->setData(json_decode($block, true));
        return $result;
    }
}