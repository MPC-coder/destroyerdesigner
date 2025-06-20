<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Rewrite\Checkout\Model;

use Magento\Checkout\Model\Cart as MagentoCart;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Laurensmedia\Productdesigner\Model\ResourceModel\Saved\CollectionFactory as SavedCollectionFactory;
use Laurensmedia\Productdesigner\Model\SavedFactory;
use Magento\Framework\App\ObjectManagerInterface;

class Cart extends MagentoCart
{
    /**
     * @var Filesystem
     */
    protected Filesystem $fileSystem;
    
    /**
     * @var SavedCollectionFactory
     */
    protected SavedCollectionFactory $savedCollectionFactory;
    
    /**
     * @var SavedFactory
     */
    protected SavedFactory $savedFactory;
    
    /**
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;
    
    /**
     * Convert order item to quote item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param true|null $qtyFlag if is null set product qty like in order
     * @return $this
     */
    public function addOrderItem($orderItem, $qtyFlag = null)
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        if ($orderItem->getParentItem() === null) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                /**
                 * We need to reload product in this place, because products
                 * with the same id may have different sets of order attributes.
                 */
                $product = $this->productRepository->getById($orderItem->getProductId(), false, $storeId, true);
            } catch (NoSuchEntityException $e) {
                return $this;
            }
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new DataObject($info);
            if ($qtyFlag === null) {
                $info->setQty($orderItem->getQtyOrdered());
            } else {
                $info->setQty(1);
            }

            // Utiliser les propriétés injectées au lieu de l'ObjectManager
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
            $mediaPath = $fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $quoteItemId = $orderItem->getQuoteItemId();
            $productDesignerData = [];
            $additionalOptions = [];
            
            if ($quoteItemId > 0) {
                $additionalOptions = $orderItem->getProductOptionByCode('additional_options');
                $productDesignerData = json_decode($orderItem->getProductdesignerData() ?? '', true) ?? [];
                
                if (isset($productDesignerData['connect_id']['connect_id'])) {
                    $connectId = $productDesignerData['connect_id']['connect_id'];
                    $newConnectId = mt_rand(0, mt_getrandmax());
                    $productDesignerData['connect_id']['connect_id'] = $newConnectId;
                    
                    // Copy all data for connect id and store in new connect ID
                    $savedDesigns = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
                        ->addFieldToFilter('connect_id', $connectId);
                    
                    foreach ($savedDesigns as $design) {
                        $saveData = $design->getData();
                        unset($saveData['save_id']);
                        $saveData['connect_id'] = $newConnectId;
                        
                        // Save json to file
                        $jsonDir = $mediaPath . 'productdesigner/json/' . date('Y') . '/' . date('m') . '/';
                        if (!file_exists($jsonDir) && !is_dir($jsonDir)) {
                            mkdir($jsonDir, 0777, true);
                        }
                        $jsonFileName = date('U') . '_' . rand(0, 999999) . '.php';
                        $jsonFileLocation = $jsonDir . $jsonFileName;
                        $oldJsonFileLocation = $mediaPath . 'productdesigner/json/' . $saveData['json'];
                        if (file_exists($oldJsonFileLocation)) {
                            copy($oldJsonFileLocation, $jsonFileLocation);
                        }
                        
                        // Save png to file
                        $pngDir = $mediaPath . 'productdesigner/png_export/' . date('Y') . '/' . date('m') . '/';
                        if (!file_exists($pngDir) && !is_dir($pngDir)) {
                            mkdir($pngDir, 0777, true);
                        }
                        $pngFileName = date('U') . '_' . rand(0, 999999) . '.png';
                        $pngFileLocation = $pngDir . $pngFileName;
                        $oldPngFileLocation = $mediaPath . 'productdesigner/png_export/' . $saveData['png'];
                        if (file_exists($oldPngFileLocation)) {
                            copy($oldPngFileLocation, $pngFileLocation);
                        }

                        // Save svg to file
                        $svgDir = $mediaPath . 'productdesigner/svg/' . date('Y') . '/' . date('m') . '/';
                        if (!file_exists($svgDir) && !is_dir($svgDir)) {
                            mkdir($svgDir, 0777, true);
                        }
                        $svgFileName = date('U') . '_' . rand(0, 999999) . '.php';
                        $svgFileLocation = $svgDir . $svgFileName;
                        $oldSvgFileLocation = $mediaPath . 'productdesigner/svg/' . $saveData['svg'];
                        if (file_exists($oldSvgFileLocation)) {
                            copy($oldSvgFileLocation, $svgFileLocation);
                        }
                        
                        $svgOutputFileName = 'output_' . $svgFileName;
                        $svgOutputFileLocation = $svgDir . $svgOutputFileName;
                        $oldSvgFileName = basename($oldSvgFileLocation);
                        $oldSvgFileLocation = $mediaPath . 'productdesigner/svg/' . str_replace(
                            $oldSvgFileName, 
                            'output_' . $oldSvgFileName, 
                            $saveData['svg']
                        );
                        if (file_exists($oldSvgFileLocation)) {
                            copy($oldSvgFileLocation, $svgFileLocation);
                        }

                        $saveData['json'] = date('Y') . '/' . date('m') . '/' . $jsonFileName;
                        $saveData['png'] = date('Y') . '/' . date('m') . '/' . $pngFileName;
                        $saveData['svg'] = date('Y') . '/' . date('m') . '/' . $svgFileName;

                        $objectManager->create('Laurensmedia\Productdesigner\Model\Saved')
                            ->addData($saveData)
                            ->save();
                    }
                    
                    if (is_array($additionalOptions)) {
                        $product->addCustomOption('additional_options', json_encode($additionalOptions));
                    }
                }
            }

            $this->addProduct($product, $info);

            $lastItem = $this->getQuote()->getItemsCollection()->getLastItem();
            if ($lastItem && !empty($productDesignerData)) {
                $lastItem->setProductdesignerData(json_encode($productDesignerData));
            }
        }
        return $this;
    }
}