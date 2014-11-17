<?php namespace GSD\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Todo;
use App;
use Config;

/**
* 
*/
class CommandBase extends Command
{
	protected $repository;

	protected $nameArgumentDescription = 'List name.';

	protected $taskNoDescription = null;
	
	function __construct()
	{
		parent::__construct();

		$this->repository = App::make('GSD\Repositories\TodoRepositoryInterface');
		$aliases = Config::get('todo.aliases');
		if (array_key_exists($this->name, $aliases))
		{
			$this->setAliases($aliases[$this->name]);
		}
	}

	/**
	 * Prompt user for list id
	 * @param  boolean $existing   prompt for existing list or new list?
	 * @param  boolean $allowCanel allow user to cancel
	 * @param  boolean $archived   use archived list
	 * @return mixed string list id or null if user cancel.
	 */
	public function askForListId($existing=true, $allowCanel=true, $archived=false, $selectTitle='Select a list:')
	{
		if ($existing) {
			$title = "Choose which list to $selectTitle:";
			$abort = "cancel - do not $selectTitle a list";
			$choices = Todo::allLists($archived);

			if(count($choices) == 0) 
			{
				throw new \RuntimeException("No lists to choose from");
			}//if (count($choices) == 0)

			$result = pick_from_list($this, $title, $choices, 0, $abort);

			if($result == -1) 
			{
				return null;
			}//if($result == -1)

			return $choices[$result-1];
			
		}//if ($existing)

		$prompt = "Enter name of new list";

		if ($allowCanel) $prompt .= " (enter to cancel)";

		$prompt .= '?';

		while (true) {

			if (! ($result = $this->ask($prompt))) {

				if ($allowCanel) {
					return null;
				}//if ($allowCanel)

				$this->outputErrorBox('You must enter something');

			}//if (! ($result = $this->ask($prompt)))
			elseif ($this->repository->exists($result, $archived)) {

				$this->outputErrorBox("You already have a list with name $result");

			}//elseif ($this->repository->exists($result, $archived))
			else{
				return $result;
			}

		}//while (true)
	}

	public function outputErrorBox($message)
	{
		$formatter = $this->getHelperSet()->get('formatter');

		$block = $formatter->formatBlock($message, 'error', true);

		$this->line('');
		$this->line($block);
		$this->line('');
	}

	/**
	 * Output an error message and die
	 * @param string $message Optional message to output
	 */
	protected function abort($message = '*aborted*')
	{
		$this->outputErrorBox($message);
		exit;
	}

	/**
	 * the console command arguments. Derivied class could replace this
	 * method entirely, or merge it's own arguments with these.
	 * @return array
	 */
	protected function getArguments()
	{
		$args = array();

		if (!is_null($this->taskNoDescription)) {
			$args[] = array('task-number', InputArgument::OPTIONAL, $this->description);
		}

		$args[] = array('+name', InputArgument::OPTIONAL, $this->nameArgumentDescription);

		return $args;
	}

	/**
	 * The console command options. Derived classes could replace this
	 * method entirely, or merge its own options with these
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('listname', 'l', InputOption::VALUE_REQUIRED, "Source of listname 'prompt' or 'default'")
		);
	}

	protected function getListId($selectTitle='Select a list:')
	{
		$archived = $this->input->hasOption('archived') and $this->option('archived');

		$name = $this->argument('+name');

		$listnameOption = $this->option('listname');

		if ($name)
		{
			$name = substr($name, 1);

			if(! is_null($listnameOption))
			{
				throw new \InvalidArgumentException('Cannot specify +name and --listname together');
			}//if (! is_null($listnameOption))

		}//if ($name)
		else
		{
			if (is_null($listnameOption)) {
				$listnameOption = (Config::get('todo.gsd.noListPrompt')) ? 'prompt':'config';
			}//if (is_null($listnameOption))

			if($listnameOption == 'prompt')
			{
				$name = $this->askForListId(true, true, $archived, $selectTitle);
				if(is_null($name))
				{
					return null;
				}//if (is_null($name))
			}//if ($listnameOption == 'prompt')
			else{
				$name = Config::get('todo.gsd.defaultList');
			}//else
		}//else

		// Throw error if list doesn't exist
		if(! $this->repository->exists($name, $archived))
		{
			$archived = ($archived) ? 'archived' : '';
			throw new \InvalidArgumentException("List $archived'$name' not found");
		}//if (! $this->repository->exists($name, $archived))

		return $name;
	}

	protected function getTaskNo(\GSD\Entities\ListInterface $list, $showNext, $showNormal, $showComplete)
	{
		$taskNo = $this->argument('task-number');

		// Return the # if provided in command line
		if (!is_null($taskNo)) {
			return (int)$taskNo -1;
		}

		// Build list of tasks
		$tasks = array();

		foreach ($list->tasks() as $task) {

			if ($task->isComplete()) {
				if($showComplete) $tasks[] = (string)$task ;
			}//if ($task->isComplete())
			elseif ($task->isNextAction()) {
				if($showNext) $tasks[] = (string)$task ;
			}//elseif ($task->isNextAction())
			elseif ($showNormal) {
				$tasks[] = (string)$task;
			}//elseif ($showNormal)

		}//foreach ($list->tasks() as $task)

		// Let user pick from list, return result
		$result = pick_from_list($this, $this->taskNoDescription, $tasks, 0, "cancel, do not perform action");

		return ($result == -1) ? null : $result-1 ;
	}
}
