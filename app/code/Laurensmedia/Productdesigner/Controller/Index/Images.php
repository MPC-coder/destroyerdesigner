<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Laurensmedia\Productdesigner\Model\ResourceModel\Images\CollectionFactory as ImagesCollectionFactory;

class Images extends Action
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
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var ImagesCollectionFactory
     */
    protected ImagesCollectionFactory $imagesCollectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param ImagesCollectionFactory $imagesCollectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        ImagesCollectionFactory $imagesCollectionFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->imagesCollectionFactory = $imagesCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $categoryId = (int) $this->getRequest()->getParam('id');
        $html = '';

        if ($categoryId > 0) {
            $images = $this->imagesCollectionFactory->create()
                ->addFieldToFilter('categorie', ['finset' => $categoryId]);

            foreach ($images as $image) {
                $imagePath = $image->getImage();
                if (strpos($imagePath, 'productdesigner_images') === false) {
                    $imagePath = 'productdesigner_images/' . $imagePath;
                }
                $html .= '<img src="' . $mediaUrl . $imagePath . '" />';
            }
        }

        $result->setData(['html' => $html]);
        return $result;
    }
}