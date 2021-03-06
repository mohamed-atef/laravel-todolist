<?php namespace GSD\Commands;

use App;

use Todo;

class UnArchiveListCommand extends CommandBase {

	protected $name = 'gsd:unarchive';

	protected $description = 'Unarchive a todo list.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// Prompt user for list name
		$name = $this->askForListId(true, true, true, 'Select list to unarchive:');
		if (is_null($name))
		{
			$this->abort('*Unarchive aborted*');
		}

		// Warn if unarchived version exists
		if ($this->repository->exists($name, false))
		{
			$msg = "WARNING!\n\n"
				. " An active version of the list '$name' exists.\n"
				. " This action will destroy the active version,\n"
				. " replacing it with the archived version.";
			$this->outputErrorBox($msg);
		}

		// Ask if user is sure?
		$result = $this->ask("Are you sure you want to unarchive '$name' (yes/no)?");
		if ( ! str2bool($result))
		{
			$this->abort('*Unarchive aborted*');
		}

		// Load existing list and save as unarchived
		$list = Todo::get($name, true);
		$list->set('archived', false);
		$list->save();

		// Delete existing archived list
		if ( ! $this->repository->delete($name, true))
		{
			$this->abort('ERROR deleting archived version.');
		}
		$this->info("List '$name' has been unarchived");
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
