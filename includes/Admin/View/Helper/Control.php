<?php
namespace Redaxscript\Admin\View\Helper;

use Redaxscript\Admin\View\ViewAbstract;
use Redaxscript\Html;
use Redaxscript\Module;
use function array_replace_recursive;
use function in_array;
use function ucfirst;

/**
 * helper class to create the admin control
 *
 * @since 4.0.0
 *
 * @package Redaxscript
 * @category View
 * @author Henry Ruhs
 */

class Control extends ViewAbstract
{
	/**
	 * options of the panel
	 *
	 * @var array
	 */

	protected $_optionArray =
	[
		'className' =>
		[
			'list' => 'rs-admin-list-control',
			'item' =>
			[
				'control' => 'rs-admin-item-control',
				'disable' => 'rs-admin-item-disable',
				'enable' => 'rs-admin-item-enable',
				'future-posting' => 'rs-admin-item-future-posting',
				'unpublish' => 'rs-admin-item-unpublish',
				'publish' => 'rs-admin-item-publish',
				'edit' => 'rs-admin-item-edit',
				'delete' => 'rs-admin-item-delete',
				'install' => 'rs-admin-item-install',
				'uninstall' => 'rs-admin-item-uninstall'
			],
			'link' =>
			[
				'delete' => 'rs-admin-js-delete',
				'uninstall' => 'rs-admin-js-uninstall'
			]
		]
	];

	/**
	 * init the class
	 *
	 * @since 4.0.0
	 *
	 * @param array $optionArray options of the panel
	 */

	public function init(array $optionArray = []) : void
	{
		$this->_optionArray = array_replace_recursive($this->_optionArray, $optionArray);
	}

	/**
	 * render the view
	 *
	 * @since 4.0.0
	 *
	 * @param string $table name of the table
	 * @param int $id identifier of the item
	 * @param string $alias alias of the item
	 * @param int $status status of the item
	 *
	 * @return string|null
	 */

