<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Exception;

class Index extends Template
{
    /**
     * @var Registry
     */
    protected Registry $registry;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        // $this->pageConfig->getTitle()->set(__('Product Designer'));
        return $this;
    }

    /**
     * Get current product ID
     *
     * @return int|null
     */
    public function get_current_product_id()
    {
        $product = $this->registry->registry('current_product');
        return $product ? $product->getId() : null;
    }
    
    /**
     * Get media URL
     *
     * @return string
     */
    public function get_media_url()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }
    
    /**
     * Get base URL
     *
     * @return string
     */
    public function get_base_url()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
    }
    
    /**
     * Get media directory path
     *
     * @param string $dir
     * @return string
     */
    public function get_media_dir($dir)
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        return $mediaDirectory->getAbsolutePath($dir);
    }
    
    /**
     * Get base directory path
     *
     * @param string $dir
     * @return string
     */
    public function get_base_dir($dir)
    {
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::LIB_INTERNAL);
        return $mediaDirectory->getAbsolutePath($dir);
    }

    /**
     * Resize image
     *
     * @param int $newWidth
     * @param string $targetFile
     * @param string $originalFile
     * @return void
     * @throws Exception
     */
    public function resize($newWidth, $targetFile, $originalFile)
    {
        if (!file_exists($originalFile)) {
            return;
        }
        
        $info = getimagesize($originalFile);
        if (!$info) {
            throw new Exception('Invalid image file');
        }
        
        $mime = $info['mime'];
    
        switch ($mime) {
            case 'image/jpeg':
                $image_create_func = 'imagecreatefromjpeg';
                $image_save_func = 'imagejpeg';
                $new_image_ext = 'jpg';
                break;
    
            case 'image/png':
                $image_create_func = 'imagecreatefrompng';
                $image_save_func = 'imagepng';
                $new_image_ext = 'png';
                break;
    
            case 'image/gif':
                $image_create_func = 'imagecreatefromgif';
                $image_save_func = 'imagegif';
                $new_image_ext = 'gif';
                break;
    
            default: 
                throw new Exception('Unknown image type.');
        }
    
        $img = $image_create_func($originalFile);
        if (!$img) {
            throw new Exception('Failed to create image resource');
        }
        
        list($width, $height) = getimagesize($originalFile);
    
        $newHeight = ($height / $width) * $newWidth;
        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        if (!$tmp) {
            throw new Exception('Failed to create true color image');
        }
        
        imagesavealpha($tmp, true);
        imagealphablending($tmp, false);
        // Important part - set transparent background
        $white = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
        imagefill($tmp, 0, 0, $white);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }
        $image_save_func($tmp, "$targetFile");
        
        // Free memory
        imagedestroy($img);
        imagedestroy($tmp);
    }
}