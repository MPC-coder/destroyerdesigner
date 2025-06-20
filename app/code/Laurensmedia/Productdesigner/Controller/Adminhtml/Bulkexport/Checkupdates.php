<?php

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Bulkexport;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

class Checkupdates extends Action
{
    public function execute()
    {
        // Récupération des données POST (non utilisées ici)
        $data = $this->getRequest()->getPostValue();
        
        // Utilisation de l'ObjectManager (à remplacer par DI si possible)
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Framework\Filesystem $fileSystem */
        $fileSystem = $objectManager->create(\Magento\Framework\Filesystem::class);
        $mediaPath = $fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();

        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        // Chargement des collections Bulkexport
        /** @var \Laurensmedia\Productdesigner\Model\ResourceModel\Bulkexport\Collection $items */
        $items = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Bulkexport\Collection')
            ->addFieldToFilter('finished', '1');
        
        /** @var \Laurensmedia\Productdesigner\Model\ResourceModel\Bulkexport\Collection $itemsToProcess */
        $itemsToProcess = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Bulkexport\Collection')
            ->addFieldToFilter('finished', ['neq' => '1']);

        $types = ['engraving', 'sublimation', 'printing', 'wood'];
        $output = [
            'items'       => [],
            'downloads'   => [],
            'processing'  => []
        ];

        // Traitement des items finis
        foreach ($items as $item) {
            $itemId = $item->getId();
            $output['items'][$itemId] = [
                'pdf_file_printing'    => $item->getPdfFilePrinting(),
                'pdf_file_sublimation' => $item->getPdfFileSublimation(),
                'pdf_file_engraving'   => $item->getPdfFileEngraving(),
                'pdf_file_wood'        => $item->getPdfFileWood(),
                'store_id'             => $item->getStoreId(),
                'base_url'             => $mediaUrl . 'productdesigner/order_export/'
            ];

            foreach ($types as $type) {
                $file = $item->getData('pdf_file_' . $type);
                if ($file != '') {
                    $output['downloads'][] = 'store-' . $item->getStoreId() . '-type-' . $type;
                }
            }
        }

        // Traitement des items en cours
        foreach ($itemsToProcess as $item) {
            // Charger l'order item correspondant
            $orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
            foreach ($types as $type) {
                $product = $orderItem->getProduct();
                $techValue = $product->getResource()
                    ->getAttribute('technology')
                    ->getFrontend()
                    ->getValue($product);
                // Supprimer les espaces et découper la chaîne par virgule
                $productPrintingTypes = explode(',', str_replace(' ', '', $techValue));
                if (in_array($type, $productPrintingTypes)) {
                    $output['processing'][] = 'store-' . $orderItem->getStoreId() . '-type-' . $type;
                }
            }
        }

        // Éliminer les doublons et valeurs vides
        $output['processing'] = array_unique(array_filter($output['processing']));
        $output['downloads'] = array_unique(array_filter($output['downloads']));

        echo json_encode($output);
        exit;
    }
}
