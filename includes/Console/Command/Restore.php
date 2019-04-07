<?php
namespace Redaxscript\Console\Command;

use Redaxscript\Console\Parser;
use Redaxscript\Filesystem;
use function exec;
use function is_file;

/**
 * children class to execute the restore command
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Console
 * @author Henry Ruhs
 */

class Restore extends CommandAbstract
{
	/**
	 * array of the command
	 *
	 * @var array
	 */

	protected $_commandArray =
	[
		'restore' =>
		[
			'description' => 'Restore command',
			'argumentArray' =>
			[
				'database' =>
				[
					'description' => 'Restore the database',
					'optionArray' =>
					[
						'directory' =>
						[
							'description' => 'Required directory'
						],
						'file' =>
						[
							'description' => 'Required file'
						]
					]
				]
			]
		]
	];

	/**
	 * run the command
	 *
	 * @since 3.0.0
	 *
	 * @param string $mode name of the mode
	 *
	 * @return string|null
	 */

	public function run(string $mode = null) : ?string
	{
		$parser = new Parser($this->_request);
		$parser->init($mode);

		/* run command */

		$argumentKey = $parser->getArgument(1);
		$haltOnError = (bool)$parser->getOption('halt-on-error');
		if ($argumentKey === 'database')
		{
			return $this->_database($parser->getOption()) ? $this->success() : $this->error($haltOnError);
		}
		return $this->getHelp();
	}

	/**
	 * restore the database
	 *
	 * @since 3.0.0
	 *
	 * @param array $optionArray
	 *
	 * @return bool
	 */

	protected function _database(array $optionArray = []) : bool
	{
		$dbType = $this->_config->get('dbType');
		$dbHost = $this->_config->get('dbHost');
		$dbName = $this->_config->get('dbName');
		$dbUser = $this->_config->get('dbUser');
		$dbPassword = $this->_config->get('dbPassword');
		$directory = $this->prompt('directory', $optionArray);
		$file = $this->prompt('file', $optionArray);

		/* restore filesystem */

		$backupFilesystem = new Filesystem\Directory();
		$backupFilesystem->init($directory);

		/* restore */

		if (is_file($directory . DIRECTORY_SEPARATOR . $file))
		{
			$command = ':';
			$content = $backupFilesystem->readFile($file);
			if ($dbType === 'mysql' && $dbHost && $dbName && $dbUser && $dbPassword)
			{
				$command = $content . ' | mysql --host=' . $dbHost . ' --user=' . $dbUser . ' --password=' . $dbPassword . ' ' . $dbName;
			}
			if ($dbType === 'pgsql' && $dbHost && $dbName && $dbUser && $dbPassword)
			{
				$command = 'PGPASSWORD=' . $dbPassword . ' ' . $content . ' | psql --host=' . $dbHost . ' --username=' . $dbUser . ' ' . $dbName;
			}
			if ($dbType === 'sqlite' && is_file($dbHost))
			{
				$command = 'echo ' . $content . ' > ' . $dbHost;
			}
			exec($command, $outputArray, $error);
			return $error === 0;
		}
		return false;
	}
}
