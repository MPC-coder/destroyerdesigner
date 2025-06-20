<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Laurensmedia\Productdesigner\Model\SavedFactory;
use Laurensmedia\Productdesigner\Model\ResourceModel\Products\CollectionFactory as ProductsCollectionFactory;
use Laurensmedia\Productdesigner\Helper\Tcpdfhelper;
use TCPDF_FONTS;

class Generatepdf extends Action
{

    protected PageFactory $pageFactory;
    protected Filesystem $filesystem;
    protected StoreManagerInterface $storeManager;
    protected SavedFactory $savedFactory;
    protected ProductsCollectionFactory $productsCollectionFactory;
    protected Tcpdfhelper $tcpdfHelper;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param SavedFactory $savedFactory
     * @param ProductsCollectionFactory $productsCollectionFactory
     * @param Tcpdfhelper $tcpdfHelper
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        SavedFactory $savedFactory,
        ProductsCollectionFactory $productsCollectionFactory,
        Tcpdfhelper $tcpdfHelper
    ) {
        $this->pageFactory = $pageFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->savedFactory = $savedFactory;
        $this->productsCollectionFactory = $productsCollectionFactory;
        $this->tcpdfHelper = $tcpdfHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $incrementId = (string) $this->getRequest()->getParam('order', '');
        $sku = (string) $this->getRequest()->getParam('sku', '');
        $params = $this->getRequest()->getParams();

        $mediaDirectory = $this->filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath();

        $saveObject = $this->savedFactory->create()->load($id);

        $productId = (int) $saveObject->getProductId();
        $label = (string) $saveObject->getLabel();
        $storeId = $saveObject->getStoreId(); // peut être null selon ta logique

        $collection = $this->productsCollectionFactory->create();
        $droparea = $collection
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('label', $label)
            ->addFieldToFilter('store_id', $storeId)
            ->getFirstItem();

        if (!$droparea->getId()) {
            $collection = $this->productsCollectionFactory->create();
            $droparea = $collection
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('label', $label)
                ->addFieldToFilter('store_id', ['null' => true])
                ->getFirstItem();
        }

        $file_location = $mediaDirectory . 'productdesigner/svg/' . $saveObject->getSvg();
        $fileContents = file_get_contents($file_location);

        $outerWidth = $saveObject->getData('x2') - $saveObject->getData('x1');
        $svgWidth = $this->getStringBetween($fileContents, 'width="', '"');
        $ratio = $svgWidth / $outerWidth;
        $scaleFactor = $svgWidth / 410;

        $viewBoxX1 = ($saveObject->getData('output_x1') * $scaleFactor) + 2;
        $viewBoxY1 = ($saveObject->getData('output_y1') * $scaleFactor) + 2;
        $viewBoxWidth = ($saveObject->getData('output_x2') * $scaleFactor) - ($saveObject->getData('output_x1') * $scaleFactor);
        $viewBoxHeight = ($saveObject->getData('output_y2') * $scaleFactor) - ($saveObject->getData('output_y1') * $scaleFactor);
        $viewBox = $viewBoxX1 . ' ' . $viewBoxY1 . ' ' . $viewBoxWidth . ' ' . $viewBoxHeight;
        $fileContents = $this->replaceBetween($fileContents, 'viewBox="', '"', $viewBox);
        $fileContents = $this->replaceBetween($fileContents, 'height="', '"', $svgWidth);
        $fileContents = str_replace('&nbsp;', ' ', $fileContents);
        $fileContents = str_replace('"=""', '', $fileContents);

        $doc = new \DOMDocument();
        $doc->loadXML($fileContents);
        $images = $doc->getElementsByTagName('image');
        foreach ($images as $image) {
            if (!$image->getAttributeNode('xlink:href')) {
                continue;
            }
            $url = $image->getAttributeNode('xlink:href')->value;
            if (strpos($url, 'overlayimgs') !== false || strpos($url, 'color_img') !== false) {
                $image->parentNode->removeChild($image);
            }
        }

        $fileContents = $doc->saveXML();
        $fileContents = $this->tcpdfHelper->standardizeFonts($fileContents);
        
        // NOUVELLE IMPLÉMENTATION - Utilisation de SVGFont au lieu d'Inkscape
        // Appel à la méthode prepareSvgForTcpdf qui gère maintenant la conversion via SVGFont
        $fileContents = $this->tcpdfHelper->prepareSvgForTcpdf($fileContents);
        
        
        if ($this->getRequest()->getParam('outputsvg') == 'true') {
            $fileContents = str_replace("\t", ' ', $fileContents);
            $fileContents = preg_replace('/\s+/u', ' ', $fileContents);
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . strlen($fileContents));
            header('Content-Encoding: none');
            header('Content-Type: application/svg');
            header('Content-Disposition: attachment; filename=' . $incrementId . '-' . urldecode($sku) . '-' . rand(1000, 9999) . '.svg');
            echo $fileContents;
            exit;
        } 

        $width = $params['width'] ?? $saveObject->getOutputwidth();
        $height = $params['height'] ?? $saveObject->getOutputheight();

        if ($width == 0 || $height == 0) {
            $width = $saveObject->getData('x2') - $saveObject->getData('x1');
            $height = $saveObject->getData('y2') - $saveObject->getData('y1');
        }

        $orientation = $width <= $height ? 'P' : 'L';

        $pdf = $this->tcpdfHelper->getPdfObject($this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)->getAbsolutePath());
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Product Designer');
        $pdf->SetTitle('Label Designer Output');
        $pdf->SetSubject('Order');
        $pdf->SetKeywords('Label Designer, Product designer');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage($orientation, [$width, $height]);
        $pdf->setPageOrientation($orientation, false, 0);

        $fileContents = '@' . str_replace($this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath(), $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath(), $fileContents);
        $fileContents = str_replace('+', '', $fileContents);
        $pdf->ImageSVG($file = $fileContents, $x = 0, $y = 0, $w = $width, $h = $height, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);

        if ($droparea->getCutoutsvg() != '') {
            $file_location = $mediaDirectory . 'productdesigner/cutoutsvg/' . $droparea->getCutoutsvg();
            $pdf->ImageSVG($file = $file_location, $x = 0, $y = 0, $w = $width, $h = $height, $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
        }

        $randomnumber = $incrementId . '-' . urldecode($sku) . '-' . rand(1000, 9999);
        $pdf->Output($randomnumber . '.pdf', 'I');
        exit;
    }

    private function replaceBetween(string $str, string $needle_start, string $needle_end, string $replacement): string
    {
        $pos = strpos($str, $needle_start);
        $start = $pos === false ? 0 : $pos + strlen($needle_start);

        $pos = strpos($str, $needle_end, $start);
        $end = $pos === false ? strlen($str) : $pos;

        return substr_replace($str, $replacement, $start, $end - $start);
    }

    private function getStringBetween(string $string, string $start, string $end): string
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}