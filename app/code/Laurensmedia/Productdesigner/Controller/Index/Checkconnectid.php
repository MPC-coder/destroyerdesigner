<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Laurensmedia\Productdesigner\Model\ResourceModel\Saved\CollectionFactory as SavedCollectionFactory;

class Checkconnectid extends Action
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
     * @var SavedCollectionFactory
     */
    protected SavedCollectionFactory $savedCollectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param SavedCollectionFactory $savedCollectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        SavedCollectionFactory $savedCollectionFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->savedCollectionFactory = $savedCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $connectId = $this->getRequest()->getParam('connectid');

        $existingSaves = $this->savedCollectionFactory->create()
            ->addFieldToSelect('connect_id')
            ->addFieldToFilter('connect_id', $connectId)
            ->setPageSize(1)
            ->setCurPage(1)
            ->load();

        $count = count($existingSaves) > 0 ? 1 : 0;
        $result->setData(['count' => $count]);

        return $result;
    }
}