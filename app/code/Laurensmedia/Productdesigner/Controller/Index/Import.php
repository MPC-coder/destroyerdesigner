<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class Import extends Action
{
    /**
     * @var ResourceConnection
     */
    protected ResourceConnection $resourceConnection;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var ProductCollectionFactory
     */
    protected ProductCollectionFactory $productCollectionFactory;

    /**
     * @param Context $context
     * @param ResourceConnection $resourceConnection
     * @param Filesystem $filesystem
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        Filesystem $filesystem,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->filesystem = $filesystem;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
       /* if ($_GET['password'] !== 'aidjqmjs856202sdieionid') {
            return;
        }*/

        $csv = array_map('str_getcsv', file(__DIR__ . '/catalog_product_entity.csv'));
        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv);

        $oldProductIds = [];
        foreach ($csv as $product) {
            $sku = $product['sku'];
            $id = $product['entity_id'];
            $oldProductIds[$id] = $sku;
        }

        $productCollection = $this->productCollectionFactory->create()->addAttributeToSelect(['entity_id', 'sku']);
        $newProductIds = [];
        foreach ($productCollection as $product) {
            $sku = $product->getSku();
            $id = $product->getId();
            $newProductIds[$sku] = $id;
        }

        $mediaPath = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();

        $this->copyFonts($mediaPath);
        $this->copyImages($mediaPath, $oldProductIds, $newProductIds);

        $this->importData($oldProductIds, $newProductIds);
    }

    private function copyFonts(string $mediaPath): void
    {
        $fonts = scandir(__DIR__ . '/fonts');
        foreach ($fonts as $font) {
            if ($font !== '.' && $font !== '..' && $font !== '.DS_Store') {
                $path = $mediaPath . 'productdesigner_fonts/' . strtolower($font[0]) . '/' . strtolower($font[1]);
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                if (strpos($font, '-1.png') === false) {
                    continue;
                }
                copy(__DIR__ . '/fonts/' . $font, $path . '/' . strtolower(str_replace('-1.png', '-15.png', $font)));
            }
        }

        foreach ($fonts as $font) {
            if ($font !== '.' && $font !== '..' && $font !== '.DS_Store') {
                $path = $mediaPath . 'productdesigner_fonts/' . strtolower($font[0]) . '/' . strtolower($font[1]);
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                copy(__DIR__ . '/fonts/' . $font, $path . '/' . strtolower($font));
            }
        }
    }

    private function copyImages(string $mediaPath, array $oldProductIds, array $newProductIds): void
    {
        $this->copyImageDirectory(__DIR__ . '/images/color_img', $mediaPath . 'productdesigner/color_img', $oldProductIds, $newProductIds);
        $this->copyImageDirectory(__DIR__ . '/images/overlayimgs', $mediaPath . 'productdesigner/overlayimgs', $oldProductIds, $newProductIds);
        $this->copyImageDirectory(__DIR__ . '/images/png', $mediaPath . 'productdesigner_images');
        $this->copyImageDirectory(__DIR__ . '/images/sideimages', $mediaPath . 'productdesigner/sideimages', $oldProductIds, $newProductIds);
        $this->copyImageDirectory(__DIR__ . '/images/bevestiging', $mediaPath . 'productdesigner_bevestiging', $oldProductIds, $newProductIds);
    }

    private function copyImageDirectory(string $sourceDir, string $destDir, array $oldProductIds = [], array $newProductIds = []): void
    {
        $images = scandir($sourceDir);
        foreach ($images as $image) {
            if ($image !== '.' && $image !== '..' && $image !== '.DS_Store') {
                $newImage = $image;
                if (is_numeric($image)) {
                    $id = $image;
                    if (isset($oldProductIds[$id])) {
                        $sku = $oldProductIds[$id];
                        if (isset($newProductIds[$sku])) {
                            $newImage = $newProductIds[$sku];
                        } else {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }
                $this->copyr($sourceDir . '/' . $image, $destDir . '/' . $newImage);
            }
        }
    }

    private function importData(array $oldProductIds, array $newProductIds): void
    {
        $this->importCsvData(__DIR__ . '/import/druk_fonts_library.csv', 'druk_fonts_library');
        $this->importCsvData(__DIR__ . '/import/druk_img_category.csv', 'druk_img_category');
        $this->importCsvData(__DIR__ . '/import/druk_img_library.csv', 'druk_img_library', ['id', 'image', 'ai']);
        $this->importCsvData(__DIR__ . '/import/prod_design_attribuut.csv', 'prod_design_attribuut', [], $oldProductIds, $newProductIds);
        $this->importCsvData(__DIR__ . '/import/prod_design_bevestiging.csv', 'prod_design_bevestiging', ['id']);
        $this->importCsvData(__DIR__ . '/import/prod_design_colorimages.csv', 'prod_design_colorimages', [], $oldProductIds, $newProductIds);
        $this->importCsvData(__DIR__ . '/import/prod_design_droparea.csv', 'prod_design_droparea', ['id'], $oldProductIds, $newProductIds);
        $this->importCsvData(__DIR__ . '/import/prod_design_druktype.csv', 'prod_design_druktype', [], $oldProductIds, $newProductIds);
        $this->importCsvData(__DIR__ . '/import/prod_design_fonts.csv', 'prod_design_fonts', [], $oldProductIds, $newProductIds);
        $this->importCsvData(__DIR__ . '/import/prod_design_kleur.csv', 'prod_design_kleur', [], $oldProductIds, $newProductIds);
        $this->importCsvData(__DIR__ . '/import/prod_design_maat.csv', 'prod_design_maat', [], $oldProductIds, $newProductIds);
        $this->importCsvData(__DIR__ . '/import/prod_design_prodbevestiging.csv', 'prod_design_prodbevestiging', [], $oldProductIds, $newProductIds);
        $this->importCsvData(__DIR__ . '/import/prod_design_templates.csv', 'prod_design_templates', [], $oldProductIds, $newProductIds);
        $this->importCsvData(__DIR__ . '/import/prod_design_textcolors.csv', 'prod_design_textcolors', [], $oldProductIds, $newProductIds);
    }

    private function importCsvData(string $fileLocation, string $tableName, array $columnOverrides = [], array $oldProductIds = [], array $newProductIds = []): void
    {
        $csv = array_map('str_getcsv', file($fileLocation));
        array_walk($csv, function (&$a) use ($csv, $columnOverrides) {
            foreach ($columnOverrides as $index => $override) {
                $csv[0][$index] = $override;
            }
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv);

        foreach ($csv as $index => $row) {
            if (isset($row['product_id']) && isset($oldProductIds[$row['product_id']])) {
                $sku = $oldProductIds[$row['product_id']];
                if (isset($newProductIds[$sku])) {
                    $csv[$index]['product_id'] = $newProductIds[$sku];
                } else {
                    unset($csv[$index]);
                }
            }
        }

        foreach ($csv as $item) {
            $themeTable = $this->resourceConnection->getTableName($tableName);
            $sql = "INSERT INTO " . $themeTable . " (" . implode(', ', array_keys($item)) . ") VALUES ('" . implode("', '", array_values($item)) . "')";
            $this->resourceConnection->getConnection()->query($sql);
        }
    }

    public function copyr(string $source, string $dest): bool
    {
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        if (is_file($source)) {
            return copy($source, $dest);
        }

        if (!is_dir($dest)) {
            mkdir($dest);
        }

        $dir = dir($source);
        while (false !== ($entry = $dir->read())) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $this->copyr("$source/$entry", "$dest/$entry");
        }

        $dir->close();
        return true;
    }
}