	public function render(string $table = null, int $id = null, string $alias = null, int $status = null) : ?string
	{
		$output = Module\Hook::trigger('adminControlStart');
		$outputItem = null;
		$parameterRoute = $this->_registry->get('parameterRoute');
		$token = $this->_registry->get('token');

		/* html element */

		$element = new Html\Element();
		$listElement = $element
			->copy()
			->init('ul',
			[
				'class' => $this->_optionArray['className']['list']
			]);
		$itemElement = $element
			->copy()
			->init('li',
			[
				'class' => $this->_optionArray['className']['item']['control']
			]);
		$linkElement = $element
			->copy()
			->init('a');
		$textElement = $element
			->copy()
			->init('span');

		/* collect enable */

		if ($this->_hasPermission($table, 'edit') && $this->_showAction($table, 'enable', $id))
		{
			$enableAction = $status ? 'disable' : 'enable';
			$outputItem .= $itemElement
				->copy()
				->addClass($enableAction === 'disable' ? $this->_optionArray['className']['item']['disable'] : $this->_optionArray['className']['item']['enable'])
				->html(
					$linkElement
						->copy()
						->attr('href', $parameterRoute . 'admin/' . $enableAction . '/' . $table . '/' . $id . '/' . $token)
						->text($enableAction === 'disable' ? $this->_language->get('disable') : $this->_language->get('enable'))
				);
		}

		/* collect publish */

		if ($this->_hasPermission($table, 'edit') && $this->_showAction($table, 'publish', $id))
		{
			if ($status === 2)
			{
				$outputItem .= $itemElement
					->copy()
					->addClass($this->_optionArray['className']['item']['future-posting'])
					->html(
						$textElement
							->copy()
							->text($this->_language->get('future_posting'))
					);
			}
			else
			{
				$publishAction = $status ? 'unpublish' : 'publish';
				$outputItem .= $itemElement
					->copy()
					->addClass($publishAction === 'unpublish' ? $this->_optionArray['className']['item']['unpublish'] : $this->_optionArray['className']['item']['publish'])
					->html(
						$linkElement
							->copy()
							->attr('href', $parameterRoute . 'admin/' . $publishAction . '/' . $table . '/' . $id . '/' . $token)
							->text($publishAction === 'unpublish' ? $this->_language->get('unpublish') : $this->_language->get('publish'))
					);
			}
		}

		/* collect install */

		if ($this->_hasPermission($table, 'install') && $this->_showAction($table, 'install', $id))
		{
			$outputItem .= $itemElement
				->copy()
				->addClass($this->_optionArray['className']['item']['install'])
				->html(
					$linkElement
						->copy()
						->attr('href', $parameterRoute . 'admin/install/' . $table . '/' . $alias . '/' . $token)
						->text($this->_language->get('install'))
				);
		}

		/* collect edit */

		if ($this->_hasPermission($table, 'edit') && $this->_showAction($table, 'edit', $id))
		{
			$outputItem .= $itemElement
				->copy()
				->addClass($this->_optionArray['className']['item']['edit'])
				->html(
					$linkElement
						->copy()
						->attr('href', $parameterRoute . 'admin/edit/' . $table . '/' . $id)
						->text($this->_language->get('edit'))
				);
		}

		/* collect delete */

		if ($this->_hasPermission($table, 'delete') && $this->_showAction($table, 'delete', $id))
		{
			$outputItem .= $itemElement
				->copy()
				->addClass($this->_optionArray['className']['item']['delete'])
				->html(
					$linkElement
						->copy()
						->addClass($this->_optionArray['className']['link']['delete'])
						->attr('href', $parameterRoute . 'admin/delete/' . $table . '/' . $id . '/' . $token)
						->text($this->_language->get('delete'))
				);
		}

		/* collect uninstall */

		if ($this->_hasPermission($table, 'uninstall') && $this->_showAction($table, 'uninstall', $id))
		{
			$outputItem .= $itemElement
				->copy()
				->addClass($this->_optionArray['className']['item']['uninstall'])
				->html(
					$linkElement
						->copy()
						->addClass($this->_optionArray['className']['link']['uninstall'])
						->attr('href', $parameterRoute . 'admin/uninstall/' . $table . '/' . $alias . '/' . $token)
						->text($this->_language->get('uninstall'))
				);
		}

		/* collect output */

		if ($outputItem)
		{
			$output .= $listElement->html($outputItem);
		}
		$output .= Module\Hook::trigger('adminControlEnd');
		return $output;
	}

	/**
	 * has the permission
	 *
	 * @since 4.0.0
	 *
	 * @param string $table name of the table
	 * @param string $type
	 *
	 * @return bool
	 */

	protected function _hasPermission(string $table = null, string $type = null) : bool
	{
		return (bool)$this->_registry->get($table . ucfirst($type));
	}

	/**
	 * show the action
	 *
	 * @since 4.0.0
	 *
	 * @param string $table name of the table
	 * @param string $type
	 * @param int $id
	 *
	 * @return bool
	 */

	protected function _showAction(string $table = null, string $type = null, int $id = null) : bool
	{
		$enableArray =
		[
			'groups',
			'users',
			'modules'
		];
		$publishArray =
		[
			'categories',
			'articles',
			'extras',
			'comments'
		];
		$deleteArray =
		[
			'categories',
			'articles',
			'extras',
			'comments',
			'groups',
			'users'
		];
		if ($id === 1 && ($type === 'enable' || $type === 'delete') && ($table === 'users' || $table === 'groups'))
		{
			return false;
		}
		return $type === 'enable' && in_array($table, $enableArray) && $id ||
			$type === 'publish' && in_array($table, $publishArray) && $id ||
			$type === 'delete' && in_array($table, $deleteArray) && $id ||
			$type === 'install' && $table === 'modules' && !$id ||
			$type === 'uninstall' && $table === 'modules' && $id ||
			$type === 'edit' && $id;
	}
}
