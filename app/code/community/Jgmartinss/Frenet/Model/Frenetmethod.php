<?php

/**
 * This source file is subject to the MIT License.
 * It is also available through http://opensource.org/licenses/MIT
 *
 * @category  module
 * @package   Jgmartinss_Frenet
 * @author    João Martins <jgmartinsss@hotmail.com>
 * @copyright 2017 João Martins
 * @license   http://opensource.org/licenses/MIT MIT
 * @link      https://github.com/jgmartinss/freight-module
 */
 
class Jgmartinss_Frenet_Model_Frenetmethod 
    extends Mage_Shipping_Model_Carrier_Abstract 
    implements Mage_Shipping_Model_Carrier_Interface
{
    
    const CODE = 'jgmartinss_frenet';

    protected $_code = self::CODE;

    protected $frenetData = null;

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
        $helper = Mage::helper('jgmartinss_frenet');

        $this->prepareQuoteData($request);

        $response = json_decode(
            $helper->getFrenetData($helper->getUrlQuote(), $this->frenetData), true
        );
        
        foreach ($response['ShippingSevicesArray'] as $rateFrenet) {
            
            $serviceCode = $rateFrenet['ServiceCode'];
            $serviceDescription = $rateFrenet['ServiceDescription'];
            $shippingPrice = $rateFrenet['ShippingPrice'];
            $deliveryTime = $rateFrenet['DeliveryTime'];
    
            $method = Mage::getModel('shipping/rate_result_method');
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod($serviceCode);
            $method->setMethodTitle($helper->getWorkingDays($serviceDescription, $deliveryTime));
            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);
            $result->append($method);
        }

        return $result;
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
        }

        $productInfo['RecipientCountry'] = $request->getCountryId(); 

        $this->frenetData = json_encode($productInfo); 
    }
    
    /**
     * Returns the allowed carrier methods
     *
     * @return array
     */
    
    public function getAllowedMethods()
    {
        return array(
            'jgmartinss_frenet' => $this->getConfigData('title'),
        );
    }

    /**
     * Check if current carrier offer support to tracking
     *
     * @return bool true
     */

    public function isTrackingAvailable() 
    {  
        return false;
    }

}

