<?php
namespace Redaxscript\Tests\Controller;

use Redaxscript\Controller;
use Redaxscript\Db;
use Redaxscript\Tests\TestCaseAbstract;

/**
 * SearchTest
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 * @author Balázs Szilágyi
 *
 * @covers Redaxscript\Controller\ControllerAbstract
 * @covers Redaxscript\Controller\Search
 */

class SearchTest extends TestCaseAbstract
{
	/**
	 * setUp
	 *
	 * @since 3.1.0
	 */

	public function setUp() : void
	{
		parent::setUp();
		$optionArray =
		[
			'adminName' => 'Test',
			'adminUser' => 'test',
			'adminPassword' => 'test',
			'adminEmail' => 'test@test.com'
		];
		$installer = $this->installerFactory();
		$installer->init();
		$installer->rawCreate();
		$installer->insertSettings($optionArray);
		$categoryOne = Db::forTablePrefix('categories')->create();
		$categoryOne
			->set(
			[
				'title' => 'Category One',
				'alias' => 'category-one',
				'date' => 1451602800
			])
			->save();
		Db::forTablePrefix('categories')
			->create()
			->set(
			[
				'title' => 'Category Two',
				'alias' => 'category-two',
				'date' => 1454281200
			])
			->save();
		Db::forTablePrefix('categories')
			->create()
			->set(
			[
				'title' => 'Category Three',
				'alias' => 'category-three',
				'status' => 0,
				'date' => 1456786800
			])
			->save();
		$articleOne = Db::forTablePrefix('articles')->create();
		$articleOne
			->set(
			[
				'title' => 'Article One',
				'alias' => 'article-one',
				'category' => $categoryOne->id,
				'date' => 1451602800
			])
			->save();
		Db::forTablePrefix('articles')
			->create()
			->set(
			[
				'title' => 'Article Two',
				'alias' => 'article-two',
				'category' => $categoryOne->id,
				'date' => 1454281200
			])
			->save();
		Db::forTablePrefix('articles')
			->create()
			->set(
			[
				'title' => 'Article Three',
				'alias' => 'article-three',
				'status' => 0,
				'date' => 1456786800
			])
			->save();
		Db::forTablePrefix('comments')
			->create()
			->set(
			[
				'author' => 'Comment One',
				'text' => 'Comment One',
				'article' => $articleOne->id,
				'date' => 1451602800
			])
			->save();
		Db::forTablePrefix('comments')
			->create()
			->set(
			[
				'author' => 'Comment Two',
				'text' => 'Comment Two',
				'article' => $articleOne->id,
				'date' => 1454281200
			])
			->save();
		Db::forTablePrefix('comments')
			->create()
			->set(
			[
				'author' => 'Comment Three',
				'text' => 'Comment Three',
				'article' => $articleOne->id,
				'status' => 0,
				'date' => 1456786800
			])
			->save();
	}

	/**
	 * tearDown
	 *
	 * @since 3.1.0
	 */

	public function tearDown() : void
	{
		$this->dropDatabase();
	}

	/**
	 * testProcess
	 *
	 * @since 3.0.0
	 *
	 * @param array $registryArray
	 * @param string $expect
	 *
	 * @dataProvider providerAutoloader
	 */

	public function testProcess(array $registryArray = [], string $expect = null) : void
	{
		/* setup */

		$this->_registry->init($registryArray);
		$searchController = new Controller\Search($this->_registry, $this->_request, $this->_language, $this->_config);

		/* actual */

		$actual = $searchController->process();

		/* compare */

		$this->assertEquals($expect, $actual);
	}
}
