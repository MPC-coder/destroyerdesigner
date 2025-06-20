<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Laurensmedia\Productdesigner\Model\ResourceModel\Colorimages\CollectionFactory as ColorimagesCollectionFactory;
use Laurensmedia\Productdesigner\Model\ResourceModel\Products\CollectionFactory as ProductsCollectionFactory;
use Laurensmedia\Productdesigner\Model\ResourceModel\Saved\CollectionFactory as SavedCollectionFactory;
use Laurensmedia\Productdesigner\Model\SavedFactory;

class Savedesign extends Action
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
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;

    /**
     * @var ColorimagesCollectionFactory
     */
    protected ColorimagesCollectionFactory $colorimagesCollectionFactory;

    /**
     * @var ProductsCollectionFactory
     */
    protected ProductsCollectionFactory $productsCollectionFactory;

    /**
     * @var SavedCollectionFactory
     */
    protected SavedCollectionFactory $savedCollectionFactory;

    /**
     * @var SavedFactory
     */
    protected SavedFactory $savedFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param ColorimagesCollectionFactory $colorimagesCollectionFactory
     * @param ProductsCollectionFactory $productsCollectionFactory
     * @param SavedCollectionFactory $savedCollectionFactory
     * @param SavedFactory $savedFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        ColorimagesCollectionFactory $colorimagesCollectionFactory,
        ProductsCollectionFactory $productsCollectionFactory,
        SavedCollectionFactory $savedCollectionFactory,
        SavedFactory $savedFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->colorimagesCollectionFactory = $colorimagesCollectionFactory;
        $this->productsCollectionFactory = $productsCollectionFactory;
        $this->savedCollectionFactory = $savedCollectionFactory;
        $this->savedFactory = $savedFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $mediaPath = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();

        $customer = $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomer() : null;
        $customerId = $customer ? $customer->getId() : '';

        $data = $this->getRequest()->getParam('save');
        $productId = $data['productid'];

        $colorimages = $this->colorimagesCollectionFactory->create()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('store_id', $this->storeManager->getStore()->getId());

        if (count($colorimages->getData()) == 0) {
            $colorimages = $this->colorimagesCollectionFactory->create()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('store_id', ['null' => true]);
        }

        $sizes = isset($data['sizes']) ? $data['sizes'] : [];
        $sizesHtml = "";
        if (is_array($sizes)) {
            foreach ($sizes as $size) {
                $sizesHtml .= $size['name'] . ":" . $size['amount'] . ",";
            }
        }

        $number = $data['number'];
        $connectId = mt_rand(0, mt_getrandmax());

        if ($data[0]['id'] != '') {
            $customerDbId = $this->savedCollectionFactory->create()
                ->addFieldToSelect('customer_id')
                ->addFieldToFilter('connect_id', $data[0]['id'])
                ->setPageSize(1)
                ->setCurPage(1)
                ->getFirstItem()
                ->getCustomerId();

            if ($customerId == $customerDbId) {
                $connectId = $data[0]['id'];
                $deleteItems = $this->savedCollectionFactory->create()
                    ->addFieldToSelect('save_id')
                    ->addFieldToSelect('connect_id')
                    ->setPageSize(3)
                    ->setCurPage(1)
                    ->addFieldToFilter('connect_id', $data[0]['id'])
                    ->load();

                foreach ($deleteItems as $deleteItem) {
                    $deleteItem->delete();
                }
            }
        }

        $existingSaves = $this->savedCollectionFactory->create()
            ->addFieldToSelect('connect_id')
            ->addFieldToFilter('connect_id', $connectId)
            ->setPageSize(1)
            ->setCurPage(1)
            ->load();

        if (count($existingSaves) > 0) {
            $connectId = mt_rand(0, mt_getrandmax());
        }

        for ($i = 0; $i < $number; $i++) {
            $object = $data[$i];
            $label = str_replace(' ', '_', $object['label']);
            $json = $object['json'];
            $png = $object['png'];
            $svg = $object['svg'];
            $outputSvg = $object['outputsvg'];

            $droparea = $this->productsCollectionFactory->create()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('label', $label)
                ->addFieldToFilter('store_id', $this->storeManager->getStore()->getId())
                ->getFirstItem();

            if (empty($droparea->getData())) {
                $droparea = $this->productsCollectionFactory->create()
                    ->addFieldToFilter('product_id', $productId)
                    ->addFieldToFilter('label', $label)
                    ->addFieldToFilter('store_id', ['null' => true])
                    ->getFirstItem();
            }

            $image = $this->colorimagesCollectionFactory->create()
                ->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('label', $label)
                ->addFieldToFilter('store_id', $this->storeManager->getStore()->getId())
                ->getFirstItem();

            if (empty($image->getData())) {
                $image = $this->colorimagesCollectionFactory->create()
                    ->addFieldToFilter('product_id', $productId)
                    ->addFieldToFilter('label', $label)
                    ->addFieldToFilter('store_id', ['null' => true])
                    ->getFirstItem();
            }

            if ($image) {
                $image = $image->getImgurl();
                $imageType = 'colorimage';
            }

            $jsonDir = $mediaPath . 'productdesigner/json/' . date('Y') . '/' . date('m') . '/';
            if (!file_exists($jsonDir) && !is_dir($jsonDir)) {
                mkdir($jsonDir, 0777, true);
            }
            $jsonFileName = date('U') . '_' . rand(0, 999999) . '.php';
            $jsonFileLocation = $jsonDir . $jsonFileName;
            file_put_contents($jsonFileLocation, $json);

            $pngDir = $mediaPath . 'productdesigner/png_export/' . date('Y') . '/' . date('m') . '/';

            // Vérifier si le répertoire existe, sinon le créer (avec des permissions 0777 et mode récursif)
            if (!is_dir($pngDir)) {
                if (!mkdir($pngDir, 0777, true)) {
                    throw new \Exception("Impossible de créer le répertoire : $pngDir");
                }
            }

            // Génération d'un nom de fichier unique (basé sur l'heure Unix et un nombre aléatoire)
            $pngFileName = date('U') . '_' . rand(0, 999999) . '.png';
            $pngFileLocation = $pngDir . $pngFileName;

            // Supposons que $png contienne une chaîne de type "data:image/png;base64,...."
            $pngParts = explode(',', $png);
            if (isset($pngParts[1])) {
                $pngData = base64_decode($pngParts[1]);
                if ($pngData === false) {
                    throw new \Exception("Erreur lors du décodage de l'image PNG.");
                }
                // Sauvegarder le contenu décodé dans le fichier
                if (file_put_contents($pngFileLocation, $pngData) === false) {
                    throw new \Exception("Erreur lors de l'écriture du fichier PNG à : $pngFileLocation");
                }
            } else {
                throw new \Exception("Le format de la chaîne PNG n'est pas valide.");
            }

            $svgDir = $mediaPath . 'productdesigner/svg/' . date('Y') . '/' . date('m') . '/';
            if (!file_exists($svgDir) && !is_dir($svgDir)) {
                mkdir($svgDir, 0777, true);
            }
            $svgFileName = date('U') . '_' . rand(0, 999999) . '.php';
            $svgFileLocation = $svgDir . $svgFileName;
            file_put_contents($svgFileLocation, $svg);

            $svgOutputFileName = 'output_' . $svgFileName;
            $svgOutputFileLocation = $svgDir . $svgOutputFileName;
            file_put_contents($svgOutputFileLocation, $outputSvg);

            $saveData = [
                'color' => $data['color'] ?? '',
                'druktype' => $data['druktype'] ?? '',
                'sizes' => $sizesHtml,
                'product_id' => $productId,
                'customer_id' => $customerId,
                'json' => date('Y') . '/' . date('m') . '/' . $jsonFileName,
                'png' => date('Y') . '/' . date('m') . '/' . $pngFileName,
                'svg' => date('Y') . '/' . date('m') . '/' . $svgFileName,
                'x1' => $droparea->getData('x1'),
                'x2' => $droparea->getData('x2'),
                'y1' => $droparea->getData('y1'),
                'y2' => $droparea->getData('y2'),
                'outputwidth' => $droparea->getOutputwidth(),
                'outputheight' => $droparea->getOutputheight(),
                'output_x1' => $droparea->getData('output_x1'),
                'output_x2' => $droparea->getData('output_x2'),
                'output_y1' => $droparea->getData('output_y1'),
                'output_y2' => $droparea->getData('output_y2'),
                'imagewidth' => $droparea->getImagewidth(),
                'imageheight' => $droparea->getImageheight(),
                'image' => $image,
                'imagetype' => $imageType,
                'label' => $label,
                'connect_id' => $connectId,
                'savetype' => $object['type'],
                'store_id' => $this->storeManager->getStore()->getId(),
            ];

            if ($productId != "") {
                $this->savedFactory->create()->addData($saveData)->save();
            }
        }

        $result->setData(['connect_id' => $connectId]);
        return $result;
    }
}