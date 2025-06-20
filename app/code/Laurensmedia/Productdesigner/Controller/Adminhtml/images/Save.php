<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Images;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Laurensmedia\Productdesigner\Model\ImagesFactory;
use Exception;
use RuntimeException;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Laurensmedia_Productdesigner::images_save';

    /**
     * @var ImagesFactory
     */
    protected ImagesFactory $imagesFactory;

    /**
     * @var UploaderFactory
     */
    protected UploaderFactory $uploaderFactory;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var AdapterFactory
     */
    protected AdapterFactory $imageAdapterFactory;

    /**
     * @param Context $context
     * @param ImagesFactory $imagesFactory
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param AdapterFactory $imageAdapterFactory
     */
    public function __construct(
        Context $context,
        ImagesFactory $imagesFactory,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        AdapterFactory $imageAdapterFactory
    ) {
        $this->imagesFactory = $imagesFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->imageAdapterFactory = $imageAdapterFactory;
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
        $postData = $data;
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($data) {
            $model = $this->imagesFactory->create();

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }
            
            $filename = '';
            if (isset($_FILES['image']['tmp_name']) && $_FILES['image']['tmp_name'] != '') {
                try {
                    $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                    
                    $imageAdapter = $this->imageAdapterFactory->create();
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    
                    $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                    $result = $uploader->save($mediaDirectory->getAbsolutePath('productdesigner_images'));
                    
                    if ($result['error'] == 0) {
                        $filename = $result['file'];
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        $data['image'] = 'productdesigner_images' . $result['file'];
                        
                        // Create thumb
                        if ($ext != 'svg') {
                            $imageUrl = $mediaDirectory->getAbsolutePath('productdesigner_images') . '/' .$filename;
                            $imageResized = $mediaDirectory->getAbsolutePath('productdesigner_images') . '/thumbs/' . $filename;
                            
                            $imageObj = $this->imageAdapterFactory->create();
                            $imageObj->open($imageUrl);
                            $imageObj->constrainOnly(true);
                            $imageObj->keepAspectRatio(true);
                            $imageObj->keepFrame(false);
                            $imageObj->keepTransparency(true);
                            $imageObj->backgroundColor([255, 255, 255, 0]); // Transparent background
                            $imageObj->quality(80);
                            $imageObj->setWatermarkImageOpacity(0);
                            $imageObj->resize(300);
                            
                            // Ensure directory exists
                            $thumbDir = $mediaDirectory->getAbsolutePath('productdesigner_images') . '/thumbs';
                            if (!is_dir($thumbDir)) {
                                mkdir($thumbDir, 0755, true);
                            }
                            
                            // Save image      
                            $imageObj->save($imageResized);
                        } else {
                            // Ensure directory exists
                            $thumbDir = $mediaDirectory->getAbsolutePath('productdesigner_images') . '/thumbs';
                            if (!is_dir($thumbDir)) {
                                mkdir($thumbDir, 0755, true);
                            }
                            
                            copy(
                                $mediaDirectory->getAbsolutePath('productdesigner_images') . '/' . $filename, 
                                $mediaDirectory->getAbsolutePath('productdesigner_images') . '/thumbs/' . $filename
                            );
                        }
                    }
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                }
            }
            
            // Handle image deletion
            if (isset($data['image']['delete']) && $data['image']['delete'] == '1') {
                $data['image'] = '';
            }

            $newData = [];
            if ($filename != '') {
                $newData['label'] = $filename;
                $newData['url'] = "thumbs/" . $filename;
            }
            
            if ($id) {
                $newData['id'] = $id;
            }

            if (isset($postData['categories']) && is_array($postData['categories'])) {
                $newData['categorie'] = implode(',', $postData['categories']);
            } else {
                $newData['categorie'] = '';
            }
            
            $newData['scale_factor'] = (isset($postData['scale_factor']) && $postData['scale_factor'] > 0) 
                ? (int)$postData['scale_factor'] 
                : null;
            
            $model->setData($newData);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('The image has been saved.'));
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
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the image.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        
        return $resultRedirect->setPath('*/*/');
    }
}