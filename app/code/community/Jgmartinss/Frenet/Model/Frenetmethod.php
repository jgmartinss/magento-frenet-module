<?php
/**
 * This source file is subject to the MIT License.
 * It is also available through http://opensource.org/licenses/MIT
 *
 * @category  module-magento
 * @package   Jgmartinss_Frenet
 * @author    João Martins <jgmartinsss@hotmail.com>
 * @copyright 2018 João Martins
 * @license   http://opensource.org/licenses/MIT MIT
 * @link      https://github.com/jgmartinss/magento-frenet-module
 */
 
class Jgmartinss_Frenet_Model_Frenetmethod 
    extends Mage_Shipping_Model_Carrier_Abstract 
        implements Mage_Shipping_Model_Carrier_Interface
{
    
    const CODE = 'jgmartinss_frenet';

    protected $_code = self::CODE;


    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        $result = Mage::getModel('shipping/rate_result');
        $helper = Mage::helper('jgmartinss_frenet');

        $response = json_decode(
            $helper->curlPost(
                $helper->getUrlQuote(), 
                $this->prepareQuoteData($request, $helper)
            ), true
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
    
    private function prepareQuoteData($request, $helper)
    {
        $productIds = array();
        $productInfo = array();

        $quote = Mage::getModel('checkout/cart')->getQuote();
        $productInfo['SellerCEP'] = $helper->getRealPosteCode($request->getPostcode());
        $productInfo['RecipientCEP'] = $helper->formaterNumber($request->getDestPostcode());        
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
            $productInfo['ShippingItemArray'][]['Width'] = $productModel->getWidth();  
        }

        $productInfo['RecipientCountry'] = $request->getCountryId(); 
        
        return json_encode($productInfo); 
    }
    
    public function getAllowedMethods()
    {
        return [
            $this->$_code => $this->getConfigData('title'),
        ];
    }

    public function isTrackingAvailable() 
    {  
        return false; 
    }
}
