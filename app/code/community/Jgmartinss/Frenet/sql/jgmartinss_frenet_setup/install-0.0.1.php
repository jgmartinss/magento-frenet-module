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
 
 /** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

// Add atribute Length
$codOne = "legth";

$configOne = array(
    'position' => 1,
    'required' => 0,
    'label'    => 'Comprimento',
    'type'     => 'int',
    'input'    => 'text',
    'apply_to' => 'simple,bundle,grouped,configurable',
    'note'     => 'Comprimento mínimo 16(cm)'
);
$setup->addAttribute('catalog_product', $codOne, $configOne);

// Add atribute Width
$codTwo = "height";

$configTwo = array(
    'position' => 1,
    'required' => 0,
    'label'    => 'Altura',
    'type'     => 'int',
    'input'    => 'text',
    'apply_to' => 'simple,bundle,grouped,configurable',
    'note'     => 'Altura mínimo 2(cm)'
);
$setup->addAttribute('catalog_product', $codTwo, $configTwo);

// Add atribute Height
$codThree = "width";

$configThree = array(
    'position' => 1,
    'required' => 0,
    'label'    => 'Largura',
    'type'     => 'int',
    'input'    => 'text',
    'apply_to' => 'simple,bundle,grouped,configurable',
    'note'     => 'Largura mínimo 11(cm)'
);

$setup->addAttribute('catalog_product', $codThree, $configThree);

$installer->endSetup();
