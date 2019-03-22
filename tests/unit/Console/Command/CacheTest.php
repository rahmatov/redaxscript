<?php
namespace Redaxscript\Tests\Console\Command;

use Redaxscript\Console\Command;
use Redaxscript\Tests\TestCaseAbstract;

/**
 * CacheTest
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 *
 * @covers Redaxscript\Console\Command\Cache
 * @covers Redaxscript\Console\Command\CommandAbstract
 */

class CacheTest extends TestCaseAbstract
{
	/**
	 * tearDown
	 *
	 * @since 3.0.0
	 */

	public function tearDown() : void
	{
		$this->_request->setServer('argv', null);
	}

	/**
	 * testNoArgument
	 *
	 * @since 3.0.0
	 */

	public function testNoArgument() : void
	{
		/* setup */

		$cacheCommand = new Command\Cache($this->_registry, $this->_request, $this->_language, $this->_config);

		/* expect and actual */

		$expect = $cacheCommand->getHelp();
		$actual = $cacheCommand->run('cli');

		/* compare */

		$this->assertEquals($expect, $actual);
	}

	/**
	 * testClear
	 *
	 * @since 3.0.0
	 */

	public function testClear() : void
	{
		/* setup */

		$this->_request->setServer('argv',
		[
			'console.php',
			'cache',
			'clear',
			'--directory',
			'cache',
			'--extension',
			'css',
			'--bundle',
			'base.min.css'
		]);
		$cacheCommand = new Command\Cache($this->_registry, $this->_request, $this->_language, $this->_config);

		/* expect and actual */

		$expect = $cacheCommand->success();
		$actual = $cacheCommand->run('cli');

		/* compare */

		$this->assertEquals($expect, $actual);
	}

	/**
	 * testClearFailure
	 *
	 * @since 3.0.0
	 */

	public function testClearFailure() : void
	{
		/* setup */

		$this->_request->setServer('argv',
		[
			'console.php',
			'cache',
			'clear',
			'--no-interaction'
		]);
		$cacheCommand = new Command\Cache($this->_registry, $this->_request, $this->_language, $this->_config);

		/* expect and actual */

		$expect = $cacheCommand->error();
		$actual = $cacheCommand->run('cli');

		/* compare */

		$this->assertEquals($expect, $actual);
	}

	/**
	 * testClearInvalid
	 *
	 * @since 3.0.0
	 */

	public function testClearInvalid() : void
	{
		/* setup */

		$this->_request->setServer('argv',
		[
			'console.php',
			'cache',
			'clear-invalid',
			'--directory',
			'cache',
			'--extension',
			'js',
			'--lifetime',
			'3600'
		]);
		$cacheCommand = new Command\Cache($this->_registry, $this->_request, $this->_language, $this->_config);

		/* expect and actual */

		$expect = $cacheCommand->success();
		$actual = $cacheCommand->run('cli');

		/* compare */

		$this->assertEquals($expect, $actual);
	}

	/**
	 * testClearInvalidFailure
	 *
	 * @since 3.0.0
	 */

	public function testClearInvalidFailure() : void
	{
		/* setup */

		$this->_request->setServer('argv',
		[
			'console.php',
			'cache',
			'clear-invalid',
			'--no-interaction'
		]);
		$cacheCommand = new Command\Cache($this->_registry, $this->_request, $this->_language, $this->_config);

		/* expect and actual */

		$expect = $cacheCommand->error();
		$actual = $cacheCommand->run('cli');

		/* compare */

		$this->assertEquals($expect, $actual);
	}
}
