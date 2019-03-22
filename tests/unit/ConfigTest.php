<?php
namespace Redaxscript\Tests;

use org\bovigo\vfs\vfsStream as Stream;
use org\bovigo\vfs\vfsStreamFile as StreamFile;
use org\bovigo\vfs\vfsStreamWrapper as StreamWrapper;

/**
 * ConfigTest
 *
 * @since 2.2.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 *
 * @covers Redaxscript\Config
 */

class ConfigTest extends TestCaseAbstract
{
	/**
	 * array to restore config
	 *
	 * @var array
	 */

	protected $_configArray = [];

	/**
	 * setUp
	 *
	 * @since 3.0.0
	 */

	public function setUp() : void
	{
		parent::setUp();
		Stream::setup('root');
		$file = new StreamFile('config.php');
		StreamWrapper::getRoot()->addChild($file);
		$this->_configArray = $this->_config->get();
	}

	/**
	 * tearDown
	 *
	 * @since 3.1.0
	 */

	public function tearDown() : void
	{
		$this->_config->set('dbType', $this->_configArray['dbType']);
		$this->_config->set('dbHost', $this->_configArray['dbHost']);
		$this->_config->set('dbPrefix', $this->_configArray['dbPrefix']);
		$this->_config->set('dbName', $this->_configArray['dbName']);
		$this->_config->set('dbUser', $this->_configArray['dbUser']);
		$this->_config->set('dbPassword', $this->_configArray['dbPassword']);
	}

	/**
	 * testInit
	 *
	 * @since 2.4.0
	 */

	public function testInit() : void
	{
		/* setup */

		$this->_config->init('config.php');

		/* actual */

		$actualArray = $this->_config->get();

		/* compare */

		$this->assertArrayHasKey('dbType', $actualArray);
	}

	/**
	 * testGetAndSet
	 *
	 * @since 2.2.0
	 */

	public function testGetAndSet() : void
	{
		/* setup */

		$this->_config->set('dbHost', 'localhost');

		/* actual */

		$actual = $this->_config->get('dbHost');

		/* compare */

		$this->assertEquals('localhost', $actual);
	}

	/**
	 * testGetAll
	 *
	 * @since 2.2.0
	 */

	public function testGetAll() : void
	{
		/* actual */

		$actualArray = $this->_config->get();

		/* compare */

		$this->assertArrayHasKey('dbHost', $actualArray);
	}

	/**
	 * testParse
	 *
	 * @since 3.0.0
	 *
	 * @param string $dbUrl
	 * @param array $configArray
	 *
	 * @dataProvider providerAutoloader
	 */

	public function testParse(string $dbUrl = null, array $configArray = []) : void
	{
		/* setup */

		$this->_config->init(Stream::url('root' . DIRECTORY_SEPARATOR . 'config.php'));
		$this->_config->parse($dbUrl);

		/* actual */

		$actual = $this->_config->get();

		/* compare */

		$this->assertEquals($configArray, $actual);
	}

	/**
	 * testWrite
	 *
	 * @since 2.4.0
	 */

	public function testWrite() : void
	{
		/* setup */

		$this->_config->init(Stream::url('root' . DIRECTORY_SEPARATOR . 'config.php'));

		/* actual */

		$actual = $this->_config->write();

		/* compare */

		$this->assertNotFalse($actual);
	}
}
