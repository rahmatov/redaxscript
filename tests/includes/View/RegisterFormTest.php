<?php
namespace Redaxscript\Tests\View;

use Redaxscript\Db;
use Redaxscript\Tests\TestCase;
use Redaxscript\View;

/**
 * RegisterFormTest
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 */

class RegisterFormTest extends TestCase
{
	/**
	 * setUpBeforeClass
	 *
	 * @since 3.0.0
	 */

	public static function setUpBeforeClass()
	{
		Db::setSetting('captcha', 1);
	}

	/**
	 * tearDownAfterClass
	 *
	 * @since 3.0.0
	 */

	public static function tearDownAfterClass()
	{
		Db::setSetting('captcha', 0);
	}

	/**
	 * providerRender
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */

	public function providerRender()
	{
		return $this->getProvider('tests/provider/View/register_form_render.json');
	}

	/**
	 * testRender
	 *
	 * @since 3.0.0
	 *
	 * @param array $expect
	 *
	 * @dataProvider providerRender
	 */

	public function testRender($expect = array())
	{
		/* setup */

		$registerForm = new View\RegisterForm();

		/* actual */

		$actual = $registerForm->render();

		/* compare */

		$this->assertStringStartsWith($expect['start'], $actual);
		$this->assertStringEndsWith($expect['end'], $actual);
	}
}
