<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Laurensmedia\Productdesigner\Block\Index as DesignerBlock;

class Recolorlibraryimage extends Action
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
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var DesignerBlock
     */
    protected DesignerBlock $designerBlock;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param DesignerBlock $designerBlock
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        DesignerBlock $designerBlock
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->designerBlock = $designerBlock;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $imageUrl = $this->getRequest()->getParam('imageUrl');
        $color = $this->getRequest()->getParam('newColor');
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $basePath = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . '/';
        $imagePath = $basePath . str_replace($baseUrl, '', $imageUrl);

        $image_name = str_replace($baseUrl . 'productdesigner_images/', '', $imageUrl);
        $image_name = str_replace('.png', '', $image_name);
        $checkCharacter = substr($image_name, -7, 1);
        if ($checkCharacter == '-') {
            $image_name = substr($image_name, 0, -7);
        }
        $color_image_location = $this->designerBlock->get_media_dir('') . 'productdesigner_images/' . $image_name . '.png';
        $color_image_save_location = str_replace('.png', '-' . str_replace('#', '', $color) . '.png', $color_image_location);
        $returnUrl = str_replace('.png', '-' . str_replace('#', '', $color) . '.png', $baseUrl . 'productdesigner_images/' . $image_name . '.png');

        if (!file_exists($color_image_save_location)) {
            $im = $this->loadImage($color_image_location);
            $original_color = ['red' => 255, 'green' => 255, 'blue' => 255, 'alpha' => 127];
            $replacing_color = $this->hex2rgb($color);
            $colored_image = $this->recolorImage($im, $original_color, $replacing_color);
            imagepng($colored_image, $color_image_save_location);
            imagedestroy($im);
            imagedestroy($colored_image);
        }

        $result->setData(['url' => $returnUrl]);
        return $result;
    }

    public function loadImage(string $imagePath)
    {
        $resource = false;
        if (strstr($imagePath, '.jpg') || strstr($imagePath, '.jpeg')) {
            $resource = @imagecreatefromjpeg($imagePath);
        } elseif (strstr($imagePath, '.png')) {
            $resource = @imagecreatefrompng($imagePath);
        }

        return $resource;
    }

    public function recolorImage($img, array $original_color, array $replacing_color)
    {
        $out = imagecreatetruecolor(imagesx($img), imagesy($img));
        imagesavealpha($out, true);
        imagealphablending($out, false);
        $white = imagecolorallocatealpha($out, 255, 255, 255, 127);
        $rc = imagecolorallocatealpha($out, $replacing_color['red'], $replacing_color['green'], $replacing_color['blue'], 0);
        imagefill($out, 0, 0, $white);
        for ($y = 0; $y < imagesy($img); $y++) {
            for ($x = 0; $x < imagesx($img); $x++) {
                $at = imagecolorat($img, $x, $y);
                $colors = imagecolorsforindex($img, $at);

                if ($colors['alpha'] == '127') {
                    imagesetpixel($out, $x, $y, $white);
                } elseif ($colors['red'] != $original_color['red']
                    || $colors['green'] != $original_color['green']
                    || $colors['blue'] != $original_color['blue']
                    || $colors['alpha'] != $original_color['alpha']) {
                    imagesetpixel($out, $x, $y, $rc);
                } else {
                    imagesetpixel($out, $x, $y, $white);
                }
            }
        }
        return $out;
    }

    public function hex2rgb(string $hex): array
    {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return ['red' => $r, 'green' => $g, 'blue' => $b];
    }
}