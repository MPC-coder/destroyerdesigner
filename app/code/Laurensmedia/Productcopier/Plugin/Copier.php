<?php
namespace Laurensmedia\Productcopier\Plugin;

use Magento\Catalog\Model\Product;

class Copier
{
    public function beforeCopy($subject, \Magento\Catalog\Model\Product $product)
    {
        $product->setData('url_path', '');
        $product->save();
    }
}