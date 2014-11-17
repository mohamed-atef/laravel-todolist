<?php namespace GSD\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Todo;

class UncreateCommand extends CommandBase {

	protected $name = 'gsd:uncreate';

	protected $description = 'Destroy an empty list.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// Prompt user for list id
		if (!($name = $this->askForListId(true, true, false, 'Select list to uncreate:')))
		{
			$this->abort();
		}//if (!($name = $this->askForListId(true, true)))

		// Validate list has no tasks
		$list = Todo::get($name);
		if ($list->taskCount()>0) {
			$this->abort('Cannot uncreate list with tasks');
		}//if ($list->taskCount()>0)

		// Delete
		if (!$this->repository->delete($name)) {
			$this->abort('Repository could not be delete this list $name');
		}//if (!$this->repository->delete($name))
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
