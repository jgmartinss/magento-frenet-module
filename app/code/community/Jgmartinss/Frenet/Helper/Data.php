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

class Jgmartinss_Frenet_Helper_Data 
    extends Mage_Core_Helper_Abstract
{

    public function getConfig($field)
    {
        return Mage::getStoreConfig('carriers/jgmartinss_frenet/' . $field);
    }

    public function formaterNumber($value)
    {
        return str_replace("-","", $value);
    }

    public function getToken()
    {
        return $this->getConfig('token');
    }

    public function getPostCodeConfig()
    {
        return $this->formaterNumber($this->getConfig('source_postal_code'));
    }

    public function getRealPosteCode($value)
    {
        if ($value == '') {
            return $this->getPostCodeConfig();
        } else {
            return $this->formaterNumber($value);
        }
    }

    public function getAdditionalDays()
    {
        return $this->getConfig('additional_days');
    }

    public function getWorkingDays($serviceDescription, $deliveryTime)
    {
        $totalDays = $this->getAdditionalDays() + $deliveryTime;
        return $this->__($serviceDescription.' - '.$totalDays.' Working Days');
    }

    public function getUrlQuote()
    {
        return $this->getConfig('url_quote');
    }

    public function getUrlTracking()
    {
        return $this->getConfig('url_tracking');
    }

    public function getUrlPostalCode()
    {
        return $this->getConfig('url_postal_code');
    }

    public function curlPost($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $token = $this->getToken();

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json",
          "token: {$token}"
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
} 
