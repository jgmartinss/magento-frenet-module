<?php

/**
 * This source file is subject to the MIT License.
 * It is also available through http://opensource.org/licenses/MIT
 *
 * @category  jgmartinss
 * @package   Jgmartinss_Frenet
 * @author    João Martins <jgmartinsss@hotmail.com>
 * @copyright 2017 João Martins
 * @license   http://opensource.org/licenses/MIT MIT
 * @link      https://github.com/jgmartinss/freight-module
 */

class Jgmartinss_Frenet_Helper_Data 
	extends Mage_Core_Helper_Abstract
{

	public function getConfig($field)
	{
		return Mage::getStoreConfig('carriers/jgmartinss_frenet/' . $field);
	}

	public function getUrlQuote()
	{
		return $this->getConfig('url_quote');
	}

	public function getToken()
	{
		return $this->getConfig('token');
	}

	public function getAdditionalDays()
	{
		return $this->getConfig('additional_days');
	}

	public function getWorkingDays($serviceDescription, $deliveryTime)
	{
		$totalDays = $this->getAdditionalDays() + $deliveryTime;

		return Mage::helper('jgmartinss_frenet')->__($serviceDescription . ' - Working Days - ' . $totalDays);
	}
	
	public function getFrenetData($api, $data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api);
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

