<?php
namespace Redaxscript;

use SimpleXMLElement;

/**
 * parent class to load and convert data
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Reader
 * @author Henry Ruhs
 */

class Reader
{
	/**
	 * data array
	 *
	 * @var array
	 */

	protected $_dataArray = array();

	/**
	 * get the array
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */

	public function getArray()
	{
		return $this->_dataArray;
	}

	/**
	 * get the json
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */

	public function getJSON()
	{
		return json_encode($this->_dataArray);
	}

	/**
	 * get the xml
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */

	public function getXML()
	{
		$element = new SimpleXMLElement('<root>');
		array_walk_recursive($this->_dataArray, array(
			$element,
			'addChild'
		));
		return $element->asXML();
	}

	/**
	 * load json from url
	 *
	 * @since 3.0.0
	 *
	 * @param string $url
	 * @param boolean $assoc
	 *
	 * @return Reader
	 */

	public function loadJSON($url = null, $assoc = true)
	{
		$contents = file_get_contents($url);
		$this->_dataArray = json_decode($contents, $assoc);
		return $this;
	}

	/**
	 * load xml from url
	 *
	 * @since 3.0.0
	 *
	 * @param string $url
	 * @param boolean $assoc
	 *
	 * @return Reader
	 */

	public function loadXML($url = null, $assoc = true)
	{
		$contents = file_get_contents($url);
		$xml = simplexml_load_string($contents);
		$this->_dataArray = json_decode(json_encode($xml), $assoc);
		return $this;
	}
}
