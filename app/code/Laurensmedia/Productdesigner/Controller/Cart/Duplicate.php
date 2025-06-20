<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Cart;
use Magento\Catalog\Model\ProductFactory;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Laurensmedia\Productdesigner\Model\ResourceModel\Saved\CollectionFactory as SavedCollectionFactory;
use Laurensmedia\Productdesigner\Model\SavedFactory;

class Duplicate extends Action
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
     * @var QuoteFactory
     */
    protected QuoteFactory $quoteFactory;

    /**
     * @var ItemFactory
     */
    protected ItemFactory $quoteItemFactory;

    /**
     * @var Cart
     */
    protected Cart $cart;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $productFactory;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * @var SavedCollectionFactory
     */
    protected SavedCollectionFactory $savedCollectionFactory;

    /**
     * @var SavedFactory
     */
    protected SavedFactory $savedFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param QuoteFactory $quoteFactory
     * @param ItemFactory $quoteItemFactory
     * @param Cart $cart
     * @param ProductFactory $productFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param CheckoutSession $checkoutSession
     * @param SavedCollectionFactory $savedCollectionFactory
     * @param SavedFactory $savedFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        QuoteFactory $quoteFactory,
        ItemFactory $quoteItemFactory,
        Cart $cart,
        ProductFactory $productFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        CheckoutSession $checkoutSession,
        SavedCollectionFactory $savedCollectionFactory,
        SavedFactory $savedFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->quoteFactory = $quoteFactory;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->savedCollectionFactory = $savedCollectionFactory;
        $this->savedFactory = $savedFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();

        try {
            $quoteItemId = $this->getRequest()->getParam('id');
            $quoteItem = $this->quoteItemFactory->create()->load($quoteItemId);
            $quote = $this->quoteFactory->create()->load($quoteItem->getQuoteId());
            $quoteItem = $quote->getItemsCollection()->addFieldToFilter('item_id', $quoteItemId)->getFirstItem();

            $sessionQuoteId = $this->checkoutSession->getQuote()->getId();

            if ($sessionQuoteId != $quote->getId()) {
                echo 'No access';
                return;
            }

            $productId = $quoteItem->getProductId();
            $_product = $this->productFactory->create()->load($productId);

            $options = $quoteItem->getProduct()->getTypeInstance(true)->getOrderOptions($quoteItem->getProduct());
            $additionalOptions = $quoteItem->getOptionByCode('additional_options');
            $additionalOptions = $additionalOptions ? $additionalOptions->getValue() : '';

            $pdData = $quoteItem->getProductdesignerData();
            $pdData = $pdData ? json_decode($pdData, true) : [];
            $oldConnectId = $pdData['connect_id']['connect_id'];
            $newConnectId = mt_rand(0, mt_getrandmax());

            if ($oldConnectId > 0) {
                $savedItems = $this->savedCollectionFactory->create()
                    ->addFieldToFilter('connect_id', $oldConnectId)
                    ->setPageSize(3)
                    ->setCurPage(1);

                $newSaveItems = [];
                foreach ($savedItems as $savedItem) {
                    $newSaveItem = $savedItem->getData();
                    unset($newSaveItem['save_id']);

                    // Duplicate files
                    $jsonDir = $mediaPath . 'productdesigner/json/' . date('Y') . '/' . date('m') . '/';
                    if (!file_exists($jsonDir) && !is_dir($jsonDir)) {
                        mkdir($jsonDir, 0777, true);
                    }
                    $jsonFileName = date('U') . '_' . rand(0, 999999) . '.php';
                    $jsonFileLocation = $jsonDir . $jsonFileName;
                    copy($mediaPath . 'productdesigner/json/' . $savedItem['json'], $jsonFileLocation);
                    $newSaveItem['json'] = date('Y') . '/' . date('m') . '/' . $jsonFileName;

                    $pngDir = $mediaPath . 'productdesigner/png_export/' . date('Y') . '/' . date('m') . '/';
                    if (!file_exists($pngDir) && !is_dir($pngDir)) {
                        mkdir($pngDir, 0777, true);
                    }
                    $pngFileName = date('U') . '_' . rand(0, 999999) . '.png';
                    $pngFileLocation = $pngDir . $pngFileName;
                    copy($mediaPath . 'productdesigner/png_export/' . $savedItem['png'], $pngFileLocation);
                    $newSaveItem['png'] = date('Y') . '/' . date('m') . '/' . $pngFileName;

                    $svgDir = $mediaPath . 'productdesigner/svg/' . date('Y') . '/' . date('m') . '/';
                    if (!file_exists($svgDir) && !is_dir($svgDir)) {
                        mkdir($svgDir, 0777, true);
                    }
                    $svgFileName = date('U') . '_' . rand(0, 999999) . '.php';
                    $svgFileLocation = $svgDir . $svgFileName;
                    copy($mediaPath . 'productdesigner/svg/' . $savedItem['svg'], $svgFileLocation);
                    $newSaveItem['svg'] = date('Y') . '/' . date('m') . '/' . $svgFileName;
                    $oldSvgOutputFile = str_replace(basename($savedItem['svg']), 'output_' . basename($savedItem['svg']), $savedItem['svg']);
                    $oldSvgOutputFileLocation = $mediaPath . 'productdesigner/svg/' . $oldSvgOutputFile;
                    $svgOutputFileName = 'output_' . $svgFileName;
                    if (file_exists($mediaPath . 'productdesigner/svg/' . $svgOutputFileName)) {
                        copy($mediaPath . 'productdesigner/svg/' . $svgOutputFileName, $mediaPath . 'productdesigner/svg/' . date('Y') . '/' . date('m') . '/' . $svgOutputFileName);
                    }

                    $newSaveItem['connect_id'] = $newConnectId;

                    $newSaveItems[] = $newSaveItem;
                }

                foreach ($newSaveItems as $newSaveItem) {
                    $this->savedFactory->create()->addData($newSaveItem)->save();
                }
            }
            $pdData['connect_id']['connect_id'] = $newConnectId;

            $info = $options['info_buyRequest'] ?? [];
            $request1 = new \Magento\Framework\DataObject();
            $request1->setData($info);

            $this->cart->addProduct($_product, $request1);
            $this->cart->save();

            $quote = $this->quoteFactory->create()->load($quoteItem->getQuoteId());
            $lastItem = $quote->getItemsCollection()->getLastItem();
            $lastItem->setProductdesignerData(json_encode($pdData));
            $lastItem->addOption([
                'product' => $lastItem->getProduct(),
                'code' => 'additional_options',
                'value' => $additionalOptions
            ]);

            $itemPrice = $quoteItem->getCustomPrice();
            $lastItem->setCustomPrice($itemPrice);
            $lastItem->setOriginalCustomPrice($itemPrice);
            $lastItem->setPrice($itemPrice);
            $lastItem->setOriginalPrice($itemPrice);
            $lastItem->getProduct()->setIsSuperMode(true);
            $lastItem->addOption([
                'product' => $lastItem->getProduct(),
                'code' => 'pd_processed_price',
                'value' => 1
            ]);

            $lastItem->save();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        $this->_redirect('checkout/cart/');
    }
}