<?php
namespace Laurensmedia\Productdesigner\Controller\Adminhtml\products;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Save extends \Magento\Backend\App\Action
{

    /**
     * @param Action\Context $context
     */
    public function __construct(Action\Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        
        $testId = $this->getRequest()->getParam('id');
        $_product = $this->_objectManager->create('\Magento\Catalog\Model\Product')->load($testId);
        $postData = $data;
        
        $product_id = $data['product_id'];

        $storeId = $this->getRequest()->getParam('store_switch');
        if($storeId == 0){
	        $storeId = null;
        }
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
	        try{
	            $model = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection');
				$fileSystem = $this->_objectManager->create('\Magento\Framework\Filesystem');
				$mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
// 				echo '<pre>';print_r($postData);exit;
	            
	
				/* ZIJDEN OPSLAAN */
	            $current = $model
	                  ->addFieldToFilter('product_id', $product_id);
		        if($storeId > 0){
					$current->addFieldToFilter('store_id', $storeId);
				} else {
					$current->addFieldToFilter('store_id', array('null' => true));
				}
	            foreach ($current as $currentitem) {
	                $id = $currentitem['id'];
	                $tmpModel = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Products');
	                $tmpModel->setId($id);
	                $tmpModel->delete();
	            }
	
	            $sidecounter = $postData['sidecounter'];
	            $groups = '';
	            if(isset($postData['group'])){
		            if(is_array($postData['group'])){
			            $groups = implode(',', $postData['group']);
		            } else {
			            $groups = implode(',', array($postData['group']));
		            }
		        }
	            for ($z=1; $z<=$sidecounter;$z++) {
	                $sides = $postData['side'];
	                if(!isset($sides[$z])){
	                    continue;
	                }
	                $side = $sides[$z];
	                $label = $side['label'];
	                $label = str_replace(' ', '_', $side['label']);
	//                     $image = $side['image'];
	                $image = $_FILES['side']['name'][$z]['image'];
	                $imgurl = "imgurl_" . $z;
	//                     $overlayimage = $side['overlay'];
	                $overlayimage = $_FILES['side']['name'][$z]['overlay'];
	                $overlayimgurl = "overlayimgurl_" . $z;
	                $pdfoverlayimage = '';
	                if(isset($_FILES['side']['name'][$z]['pdfoverlay'])){
		                $pdfoverlayimage = $_FILES['side']['name'][$z]['pdfoverlay'];
		            }
	                $pdfoverlayimgurl = "pdfoverlayimgurl_" . $z;
	                $cutoutsvgimage = '';
	                if(isset($_FILES['side']['name'][$z]['cutoutsvg'])){
		                $cutoutsvgimage = $_FILES['side']['name'][$z]['cutoutsvg'];
		            }
	                $cutoutsvgimgurl = "cutoutsvgimgurl_" . $z;
	                $surcharge = $side['surcharge'];
	                
					$surchargeTable = array();
					if(isset($postData['surchargeQty'][$z])){
						foreach($postData['surchargeQty'][$z] as $index => $surchargeQty){
							$surchargePrice = $postData['surchargePrice'][$z][$index];
							$surchargeTable[$surchargeQty] = $surchargePrice;
						}
					}
	                
	                $x1label = $label."_x1";
	                if(isset($postData[$x1label])){
		                $x1 = $postData[$x1label];
		                $x2label = $label."_x2";
		                $x2 = $postData[$x2label];
		                $y1label = $label."_y1";
		                $y1 = $postData[$y1label];
		                $y2label = $label."_y2";
		                $y2 = $postData[$y2label];
						$x1outputlabel = $label."_output_x1";
						$x1output = $postData[$x1outputlabel];
						$x2outputlabel = $label."_output_x2";
						$x2output = $postData[$x2outputlabel];
						$y1outputlabel = $label."_output_y1";
						$y1output = $postData[$y1outputlabel];
						$y2outputlabel = $label."_output_y2";
						$y2output = $postData[$y2outputlabel];
		                $outputWidthLabel = $label."_outputwidth";
		                $outputWidth = $postData[$outputWidthLabel];
		                $outputHeightLabel = $label."_outputheight";
		                $outputHeight = $postData[$outputHeightLabel];
		            }
	                if ($label != null) {
	                    $tmpModel = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Products');
	                    $tmpModel->addData(array(
	                        'label' => $label,
	                        'product_id'   => $product_id,
	                        'group'   => $groups,
	                        'image' => "0",
	                        'surcharge' => $surcharge,
							'surcharge_table' => json_encode($surchargeTable),
							'store_id' => $storeId
	                    ))->save();
	                    if ($image != null || (isset($postData[$imgurl]) && ($postData[$imgurl] != "0" || $postData[$imgurl] != null))) {
	                        $folder = $mediaPath .'/'. 'productdesigner' .'/'. 'sideimages';
	                        $overlayfolder = $mediaPath .'/'. 'productdesigner' .'/'. 'overlayimgs';
	                        if ($image) {
	                            $imagename = $product_id . "_" . $image;
	                            $pngimagename = strtolower(strstr($imagename, '.', true)).'.png';
	                            $overlayimagename = $product_id . "_" . $image;
	                            $filename = $folder .'/'. $imagename;
	                            $pngfilename = $folder .'/'. $pngimagename;
	                            $overlayfilename = $overlayfolder .'/'. $imagename;
	                            $copied = move_uploaded_file($_FILES['side']['tmp_name'][$z]['image'], $filename);
	
	                            $copiedoverlay = copy($filename, $overlayfilename);
	
	                            $imgFile = $overlayfilename;
	
	                            // Begin resizen overlay
	                            $ext = pathinfo($imgFile, PATHINFO_EXTENSION);
	                            if($ext == 'png'){
		                            $img = imagecreatefrompng($imgFile);
								} else {
		                            $img = imagecreatefromjpeg($imgFile);
								}
	                            $width = imagesx($img);
	                            $height = imagesy($img);
	                            $transparentImage = imagecreatetruecolor($width, $height);
	
	                            $black = imagecolorallocatealpha($img, 0, 0, 0, 127);
	                            imagefill($transparentImage, 0, 0, $black);
	                            $white = imagecolorallocatealpha($img, 255, 255, 255, 50);
	
	                            for ($x = 0; $x < $width; $x++) {
	                                for ($y = 0; $y < $height; $y++) {
	                                    $color = imagecolorat($img, $x, $y);
	                                    $color = imagecolorsforindex($img, $color);
	                                    if ($color['alpha'] == 127) {
	                                        imagesetpixel($transparentImage, $x, $y, $white);
	                                    } else {
	                                        imagesetpixel($transparentImage, $x, $y, $black);
	                                    }
	                                }
	                            }
	                            ImageColorTransparent($img, $black);
	                            imageAlphaBlending($transparentImage, true);
	                            imageSaveAlpha($transparentImage, true);
	
	                            ImagePng($transparentImage, $overlayfilename);
	                            ImageDestroy($img);
	                            ImageDestroy($transparentImage);
	
	                            $xBorder = 0;
	                            $yBorder = 0;
	                            $newWidth = 850;
	                            $newHeight = 600;
	                            $imageWithBorder = imagecreatetruecolor($newWidth, $newHeight);
	                            imagefill($imageWithBorder, 0, 0, $black);
	                            if ($width >= $height) {
	                                $scaleFactor = $height / $newHeight;
	                                $resizedWidth = $width / $scaleFactor;
	                                $totalBorder = $newWidth - $resizedWidth;
	                                $xBorder = $totalBorder / 2;
	                                imagefilledrectangle($imageWithBorder, 0, 0, $xBorder, $newHeight, $white);
	                                imagefilledrectangle($imageWithBorder, $xBorder+$resizedWidth, 0, $newWidth, $newHeight, $white);
	                            } else {
	                                $scaleFactor = $width / $newWidth;
	                                $resizedWidth = $height / $scaleFactor;
	                                $totalBorder = $newHeight - $resizedHeight;
	                                $yBorder = $totalBorder / 2;
	                                imagefilledrectangle($imageWithBorder, 0, 0, $yBorder, $newWidth, $white);
	                                imagefilledrectangle($imageWithBorder, $yBorder+$resizedHeight, 0, $newHeight, $newWidth, $white);
	                            }
	
	                            $source = imagecreatefrompng($overlayfilename);
	                            imagealphablending($imageWithBorder, true);
	                            imagesavealpha($imageWithBorder, true);
	                            imagecopyresampled($imageWithBorder, $source, $xBorder, $yBorder, 0, 0, $resizedWidth, $newHeight, $width, $height);
	                            imagepng($imageWithBorder, $overlayfilename);
	                            ImageDestroy($imageWithBorder);
	                            // Einde resizen overlay
	
	                            // Resize image
	/*
	                            $rimage = new Varien_Image($filename);
	                            $rimage->constrainOnly(false);
	                            $rimage->keepAspectRatio(true);
	                            $rimage->keepFrame(true);
	                            $rimage->keepTransparency(true);
	                            $rimage->backgroundColor(array(255,255,255));
	                            $rimage->resize(850, 600);
	                            $rimage->save($filename);
	*/
	                        } else {
	                            if (isset($postData[$imgurl]) && $postData[$imgurl]) {
	                                $imagename = $postData[$imgurl];
	                                $overlayimagename = $postData[$overlayimgurl];
	                            } else {
	                                $imagename = "0";
	                                $overlayimagename = "0";
	                            }
	                        }
							$id = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
								->addFieldToFilter('product_id', $product_id)
								->addFieldToFilter('label', $label);
					        if($storeId > 0){
								$id->addFieldToFilter('store_id', $storeId);
							} else {
								$id->addFieldToFilter('store_id', array('null' => true));
							}
							$id = $id->getFirstItem();
	                        $id = $id['id'];
	                        $data = array();
	                        $data['image'] = $imagename;
	                        $data['overlayimage'] = $overlayimagename;
							$data['group'] = $groups;
	                        $datamodel = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Products')->load($id)->addData($data);
	                        $datamodel->setId($id)->save();
	                    }
	                    if (isset($postData[$overlayimgurl]) && ($overlayimage != null || $postData[$overlayimgurl] != "0" || $postData[$overlayimgurl] != null)) {
	                        $overlayfolder = $mediaPath .'/'. 'productdesigner/' .'/'. 'overlayimgs/';
	                        if ($overlayimage) {
	                            $overlayimagename = $product_id . "_" . $overlayimage;
	                            $filename = $overlayfolder . $overlayimagename;
	                            $thumbFile = str_replace('.png', '_400.png', $filename);
	                            if(file_exists($thumbFile)){
		                            unlink($thumbFile);
	                            }
	                            $copied = move_uploaded_file($_FILES['side']['tmp_name'][$z]['overlay'], $filename);
	                        }
							$id = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
								->addFieldToFilter('product_id', $product_id)
								->addFieldToFilter('label', $label);
					        if($storeId > 0){
								$id->addFieldToFilter('store_id', $storeId);
							} else {
								$id->addFieldToFilter('store_id', array('null' => true));
							}
							$id = $id->getFirstItem();
	                        $id = $id['id'];
	                        $data = array();
	                        if ($overlayimagename != "") {
	                            $data['overlayimage'] = $overlayimagename;
	                        }
	                        $datamodel = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Products')->load($id)->addData($data);
	                        $datamodel->setId($id)->save();
	                        if ($postData['side'][$z]['useoverlay'] == '1') {
	                            //$x1 = 0;
	                            //$y1 = 0;
	                            //$x2 = 850;
	                            //$y2 = 600;
	                            $useOverlay = '1';
	                        } else {
	                            $useOverlay = '0';
	                        }
	                    }
	                    if (isset($postData[$pdfoverlayimgurl]) && ($pdfoverlayimage != null || $postData[$pdfoverlayimgurl] != "0" || $postData[$pdfoverlayimgurl] != null)) {
	                        $pdfoverlayfolder = $mediaPath .'/'. 'productdesigner/' .'/'. 'overlayimgs/';
	                        if (isset($pdfoverlayimage) && $pdfoverlayimage) {
	                            $pdfoverlayimagename = $product_id . "_" . $pdfoverlayimage;
	                            $filename = $pdfoverlayfolder . $pdfoverlayimagename;
	                            $copied = move_uploaded_file($_FILES['side']['tmp_name'][$z]['pdfoverlay'], $filename);
	                        }
							$id = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
								->addFieldToFilter('product_id', $product_id)
								->addFieldToFilter('label', $label);
					        if($storeId > 0){
								$id->addFieldToFilter('store_id', $storeId);
							} else {
								$id->addFieldToFilter('store_id', array('null' => true));
							}
							$id = $id->getFirstItem();
	                        $id = $id['id'];
	                        $data = array();
	                        if (isset($pdfoverlayimagename) && $pdfoverlayimagename != "") {
	                            $data['pdfoverlayimage'] = $pdfoverlayimagename;
	                        }
	                        $datamodel = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Products')->load($id)->addData($data);
	                        $datamodel->setId($id)->save();
	                    }
	                    if (isset($postData[$cutoutsvgimgurl]) && ($cutoutsvgimage != null || $postData[$cutoutsvgimgurl] != "0" || $postData[$cutoutsvgimgurl] != null)) {
	                        $cutoutsvgfolder = $mediaPath .'/'. 'productdesigner/' .'/'. 'cutoutsvg/';
	                        if (isset($cutoutsvgimage) && $cutoutsvgimage) {
	                            $cutoutsvgimagename = $product_id . "_" . $cutoutsvgimage;
	                            $filename = $cutoutsvgfolder . $cutoutsvgimagename;
	                            $copied = move_uploaded_file($_FILES['side']['tmp_name'][$z]['cutoutsvg'], $filename);
	                        }
							$id = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
								->addFieldToFilter('product_id', $product_id)
								->addFieldToFilter('label', $label);
					        if($storeId > 0){
								$id->addFieldToFilter('store_id', $storeId);
							} else {
								$id->addFieldToFilter('store_id', array('null' => true));
							}
							$id = $id->getFirstItem();
	                        $id = $id['id'];
	                        $data = array();
	                        if (isset($cutoutsvgimagename) && $cutoutsvgimagename != "") {
	                            $data['cutoutsvg'] = $cutoutsvgimagename;
	                        }
	                        $datamodel = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Products')->load($id)->addData($data);
	                        $datamodel->setId($id)->save();
	                    }
						$sides = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
							->addFieldToFilter('product_id', $product_id)
							->addFieldToFilter('label', $label);
				        if($storeId > 0){
							$sides->addFieldToFilter('store_id', $storeId);
						} else {
							$sides->addFieldToFilter('store_id', array('null' => true));
						}
						$sides = $sides->getFirstItem();
	
						if(isset($postData[$x1label])){
							$id = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Products\Collection')
								->addFieldToFilter('product_id', $product_id)
								->addFieldToFilter('label', $label);
					        if($storeId > 0){
								$id->addFieldToFilter('store_id', $storeId);
							} else {
								$id->addFieldToFilter('store_id', array('null' => true));
							}
							$id = $id->getFirstItem();
		                    $id = $id['id'];
		                    $data = array();
		                    $data['x1'] = $x1;
		                    $data['x2'] = $x2;
		                    $data['y1'] = $y1;
		                    $data['y2'] = $y2;
							$data['output_x1'] = $x1output;
							$data['output_x2'] = $x2output;
							$data['output_y1'] = $y1output;
							$data['output_y2'] = $y2output;
		                    $data['outputwidth'] = $outputWidth;
		                    $data['outputheight'] = $outputHeight;
		                    $data['use_overlay'] = $useOverlay;
							$data['group'] = $groups;

							// Delete image(s)
							if($postData['side'][$z]['deleteimages'] == 'both') {
								$data['use_overlay'] = 0;
								$data['image'] = '';
								$data['overlayimage'] = '';
								$data['pdfoverlayimage'] = '';
							} elseif($postData['side'][$z]['deleteimages'] == 'background'){
								$data['image'] = '';
							} elseif($postData['side'][$z]['deleteimages'] == 'pdfoverlay'){
								$data['pdfoverlayimage'] = '';
							} elseif($postData['side'][$z]['deleteimages'] == 'overlay'){
								$data['use_overlay'] = 0;
								$data['overlayimage'] = '';
							}

		                    $datamodel = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Products')->load($id)->addData($data);
		                    $datamodel->setId($id)->save();
		                }
	                }
	            }
	
	
				/* EINDE ZIJDEN OPSLAAN */



				/* MATEN OPSLAAN */
				$maatids = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Sizes\Collection')
					->addFieldToFilter('product_id', $product_id);
		        if($storeId > 0){
					$maatids->addFieldToFilter('store_id', $storeId);
				} else {
					$maatids->addFieldToFilter('store_id', array('null' => true));
				}
				$maatids = $maatids->load();
                foreach ($maatids as $maatid) {
                    $id = $maatid['prod_design_maat_id'];
	                $tmpModel = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Sizes');
	                $tmpModel->setId($id);
	                $tmpModel->delete();
	            }

                $maatcounter = $postData['maatcounter'];
                $i = 1;
                for ($i=1; $i<=$maatcounter;$i++) {
                    $maatdata = array();
	                if(!isset($postData["maat_$i"])){
		                continue;
	                }
                    $name = $postData["maat_$i"];
                    if ($postData["maat_$i"] != "") {
                        if (is_numeric($postData["meerprijs_$i"])) {
                            $meerprijs = $postData["meerprijs_$i"];
                            $meerprijs = round($meerprijs, 2);
                        } else {
                            $meerprijs = round("0", 2);
                        }

                        $product_id = $testId;
						$this->_objectManager->create('Laurensmedia\Productdesigner\Model\Sizes')
	                        ->addData(array(
	                            'name' => $name,
	                            'meerprijs'   => $meerprijs,
	                            'product_id'   => $product_id,
	                            'store_id' => $storeId
	                        ))
	                        ->save();
                    }
                }
				/* EINDE MATEN OPSLAAN */

				/* DRUKTYPE OPSLAAN */
                $productdrukid = $this->getRequest()->getParam('id');
				$druktypeids = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Printingquality\Collection')
					->addFieldToFilter('product_id', $product_id);
		        if($storeId > 0){
					$druktypeids->addFieldToFilter('store_id', $storeId);
				} else {
					$druktypeids->addFieldToFilter('store_id', array('null' => true));
				}
				$druktypeids = $druktypeids->load();
                foreach ($druktypeids as $druktypeid) {
                    $id = $druktypeid['prod_design_druktype_id'];
	                $tmpModel = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Printingquality');
	                $tmpModel->setId($id);
	                $tmpModel->delete();
	            }

                $druktypecounter = $postData['druktypecounter'];
                $i = 1;
                for ($i=1; $i<=$druktypecounter;$i++) {
                    $druktypedata = array();
	                if(!isset($postData["druktype_$i"])){
		                continue;
	                }
                    $name = $postData["druktype_$i"];
                    if ($postData["druktype_$i"] != "") {
                        if (is_numeric($postData["druktypemeerprijs_$i"])) {
                            $meerprijs = $postData["druktypemeerprijs_$i"];
                            $meerprijs = round($meerprijs, 2);
                        } else {
                            $meerprijs = round("0", 2);
                        }

                        $product_id = $testId;
						$this->_objectManager->create('Laurensmedia\Productdesigner\Model\Printingquality')
	                        ->addData(array(
	                            'name' => $name,
	                            'meerprijs'   => $meerprijs,
	                            'product_id'   => $product_id,
	                            'store_id' => $storeId
	                        ))
	                        ->save();
                    }
                }
				/* EINDE DRUKTYPE OPSLAAN */



				/* KLEUREN OPSLAAN */
				if(isset($postData['colorimagescounter'])){
	                $colorimagescounter = $postData['colorimagescounter'];
	                $product_id = $testId;
					$kleurids = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\ResourceModel\Colorimages\Collection')
						->addFieldToFilter('product_id', $product_id);
			        if($storeId > 0){
						$kleurids->addFieldToFilter('store_id', $storeId);
					} else {
						$kleurids->addFieldToFilter('store_id', array('null' => true));
					}
					$kleurids = $kleurids->load();

	                foreach ($kleurids as $kleurid) {
	                    $id = $kleurid['colorimages_id'];
		                $tmpModel = $this->_objectManager->create('Laurensmedia\Productdesigner\Model\Colorimages');
		                $tmpModel->setId($id);
		                $tmpModel->delete();
		            }
	                for ($c=0; $c <= $colorimagescounter; $c++) {
	                    if (isset($postData["color"][$c]) && $postData["color"][$c]['label'] != "") {
	                        $labels = $postData["color"][$c]['label'];
	                        $foldername = $_product->getId();
	                        $folder = $mediaPath .'/'. 'productdesigner' .'/'. 'color_img/'.$foldername;
	                        if(!file_exists($folder)){
		                        mkdir($folder, 0777, true);
		                    }
	                        foreach ($labels as $label) {
	                            $kleurcode = $postData["kleurcode_$c"];
	                            if ($kleurcode != "") {
	                                $kleurmeerprijs = $postData["kleurmeerprijs_$c"];
	                                if ($kleurmeerprijs == "") {
	                                    $kleurmeerprijs = "0.00";
	                                }
	                                $image = $_FILES['color']['name'][$c][$label]['image'];
	                                if ($image == null) {
	                                    $imgurl = $postData["color"][$c][$label]['imgurl'];
	                                }
	                                if ($image != null) {
	                                    $imagename = $product_id . "_" . $image;
	                                    $pngimagename = strtolower(strstr($imagename, '.', true)).'.png';
	                                    $overlayimagename = $product_id . "_" . $image;
	                                    $filename = $folder .'/'. $imagename;
	                                    $imgurl = $foldername."/".$imagename;
	                                    $pngfilename = $folder .'/'. $pngimagename;
	                                    $overlayfilename = $overlayfolder .'/'. $imagename;
	                                    $copied = move_uploaded_file($_FILES['color']['tmp_name'][$c][$label]['image'], $filename);
	
	                                    // Resize image
/*
	                                    $rimage = new Varien_Image($filename);
	                                    $rimage->constrainOnly(false);
	                                    $rimage->keepAspectRatio(true);
	                                    $rimage->keepFrame(true);
	                                    $rimage->keepTransparency(true);
	                                    $rimage->backgroundColor(array(255,255,255));
	                                    $rimage->resize(850, 600);
	                                    $rimage->save($filename);
*/
	                                }
	                                if ($imgurl != "") {
										$this->_objectManager->create('Laurensmedia\Productdesigner\Model\Colorimages')
					                        ->addData(array(
		                                        'product_id' => $product_id,
		                                        'kleurcode'   => $kleurcode,
		                                        'label'   => $label,
		                                        'imgurl'   => $imgurl,
		                                        'meerprijs' => $kleurmeerprijs,
		                                        'store_id' => $storeId
					                        ))
					                        ->save();
	                                }
	                            }
	                        }
	                    }
	                }
	
/*
	                $attriData = $this->getRequest()->getPost();
	                $attModel = Mage::getModel('shirt/attribute')->getCollection()
	                	->addFieldToFilter('product_id', $testId)->getFirstItem();
					$attModel
						->addData($attriData)
						->setId($attModel->getId())
						->save();
*/
				}
				/* EINDE KLEUREN OPSLAAN */



				
            } catch (\Exception $e) {
	            echo 'exception';
				echo $e->getMessage();exit;
            }

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Products has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('productdesigner/products/edit', ['id' => $product_id, '_current' => true, 'store' => $storeId]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Products.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id'), 'store' => $storeId]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}