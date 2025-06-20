<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Imagecategories;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Laurensmedia\Productdesigner\Model\ImagecategoriesFactory;
use Laurensmedia\Productdesigner\Model\ImagesFactory;
use Laurensmedia\Productdesigner\Model\ResourceModel\Images\CollectionFactory;
use Exception;
use RuntimeException;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Laurensmedia_Productdesigner::imagecategories_save';

    /**
     * @var ImagecategoriesFactory
     */
    protected ImagecategoriesFactory $imagecategoriesFactory;

    /**
     * @var ImagesFactory
     */
    protected ImagesFactory $imagesFactory;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $imagesCollectionFactory;

    /**
     * @param Context $context
     * @param ImagecategoriesFactory $imagecategoriesFactory
     * @param ImagesFactory $imagesFactory
     * @param CollectionFactory $imagesCollectionFactory
     */
    public function __construct(
        Context $context,
        ImagecategoriesFactory $imagecategoriesFactory,
        ImagesFactory $imagesFactory,
        CollectionFactory $imagesCollectionFactory
    ) {
        $this->imagecategoriesFactory = $imagecategoriesFactory;
        $this->imagesFactory = $imagesFactory;
        $this->imagesCollectionFactory = $imagesCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $data = $this->getRequest()->getPostValue();
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($data) {
            $model = $this->imagecategoriesFactory->create();

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }
            
            $images = $data['images'] ?? [];
            // $data['stores'] = isset($data['stores']) ? implode(',', $data['stores']) : '';
            
            $model->setData($data);

            try {
                $model->save();
                
                $catId = $model->getId();
                
                // Remove category from all images first
                $allImages = $this->imagesCollectionFactory->create()
                    ->addFieldToFilter('categorie', ['finset' => $id]);
                
                foreach ($allImages as $image) {
                    $imageId = $image->getId();
                    $imageModel = $this->imagesFactory->create();
                    $imageModel->load($imageId);
                    
                    $categories = explode(',', $imageModel->getCategorie());
                    if (($key = array_search($catId, $categories)) !== false) {
                        unset($categories[$key]);
                    }
                    
                    $categories = implode(',', array_filter(array_unique($categories)));
                    $imageModel->setId($imageId);
                    $imageModel->setData('categorie', $categories);
                    $imageModel->save();
                }

                // Add category to selected images
                foreach ($images as $imageId) {
                    $imageModel = $this->imagesFactory->create();
                    $imageModel->load($imageId);
                    
                    $categories = explode(',', $imageModel->getCategorie());
                    $categories[] = $catId;
                    $categories = implode(',', array_filter(array_unique($categories)));
                    
                    $imageModel->setId($imageId);
                    $imageModel->setData('categorie', $categories);
                    $imageModel->save();
                }

                $this->messageManager->addSuccessMessage(__('The Image Category has been saved.'));
                $this->_getSession()->setFormData(false);
                
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Image Category.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        
        return $resultRedirect->setPath('*/*/');
    }
}