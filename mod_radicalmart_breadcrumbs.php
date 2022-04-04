<?php
/*
 * @package   mod_radicalmart_breadcrumbs
 * @version   1.0.1
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

// Include helper
JLoader::register('modRadicalMartBreadcrumbsHelper', __DIR__ . '/src/Helpers/Helper.php');

// Get the breadcrumbs
$helper          = new modRadicalMartBreadcrumbsHelper($params);
$list            = $helper->getList();
$count           = count($list);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_radicalmart_breadcrumbs', $params->get('layout', 'default'));
