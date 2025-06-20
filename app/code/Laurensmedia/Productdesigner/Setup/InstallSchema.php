<?php

namespace Laurensmedia\Productdesigner\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0){
			$installer->run('
CREATE TABLE IF NOT EXISTS `druk_fonts_library` (
  `id_fonts` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `fontfamily` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id_fonts`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `druk_img_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `is_background` int(1) NOT NULL,
  `is_frame` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `druk_img_library` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `svg` varchar(255) NOT NULL,
  `categorie` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_attribuut` (
  `prod_design_attribuut_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `kleur_id` int(11) NOT NULL,
  `maat_id` int(11) NOT NULL,
  `druktype_id` int(11) NOT NULL,
  PRIMARY KEY (`prod_design_attribuut_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_colorimages` (
  `colorimages_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `kleurcode` varchar(23) NOT NULL,
  `label` varchar(255) NOT NULL,
  `imgurl` varchar(255) NOT NULL,
  `meerprijs` decimal(6,2) NOT NULL,
  PRIMARY KEY (`colorimages_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_droparea` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `x1` int(11) NOT NULL,
  `x2` int(11) NOT NULL,
  `y1` int(11) NOT NULL,
  `y2` int(11) NOT NULL,
  `outputwidth` int(11) NOT NULL,
  `outputheight` int(11) NOT NULL,
  `output_x1` int(11) NOT NULL,
  `output_x2` int(11) NOT NULL,
  `output_y1` int(11) NOT NULL,
  `output_y2` int(11) NOT NULL,
  `imagewidth` int(11) NOT NULL,
  `imageheight` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `overlayimage` varchar(255) NOT NULL,
  `use_overlay` int(11) NOT NULL,
  `pdfoverlayimage` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `surcharge` varchar(255) NOT NULL,
  `surcharge_table` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_druktype` (
  `prod_design_druktype_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `meerprijs` decimal(6,2) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`prod_design_druktype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_exportimages` (
  `export_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quoteitem_id` int(11) NOT NULL,
  `connect_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`export_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_exportlayers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `connect_id` int(11) NOT NULL,
  `svg` longtext NOT NULL,
  `label` varchar(255) NOT NULL,
  `side` varchar(255) NOT NULL,
  `svg_file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_export_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `export_combining` varchar(255) NOT NULL,
  `empty_design` varchar(255) NOT NULL,
  `pdf_width` varchar(255),
  `pdf_height` varchar(255),
  `pdf_margin_vertical` int(11),
  `pdf_margin_horizontal` int(11),
  `pdf_margin_items_vertical` int(11),
  `pdf_margin_items_horizontal` int(11),
  `finished` int(11) NOT NULL,
  `pdf_file` varchar(255) NOT NULL,
  `pdf_file_printing` varchar(255),
  `pdf_file_sublimation` varchar(255),
  `pdf_file_engraving` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `fonts` varchar(255) NOT NULL,
  `fontsizes` text NOT NULL,
  `image_categories` varchar(255),
  `colors` varchar(255),
  `store_ids` varchar(255) NOT NULL,
  `display_header` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_kleur` (
  `prod_design_kleur_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `kleurcode` varchar(23) NOT NULL,
  `meerprijs` decimal(6,2) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`prod_design_kleur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_maat` (
  `prod_design_maat_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `meerprijs` decimal(6,2) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`prod_design_maat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_qrcodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_saved` (
  `save_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `color` varchar(255) NOT NULL,
  `sizes` varchar(255) NOT NULL,
  `druktype` varchar(255) NOT NULL,
  `json` mediumtext NOT NULL,
  `svg` mediumtext NOT NULL,
  `svgfile` varchar(255) NOT NULL,
  `pdf` varchar(255) NOT NULL,
  `png` varchar(255) NOT NULL,
  `x1` int(11) NOT NULL,
  `x2` int(11) NOT NULL,
  `y1` int(11) NOT NULL,
  `y2` int(11) NOT NULL,
  `output_x1` int(11) NOT NULL,
  `output_x2` int(11) NOT NULL,
  `output_y1` int(11) NOT NULL,
  `output_y2` int(11) NOT NULL,
  `outputwidth` int(11) NOT NULL,
  `outputheight` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `imagetype` varchar(255) NOT NULL,
  `imagewidth` int(11) NOT NULL,
  `imageheight` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `connect_id` int(11) NOT NULL,
  `savetype` varchar(255) NOT NULL,
  `pdf_file` varchar(255) NOT NULL,
  `is_ordered` int(11) NOT NULL,
  `qrcodes` varchar(255) NOT NULL,
  PRIMARY KEY (`save_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `connect_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `json` mediumtext NOT NULL,
  `svg` mediumtext NOT NULL,
  `autoload` int(1) NOT NULL,
  `title` varchar(255),
  `products` varchar(255),
  `image` varchar(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			');

			$installer->run('
CREATE TABLE IF NOT EXISTS `prod_design_textcolors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `color` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			');

		}

        $installer->endSetup();

    }
}