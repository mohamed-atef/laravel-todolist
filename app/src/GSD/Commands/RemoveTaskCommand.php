<?php namespace GSD\Commands;

use Symfony\Component\Console\Input\InputOption;
use Todo;

class RemoveTaskCommand extends CommandBase {

	protected $name = 'gsd:remove';

	protected $description = 'Remove a task from a list.';

	protected $taskNoDescription = 'Task # to remove.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		//Load list , prompt if needed
		$name = $this->getListId('Select list with task to remove:');
		if (is_null($name)) {
			$this->abort();
		}

		$list = Todo::get($name);

		// prompt for task if needed
		$taskNo = $this->getTaskNo($list, true, true, false);
		if (is_null($taskNo)) {
			$this->abort();
		}

		// Show warning, prompt if needed
		$description = $list->taskGet($taskNo, 'description');
		if (!$this->option('force')) {
			$this->outputErrorBox("WARNING! This will remove the task '$description'.");
			$result = $this->ask("Are you sure (yes/no)?");
			if (!str2bool($result)) {
				$this->abort();
			}
		}

		// Delete task
		$list->taskRemove($taskNo)->save();
		$this->info("Task '$description' removed from '+$name'");
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(), array(
			array('force', 'f', InputOption::VALUE_NONE, 'Force the removal, no prompting.', null),
		));
	}

}
