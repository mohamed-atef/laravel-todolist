<?php namespace GSD\Commands;

use Todo;
use Symfony\Component\Console\Input\InputOption;
use Config;

class ListTasksCommand extends CommandBase {

	protected $name = 'gsd:list';

	protected $description = 'List tasks.';

	protected $nameArgumentDescription = 'List name to display tasks.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$name = $this->getListId('Select list to show tasks:');
		if (is_null($name)) {
			$this->abort("ListTasks aborted");
		}

		$list = Todo::get($name);

		$nextOnly = $this->option('action');

		$skipDone = $this->option('skip-done');

		if ($nextOnly and $skipDone) {
			$this->abort("Options --action and --skip-done can't be used together.");
		}

		// Gather rows to display
		$rows = array();

		$rowNo = 1;

		$completeFmt = Config::get('todo.gsd.dateCompleteFormat');

		$dueFmt = Config::get('todo.gsd.dateDueFormat');

		foreach($list->tasks() as $task)
		{
			
			if($task->isComplete())
			{
				if($skipDone or $nextOnly) continue ;

				$rows[] = array('','done', $task->description(),'Done '.$task->dateCompleted()->format($completeFmt),);
			}//if ($task->isComplete())
			elseif($task->isNextAction() or !$nextOnly)
			{
				$next = ($task->isNextAction()) ? 'Yes': '';

				$due = ($task->dateDue()) ? 'Due '.$task->dateDue()->format($dueFmt) : '';

				$rows[] = array( $rowNo++, $next, $task->description(), $due, );
			}//elseif ($task->isNextAction or !$nextOnly)
		}//foreach ($list->tasks() as $task)

		// Output pretty table
		$title = ($nextOnly) ? "Next Actions" : (($skipDone) ? "Active Tasks" : "All Tasks");

		$this->info("$title in list '+$name'");

		if (count($rows) == 0)
		{
			$this->abort("Nothing found");
		}//if (count($rows) == 0)

		$table = $this->getHelperSet()->get('table');

		$table
			->setHeaders(array('#', 'Next', 'Description', 'Extra'))
			->setRows($rows)
			->render($this->getOutput());
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(),array(
			array('action', 'a', InputOption::VALUE_NONE, 'Show only next actions.', null),
			array('skip-done', 'x', InputOption::VALUE_NONE, 'Skip completed actions.', null),
		));
	}

}
