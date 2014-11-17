<?php namespace GSD\Commands;

use Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ListAllCommand extends CommandBase {

	protected $name = 'gsd:listall';

	protected $description = 'Lists all Todo lists (and possibly tasks).';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$archived = $this->option('archived');

		$tasks = $this->option('tasks');

		if (!is_null($tasks)) {
			$validTasks = array('all', 'next', 'normal', 'done');
			if (!in_array($tasks, $validTasks)) {
				$msg = sprintf("Invalid --tasks=%s. Must be one of '%s'", $tasks, join(', ', $validTasks));
				$this->abort();
			}

			if($tasks == 'next') $tasks = 'next action';
			$completeFmt = Config::get('todo.gsd.dateCompleteFormat');
			$dueFmt = Config::get('todo.gsd.dateDueFormat');
		}

		

		// Get lists
		$lists = \Todo::allLists($archived);

		$lists = $this->sortListIds($lists);

		// Output title
		$listType = ($archived) ? 'archived lists' : 'lists';
		$listWhat = is_null($tasks) ? 'all' : "$tasks tasks in all";
		$this->info("Listing $listWhat $listType");

		// Different headers based on tasks usage
		if (is_null($tasks))
		{
			$headers = array('List', 'Next', 'Normal', 'Completed');
		}
		else
		{
			$headers = array('List', 'Next', 'Description', 'Extra');
		}

		$rows = array();

		foreach ($lists as $listId) {
			$list = \Todo::get($listId, $archived);

			// We're just outputing the lists
			if (is_null($tasks))
			{
				$rows[] = array(
				$listId,
				$list->taskCount('next'),
				$list->taskCount('todo'),
				$list->taskCount('done'),
				);
			}
			else
			{
				// Loop through tasks to figure which to output
				foreach ($list->tasks() as $task)
				{
					if ($task->isComplete())
					{
						if ($tasks == 'done' || $tasks == 'all')
						{
							$done = $task->dateCompleted()->format($completeFmt);
							$rows[] = array($listId, '', $task->description(), "Done ".$done);
						}
					}
					// Other, unfinished tasks
					else
					{
						$next = ($task->isNextAction()) ? 'YES' : '';
						$due = ($task->dateDue()) ? 'Due '.$task->dateDue()->format($dueFmt) : '';
						if (($tasks == 'all') or ($tasks == 'next action' && $next == 'YES') or ($tasks == 'normal' && $next == ''))
						{
							$rows[] = array($listId, $next, $task->description(), $due);
						}
					}
				}
			}
		}//foreach ($lists as $listId)

		// Output a pretty table
		$table = $this->getHelperSet()->get('table');
		$table
		->setHeaders($headers)
		->setRows($rows)
		->render($this->getOutput());
	}

	/**
	 * [sort List Ids ]
	 * @param  array  $listIds
	 */
	protected function sortListIds(array $listIds)
	{
		// Pull the names
		$special = array();

		foreach (\Config::get('todo.gsd.listOrder') as $name) {
			$special[$name] = false;
		}//foreach (\Config::get('app.gsd.listOrder') as $name)

		// Peel of the special
		$tosort = array();

		foreach ($listIds as $listId) {
			if (array_key_exists($listId, $special)) {
				$special[$listId] = true;
			}//if (array_key_exists($listId, $special))
			else{
				$tosort[] = $listId;
			}
		}//foreach ($listIds as $listId)

		// Put the specials first then sort the remaining and add them in
		$return = array();

		foreach ($special as $listId => $flag) {
			if ($flag) {
				$return[] = $listId;
			}//if ($flag)
		}//foreach ($special as $listId => $flag)

		natcasesort($tosort);

		return array_merge($return, $tosort);
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
		return array(
			array('archived', 'a', InputOption::VALUE_NONE, 'use archive lists?'),
			array('tasks', 't', InputOption::VALUE_REQUIRED, 'Output (all|next|normal|done) tasks?'),
		);
	}

}
