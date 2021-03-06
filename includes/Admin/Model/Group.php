<?php
namespace Redaxscript\Admin\Model;

use Redaxscript\Model as BaseModel;

/**
 * parent class to provide the admin group model
 *
 * @since 4.0.0
 *
 * @package Redaxscript
 * @category Model
 * @author Henry Ruhs
 */

class Group extends BaseModel\Group
{
	/**
	 * is unique by id and alias
	 *
	 * @since 4.0.0
	 *
	 * @param int $groupId identifier of the group
	 * @param string $groupAlias alias of the group
	 *
	 * @return bool
	 */

	public function isUniqueByIdAndAlias(int $groupId = null, string $groupAlias = null) : bool
	{
		return !$this->getByAlias($groupAlias)->id || $this->getByAlias($groupAlias)->id === $this->getById($groupId)->id;
	}

	/**
	 * create the group by array
	 *
	 * @since 4.0.0
	 *
	 * @param array $createArray array of the create
	 *
	 * @return bool
	 */

	public function createByArray(array $createArray = []) : bool
	{
		return $this
			->query()
			->create()
			->set($createArray)
			->save();
	}

	/**
	 * update the group by id and array
	 *
	 * @since 4.0.0
	 *
	 * @param int $groupId identifier of the group
	 * @param array $updateArray array of the update
	 *
	 * @return bool
	 */

	public function updateByIdAndArray(int $groupId = null, array $updateArray = []) : bool
	{
		return $this
			->query()
			->whereIdIs($groupId)
			->findOne()
			->set($updateArray)
			->save();
	}

	/**
	 * enable the group by id
	 *
	 * @since 4.0.0
	 *
	 * @param int $groupId identifier of the group
	 *
	 * @return bool
	 */

	public function enableById(int $groupId = null) : bool
	{
		return $this
			->query()
			->whereIdIs($groupId)
			->findOne()
			->set('status', 1)
			->save();
	}

	/**
	 * disable the group by id
	 *
	 * @since 4.0.0
	 *
	 * @param int $groupId identifier of the group
	 *
	 * @return bool
	 */

	public function disableById(int $groupId = null) : bool
	{
		return $this
			->query()
			->whereIdIs($groupId)
			->findOne()
			->set('status', 0)
			->save();
	}

	/**
	 * delete the group by id
	 *
	 * @since 4.0.0
	 *
	 * @param int $groupId identifier of the group
	 *
	 * @return bool
	 */

	public function deleteById(int $groupId = null) : bool
	{
		return $this->query()->whereIdIs($groupId)->deleteMany();
	}
}
