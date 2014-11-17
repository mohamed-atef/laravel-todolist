<?php namespace GSD\Commands;

use Config;
use Todo;

class ArchiveListCommand extends CommandBase {

	protected $name = 'gsd:archive';

	protected $description = 'Archive a todo list.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		//Get list name to archive
		$name = $this->askForListId(true, true, false,'Select list to archive:');
		if (is_null($name))
		{
			$this->abort();
		}

		if(Config::get('todo.defaultList') == $name)
		{
			$this->outputErrorBox('Cannot archive default list');
			return;
		}

		// Warn if list exists
		if($this->repository->exists($name, true))
		{
			$msg = "WARNING!\n\n"
				. " An archived version of the list '$name' exists.\n"
				. " This action will destroy the old archived version.";
			$this->outputErrorBox($msg);
		}

		$result = $this->ask("Are you sure you want to archive '$name' (yes/no)?");
		
		if ( ! str2bool($result))
		{
			$this->abort();
		}

		// Archive the list
		$list = Todo::get($name);
		$list->archive();
		$this->info("List '$name' has been archived");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}

}
