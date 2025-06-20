<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\fonts;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Save extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context)
    {
        parent::__construct($context);
    }

	public function get_string_between($string, $start, $end){
	    $string = ' ' . $string;
	    $ini = strpos($string, $start);
	    if ($ini == 0) return '';
	    $ini += strlen($start);
	    $len = strpos($string, $end, $ini) - $ini;
	    return substr($string, $ini, $len);
	}

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Fonts');

            $id = $this->getRequest()->getParam('id_fonts');
            if ($id) {
                $model->load($id);
                $model->setCreatedAt(date('Y-m-d H:i:s'));
            }
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
			$mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();

			try{
				$uploader = $this->_objectManager->create(
					'Magento\MediaStorage\Model\File\Uploader',
					['fileId' => 'font_ttf']
				);
				$uploader->setAllowedExtensions(['ttf']);
				/** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
				$imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
				$uploader->setAllowRenameFiles(true);
				$uploader->setFilesDispersion(true);
				/** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
				$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
					->getDirectoryRead(DirectoryList::MEDIA);
				$result = $uploader->save($mediaDirectory->getAbsolutePath('productdesigner_fonts'));
					if($result['error']==0)
					{
						$data['font_ttf'] = 'productdesigner_fonts' . $result['file'];
						$data['file'] = 'productdesigner_fonts' . $result['file'];
					}
			} catch (\Exception $e) {
				//unset($data['image']);
            }
			//var_dump($data);die;
			if(isset($data['font_ttf']) && isset($data['font_ttf']['delete']) && $data['font_ttf']['delete'] == '1'){
				$data['font_ttf'] = '';
			}



			if(isset($data['font_ttf']) && $data['font_ttf'] != ''){
				$font_file_location = $mediaPath.$data['font_ttf'];
				$file = str_replace('.ttf', '.png', $font_file_location);
				$im = imagecreatetruecolor(220, 30);
				$white = imagecolorallocate($im, 255, 255, 255);
				$grey = imagecolorallocate($im, 128, 128, 128);
				$black = imagecolorallocate($im, 0, 0, 0);
				imagefilledrectangle($im, 0, 0, 299, 29, $white);
				$text = $data['name'][0];
				imagettftext($im, 12, 0, 0, 25, $black, $font_file_location, $text);
				imagepng($im, $file);
				imagedestroy($im);
				$data['font_image'] = str_replace('.ttf', '.png', $data['font_ttf']);
				$data['image'] = str_replace('.ttf', '.png', $data['font_ttf']);
			}
			
			// Save name
			if(isset($data['file'])){
				$fontFile = $mediaPath.$data['file'].'.ttf';
				$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
					->getDirectoryRead(DirectoryList::MEDIA);
				$path = $mediaDirectory->getAbsolutePath('productdesigner_fonts').'/';
				if(file_exists($fontFile)){
					$text = $data['name'];
					$file = dirname($fontFile).'/'.strtolower($data['fontfamily']).'.png';
					$im = imagecreatetruecolor(220, 30);
					$white = imagecolorallocate($im, 255, 255, 255);
					$grey = imagecolorallocate($im, 128, 128, 128);
					$black = imagecolorallocate($im, 0, 0, 0);
					imagefilledrectangle($im, 0, 0, 299, 29, $white);
					imagettftext($im, 12, 0, 0, 25, $black, $fontFile, $text);
					imagepng($im, $file);
					imagedestroy($im);
					//$data['image'] = $fontFamily.'.png';
				}
			}
			
            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Fonts has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Fonts.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('fonts_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}