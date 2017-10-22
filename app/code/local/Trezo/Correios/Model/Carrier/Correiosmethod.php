<?php

/**
 * This source file is subject to the MIT License.
 * It is also available through http://opensource.org/licenses/MIT
 *
 * @category  jgmartinss
 * @package   Trezo_Correios
 * @author    João Martins <jgmartinsss@hotmail.com>
 * @copyright 2017 João Martins
 * @license   http://opensource.org/licenses/MIT MIT
 * @link      https://github.com/jgmartinss/freight-module
 */
 
class Trezo_Correios_Model_Carrier_Correiosmethod 
    extends Mage_Shipping_Model_Carrier_Abstract 
    implements Mage_Shipping_Model_Carrier_Interface
{
    
    /**
     * _code property
     *
     * @var string
     */

    protected $_code = 'trezo_correios';

    /**
     *  response var
     *
     *  @var array
     */

    protected $frenetQuoteData = null;
    protected $frenetTrackingData = null; 


    /**
     * Collect Rate Frenet Api
     *
     * @param Mage_Shipping_Model_Rate_Request $request Mage request
     *
     */

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
        $this->prepareQuoteData($request);
        $this->prepareTrackingData($request); 

        return $this->getFrenetRate($result);
    }

    /**
     * Returns the allowed carrier methods
     *
     * @return array
     */

    public function getAllowedMethods()
    {
        return array(
            'trezo_correios' => $this->getConfigData('title'),
        );
    }

    /**
     * Check if current carrier offer support to tracking
     *
     * @return bool true
     */

    public function isTrackingAvailable() 
    {  
        return true;
    }

    /**
     *  Returns the end result of additional days 
     *
     *  @param String $serviceDescription , Int $deliveryTime
     *
     *  @return String
     */

    public function getWorkingDays($serviceDescription, $deliveryTime)
    {
        $totalDays = $this->getConfigData('additional_days') + $deliveryTime;

        return $serviceDescription." - Dias Ùteis ".$totalDays;
    }

    /**
     *  Api/System.xml methods
     *
     *  @return string
     */

    private function getApiQuote()
    {
        return $this->getConfigData('api_quote');
    }

    private function getApiTracking()
    {
        return $this->getConfigData('api_tracking');
    }

    private function getApiToken()
    {
        return $this->getConfigData('api_token');
    }

    /**
     *  @param result
     *
     *  @return Rate $result
     */

    public function getFrenetRate($result)
    {
        $quoteResponse = json_decode($this->getFrenetData($this->getApiQuote(), $this->frenetQuoteData), true);
        
        foreach ($quoteResponse['ShippingSevicesArray'] as $rateFrenet) {
            $serviceCode = $rateFrenet['ServiceCode'];
            $serviceDescription = $rateFrenet['ServiceDescription'];
            $shippingPrice = $rateFrenet['ShippingPrice'];
            $deliveryTime = $rateFrenet['DeliveryTime'];

            $rate = Mage::getModel('shipping/rate_result_method');
            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle($this->getConfigData('title'));
            $rate->setMethod($serviceCode);
            $rate->setMethodTitle($this->getWorkingDays($serviceDescription, $deliveryTime));
            $rate->setPrice($shippingPrice);
            $rate->setCost($shippingPrice);
            $result->append($rate);
        }

        return $result;
    }


    /**
     * Get Tracking for popup
     */

    public function getTrackingInfo($tracking) 
    {
        $trackInfo = Mage::getModel('sales/order_shipment_track')->load($tracking, 'track_number');
        $this->_result = Mage::getModel('shipping/rate_result');
        $this->getApiTrackingInfo();

        return $this->_result;
    }

    /**
     *  Get response api 
     *
     *  @return result Tracking
     */

    public function getApiTrackingInfo()
    {
        //$trackingResponse = json_decode($this->getFrenetData($this->getApiTracking(), $this->frenetTrackingData), true);

        /*Add false values ​​if api does not work*/

        $track = Mage::getModel('shipping/tracking_result_status');
        $track->setTracking('TE123456785AA');
        $track->setCarrier($this->_code);
        $track->setUrl('wwww.coorioes.com/test');
        $track->setTrackSummary('Objeto aguardando retirada no endereço indicado');
        $track->setCarrierTitle($this->getConfigData('title'));
        $track->setProgressdetail(
            array(
                array(
                    'deliverydate'     => '09/02/2017 08:47', 
                    'deliverylocation' => 'São Paulo/SP',
                    'activity'         => 'Objeto postado'
                ),
                array(
                    'deliverydate'     => now(), 
                    'deliverylocation' => 'Blumenau-SC',
                    'activity'         => 'Saiu para entrega ao destinatário'
                    )
                )
            );

        $this->_result = $track;
    }

    /**
     *  @param String $api
     *  @param Json   $data
     *
     *  @return json
     */

    private function getFrenetData($api, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $token = $this->getApiToken();

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "token: {$token}"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     *  @param $request
     *
     *  @return json
     */

    private function prepareTrackingData($request)
    {
        $trackingInfo = array();

        $orderId = $this->getOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
         
        $shippingServiceCode = $order->getShippingMethod();
        $realCode = substr($shippingServiceCode, -5);

        $trackingInfo['ShippingServiceCode'] = $realCode;

        foreach ($order->getTracksCollection() as $track){
            $trackingInfo['NumberTracking'] = $track->getNumber();
        }

        $this->frenetTrackingData = json_encode($trackingInfo);
    }

    /**
     *  @param $request
     *
     *  @return json
     */

    private function prepareQuoteData($request)
    {
        $productIds   = array();
        $productInfo  = array();

        $quote = Mage::getModel('checkout/cart')->getQuote();
        $productInfo['SellerCEP'] = $request->getPostcode();
        $productInfo['RecipientCEP'] = $request->getDestPostcode();        
        $productInfo['ShipmentInvoiceValue'] = $request->getPackageValueWithDiscount();

        foreach($quote->getAllItems() as $item) {
            $productIds[] = $item->getProductId();
            $productInfo['ShippingItemArray'][]['Quantity'] = $item->getQty();
            $productInfo['ShippingItemArray'][]['SKU'] = $item->getSku();
        }
        
        foreach ($productIds as $productId) {
            $productModel = Mage::getModel('catalog/product')->load($productId);
            $productInfo['ShippingItemArray'][]['Weight'] = $productModel->getWeight();
            $productInfo['ShippingItemArray'][]['Length'] = $productModel->getLength();
            $productInfo['ShippingItemArray'][]['Height'] = $productModel->getHeight();
            $productInfo['ShippingItemArray'][]['Width']  = $productModel->getWidth(); 
            
            $category = $productModel->getCategoryIds();
            foreach ($category as $categoryId) {
                $cat = Mage::getModel('catalog/category')->load($categoryId);
                $productInfo['ShippingItemArray'][]['Category'] = $cat->getName();
            } 
        }

        $productInfo['RecipientCountry'] = $request->getCountryId(); 

        $this->frenetQuoteData = json_encode($productInfo); 
    }
}
