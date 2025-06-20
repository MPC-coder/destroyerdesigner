<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\Bulkexport;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Laurensmedia\Productdesigner\Helper\Tcpdfhelper;

class Engravingzip extends AbstractMassAction
{
    const ADMIN_RESOURCE = 'Magento_Sales::delete';

    protected $orderRepository;
    protected $tcpdfHelper;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderRepository $orderRepository
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
    }

    protected function getTcpdfHelper()
    {
        if ($this->tcpdfHelper === null) {
            $this->tcpdfHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(Tcpdfhelper::class);
        }
        return $this->tcpdfHelper;
    }

    protected function massAction(AbstractCollection $collection)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mediaPath = $objectManager->get(\Magento\Framework\Filesystem::class)
            ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();

        $files = [];
        foreach ($collection->getItems() as $order) {
            $orderItems = $order->getAllItems();
            foreach ($orderItems as $orderItem) {
                $product = $orderItem->getProduct();
                $technology = $product->getAttributeText('technology');
                if (!is_array($technology)) {
                    $technology = [$technology];
                }
                if (in_array('engraving', $technology)) {
                    $orderItemOptions = json_decode($orderItem->getProductdesignerData(), true);
                    $connectId = $orderItemOptions['connect_id']['connect_id'] ?? 0;
                    if ($connectId > 0) {
                        $savedItems = $objectManager->create('Laurensmedia\\Productdesigner\\Model\\ResourceModel\\Saved\\Collection')
                            ->addFieldToFilter('connect_id', $connectId);
                        foreach ($savedItems as $savedItem) {
                            $randomId = rand(1000, 9999);
                            $files[] = $this->createFile('pdf', $savedItem, $order->getIncrementId(), $orderItem->getSku(), $randomId);
                            // SVG and EPS export disabled
                            //$files[] = $this->createFile('svg', $savedItem, $order->getIncrementId(), $orderItem->getSku(), $randomId);
                            // $files[] = $this->createFile('eps', $savedItem, $order->getIncrementId(), $orderItem->getSku(), $randomId);
                        }
                    }
                }
            }
        }

        $libDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)
            ->getDirectoryRead(DirectoryList::LIB_INTERNAL)->getAbsolutePath();
        require_once($libDirectory . 'zip/Zip.php');

        $zipLocation = $mediaPath . 'productdesigner/tmp/' . date('Y-m-d') . '.zip';
        if (file_exists($zipLocation)) {
            unlink($zipLocation);
        }

        $zip = new \Zip();
        $zip->zip_start($zipLocation);
        foreach ($files as $fileEntry) {
            if (!empty($fileEntry['file_location']) && file_exists($fileEntry['file_location']) && filesize($fileEntry['file_location']) > 0) {
                $zip->zip_add($fileEntry['file_location'], $fileEntry['file_name']);
            }
        }
        $zip->zip_end();

        foreach ($files as $fileEntry) {
            if (!empty($fileEntry['file_location'])) {
                @unlink($fileEntry['file_location']);
            }
        }

        if (empty($files)) {
            echo 'No files to export';
            return;
        }

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . basename($zipLocation));
        header('Content-Length: ' . filesize($zipLocation));
        readfile($zipLocation);

        unlink($zipLocation);
        return;
    }

    public function createFile($outputType, $saveObject, $incrementId, $sku, $randomId)
    {
        $mediaDirectory = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Filesystem::class)
            ->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();

        $svgPath = $mediaDirectory . 'productdesigner/svg/' . $saveObject->getSvg();
        $svgContent = file_get_contents($svgPath);
        $convertedSvg = $this->getTcpdfHelper()->prepareSvgForTcpdf($svgContent);
        //$convertedSvg = $this->getTcpdfHelper()->prepareSvgForLaserStar($svgContent);
        $baseName = $incrementId . '-' . urldecode($sku) . '-' . $randomId;
        $tmpDir = $mediaDirectory . 'productdesigner/tmp/';

    if ($outputType === 'svg') {
        $svgOutputPath = $tmpDir . $baseName . '.svg';
        file_put_contents($svgOutputPath, $convertedSvg);
        return ['file_name' => $baseName . '.svg', 'file_location' => $svgOutputPath];
    }

        $pdf = $this->getTcpdfHelper()->getPdfObject($tmpDir);
        $pdf->AddPage();
        $pdf->ImageSVG('@' . $convertedSvg, 0, 0, 180);
        $pdfPath = $tmpDir . $baseName . '.pdf';
        $pdf->Output($pdfPath, 'F');

        if ($outputType === 'pdf') {
            return ['file_name' => $baseName . '.pdf', 'file_location' => $pdfPath];
        }

        if ($outputType === 'eps') {
            return [];
        }

        return [];
    }
}
