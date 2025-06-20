<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Upload extends Action
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
     * @var UploaderFactory
     */
    protected UploaderFactory $uploaderFactory;

    /**
     * @var AdapterFactory
     */
    protected AdapterFactory $imageAdapterFactory;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param UploaderFactory $uploaderFactory
     * @param AdapterFactory $imageAdapterFactory
     * @param Filesystem $filesystem
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        UploaderFactory $uploaderFactory,
        AdapterFactory $imageAdapterFactory,
        Filesystem $filesystem,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->imageAdapterFactory = $imageAdapterFactory;
        $this->filesystem = $filesystem;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $mediaUrl = $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);

        $minimalImageWidth = (int) $this->scopeConfig->getValue('lm_productdesigner/lm_pd_settings/lm_pd_imagewidth');
        $minimalImageHeight = (int) $this->scopeConfig->getValue('lm_productdesigner/lm_pd_settings/lm_pd_imageheight');

        if (!isset($_FILES['images']) || !$_FILES['images']['name']) {
            $result->setData(['html' => 'No file entered']);
            return $result;
        }

        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'images[0]']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'svg']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $savePath = $mediaDirectory->getAbsolutePath('productdesigner_uploads') . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
            $uploadResult = $uploader->save($savePath, $this->randomKey(20) . '.' . $uploader->getFileExtension());

            if ($uploadResult['error'] == 0) {
                if ($uploadResult['type'] == 'image/jpeg') {
                    $exif = exif_read_data($uploadResult['path'] . $uploadResult['file']);
                    if (!empty($exif['Orientation'])) {
                        $image = imagecreatefromstring(file_get_contents($uploadResult['path'] . $uploadResult['file']));
                        switch ($exif['Orientation']) {
                            case 8:
                                $image = imagerotate($image, 90, 0);
                                break;
                            case 3:
                                $image = imagerotate($image, 180, 0);
                                break;
                            case 6:
                                $image = imagerotate($image, -90, 0);
                                break;
                        }
                        imagejpeg($image, $uploadResult['path'] . $uploadResult['file']);
                        imagedestroy($image);
                    }
                }

                if ($this->getRequest()->getParam('grayscale') == 'true' && file_exists($uploadResult['path'] . $uploadResult['file'])) {
                    exec("/usr/bin/convert " . $uploadResult['path'] . $uploadResult['file'] . " -set colorspace Gray -separate -average " . $uploadResult['path'] . $uploadResult['file']);
                }

                $url = $mediaUrl . 'productdesigner_uploads/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $uploadResult['file'];
                $ext = pathinfo($url, PATHINFO_EXTENSION);

                if ($ext == 'svg' || $ext == 'SVG') {
                    $width = 300;
                    $height = 300;
                } else {
                    list($width, $height) = getimagesize($savePath . $uploadResult['file']);

                    if ($width < $minimalImageWidth || $height < $minimalImageHeight) {
                        $result->setData(['error' => 'Image quality not good enough']);
                        return $result;
                    }

                    $factor = $width / $height;
                    $width = 300;
                    $height = $width / $factor;
                }

                $result->setData(['image_src' => $url, 'filename' => basename($url)]);
                return $result;
            }
        } catch (\Exception $e) {
            $result->setData(['html' => $e->getMessage()]);
            return $result;
        }

        $result->setData(['html' => 'Upload failed']);
        return $result;
    }

    public function randomKey(int $length): string
    {
        $pool = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $pool[mt_rand(0, count($pool) - 1)];
        }
        return $key;
    }
}