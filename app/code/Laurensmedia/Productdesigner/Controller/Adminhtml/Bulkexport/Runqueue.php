<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Bulkexport;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Runqueue extends \Magento\Backend\App\Action
{
    private $tcpdfHelper = null;

    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);
    }

    protected function getTcpdfHelper()
    {
        if ($this->tcpdfHelper === null) {
            $this->tcpdfHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Laurensmedia\Productdesigner\Helper\Tcpdfhelper::class);
        }
        return $this->tcpdfHelper;
    }

    public function execute()
    {
        ini_set('memory_limit', "2048M");
        error_reporting(0);
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
        $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        
        $pdfPath = $mediaPath . 'productdesigner/order_export/' . date('Y') . '/' . date('m') . '/';
        if (!file_exists($pdfPath) && !is_dir($pdfPath)) {
            mkdir($pdfPath, 0777, true);
        }
        
        $firstItem = $this->getFirstBulkExportItem($objectManager);
        if (!$firstItem->getId()) {
            return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
                ->setPath('productdesigner/bulkexport');
        }
        
        $exportParams = $this->getExportParameters($firstItem);
        
        $allItems = $this->getAllBulkExportItems($objectManager, $exportParams);
        
        $fileNames = $this->generateFileNames($objectManager, $allItems, $pdfPath);
        
        $layout = $this->_view->getLayout();
        $block = $layout->createBlock('Laurensmedia\Productdesigner\Block\Index');
        $helper = $this->getTcpdfHelper();
        
        match ($exportParams['exportCombining']) {
            'multiple_on_page'        => $this->processMultipleOnPage($allItems, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper),
            'new_page'                => $this->processNewPage($allItems, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper),
            'new_pdf'                 => $this->processNewPdf($firstItem, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper),
            'new_line'                => $this->processNewLine($allItems, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper),
            'new_line_with_summary'   => $this->processNewLineWithSummary($allItems, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper),
            'wood_board'              => $this->processWoodBoard($allItems, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper),
            default                   => null, // Optionnel : à inclure si un cas non prévu doit être géré
        };
        
        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->setPath('productdesigner/bulkexport');
    }
    
    protected function getFirstBulkExportItem($objectManager)
    {
        return $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Bulkexport\Collection')
            ->addFieldToFilter('finished', 0)
            ->setPageSize(1)
            ->setCurPage(1)
            ->load()
            ->getFirstItem();
    }
    
    protected function getExportParameters($firstItem)
    {
        return [
            'exportCombining' => $firstItem->getExportCombining(),
            'emptyDesign' => $firstItem->getEmptyDesign(),
            'pdfWidth' => $firstItem->getPdfWidth(),
            'pdfHeight' => $firstItem->getPdfHeight(),
            'marginVertical' => $firstItem->getPdfMarginVertical(),
            'marginHorizontal' => $firstItem->getPdfMarginHorizontal(),
            'itemsMarginVertical' => $firstItem->getPdfMarginItemsVertical(),
            'itemsMarginHorizontal' => $firstItem->getPdfMarginItemsHorizontal()
        ];
    }
    
    protected function getAllBulkExportItems($objectManager, $exportParams)
    {
        $allItems = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Bulkexport\Collection')
            ->addFieldToFilter('export_combining', $exportParams['exportCombining'])
            ->addFieldToFilter('empty_design', $exportParams['emptyDesign'])
            ->addFieldToFilter('pdf_margin_vertical', $exportParams['marginVertical'])
            ->addFieldToFilter('pdf_margin_horizontal', $exportParams['marginHorizontal'])
            ->addFieldToFilter('pdf_margin_items_vertical', $exportParams['itemsMarginVertical'])
            ->addFieldToFilter('pdf_margin_items_horizontal', $exportParams['itemsMarginHorizontal']);
            
        if ($exportParams['pdfWidth'] > 0 && $exportParams['pdfHeight'] > 0) {
            $allItems = $allItems
                ->addFieldToFilter('pdf_width', $exportParams['pdfWidth'])
                ->addFieldToFilter('pdf_height', $exportParams['pdfHeight']);
        }
        
        return $allItems;
    }
    
    protected function generateFileNames($objectManager, $allItems, $pdfPath)
    {
        $firstOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getFirstItem()->getOrderId())->getRealOrderId();
        $lastOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getLastItem()->getOrderId())->getRealOrderId();
        
        $random = $firstOrderId . '-' . $lastOrderId;
        
        return [
            'pdfFile' => $pdfPath . $random . '.pdf',
            'dbFile' => date('Y') . '/' . date('m') . '/' . $random . '.pdf'
        ];
    }
    
    protected function loadSavedItems($objectManager, $connectId)
    {
        return $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Saved\Collection')
            ->addFieldToFilter('connect_id', $connectId)
            ->setPageSize(4)
            ->setCurPage(1)
            ->load();
    }
    
    protected function markItemAsProcessed($objectManager, $item, $dbFile, $type = null, $storeId = null)
    {
        $data = ['finished' => 1, 'pdf_file' => $dbFile];
        
        if ($type !== null) {
            $data['pdf_file_' . $type] = $dbFile;
        }
        
        if ($storeId !== null) {
            $data['store_id'] = $storeId;
        }
        
        $objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport')
            ->load($item->getId())
            ->setData($data)
            ->setId($item->getId())
            ->save();
    }
    
    protected function loadSvgContent($filePath)
    {
        if (file_exists($filePath)) {
            return file_get_contents($filePath);
        }
        
        error_log("SVG file not found: " . $filePath);
        return '';
    }
    
    protected function processSvgWithSvgFont($fileContents)
    {
        if (empty($fileContents)) {
            return '';
        }
        
        try {
            return $this->getTcpdfHelper()->prepareSvgForTcpdf($fileContents);
        } catch (\Exception $e) {
            error_log("Error processing SVG with SVGFont: " . $e->getMessage());
            return $fileContents;
        }
    }
    
    protected function saveSvgContent($fileContents, $outputPath)
    {
        try {
            return file_put_contents($outputPath, $fileContents) !== false;
        } catch (\Exception $e) {
            error_log("Error saving SVG content: " . $e->getMessage());
            return false;
        }
    }
    
    protected function svgHasDesign($fileContents)
    {
        if (empty($fileContents)) {
            return false;
        }
        
        if (strpos($fileContents, 'path') === false && strpos($fileContents, 'image') === false) {
            return false;
        }
        
        $hasOverlay = strpos($fileContents, 'overlayimgs') !== false;
        $hasColorImg = strpos($fileContents, 'color_img') !== false;
        $hasText = strpos($fileContents, 'text') !== false;
        $imageCount = substr_count($fileContents, 'image');
        
        if ($hasOverlay && !$hasColorImg && $imageCount <= 1 && !$hasText) {
            return false;
        }
        
        if (!$hasOverlay && $hasColorImg && $imageCount <= 1 && !$hasText) {
            return false;
        }
        
        if ($hasOverlay && $hasColorImg && $imageCount <= 2 && !$hasText) {
            return false;
        }
        
        return true;
    }
    
    protected function prepareSvgFile($file, $mediaPath, $mediaUrl)
    {
        $pathInfo = pathinfo($mediaPath . 'productdesigner/svg/' . $file);
        $fileName = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        $dirName = $pathInfo['dirname'];
        
        $svgPath = $dirName . '/' . $fileName . '.' . $extension;
        $fileContents = $this->loadSvgContent($svgPath);
        
        if (empty($fileContents)) {
            return [
                'success' => false,
                'fileName' => $fileName,
                'extension' => $extension,
                'fileContents' => ''
            ];
        }
        
        $processedContent = $this->processSvgWithSvgFont($fileContents);
        
        $outputPath = $dirName . '/' . $fileName . '-svgfont.' . $extension;
        $success = $this->saveSvgContent($processedContent, $outputPath);
        
        $debugPath = $mediaPath . 'productdesigner/tmp/' . $fileName . '-debug.' . $extension;
        $this->saveSvgContent($processedContent, $debugPath);
        
        return [
            'success' => $success,
            'fileName' => $fileName,
            'extension' => $extension,
            'originalPath' => $svgPath,
            'processedPath' => $outputPath,
            'fileContents' => $processedContent,
            'hasDesign' => $this->svgHasDesign($fileContents)
        ];
    }
    
    protected function calculateViewBox($productSide, $fileContents)
    {
        $outerWidth = (float)$productSide->getData('x2') - (float)$productSide->getData('x1');
        $svgWidthRaw = $this->get_string_between($fileContents, 'width="', '"');
        $svgWidth = (float)preg_replace('/[^\d.]/', '', $svgWidthRaw);
        
        if ($outerWidth <= 0 || $svgWidth <= 0) {
            return [
                'ratio' => 1.0,
                'scaleFactor' => 1.0,
                'viewBox' => '0 0 100 100',
                'svgWidth' => 100
            ];
        }
        
        $ratio = $svgWidth / $outerWidth;
        $scaleFactor = $svgWidth / 410;
        
        $viewBoxX1 = ($productSide->getData('output_x1') * $scaleFactor) + 2;
        $viewBoxY1 = ($productSide->getData('output_y1') * $scaleFactor) + 2;
        $viewBoxWidth = ($productSide->getData('output_x2') * $scaleFactor) - ($productSide->getData('output_x1') * $scaleFactor);
        $viewBoxHeight = ($productSide->getData('output_y2') * $scaleFactor) - ($productSide->getData('output_y1') * $scaleFactor);
        
        return [
            'ratio' => $ratio,
            'scaleFactor' => $scaleFactor,
            'viewBox' => $viewBoxX1 . ' ' . $viewBoxY1 . ' ' . $viewBoxWidth . ' ' . $viewBoxHeight,
            'svgWidth' => $svgWidth
        ];
    }
    
    protected function prepareSvgForPdf($fileContents, $viewBoxParams, $mediaUrl, $mediaPath)
    {
        if (empty($fileContents)) {
            return '@';
        }
        
        $fileContents = $this->replace_between($fileContents, 'viewBox="', '"', $viewBoxParams['viewBox']);
        $fileContents = $this->replace_between($fileContents, 'height="', '"', $viewBoxParams['svgWidth']);
        
        try {
            $doc = new \DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadXML($fileContents);
            
            $images = $doc->getElementsByTagName('image');
            foreach ($images as $image) {
                $attr = $image->getAttributeNode('xlink:href');
                if (!$attr) {
                    continue;
                }
                $url = $attr->value;
                if (strpos($url, 'overlayimgs') !== false || strpos($url, 'color_img') !== false) {
                    $image->parentNode->removeChild($image);
                }
            }
            
            $fileContents = $doc->saveXML();
        } catch (\Exception $e) {
            error_log("Error cleaning SVG: " . $e->getMessage());
        }
        
        $fileContents = '@' . str_replace($mediaUrl, $mediaPath, $fileContents);
        $fileContents = str_replace('+', '', $fileContents);
        
        return $fileContents;
    }
    
    protected function processMultipleOnPage($allItems, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper)
    {
        $pdf = $helper->getPdfObject($block->get_base_dir(''));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $curXPos = 0;
        $curYPos = 0;
        $maxHeight = 0;
        $needNewPage = true;
        
        foreach ($allItems as $item) {
            $prevItem = null;
            
            $orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
            $mediaUrl = $storeManager->getStore($orderItem->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $qty = (float)$orderItem->getQtyOrdered();
            
            $orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
            $connectId = $orderItemOptions['connect_id']['connect_id'];
            
            if ($connectId > 0) {
                $savedItems = $this->loadSavedItems($objectManager, $connectId);
                
                for ($i = 0; $i < $qty; $i++) {
                    foreach ($savedItems as $productSide) {
                        $file = $productSide->getSvg();
                        $fileName = basename($file);
                        $outputFileName = 'output_' . $fileName;
                        $file = str_replace($fileName, $outputFileName, $file);
                        
                        $svgFilePath = $mediaPath . 'productdesigner/svg/' . $file;
                        $fileContents = $this->loadSvgContent($svgFilePath);
                        
                        if (!$this->svgHasDesign($fileContents)) {
                            if ($exportParams['emptyDesign'] == 'do_not_print' || !$prevItem) {
                                continue;
                            } elseif ($exportParams['emptyDesign'] == 'print_other_side') {
                                $productSide = $prevItem;
                                $file = $productSide->getSvg();
                                $fileName = basename($file);
                                $outputFileName = 'output_' . $fileName;
                                $file = str_replace($fileName, $outputFileName, $file);
                                $svgFilePath = $mediaPath . 'productdesigner/svg/' . $file;
                                $fileContents = $this->loadSvgContent($svgFilePath);
                            }
                        } else {
                            $prevItem = $productSide;
                        }
                        
                        if (!$exportParams['pdfWidth'] > 0 || !$exportParams['pdfHeight'] > 0) {
                            $exportParams['pdfWidth'] = $productSide->getOutputwidth();
                            $exportParams['pdfHeight'] = $productSide->getOutputheight();
                        }
                        
                        $outputWidth = $productSide->getOutputwidth();
                        $outputHeight = $productSide->getOutputheight();
                        
                        if ($outputHeight > $maxHeight) {
                            $maxHeight = $outputHeight;
                        }
                        
                        if ($curXPos == 0) {
                            $curXPos = $exportParams['marginHorizontal'];
                        }
                        
                        if ($curYPos == 0) {
                            $curYPos = $exportParams['marginVertical'];
                        }
                        
                        if (($curXPos + $outputWidth + $exportParams['marginHorizontal']) > $exportParams['pdfWidth']) {
                            $curXPos = $exportParams['marginHorizontal'];
                            $curYPos = $curYPos + $maxHeight + $exportParams['itemsMarginHorizontal'];
                            $maxHeight = 0;
                        }
                        
                        if (($curYPos + $outputHeight) > $exportParams['pdfHeight']) {
                            $needNewPage = true;
                        }
                        
                        if ($needNewPage) {
                            $orientation = ($exportParams['pdfWidth'] < $exportParams['pdfHeight']) ? 'P' : 'L';
                            $pdf->AddPage($orientation, [$exportParams['pdfWidth'], $exportParams['pdfHeight']]);
                            $pdf->setPageOrientation($orientation, false, 0);
                            $needNewPage = false;
                            $curXPos = $exportParams['marginHorizontal'];
                            $curYPos = $exportParams['marginVertical'];
                        }
                        
                        $viewBoxParams = $this->calculateViewBox($productSide, $fileContents);
                        $processedSvgContent = $this->prepareSvgForPdf($fileContents, $viewBoxParams, $mediaUrl, $mediaPath);
                        
                        $pdf->ImageSVG(
                            $file = $processedSvgContent,
                            $x = $curXPos,
                            $y = $curYPos,
                            $w = $outputWidth,
                            $h = $outputHeight,
                            $link = '',
                            $align = '',
                            $palign = '',
                            $border = 0,
                            $fitonpage = false
                        );
                        
                        $curXPos = $curXPos + $outputWidth + $exportParams['itemsMarginHorizontal'];
                    }
                }
            }
            
            $this->markItemAsProcessed($objectManager, $item, $fileNames['dbFile']);
        }
        
        if (count($allItems) > 0) {
            $pdf->Output($fileNames['pdfFile'], 'F');
        }
    }
    
    protected function processNewPage($allItems, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper)
    {
        $pdf = $helper->getPdfObject($block->get_base_dir(''));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $needNewPage = true;
        
        foreach ($allItems as $item) {
            $prevItem = null;
            
            $orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
            $mediaUrl = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')
                ->getStore($orderItem->getStoreId())
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                
            $qty = (float)$orderItem->getQtyOrdered();
            
            $orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
            $connectId = $orderItemOptions['connect_id']['connect_id'];
            
            if ($connectId > 0) {
                $savedItems = $this->loadSavedItems($objectManager, $connectId);
                
                for ($i = 0; $i < $qty; $i++) {
                    foreach ($savedItems as $productSide) {
                        $file = $productSide->getSvg();
                        $fileName = basename($file);
                        $outputFileName = 'output_' . $fileName;
                        $file = str_replace($fileName, $outputFileName, $file);
                        
                        $svgFilePath = $mediaPath . 'productdesigner/svg/' . $file;
                        $fileContents = $this->loadSvgContent($svgFilePath);
                        
                        if (!$this->svgHasDesign($fileContents)) {
                            if ($exportParams['emptyDesign'] == 'do_not_print' || !$prevItem) {
                                continue;
                            } elseif ($exportParams['emptyDesign'] == 'print_other_side') {
                                $productSide = $prevItem;
                                $file = $productSide->getSvg();
                                $fileName = basename($file);
                                $outputFileName = 'output_' . $fileName;
                                $file = str_replace($fileName, $outputFileName, $file);
                                $svgFilePath = $mediaPath . 'productdesigner/svg/' . $file;
                                $fileContents = $this->loadSvgContent($svgFilePath);
                            }
                        } else {
                            $prevItem = $productSide;
                        }
                        
                        $pdfWidth = $productSide->getOutputwidth() + (2 * $exportParams['marginHorizontal']);
                        $pdfHeight = $productSide->getOutputheight() + (2 * $exportParams['marginVertical']);
                        $outputWidth = $productSide->getOutputwidth();
                        $outputHeight = $productSide->getOutputheight();
                        
                        $orientation = ($pdfWidth < $pdfHeight) ? 'P' : 'L';
                        $pdf->AddPage($orientation, [$pdfWidth, $pdfHeight]);
                        $pdf->setPageOrientation($orientation, false, 0);
                        $curXPos = $exportParams['marginHorizontal'];
                        $curYPos = $exportParams['marginVertical'];
                        
                        $viewBoxParams = $this->calculateViewBox($productSide, $fileContents);
                        $processedSvgContent = $this->prepareSvgForPdf($fileContents, $viewBoxParams, $mediaUrl, $mediaPath);
                        
                        $pdf->ImageSVG(
                            $file = $processedSvgContent,
                            $x = $curXPos,
                            $y = $curYPos,
                            $w = $outputWidth,
                            $h = $outputHeight,
                            $link = '',
                            $align = '',
                            $palign = '',
                            $border = 0,
                            $fitonpage = false
                        );
                    }
                }
            }
            
            $this->markItemAsProcessed($objectManager, $item, $fileNames['dbFile']);
        }
        
        if (count($allItems) > 0) {
            $pdf->Output($fileNames['pdfFile'], 'F');
        }
    }
    
    protected function processNewPdf($item, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper)
    {
        $pdf = $helper->getPdfObject($block->get_base_dir(''));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $needNewPage = true;
        
        $orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
        $mediaUrl = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')
            ->getStore($orderItem->getStoreId())
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            
        $qty = (float)$orderItem->getQtyOrdered();
        
        $orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
        $connectId = $orderItemOptions['connect_id']['connect_id'];
        
        if ($connectId > 0) {
            $savedItems = $this->loadSavedItems($objectManager, $connectId);
            
            for ($i = 0; $i < $qty; $i++) {
                foreach ($savedItems as $productSide) {
                    $file = $productSide->getSvg();
                    $fileName = basename($file);
                    $outputFileName = 'output_' . $fileName;
                    $file = str_replace($fileName, $outputFileName, $file);
                    
                    $pdfWidth = $productSide->getOutputwidth() + (2 * $exportParams['marginHorizontal']);
                    $pdfHeight = $productSide->getOutputheight() + (2 * $exportParams['marginVertical']);
                    $outputWidth = $productSide->getOutputwidth();
                    $outputHeight = $productSide->getOutputheight();
                    
                    if ($needNewPage) {
                        $orientation = ($pdfWidth < $pdfHeight) ? 'P' : 'L';
                        $pdf->AddPage($orientation, [$pdfWidth, $pdfHeight]);
                        $pdf->setPageOrientation($orientation, false, 0);
                        $curXPos = $exportParams['marginHorizontal'];
                        $curYPos = $exportParams['marginVertical'];
                    }
                    
                    $svgFilePath = $mediaPath . 'productdesigner/svg/' . $file;
                    $fileContents = $this->loadSvgContent($svgFilePath);
                    
                    $viewBoxParams = $this->calculateViewBox($productSide, $fileContents);
                    $processedSvgContent = $this->prepareSvgForPdf($fileContents, $viewBoxParams, $mediaUrl, $mediaPath);
                    
                    $pdf->ImageSVG(
                        $file = $processedSvgContent,
                        $x = $curXPos,
                        $y = $curYPos,
                        $w = $outputWidth,
                        $h = $outputHeight,
                        $link = '',
                        $align = '',
                        $palign = '',
                        $border = 0,
                        $fitonpage = false
                    );
                }
            }
        }
        
        $this->markItemAsProcessed($objectManager, $item, $fileNames['dbFile']);
        
        $pdf->Output($fileNames['pdfFile'], 'F');
    }
    
    protected function processNewLine($allItems, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper)
    {
        $pdf = $helper->getPdfObject($block->get_base_dir(''));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $lastOrderId = '';
        $needNewLine = false;
        $curXPos = 0;
        $curYPos = 0;
        $maxHeight = 0;
        $needNewPage = true;
        
        foreach ($allItems as $item) {
            $prevItem = null;
            
            $orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
            $mediaUrl = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')
                ->getStore($orderItem->getStoreId())
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                
            $orderId = $orderItem->getOrderId();
            if ($orderId != $lastOrderId) {
                $needNewLine = true;
            }
            
            if ($lastOrderId == '') {
                $curYPos = $exportParams['marginVertical'] + 16;
            }
            
            $lastOrderId = $orderId;
            $realOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($orderId)->getRealOrderId();
            $qty = (float)$orderItem->getQtyOrdered();
            
            $orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
            $connectId = $orderItemOptions['connect_id']['connect_id'];
            
            if ($connectId > 0) {
                $savedItems = $this->loadSavedItems($objectManager, $connectId);
                
                for ($i = 0; $i < $qty; $i++) {
                    foreach ($savedItems as $productSide) {
                        $file = $productSide->getSvg();
                        $fileName = basename($file);
                        $outputFileName = 'output_' . $fileName;
                        $file = str_replace($fileName, $outputFileName, $file);
                        
                        $svgFilePath = $mediaPath . 'productdesigner/svg/' . $file;
                        $fileContents = $this->loadSvgContent($svgFilePath);
                        
                        if (!$this->svgHasDesign($fileContents)) {
                            if ($exportParams['emptyDesign'] == 'do_not_print' || !$prevItem) {
                                continue;
                            } elseif ($exportParams['emptyDesign'] == 'print_other_side') {
                                $productSide = $prevItem;
                                $file = $productSide->getSvg();
                                $fileName = basename($file);
                                $outputFileName = 'output_' . $fileName;
                                $file = str_replace($fileName, $outputFileName, $file);
                                $svgFilePath = $mediaPath . 'productdesigner/svg/' . $file;
                                $fileContents = $this->loadSvgContent($svgFilePath);
                            }
                        } else {
                            $prevItem = $productSide;
                        }
                        
                        if (!$exportParams['pdfWidth'] > 0 || !$exportParams['pdfHeight'] > 0) {
                            $exportParams['pdfWidth'] = $productSide->getOutputwidth();
                            $exportParams['pdfHeight'] = $productSide->getOutputheight();
                        }
                        
                        $outputWidth = $productSide->getOutputwidth();
                        $outputHeight = $productSide->getOutputheight();
                        
                        if ($outputHeight > $maxHeight) {
                            $maxHeight = $outputHeight;
                        }
                        
                        if ($curXPos == 0) {
                            $curXPos = $exportParams['marginHorizontal'];
                        }
                        
                        if ($curYPos == 0) {
                            $curYPos = $exportParams['marginVertical'];
                        }
                        
                        if (($curXPos + $outputWidth + $exportParams['marginHorizontal']) > $exportParams['pdfWidth'] || $needNewLine) {
                            $curXPos = $exportParams['marginHorizontal'];
                            $curYPos = $curYPos + $maxHeight + $exportParams['itemsMarginHorizontal'];
                            $maxHeight = 0;
                        }
                        
                        if ($needNewLine) {
                            $curYPos += 8;
                        }
                        
                        if (($curYPos + $outputHeight) > $exportParams['pdfHeight']) {
                            $needNewPage = true;
                        }
                        
                        if ($needNewPage) {
                            $orientation = ($exportParams['pdfWidth'] < $exportParams['pdfHeight']) ? 'P' : 'L';
                            $pdf->AddPage($orientation, [$exportParams['pdfWidth'], $exportParams['pdfHeight']]);
                            $pdf->setPageOrientation($orientation, false, 0);
                            $needNewPage = false;
                            $curXPos = $exportParams['marginHorizontal'];
                            $curYPos = $exportParams['marginVertical'] + 8;
                        }
                        
                        if ($needNewLine) {
                            $pdf->Text(
                                $x = $curXPos,
                                $y = ($curYPos - 8),
                                $txt = $realOrderId
                            );
                        }
                        
                        $needNewLine = false;
                        
                        $viewBoxParams = $this->calculateViewBox($productSide, $fileContents);
                        $processedSvgContent = $this->prepareSvgForPdf($fileContents, $viewBoxParams, $mediaUrl, $mediaPath);
                        
                        $pdf->ImageSVG(
                            $file = $processedSvgContent,
                            $x = $curXPos,
                            $y = $curYPos,
                            $w = $outputWidth,
                            $h = $outputHeight,
                            $link = '',
                            $align = '',
                            $palign = '',
                            $border = 0,
                            $fitonpage = false
                        );
                        
                        $curXPos = $curXPos + $outputWidth + $exportParams['itemsMarginHorizontal'];
                    }
                }
            }
            
            $this->markItemAsProcessed($objectManager, $item, $fileNames['dbFile']);
        }
        
        if (count($allItems) > 0) {
            $pdf->Output($fileNames['pdfFile'], 'F');
        }
    }
    
    protected function processNewLineWithSummary($allItems, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper)
    {
        $firstOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getFirstItem()->getOrderId())->getRealOrderId();
        $lastOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getLastItem()->getOrderId())->getRealOrderId();
        
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $types = ['engraving', 'sublimation', 'printing', 'wood'];
        $stores = $storeManager->getStores();
        
        foreach ($types as $type) {
            foreach ($stores as $store) {
                $random = $store->getStoreId() . '-' . $firstOrderId . '-' . $lastOrderId . '-' . $type;
                $pdfFile = $mediaPath . 'productdesigner/order_export/' . date('Y') . '/' . date('m') . '/' . $random . '.pdf';
                $dbFile = date('Y') . '/' . date('m') . '/' . $random . '.pdf';
                
                $pdf = $helper->getPdfObject($block->get_base_dir(''));
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                
                $lastOrderId = '';
                $needNewLine = false;
                $curXPos = 0;
                $curYPos = 0;
                $maxHeight = 0;
                
                $this->createSummaryPage(
                    $pdf, 
                    $allItems, 
                    $type, 
                    $store, 
                    $objectManager, 
                    $exportParams, 
                    $curXPos, 
                    $curYPos
                );
                
                $orderData = $this->prepareOrderData($allItems, $objectManager, $type, $store);
                
                foreach ($allItems as $item) {
                    $orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
                    $mediaUrl = $storeManager->getStore($orderItem->getStoreId())->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                    
                    $product = $orderItem->getProduct();
                    $technologyAttribute = $product->getResource()->getAttribute('technology');
                    $productPrintingTypes = $technologyAttribute 
                        ? array_filter(explode(',', str_replace(' ', '', $technologyAttribute->getFrontend()->getValue($product))))
                        : [];
                        
                    if (!in_array($type, $productPrintingTypes)) {
                        continue;
                    }
                    
                    if ($store->getStoreId() != $orderItem->getStoreId()) {
                        continue;
                    }
                    
                    $_resource = $objectManager->create('Magento\Catalog\Model\Product')->getResource();
                    $enableRotate = $_resource->getAttributeRawValue(
                        $orderItem->getProductId(),
                        'pd_rotate_in_export',
                        $storeManager->getStore()
                    );
                    
                    $orderId = $orderItem->getOrderId();
                    if ($orderId != $lastOrderId) {
                        $needNewLine = true;
                    }
                    
                    $needNewLine = true;
                    $lastOrderId = $orderId;
                    
                    $lastOrder = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
                    $realOrderId = $lastOrder->getRealOrderId();
                    $shippingData = $lastOrder->getShippingAddress()->getData();
                    $city = $shippingData['lastname'];
                    
                    $qty = (float)$orderItem->getQtyOrdered();
                    if ($qty < 1 && $orderItem->getQtyInvoiced() > 0) {
                        $qty = (float)$orderItem->getQtyInvoiced();
                    }
                    
                    $orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
                    $connectId = $orderItemOptions['connect_id']['connect_id'];
                    
                    if ($connectId > 0) {
                        $savedItems = $this->loadSavedItems($objectManager, $connectId);
                        
                        if (in_array('sublimation', $productPrintingTypes) && in_array('engraving', $productPrintingTypes)) {
                            if ($type == 'sublimation') {
                                $savedItems = [$savedItems->getFirstItem()];
                            } elseif ($type == 'engraving') {
                                $savedItems->setPageSize(1)->setCurPage(2);
                                if (count($savedItems) < 1) {
                                    $savedItems = [];
                                }
                            }
                        }
                        
                        $skuCounter = 0;
                        for ($i = 0; $i < $qty; $i++) {
                            $skuCounter++;
                            
                            $amountOfSides = count($savedItems);
                            $sidesTotalWidth = 0;
                            foreach ($savedItems as $productSide) {
                                $outputWidth = $productSide->getOutputwidth();
                                $outputHeight = $productSide->getOutputheight();
                                
                                if ($enableRotate == '1') {
                                    $temp = $outputWidth;
                                    $outputWidth = $outputHeight;
                                    $outputHeight = $temp;
                                }
                                
                                $sidesTotalWidth += $outputWidth + $exportParams['itemsMarginHorizontal'];
                            }
                            
                            foreach ($savedItems as $productSide) {
                                $overlayImage = $this->getOverlayImage($objectManager, $productSide, $mediaPath);
                                
                                $file = $productSide->getSvg();
                                $fileName = basename($file);
                                $outputFileName = 'output_' . $fileName;
                                $file = str_replace($fileName, $outputFileName, $file);
                                
                                $svgFilePath = $mediaPath . 'productdesigner/svg/' . $file;
                                $fileContents = $this->loadSvgContent($svgFilePath);
                                
                                $svgInfo = $this->prepareSvgFile($file, $mediaPath, $mediaUrl);
                                
                                if (!$svgInfo['hasDesign']) {
                                    if ($exportParams['emptyDesign'] == 'do_not_print' || !$prevItem) {
                                        continue;
                                    } elseif ($exportParams['emptyDesign'] == 'print_other_side') {
                                        $productSide = $prevItem;
                                        $file = $productSide->getSvg();
                                        $fileName = basename($file);
                                        $outputFileName = 'output_' . $fileName;
                                        $file = str_replace($fileName, $outputFileName, $file);
                                    }
                                } else {
                                    $prevItem = $productSide;
                                }
                                
                                if (!$exportParams['pdfWidth'] > 0 || !$exportParams['pdfHeight'] > 0) {
                                    $exportParams['pdfWidth'] = $productSide->getOutputwidth();
                                    $exportParams['pdfHeight'] = $productSide->getOutputheight();
                                }
                                
                                $outputWidth = $productSide->getOutputwidth();
                                $outputHeight = $productSide->getOutputheight();
                                
                                if ($outputHeight > $maxHeight) {
                                    $maxHeight = $outputHeight;
                                }
                                
                                $origOutputWidth = $outputWidth;
                                $origOutputHeight = $outputHeight;
                                
                                if ($enableRotate == '1') {
                                    $outputWidth = $origOutputHeight;
                                    $outputHeight = $origOutputWidth;
                                    $maxHeight = $origOutputWidth;
                                }
                                
                                if ($curXPos == 0) {
                                    $curXPos = $exportParams['marginHorizontal'];
                                }
                                
                                if ($curYPos == 0) {
                                    $curYPos = $exportParams['marginVertical'];
                                }
                                
                                $neededNewLine = false;
                                if (($curXPos + $outputWidth + $exportParams['marginHorizontal']) > $exportParams['pdfWidth'] || $needNewLine) {
                                    $curXPos = $exportParams['marginHorizontal'];
                                    $curYPos = $curYPos + $maxHeight + $exportParams['itemsMarginHorizontal'] + 2;
                                    $maxHeight = 0;
                                    $skuCounter = 0;
                                    $neededNewLine = true;
                                }
                                
                                if ($needNewLine) {
                                    $curYPos += 8;
                                }
                                
                                if (($curYPos + $outputHeight) > $exportParams['pdfHeight']) {
                                    $orientation = ($exportParams['pdfWidth'] < $exportParams['pdfHeight']) ? 'P' : 'L';
                                    $pdf->AddPage($orientation, [$exportParams['pdfWidth'], $exportParams['pdfHeight']]);
                                    $pdf->setPageOrientation($orientation, false, 0);
                                    $curXPos = $exportParams['marginHorizontal'];
                                    $curYPos = $exportParams['marginVertical'] + 8;
                                }
                                
                                if ($needNewLine) {
                                    $orderProducts = implode(', ', $orderData[$orderId]);
                                    $pdf->Text(
                                        $x = $curXPos,
                                        $y = ($curYPos - 8),
                                        $txt = $realOrderId . ' - ' . $city . ' - ' . $orderProducts
                                    );
                                }
                                
                                $needNewLine = false;
                                
                                if ($enableRotate == '1') {
                                    $this->processSvgWithRotation(
                                        $pdf,
                                        $productSide,
                                        $fileContents, 
                                        $mediaUrl, 
                                        $mediaPath, 
                                        $curXPos, 
                                        $curYPos, 
                                        $neededNewLine,
                                        $origOutputWidth,
                                        $origOutputHeight,
                                        $overlayImage
                                    );
                                } else {
                                    $this->processSvgWithoutRotation(
                                        $pdf,
                                        $productSide,
                                        $fileContents,
                                        $mediaUrl,
                                        $mediaPath,
                                        $curXPos,
                                        $curYPos,
                                        $outputWidth,
                                        $outputHeight,
                                        $objectManager,
                                        $overlayImage
                                    );
                                }
                                
                                if ($enableRotate == '1') {
                                    $curXPos = $curXPos + $origOutputHeight + $exportParams['itemsMarginHorizontal'];
                                } else {
                                    $curXPos = $curXPos + $outputWidth + $exportParams['itemsMarginHorizontal'];
                                }
                            }
                            
                            $this->addSkuLabel(
                                $pdf,
                                $orderItem,
                                $sidesTotalWidth,
                                $skuCounter,
                                $curYPos,
                                $outputHeight,
                                $exportParams
                            );
                        }
                    }
                    
                    $saveData = [
                        'finished' => 1,
                        'pdf_file_' . $type => $dbFile,
                        'store_id' => $orderItem->getStoreId()
                    ];
                    
                    $objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport')
                        ->load($item->getId())
                        ->setData($saveData)
                        ->setId($item->getId())
                        ->save();
                }
                
                if (count($allItems) > 0 && count($orderData) > 0) {
                    $pdf->Output($pdfFile, 'F');
                }
            }
        }
    }
    
    protected function createSummaryPage($pdf, $allItems, $type, $store, $objectManager, $exportParams, &$curXPos, &$curYPos)
    {
        $orientation = ($exportParams['pdfWidth'] < $exportParams['pdfHeight']) ? 'P' : 'L';
        $pdf->AddPage($orientation, [$exportParams['pdfWidth'], $exportParams['pdfHeight']]);
        $pdf->setPageOrientation($orientation, false, 0);
        
        $curXPos = $exportParams['marginHorizontal'];
        $curYPos = $exportParams['marginVertical'];
        
        $pdf->SetFont('helvetica', '', 8, '', 'false');
        
        $allOrderIds = $allItems->getColumnValues('order_id');
        $firstOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allOrderIds[0])->getRealOrderId();
        $lastOrderId = $objectManager->create('Magento\Sales\Model\Order')->load(end($allOrderIds))->getRealOrderId();
        
        $pdf->Text(
            $x = $curXPos,
            $y = $curYPos,
            $txt = 'BULK :: ' . $firstOrderId . '-' . $lastOrderId . '.pdf'
        );
        $curYPos += 4;
        
        $pdf->Text(
            $x = $curXPos,
            $y = $curYPos,
            $txt = 'Printing type: ' . $type
        );
        $curYPos += 4;
        
        $pdf->Text(
            $x = $curXPos,
            $y = $curYPos,
            $txt = 'For this bulk, you need:'
        );
        $curYPos += 4;
        
        $orderItemIds = $allItems->getColumnValues('order_item_id');
        $orderItemsArray = [];
        
        foreach ($orderItemIds as $orderItemId) {
            $orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($orderItemId);
            $storeId = $orderItem->getStoreId();
            
            if ($store->getStoreId() != $storeId) {
                continue;
            }
            
            $product = $orderItem->getProduct();
            $technologyAttribute = $product->getResource()->getAttribute('technology');
            $productPrintingTypes = $technologyAttribute 
                ? array_filter(explode(',', str_replace(' ', '', $technologyAttribute->getFrontend()->getValue($product))))
                : [];
                
            if (!in_array($type, $productPrintingTypes)) {
                continue;
            }
            
            $productSku = $orderItem->getSku();
            $qty = (int)$orderItem->getQtyOrdered();
            
            if (isset($orderItemsArray[$productSku]) && !empty($orderItemsArray[$productSku])) {
                $qty += $orderItemsArray[$productSku]['qty'];
            }
            
            $orderItemsArray[$productSku] = [
                'sku' => $productSku,
                'qty' => $qty
            ];
        }
        
        foreach ($orderItemsArray as $orderItem) {
            $pdf->Text(
                $x = $curXPos,
                $y = $curYPos,
                $txt = $orderItem['sku'] . ' x ' . $orderItem['qty']
            );
            $curYPos += 4;
        }
        
        $curYPos = $curYPos - $exportParams['marginVertical'] - 25;
    }
    
    protected function prepareOrderData($allItems, $objectManager, $type, $store)
    {
        $orderData = [];
        
        foreach ($allItems as $item) {
            $orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
            $storeId = $orderItem->getStoreId();
            
            if ($store->getStoreId() != $storeId) {
                continue;
            }
            
            $product = $orderItem->getProduct();
            $technologyAttribute = $product->getResource()->getAttribute('technology');
            
            if (!$technologyAttribute) {
                continue;
            }
            
            $technologyValue = $technologyAttribute->getFrontend()->getValue($product);
            $productPrintingTypes = array_filter(explode(',', str_replace(' ', '', $technologyValue)));
            
            if (!in_array($type, $productPrintingTypes)) {
                continue;
            }
            
            $orderId = $orderItem->getOrderId();
            $qtyOrdered = (int)$orderItem->getQtyOrdered();
            
            if (isset($orderData[$orderId][$orderItem->getSku()])) {
                $existingEntry = $orderData[$orderId][$orderItem->getSku()];
                $parts = explode(' x ', $existingEntry);
                $existingQty = isset($parts[1]) ? floatval(trim($parts[1])) : 0;
                $qtyOrdered += $existingQty;
            }
            
            $orderData[$orderId][$orderItem->getSku()] = $orderItem->getSku() . ' x ' . $qtyOrdered;
        }
        
        return $orderData;
    }
    
    protected function getOverlayImage($objectManager, $productSide, $mediaPath)
    {
        $overlayImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
            ->addFieldToFilter('product_id', $productSide->getProductId())
            ->addFieldToFilter('label', $productSide->getLabel())
            ->addFieldToFilter('store_id', $productSide->getStoreId())
            ->setPageSize(1)
            ->setCurPage(1)
            ->load()
            ->getFirstItem();
            
        if (empty($overlayImage->getData())) {
            $overlayImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
                ->addFieldToFilter('product_id', $productSide->getProductId())
                ->addFieldToFilter('label', $productSide->getLabel())
                ->addFieldToFilter('store_id', ['null' => true])
                ->setPageSize(1)
                ->setCurPage(1)
                ->load()
                ->getFirstItem();
        }
        
        $overlayImageFile = $overlayImage->getPdfoverlayimage();
        if (!empty($overlayImageFile)) {
            $overlayImagePath = $mediaPath . 'productdesigner/overlayimgs/' . $overlayImageFile;
            if (file_exists($overlayImagePath)) {
                return $overlayImagePath;
            }
        }
        
        return null;
    }
    
    protected function processSvgWithRotation(
        $pdf,
        $productSide,
        $fileContents,
        $mediaUrl,
        $mediaPath,
        $curXPos,
        $curYPos,
        $neededNewLine,
        $origOutputWidth,
        $origOutputHeight,
        $overlayImagePath = null
    ) {
        $pdf->StartTransform();
        
        if ($neededNewLine) {
            $curXPos += $origOutputHeight;
        }
        
        $pdf->Rotate(90, $curXPos, $curYPos + $origOutputHeight);
        
        $outerWidth = (float)$productSide->getData('x2') - (float)$productSide->getData('x1');
        $svgWidthRaw = $this->get_string_between($fileContents, 'width="', '"');
        $svgWidth = (float)preg_replace('/[^\d.]/', '', $svgWidthRaw);
        
        $ratio = $svgWidth / $outerWidth;
        $scaleFactor = $svgWidth / 410;
        $ratio = $ratio * $scaleFactor;
        
        if (!$productSide->getOrigOutputX1() > 0) {
            $productSide->setOrigOutputX1($productSide->getData('output_x1'));
            $productSide->setOrigOutputX2($productSide->getData('output_x2'));
            $productSide->setOrigOutputY1($productSide->getData('output_y1'));
            $productSide->setOrigOutputY2($productSide->getData('output_y2'));
        }
        
        $productSide->setOutputX1($productSide->getOrigOutputX1() * $ratio);
        $productSide->setOutputX2($productSide->getOrigOutputX2() * $ratio);
        $productSide->setOutputY1($productSide->getOrigOutputY1() * $ratio);
        $productSide->setOutputY2($productSide->getOrigOutputY2() * $ratio);
        
        $viewBoxX1 = ($productSide->getData('output_x1') * $scaleFactor) + 2;
        $viewBoxY1 = ($productSide->getData('output_y1') * $scaleFactor) + 2;
        $viewBoxWidth = ($productSide->getData('output_x2') * $scaleFactor) - ($productSide->getData('output_x1') * $scaleFactor);
        $viewBoxHeight = ($productSide->getData('output_y2') * $scaleFactor) - ($productSide->getData('output_y1') * $scaleFactor);
        $viewBox = $viewBoxX1 . ' ' . $viewBoxY1 . ' ' . $viewBoxWidth . ' ' . $viewBoxHeight;
        
        $fileContents = $this->replace_between($fileContents, 'viewBox="', '"', $viewBox);
        $fileContents = $this->replace_between($fileContents, 'height="', '"', $svgWidth);
        
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
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
        
        $fileContents = '@' . str_replace($mediaUrl, $mediaPath, $fileContents);
        $fileContents = str_replace('+', '', $fileContents);
        
        $pdf->ImageSVG(
            $file = $fileContents,
            $x = $curXPos,
            $y = $curYPos,
            $w = $outputWidth,
            $h = $outputHeight,
            $link = '',
            $align = '',
            $palign = '',
            $border = 0,
            $fitonpage = false
        );
        
        if ($overlayImagePath !== null) {
            $pdf->Image(
                $file = $overlayImagePath,
                $x = $curXPos,
                $y = $curYPos,
                $w = $outputWidth,
                $h = $outputHeight
            );
        }
        
        $cutoutImage = $this->getCutoutSvg($productSide, $mediaPath);
        if ($cutoutImage) {
            $pdf->ImageSVG(
                $file = $cutoutImage,
                $x = $curXPos,
                $y = $curYPos,
                $w = $outputWidth,
                $h = $outputHeight,
                $link = '',
                $align = '',
                $palign = '',
                $border = 0,
                $fitonpage = false
            );
        }
    }
    protected function processSvgWithoutRotation(
        $pdf,
        $productSide,
        $fileContents,
        $mediaUrl,
        $mediaPath,
        $curXPos,
        $curYPos,
        $outputWidth,
        $outputHeight,
        $objectManager,
        $overlayImagePath = null
    ) {
        $outerWidth = (float)$productSide->getData('x2') - (float)$productSide->getData('x1');
        $svgWidthRaw = $this->get_string_between($fileContents, 'width="', '"');
        $svgWidth = (float)preg_replace('/[^\d.]/', '', $svgWidthRaw);
        
        $ratio = ($outerWidth > 0 && $svgWidth > 0) ? ($svgWidth / $outerWidth) : 1.0;
        $scaleFactor = ($svgWidth > 0) ? ($svgWidth / 410) : 1.0;
    
        $viewBoxX1 = ($productSide->getData('output_x1') * $scaleFactor) + 2;
        $viewBoxY1 = ($productSide->getData('output_y1') * $scaleFactor) + 2;
        $viewBoxWidth = ($productSide->getData('output_x2') * $scaleFactor) - ($productSide->getData('output_x1') * $scaleFactor);
        $viewBoxHeight = ($productSide->getData('output_y2') * $scaleFactor) - ($productSide->getData('output_y1') * $scaleFactor);
        $viewBox = $viewBoxX1 . ' ' . $viewBoxY1 . ' ' . $viewBoxWidth . ' ' . $viewBoxHeight;
    
        $fileContents = $this->replace_between($fileContents, 'viewBox="', '"', $viewBox);
        $fileContents = $this->replace_between($fileContents, 'height="', '"', $svgWidth);
    
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
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
        $fileContents = '@' . str_replace($mediaUrl, $mediaPath, $fileContents);
        $fileContents = str_replace('+', '', $fileContents);
    
        $pdf->ImageSVG(
            $file = $fileContents,
            $x = $curXPos,
            $y = $curYPos,
            $w = $outputWidth,
            $h = $outputHeight,
            $link = '',
            $align = '',
            $palign = '',
            $border = 0,
            $fitonpage = false
        );
    
        if ($overlayImagePath !== null) {
            $pdf->Image(
                $file = $overlayImagePath,
                $x = $curXPos,
                $y = $curYPos,
                $w = $outputWidth,
                $h = $outputHeight
            );
        }
    
        $cutoutImage = $this->getCutoutSvg($productSide, $mediaPath);
        if ($cutoutImage) {
            $pdf->ImageSVG(
                $file = $cutoutImage,
                $x = $curXPos,
                $y = $curYPos,
                $w = $outputWidth,
                $h = $outputHeight,
                $link = '',
                $align = '',
                $palign = '',
                $border = 0,
                $fitonpage = false
            );
        }
    } 
    protected function getCutoutSvg($productSide, $mediaPath)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $cutoutImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
            ->addFieldToFilter('product_id', $productSide->getProductId())
            ->addFieldToFilter('label', $productSide->getLabel())
            ->addFieldToFilter('store_id', $productSide->getStoreId())
            ->setPageSize(1)
            ->setCurPage(1)
            ->load()
            ->getFirstItem();
            
        if (empty($cutoutImage->getData())) {
            $cutoutImage = $objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
                ->addFieldToFilter('product_id', $productSide->getProductId())
                ->addFieldToFilter('label', $productSide->getLabel())
                ->addFieldToFilter('store_id', ['null' => true])
                ->setPageSize(1)
                ->setCurPage(1)
                ->load()
                ->getFirstItem();
        }
        
        $cutoutSvg = $cutoutImage->getCutoutsvg();
        if (!empty($cutoutSvg)) {
            $cutoutPath = $mediaPath . 'productdesigner/cutoutsvg/' . $cutoutSvg;
            if (file_exists($cutoutPath)) {
                return $cutoutPath;
            }
        }
        
        return null;
    }
    
    protected function addSkuLabel($pdf, $orderItem, $sidesTotalWidth, $skuCounter, $curYPos, $outputHeight, $exportParams)
    {
        $sku = $orderItem->getSku();
        $skuLength = strlen($sku);
        
        $xPos = ($sidesTotalWidth * $skuCounter) + ($sidesTotalWidth - $skuLength) / 2;
        
        $pdf->Text(
            $x = $xPos,
            $y = ($curYPos + $outputHeight + 1),
            $txt = $sku
        );
        
        $lineStartPos = ($sidesTotalWidth * $skuCounter) + $exportParams['marginHorizontal'];
        $lineEndPos = ($sidesTotalWidth * $skuCounter) + ($sidesTotalWidth - $skuLength) / 2;
        
        $pdf->Line(
            $lineStartPos,
            ($curYPos + $outputHeight + 2),
            $lineEndPos,
            ($curYPos + $outputHeight + 2)
        );
        
        $diff = $lineEndPos - $lineStartPos;
        $lineStartPos = $lineEndPos + ($skuLength * 2);
        $lineEndPos = $lineStartPos + $diff;
        
        $pdf->Line(
            $lineStartPos,
            ($curYPos + $outputHeight + 2),
            $lineEndPos,
            ($curYPos + $outputHeight + 2)
        );
    }
    
    protected function processWoodBoard($allItems, $fileNames, $exportParams, $objectManager, $mediaPath, $mediaUrl, $block, $helper)
    {
        $firstOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getFirstItem()->getOrderId())->getRealOrderId();
        $lastOrderId = $objectManager->create('Magento\Sales\Model\Order')->load($allItems->getLastItem()->getOrderId())->getRealOrderId();
        
        $type = 'wood';
        $random = $firstOrderId . '-' . $lastOrderId . '-' . $type;
        $pdfFile = $mediaPath . 'productdesigner/order_export/' . date('Y') . '/' . date('m') . '/' . $random . '.pdf';
        $dbFile = date('Y') . '/' . date('m') . '/' . $random . '.pdf';
        
        $pdf = $helper->getPdfObject($block->get_base_dir(''));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $curXPos = $exportParams['marginHorizontal'];
        $curYPos = $exportParams['marginVertical'];
        
        $orientation = ($exportParams['pdfWidth'] < $exportParams['pdfHeight']) ? 'P' : 'L';
        $pdf->AddPage($orientation, [$exportParams['pdfWidth'], $exportParams['pdfHeight']]);
        $pdf->setPageOrientation($orientation, false, 0);
        
        $pdf->startLayer('repere', true, true, false);
        $pdf->Rect(
            $x = 0,
            $y = 0,
            $w = 1,
            $h = 1,
            'F',
            ['color' => [0, 0, 0]]
        );
        $pdf->endLayer();
        
        for ($layerCounter = 0; $layerCounter < 3; $layerCounter++) {
            $curXPos = $exportParams['marginHorizontal'];
            $curYPos = $exportParams['marginVertical'];
            
            if ($layerCounter > 0) {
                $pdf->AddPage($orientation, [$exportParams['pdfWidth'], $exportParams['pdfHeight']]);
                $pdf->setPageOrientation($orientation, false, 0);
                $pdf->startLayer('repere', true, true, false);
                $pdf->Rect(
                    $x = 0,
                    $y = 0,
                    $w = 1,
                    $h = 1,
                    'F',
                    ['color' => [0, 0, 0]]
                );
                $pdf->endLayer();
            }
            
            if ($layerCounter == 0) {
                $pdf->startLayer('verso', true, true, false);
            } elseif ($layerCounter == 1) {
                $pdf->startLayer('recto', true, true, false);
            } elseif ($layerCounter == 2) {
                $pdf->startLayer('decoupe', true, true, false);
            }
            
            $lastOrderId = '';
            foreach ($allItems as $item) {
                $orderItem = $objectManager->create('Magento\Sales\Model\Order\Item')->load($item->getOrderItemId());
                $mediaUrl = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')
                    ->getStore($orderItem->getStoreId())
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                
                $product = $orderItem->getProduct();
                $technologyAttribute = $product->getResource()->getAttribute('technology');
                if (!$technologyAttribute) {
                    continue;
                }
                
                $technologyValue = $technologyAttribute->getFrontend()->getValue($product);
                $productPrintingTypes = array_filter(explode(',', str_replace(' ', '', $technologyValue)));
                
                if (!in_array($type, $productPrintingTypes)) {
                    continue;
                }
                
                $_resource = $objectManager->create('Magento\Catalog\Model\Product')->getResource();
                $enableRotate = $_resource->getAttributeRawValue(
                    $orderItem->getProductId(),
                    'pd_rotate_in_export',
                    $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()
                );
                
                $orderId = $orderItem->getOrderId();
                $lastOrderId = $orderId;
                $lastOrder = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
                $realOrderId = $lastOrder->getRealOrderId();
                $shippingData = $lastOrder->getShippingAddress()->getData();
                
                $qty = (float)$orderItem->getQtyOrdered();
                if ($qty < 1 && $orderItem->getQtyInvoiced() > 0) {
                    $qty = (float)$orderItem->getQtyInvoiced();
                }
                
                $orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
                $connectId = $orderItemOptions['connect_id']['connect_id'];
                
                if ($connectId > 0) {
                    $savedItems = $this->loadSavedItems($objectManager, $connectId);
                    
                    $skuCounter = 0;
                    $maxHeight = 0;
                    $needNewPage = false;
                    
                    for ($i = 0; $i < $qty; $i++) {
                        $skuCounter++;
                        
                        $amountOfSides = count($savedItems);
                        $sidesTotalWidth = 0;
                        $count = 0;
                        
                        foreach ($savedItems as $productSide) {
                            $count++;
                            
                            if (!$exportParams['pdfWidth'] > 0 || !$exportParams['pdfHeight'] > 0) {
                                $exportParams['pdfWidth'] = $productSide->getOutputwidth();
                                $exportParams['pdfHeight'] = $productSide->getOutputheight();
                            }
                            
                            $outputWidth = $productSide->getOutputwidth();
                            $outputHeight = $productSide->getOutputheight();
                            
                            if ($outputHeight > $maxHeight) {
                                $maxHeight = $outputHeight;
                            }
                            
                            $origOutputWidth = $outputWidth;
                            $origOutputHeight = $outputHeight;
                            $sidesTotalWidth += $outputWidth + $exportParams['itemsMarginHorizontal'];
                            
                            if ($curXPos == 0) {
                                $curXPos = $exportParams['marginHorizontal'];
                            }
                            
                            if ($curYPos == 0) {
                                $curYPos = $exportParams['marginVertical'];
                            }
                            
                            $neededNewLine = false;
                            if (($curXPos + $outputWidth + $exportParams['marginHorizontal']) > $exportParams['pdfWidth']) {
                                $curXPos = $exportParams['marginHorizontal'];
                                $curYPos = $curYPos + $maxHeight + $exportParams['itemsMarginHorizontal'] + 2;
                                $maxHeight = 0;
                                $skuCounter = 0;
                                $neededNewLine = true;
                            }
                            
                            if (($curYPos + $outputHeight) > $exportParams['pdfHeight']) {
                                $needNewPage = true;
                            }
                            
                            if ($needNewPage) {
                                $orientation = ($exportParams['pdfWidth'] < $exportParams['pdfHeight']) ? 'P' : 'L';
                                $pdf->AddPage($orientation, [$exportParams['pdfWidth'], $exportParams['pdfHeight']]);
                                $pdf->setPageOrientation($orientation, false, 0);
                                $needNewPage = false;
                                $curXPos = $exportParams['marginHorizontal'];
                                $curYPos = $exportParams['marginVertical'] + 8;
                            }
                            
                            if ($layerCounter == 0 || $layerCounter == 1) {
                                if (($layerCounter == 0 && $count == 1) || ($layerCounter == 1 && $count == 2)) {
                                    $file = $productSide->getSvg();
                                    $fileName = basename($file);
                                    $outputFileName = 'output_' . $fileName;
                                    $file = str_replace($fileName, $outputFileName, $file);
                                    
                                    $svgInfo = $this->prepareSvgFile($file, $mediaPath, $mediaUrl);
                                    
                                    if ($svgInfo['success']) {
                                        $fileContents = file_get_contents($svgInfo['processedPath']);
                                        
                                        $viewBoxParams = $this->calculateViewBox($productSide, $fileContents);
                                        
                                        $processedSvgContent = $this->prepareSvgForPdf($fileContents, $viewBoxParams, $mediaUrl, $mediaPath);
                                        
                                        $pdf->ImageSVG(
                                            $file = $processedSvgContent,
                                            $x = $curXPos,
                                            $y = $curYPos,
                                            $w = $outputWidth,
                                            $h = $outputHeight,
                                            $link = '',
                                            $align = '',
                                            $palign = '',
                                            $border = 0,
                                            $fitonpage = false
                                        );
                                    }
                                }
                            } elseif ($layerCounter == 2 && $count == 2) {
                                $cutoutImage = $this->getCutoutSvg($productSide, $mediaPath);
                                if ($cutoutImage) {
                                    $pdf->ImageSVG(
                                        $file = $cutoutImage,
                                        $x = $curXPos,
                                        $y = $curYPos,
                                        $w = $outputWidth,
                                        $h = $outputHeight,
                                        $link = '',
                                        $align = '',
                                        $palign = '',
                                        $border = 0,
                                        $fitonpage = false
                                    );
                                }
                            }
                            
                            if ($count == 2) {
                                $curXPos = $curXPos + $outputWidth + $exportParams['itemsMarginHorizontal'];
                            }
                        }
                    }
                }
                
                $saveData = [
                    'finished' => 1,
                    'pdf_file_' . $type => $dbFile,
                    'store_id' => $orderItem->getStoreId()
                ];
                
                $objectManager->create('Laurensmedia\Productdesigner\Model\Bulkexport')
                    ->load($item->getId())
                    ->setData($saveData)
                    ->setId($item->getId())
                    ->save();
            }
            
            $pdf->endLayer();
        }
        
        if (count($allItems) > 0) {
            $pdf->Output($pdfFile, 'F');
        }
    }
    
    public function replace_between($str, $needle_start, $needle_end, $replacement)
    {
        $pos = strpos($str, $needle_start);
        $start = $pos === false ? 0 : $pos + strlen($needle_start);
        
        $pos = strpos($str, $needle_end, $start);
        $end = $pos === false ? strlen($str) : $pos;
        
        return substr_replace($str, $replacement, $start, $end - $start);
    }
    
    public function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
    
    function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);
        
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        $rgb = array($r, $g, $b);
        return $rgb;
    }
    
    public function recolorimage($oldImagePath, $newImagePath, $newImageColor)
    {
        $imageSize = getimagesize($oldImagePath);
        $width = $imageSize[0];
        $height = $imageSize[1];
        $pathInfo = pathinfo($oldImagePath);
        $ext = $pathInfo['extension'];
        
        $rgbColor = $this->hex2rgb($newImageColor);
        
        if ($ext != 'png') {
            return $oldImagePath;
        }
        
        $img = imagecreatefrompng($oldImagePath);
        $transparentImage = imagecreatetruecolor($width, $height);
        
        $black = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($transparentImage, 0, 0, $black);
        $white = imagecolorallocatealpha($img, $rgbColor[0], $rgbColor[1], $rgbColor[2], 0);
        
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $color = imagecolorat($img, $x, $y);
                $color = imagecolorsforindex($img, $color);
                if ($color['alpha'] < 127) {
                    imagesetpixel($transparentImage, $x, $y, $white);
                } else {
                    imagesetpixel($transparentImage, $x, $y, $black);
                }
            }
        }
        
        ImageColorTransparent($img, $black);
        imageAlphaBlending($transparentImage, true);
        imageSaveAlpha($transparentImage, true);
        
        ImagePng($transparentImage, $newImagePath);
        ImageDestroy($img);
        ImageDestroy($transparentImage);
        
        return $newImagePath;
    }
}

