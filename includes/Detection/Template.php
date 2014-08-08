<?php
namespace Redaxscript\Detection;
use Redaxscript\Db as Db;
use Redaxscript\Detection as Detection;
use Redaxscript\Request as Request;

/**
 * children class to detect the required template
 *
 * @since 2.0.0
 *
 * @category Detection
 * @package Redaxscript
 * @author Henry Ruhs
 */

class Template extends Detection
{
	/**
	 * init the class
	 *
	 * @since 2.1.0
	 */

	public function init()
	{
		$this->_detect(array(
			'query' => Request::getQuery('t'),
			'session' => Request::getSession($this->_registry->get('root') . '/template'),
			'contents' => $this->_registry->get('lastTable') ? Db::forPrefixTable($this->_registry->get('lastTable'))->where('id', $this->_registry->get('lastId'))->findOne()->template : null,
			'settings' => Db::getSettings('template'),
			'fallback' => 'default'
		), 'template', 'templates/{value}/index.phtml');
	}
}