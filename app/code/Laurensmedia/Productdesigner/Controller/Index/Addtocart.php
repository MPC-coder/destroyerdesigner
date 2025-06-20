<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Cart;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Catalog\Model\ProductFactory;

class Addtocart extends Action
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
     * @var Cart
     */
    protected Cart $cart;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var ItemFactory
     */
    protected ItemFactory $quoteItemFactory;

    /**
     * @var ProductFactory
     */
    protected ProductFactory $productFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Cart $cart
     * @param StoreManagerInterface $storeManager
     * @param ItemFactory $quoteItemFactory
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Cart $cart,
        StoreManagerInterface $storeManager,
        ItemFactory $quoteItemFactory,
        ProductFactory $productFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->productFactory = $productFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $data = $this->getRequest()->getParam('cart');
        $options = $this->getRequest()->getParam('options');

        $connectId = $this->getRequest()->getParam('connect_id');
        $quote = $this->cart->getQuote();

        if ($this->getRequest()->getParam('isupdatequoteitem') !== 'false') {
            $quoteItem = $this->quoteItemFactory->create()->load($this->getRequest()->getParam('isupdatequoteitem'));
            $quote->removeItem($quoteItem->getId())->save();
        }

        $cartItems = $quote->getAllVisibleItems();
        foreach ($cartItems as $item) {
            if ($item->getOptionByCode('connect_id')) {
                $itemConnectId = $item->getOptionByCode('connect_id')->getValue();
                if (is_array($itemConnectId)) {
                    $itemConnectId = $itemConnectId['connect_id'];
                }
                if ($itemConnectId == $connectId) {
                    $quote->removeItem($item->getId())->save();
                }
            }
        }

        if ($connectId === '') {
            $connectId = mt_rand(0, mt_getrandmax());
        }

        $productId = $data['productid'];
        $product = $this->productFactory->create()->load($productId);

        $productOptions = [];
        $superAttributes = [];
        if ($options !== "") {
            foreach ($options as $option) {
                if (strpos($option['name'], 'option') !== false) {
                    $id = preg_replace("/[^0-9]+/", "", $option['name']);
                    $value = $option['value'];
                    if (strpos($option['name'], '[]') !== false) {
                        if (!isset($productOptions[$id])) {
                            $productOptions[$id] = [];
                        }
                        $productOptions[$id][] = $value;
                    } else {
                        $productOptions[$id] = $value;
                    }
                } elseif (strpos($option['name'], 'super_attribute') !== false) {
                    $id = preg_replace("/[^0-9]+/", "", $option['name']);
                    $value = $option['value'];
                    $superAttributes[$id] = $value;
                }
            }
        }
        $productOptions = array_filter($productOptions);

        $qty = $this->getRequest()->getParam('qty');
        if ($qty < 1) {
            $qty = 1;
        }

        // Préparation des données de personnalisation
        $cartData = [];
        if (isset($connectId) && $connectId) {
            $cartData['connect_id'] = ['connect_id' => $connectId];
        }
        if (isset($data['labels'])) {
            $cartData['labels'] = $data['labels'];
        }

        // Ajouter les options nécessaires
        $buyRequest = [
            'qty' => $qty,
            'product' => $product->getId(),
            'options' => $productOptions,
            'super_attribute' => $superAttributes
        ];

        // Ajouter le produit au panier
        try {
            $item = $this->cart->addProduct($product, $buyRequest);
            
            // Si l'ajout réussit, définir les données personnalisées
            if ($item && !is_string($item)) {
                // Trouver le dernier élément ajouté
                $lastItem = $this->cart->getQuote()->getLastAddedItem();
                if ($lastItem) {
                    // Définir les données du designer
                    $lastItem->setProductdesignerData(json_encode($cartData));
                    
                    // Définir le prix si disponible
                    if (isset($data['finalprice']) && is_numeric($data['finalprice'])) {
                        $finalPrice = floatval($data['finalprice']);
                        if ($finalPrice > 0) {
                            $lastItem->setCustomPrice($finalPrice);
                            $lastItem->setOriginalCustomPrice($finalPrice);
                            $lastItem->setPrice($finalPrice);
                            $lastItem->setBasePrice($finalPrice);
                            $lastItem->getProduct()->setIsSuperMode(true);
                        }
                    }
                    
                    // Sauvegarder l'élément
                    $lastItem->save();
                }
            }
            
            // Sauvegarder le panier et recalculer les totaux
            $this->cart->save();
            $this->cart->getQuote()->collectTotals()->save();
            $result->setData(['success' => true]);
            return $result;
        } catch (\Exception $e) {
            $result->setData([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $result;
        }
    }
}