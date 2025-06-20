<?php
declare(strict_types=1);

namespace Laurensmedia\Productdesigner\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class Customprice implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $_request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->_request = $request;
    }
    
    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $data = $this->_request->getParam('cart');
        if (empty($data)) {
            return $this;
        }
        
        $item = $observer->getEvent()->getData('quote_item');
        if (!$item) {
            return $this;
        }
        
        $connectId = $this->_request->getParam('connect_id');
        $number = (int)($data['number'] ?? 0) - 1;
        
        if ($number < 0) {
            return $this;
        }
        
        $labels = [];
        $colorimages = [];
        $width = [];
        $height = [];
        $x = [];
        $y = [];
        
        for ($i = 0; $i <= $number; $i++) {
            if (!isset($data[$i]) || !isset($data[$i]['label'])) {
                continue;
            }
            
            $label = $data[$i]['label'];
            $labels[] = $label;
            $colorimages[$label] = $data[$i]['image'] ?? '';
            $width[$label] = $data[$i]['width'] ?? 0;
            $height[$label] = $data[$i]['height'] ?? 0;
            $x[$label] = $data[$i]['x'] ?? 0;
            $y[$label] = $data[$i]['y'] ?? 0;
        }
        
        // Store only required data
        $saveData = [
            'labels' => $labels,
            'colorimages' => $colorimages,
            'width' => $width,
            'height' => $height,
            'x' => $x,
            'y' => $y,
            'connect_id' => $connectId,
            'color' => $data['color'] ?? ''
        ];
        
        $item->setProductdesignerData(json_encode($saveData));
        
        return $this;
    }
}