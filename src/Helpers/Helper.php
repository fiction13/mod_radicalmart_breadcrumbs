<?php
/*
 * @package   mod_radicalmart_breadcrumbs
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class modRadicalMartBreadcrumbsHelper
{
	/**
	 * @var array
	 *
	 * @since 1.0.0
	 */
	protected $params = [];


	/**
	 * @var array
	 *
	 * @since 1.0.0
	 */
	protected $categoriesModel = [];

	/**
	 * @var array
	 *
	 * @since 1.0.0
	 */
	protected $categories = [];

	/**
	 * @var array
	 *
	 * @since 1.0.0
	 */
	protected $tree = [];

	/**
	 * @param   Registry  $params
	 *
	 * @throws Exception
	 */
	public function __construct(Registry $params)
	{
		$this->params = $params;
	}

	/**
	 * Retrieve breadcrumb items
	 *
	 * @return array
	 */
	public function getList()
	{
		// Get the PathWay object from the application
		$app     = Factory::getApplication();
		$pathway = $app->getPathway();
		$items   = $pathway->getPathWay();
		$lang    = Factory::getLanguage();
		$menu    = $app->getMenu();

		// Look for the home menu
		if (JLanguageMultilang::isEnabled())
		{
			$home = $menu->getDefault($lang->getTag());
		}
		else
		{
			$home = $menu->getDefault();
		}

		$count = count($items);

		// Don't use $items here as it references JPathway properties directly
		$crumbs = array();

		for ($i = 0; $i < $count; $i++)
		{
			$crumbs[$i]         = new stdClass;
			$crumbs[$i]->name   = stripslashes(htmlspecialchars($items[$i]->name, ENT_COMPAT, 'UTF-8'));
			$crumbs[$i]->link   = !is_null($items[$i]->link) ? Route::_($items[$i]->link) : '';
			$crumbs[$i]->childs = $i ? $this->getChilds($items[$i - 1]->link, $items[$i]->link) : false;
		}

		if ($this->params->get('showHome', 1))
		{
			$item         = new stdClass;
			$item->name   = htmlspecialchars($this->params->get('homeText', Text::_('MOD_RADICALMART_BREADCRUMBS_HOME')), ENT_COMPAT, 'UTF-8');
			$item->link   = Route::_('index.php?Itemid=' . $home->id);
			$item->childs = false;

			array_unshift($crumbs, $item);
		}

		return $crumbs;
	}

	/**
	 * @param $link
	 *
	 * @return false|array
	 *
	 * @since 1.0.0
	 */
	public function getChilds($parentLink, $currentLink)
	{
		if (Factory::getApplication()->input->get('option') != 'com_radicalmart')
		{
			return false;
		}

		$parentId  = $this->getCategoryId($parentLink);
		$currentId = $this->getCategoryId($currentLink);

		if (!$parentId)
		{
			return false;
		}

		// Get model
		$model = $this->getCategoriesModel();

		$model->setState('category.id', $parentId);
		$model->setState('filter.item_id', $currentId);
		$model->setState('filter.item_id.include', false);

		// Get categories
		$categories = $model->getItems();

		return $categories;
	}

	/**
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getAllCategories()
	{
		if (!$this->categories)
		{
			$model = $this->getCategoriesModel();

			$model->setState('category.id', null);
			$model->setState('filter.item_id', null);

			$this->categories = $model->getItems();
		}

		return $this->categories;

	}

	/**
	 * @param $link
	 *
	 * @return false|int
	 *
	 * @since 1.0.0
	 */
	public function getCategoryId($link)
	{
		// Check if link non sef
		if (strpos($link, 'index.php',) !== false)
		{
			parse_str(parse_url($link, PHP_URL_QUERY), $queryArray);

			if ($queryArray['option'] == 'com_radicalmart' && isset($queryArray['view']) && $queryArray['view'] == 'category')
			{
				return (int) $queryArray['id'];
			}

			return false;
		}

		// Filter all categories by sef link for get category id if sef link is set
		$categories = $this->getAllCategories();

		$category = array_filter($categories, static function ($category) use ($link) {
			return $category->link == $link;
		});

		if ($category)
		{
			$category = array_shift($category);

			return (int) $category->id;
		}

		return false;
	}

	/**
	 * Method get RadicalMart categories model object
	 *
	 * @return object RadicalMartModelCategories
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function getCategoriesModel()
	{
		if (!$this->categoriesModel)
		{
			JLoader::register('RadicalMartHelperIntegration', JPATH_ADMINISTRATOR . '/components/com_radicalmart/helpers/integration.php');
			JLoader::register('modRadicalMartCategoriesHelper', __DIR__ . '/src/Helpers/Helper.php');

			RadicalMartHelperIntegration::initializeSite();

			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_radicalmart/models');
			$model = BaseDatabaseModel::getInstance('Categories', 'RadicalMartModel', array('ignore_request' => true));

			$model->setState('params', Factory::getApplication()->getParams());
			$model->setState('filter.published', 1);
			$model->setState('filter.show', 1);

			$this->categoriesModel = $model;
		}

		return $this->categoriesModel;
	}
}