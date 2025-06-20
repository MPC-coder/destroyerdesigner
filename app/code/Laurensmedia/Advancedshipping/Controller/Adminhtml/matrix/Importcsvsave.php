<?php
namespace Laurensmedia\Advancedshipping\Controller\Adminhtml\matrix;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;


class Importcsvsave extends \Magento\Backend\App\Action
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
		if(file_exists($_FILES['import']['tmp_name'])){
			$csvContent = $this->csvToArray($_FILES['import']['tmp_name']);
            $model = $this->_objectManager->create('Laurensmedia\Advancedshipping\Model\Matrix');
			unset($csvContent[0]);
			foreach($csvContent as $row){
				$data = array();
				$data['id'] = $row[0];
				$data['store_ids'] = $row[1];
				$data['customer_group_ids'] = $row[2];
				$data['shipping_groups'] = $row[3];
				$data['country'] = $row[4];
				$data['city'] = $row[5];
				$data['zip_from'] = $row[6];
				$data['zip_to'] = $row[7];
				$data['totalitems_from'] = $row[8];
				$data['totalitems_to'] = $row[9];
				$data['items_from'] = $row[10];
				$data['items_to'] = $row[11];
				$data['weight_from'] = $row[12];
				$data['weight_to'] = $row[13];
				$data['subtotal_from'] = $row[14];
				$data['subtotal_to'] = $row[15];
				$data['shipping_costs'] = $row[16];
				$data['shipping_description'] = $row[17];
				$data['stop_processing'] = $row[18];
				$data['stop_other_shippinggroups'] = $row[19];
				$data['shipping_code'] = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', '_', urldecode(html_entity_decode(strip_tags(str_replace(' ', '_', strtolower($data['shipping_description']))))))));
				// Save data
				if($data['id'] > 0){
					$this->_objectManager->create('Laurensmedia\Advancedshipping\Model\Matrix')
						->load($data['id'])
						->setData($data)
						->setId($data['id'])
						->save();
				} else {
					unset($data['id']);
					$this->_objectManager->create('Laurensmedia\Advancedshipping\Model\Matrix')
						->addData($data)
						->setId()
						->save();
				}
			}
		}
        
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

	public function csvToArray($filename='', $delimiter=',')
	{
		$arrCSV = array();
		if (($handle = fopen($filename, "r")) !==FALSE) {
			$key = 0;
			$headers = array();
			while (($data = fgetcsv($handle, 0, $delimiter)) !==FALSE) {
				$c = count($data);
				for ($x=0; $x < $c; $x++) {
					$arrCSV[$key][] = $data[$x];
				}
				$key++;
			}
			fclose($handle);
		}
		return $arrCSV;
	}
}