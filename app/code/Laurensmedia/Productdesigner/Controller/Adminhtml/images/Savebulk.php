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

class Savebulk extends Action implements HttpPostActionInterface
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
     * Save bulk images action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $data = $this->getRequest()->getPostValue();
        $postData = $data;
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        
        if ($data && isset($_FILES['image']['name']) && is_array($_FILES['image']['name'])) {
            $successCount = 0;
            $errorCount = 0;
            
            // Ensure the thumb directory exists
            $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $thumbDir = $mediaDirectory->getAbsolutePath('productdesigner_images') . '/thumbs';
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }
            
            foreach ($_FILES['image']['name'] as $count => $filename) {
                if (empty($filename)) {
                    continue;
                }
                
                try {
                    $uploader = $this->uploaderFactory->create([
                        'fileId' => 'image['.$count.']'
                    ]);
                    
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    
                    $result = $uploader->save($mediaDirectory->getAbsolutePath('productdesigner_images'));
                    
                    if ($result['error'] == 0) {
                        $uploadedFilename = $result['file'];
                        $ext = pathinfo($uploadedFilename, PATHINFO_EXTENSION);
                        
                        // Create thumb
                        if ($ext != 'svg') {
                            $imageUrl = $mediaDirectory->getAbsolutePath('productdesigner_images') . '/' . $uploadedFilename;
                            $imageResized = $mediaDirectory->getAbsolutePath('productdesigner_images') . '/thumbs/' . $uploadedFilename;
                            
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
                            
                            // Save image      
                            $imageObj->save($imageResized);
                        } else {
                            copy(
                                $mediaDirectory->getAbsolutePath('productdesigner_images') . '/' . $uploadedFilename, 
                                $mediaDirectory->getAbsolutePath('productdesigner_images') . '/thumbs/' . $uploadedFilename
                            );
                        }
                        
                        // Save image data to database
                        $model = $this->imagesFactory->create();
                        $imageData = [
                            'label' => $uploadedFilename,
                            'url' => "thumbs/" . $uploadedFilename,
                            'categorie' => isset($postData['categories']) && is_array($postData['categories']) 
                                ? implode(',', $postData['categories']) 
                                : ''
                        ];
                        
                        $model->setData($imageData);
                        $model->save();
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $errorCount++;
                } catch (RuntimeException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $errorCount++;
                } catch (Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Error uploading file: %1', $e->getMessage()));
                    $errorCount++;
                }
            }
            
            if ($successCount > 0) {
                $this->messageManager->addSuccessMessage(
                    __('%1 image(s) have been successfully uploaded.', $successCount)
                );
            }
            
            if ($errorCount > 0) {
                $this->messageManager->addErrorMessage(
                    __('%1 image(s) failed to upload.', $errorCount)
                );
            }
            
            $this->_getSession()->setFormData(false);
        } else {
            $this->messageManager->addErrorMessage(__('No images were uploaded.'));
        }
        
        return $resultRedirect->setPath('*/*/');
    }
}