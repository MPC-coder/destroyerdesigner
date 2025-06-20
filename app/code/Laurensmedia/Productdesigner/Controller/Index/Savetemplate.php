<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Laurensmedia\Productdesigner\Model\TemplatesFactory;
use Laurensmedia\Productdesigner\Model\ResourceModel\Templatedata\CollectionFactory;

class Savetemplate extends Action
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
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var TemplatesFactory
     */
    protected TemplatesFactory $templatesFactory;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param TemplatesFactory $templatesFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        TemplatesFactory $templatesFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->templatesFactory = $templatesFactory;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $mediaPath = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();

        $data = $this->getRequest()->getPostValue();
        $template = $this->templatesFactory->create()->load($data['template_id']);

        if ($template->getPassword() === $data['password']) {
            $deleteItems = $this->collectionFactory->create()
                ->addFieldToFilter('template_id', $template->getId());
            foreach ($deleteItems as $deleteItem) {
                $deleteItem->delete();
            }

            if (!empty($data['json'])) {
                $count = 0;
                foreach ($data['json'] as $label => $jsonData) {
                    $saveData = [
                        'template_id' => $template->getId(),
                        'label' => $label,
                        'svg' => $data['svg'][$count],
                        'json' => $jsonData,
                    ];
                    $this->collectionFactory->create()->addData($saveData)->save();

                    if (isset($data['images']) && isset($data['images'][$label])) {
                        file_put_contents($mediaPath . 'productdesigner/templatethumbs/' . $data['template_id'] . '-' . $label . '.png', base64_decode(str_replace('data:image/png;base64,', '', $data['images'][$label])));
                    }

                    $count++;
                }
            }
        }
        echo 'Template saved';
    }
}