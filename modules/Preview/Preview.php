<?php
namespace Redaxscript\Modules\Preview;

use Redaxscript\Filesystem;
use Redaxscript\Head;
use Redaxscript\Html;
use Redaxscript\Module;
use function str_replace;

/**
 * preview template elements
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 */

class Preview extends Module\Module
{
	/**
	 * array of the module
	 *
	 * @var array
	 */

	protected static $_moduleArray =
	[
		'name' => 'Preview',
		'alias' => 'Preview',
		'author' => 'Redaxmedia',
		'description' => 'Preview template elements',
		'version' => '4.0.0'
	];

	/**
	 * array of the option
	 *
	 * @var array
	 */

	protected $_optionArray =
	[
		'className' =>
		[
			'title' => 'rs-title-preview',
			'box' => 'rs-is-preview'
		]
	];

	/**
	 * renderStart
	 *
	 * @since 4.0.0
	 */

	public function renderStart() : void
	{
		if ($this->_registry->get('firstParameter') === 'module' && $this->_registry->get('secondParameter') === 'preview')
		{
			$link = Head\Link::getInstance();
			$link
				->init()
				->appendFile(
				[
					'modules/Dialog/dist/styles/dialog.min.css',
					'modules/Preview/dist/styles/preview.min.css'
				]);
		}
	}

	/**
	 * routeHeader
	 *
	 * @since 3.3.0
	 */

	public function routeHeader() : void
	{
		if ($this->_registry->get('firstParameter') === 'module' && $this->_registry->get('secondParameter') === 'preview')
		{
			$this->_registry->set('useTitle', $this->_language->get('preview', '_preview'));
			$this->_registry->set('useDescription', $this->_language->get('description', '_preview'));
			$this->_registry->set('routerBreak', true);
		}
	}

	/**
	 * routeContent
	 *
	 * @since 3.3.0
	 */

	public function routeContent() : void
	{
		if ($this->_registry->get('firstParameter') === 'module' && $this->_registry->get('secondParameter') === 'preview')
		{
			echo $this->render();
		}
	}

	/**
	 * render
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */

	public function render() : string
	{
		$output = null;
		$partialsFilesystem = new Filesystem\File();
		$partialsFilesystem->init('modules/Preview/partials');
		$thirdParameter = $this->_registry->get('thirdParameter');
		$extension = '.phtml';

		/* collect output */

		if ($thirdParameter)
		{
			$output = $this->_renderPartial($thirdParameter, $partialsFilesystem->renderFile($thirdParameter . $extension));
		}
		else
		{
			foreach ($partialsFilesystem->getSortArray() as $value)
			{
				$alias = str_replace($extension, '', $value);
				$output .= $this->_renderPartial($alias, $partialsFilesystem->renderFile($value));
			}
		}
		return $output;
	}

	/**
	 * renderPartial
	 *
	 * @since 3.2.0
	 *
	 * @param string $alias
	 * @param string $html
	 *
	 * @return string
	 */

	protected function _renderPartial(string $alias = null, string $html = null) : string
	{
		$thirdParameter = $this->_registry->get('thirdParameter');
		$parameterRoute = $this->_registry->get('parameterRoute');

		/* html element */

		$element = new Html\Element();
		$linkElement = $element
			->copy()
			->init('a',
			[
				'href' => $thirdParameter === $alias ? $parameterRoute . 'module/preview#' . $alias : $parameterRoute . 'module/preview/' . $alias
			])
			->text($thirdParameter === $alias ? $this->_language->get('back') : $alias);
		$titleElement = $element
			->copy()
			->init('h2',
			[
				'class' => $this->_optionArray['className']['title'],
				'id' => $alias
			])
			->html($linkElement);
		$boxElement = $element
			->copy()
			->init('div',
			[
				'class' => $this->_optionArray['className']['box'],
			])
			->html($html);

		/* collect output */

		$output = $titleElement . $boxElement;
		return $output;
	}
}
