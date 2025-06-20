<?php
namespace Laurensmedia\Productdesigner\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class Reorder implements ObserverInterface
{
    protected RequestInterface $_request;

    public function __construct(
        RequestInterface $request
    ) {
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $orderItem = $observer->getOrderItem();
        
        $productDesignerData = $orderItem->getProductdesignerData();
        
        if (empty($productDesignerData)) {
            return;
        }

        try {
            // Utiliser ObjectManager pour éviter les problèmes de DI
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            
            // Décoder les données de personnalisation
            $designData = json_decode($productDesignerData, true);
            
            if (!$designData || !isset($designData['connect_id']['connect_id'])) {
                // Copier les données existantes si pas de connect_id structuré
                $quoteItem->setProductdesignerData($productDesignerData);
                return;
            }

            $originalConnectId = $designData['connect_id']['connect_id'];
            
            // Générer un nouveau connect_id pour la duplication
            $newConnectId = mt_rand(100000000, 999999999);
            
            // Récupérer tous les designs associés à l'ancien connect_id
            $savedCollectionFactory = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\CollectionFactory');
            $originalDesigns = $savedCollectionFactory->create()
                ->addFieldToFilter('connect_id', $originalConnectId)
                ->load();

            if ($originalDesigns->getSize() > 0) {
                $savedFactory = $objectManager->create('Laurensmedia\Productdesigner\Model\SavedFactory');
                
                // Dupliquer chaque design avec le nouveau connect_id
                foreach ($originalDesigns as $originalDesign) {
                    $newDesign = $savedFactory->create();
                    $newDesign->setData($originalDesign->getData());
                    
                    // Changer l'ID et le connect_id
                    $newDesign->unsetData('save_id');
                    $newDesign->setConnectId($newConnectId);
                    $newDesign->setIsOrdered(0); // Marquer comme non commandé
                    $newDesign->setCustomerId(0); // Reset customer_id pour reorder
                    
                    // Sauvegarder le nouveau design
                    $newDesign->save();
                }
                
                // Mettre à jour les données avec le nouveau connect_id
                $designData['connect_id']['connect_id'] = $newConnectId;
                $updatedProductDesignerData = json_encode($designData);
                
                $quoteItem->setProductdesignerData($updatedProductDesignerData);
                
                // Log du succès
                $logger = $objectManager->get('Psr\Log\LoggerInterface');
                $logger->info('Product Designer - Reorder completed: ' . $originalConnectId . ' -> ' . $newConnectId);
                
            } else {
                // Aucun design trouvé, copier les données originales
                $quoteItem->setProductdesignerData($productDesignerData);
            }
            
        } catch (\Exception $e) {
            // En cas d'erreur, utiliser les données originales
            $quoteItem->setProductdesignerData($productDesignerData);
            
            // Log de l'erreur
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $logger = $objectManager->get('Psr\Log\LoggerInterface');
            $logger->error('Product Designer - Reorder failed: ' . $e->getMessage());
        }
    }
}