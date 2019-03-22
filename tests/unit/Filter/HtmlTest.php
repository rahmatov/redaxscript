<?php
namespace Redaxscript\Tests\Filter;

use Redaxscript\Filter;
use Redaxscript\Tests\TestCaseAbstract;

/**
 * HtmlTest
 *
 * @since 2.2.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 *
 * @covers Redaxscript\Filter\Html
 */

class HtmlTest extends TestCaseAbstract
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
	 * testHtml
	 *
	 * @since 2.2.0
	 *
	 * @param string $html
	 * @param string $expect
	 *
	 * @dataProvider providerAutoloader
	 */

	public function testHtml(string $html = null, string $expect = null) : void
	{
		/* setup */

		$filter = new Filter\Html();

		/* actual */

		$actual = $filter->sanitize($html);

		/* compare */

		$this->assertEquals($expect, $actual);
	}
}
