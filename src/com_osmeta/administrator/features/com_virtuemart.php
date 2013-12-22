<?php
/**
 * @category  Joomla Component
 * @package   osmeta
 * @author    JoomBoss
 * @copyright 2012, JoomBoss. All rights reserved
 * @copyright 2013 Open Source Training, LLC. All rights reserved
 * @contact   www.ostraining.com, support@ostraining.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @version   1.0.0
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$features['com_virtuemart'] = array(
    'name'=>'Virtuemart Products',
    'priority'=>1,
    'file'=>'VM_ProductMetatagsContainer.php',
    'class'=>'VM_ProductMetatagsContainer',
    'params'=>array(array('option'=>'com_virtuemart', 'page'=>'shop.product_details'))
);
?>