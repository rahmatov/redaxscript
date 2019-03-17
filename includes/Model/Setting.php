<?php
namespace Redaxscript\Model;

/**
 * parent class to provide the setting model
 *
 * @since 3.3.0
 *
 * @package Redaxscript
 * @category Model
 * @author Henry Ruhs
 */

class Setting extends ModelAbstract
{
	/**
	 * name of the table
	 *
	 * @var string
	 */

	protected $_table = 'settings';

	/**
	 * get the value from settings
	 *
	 * @since 3.3.0
	 *
	 * @param string $key key of the item
	 *
	 * @return string|null
	 */

	public function get(string $key = null) : ?string
	{
		return $this->query()->getSetting($key);
	}

	/**
	 * set the value to settings
	 *
	 * @since 3.3.0
	 *
	 * @param string $key key of the item
	 * @param string $value value of the item
	 *
	 * @return bool
	 */

	public function set(string $key = null, string $value = null) : bool
	{
		return $this
			->query()
			->where('name', $key)
			->findOne()
			->set('value', $value)
			->save();
	}
}
