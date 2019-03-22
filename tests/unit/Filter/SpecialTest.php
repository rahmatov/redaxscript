<?php
namespace Redaxscript\Tests\Filter;

use Redaxscript\Filter;
use Redaxscript\Tests\TestCaseAbstract;

/**
 * SpecialTest
 *
 * @since 2.2.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 *
 * @covers Redaxscript\Filter\Special
 */

class SpecialTest extends TestCaseAbstract
{
	/**
	 * testSpecial
	 *
	 * @since 2.2.0
	 *
	 * @param string $special
	 * @param string $expect
	 *
	 * @dataProvider providerAutoloader
	 */

	public function testSpecial(string $special = null, string $expect = null) : void
	{
		/* setup */

		$filter = new Filter\Special();

		/* actual */

		$actual = $filter->sanitize($special);

		/* compare */

		$this->assertEquals($expect, $actual);
	}
}
