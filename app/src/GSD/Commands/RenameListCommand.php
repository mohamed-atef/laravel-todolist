<?php namespace GSD\Commands;

use Config;
use Symfony\Component\Console\Input\InputOption;
use Todo;

class RenameListCommand extends CommandBase {

	protected $name = 'gsd:rename';

	protected $description = 'Rename a List.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		//Getting archived flag
		$archived = $this->option('archived');

		$name = $this->askForListId(true, true, $archived, 'Select list to rename:');
		if (is_null($name))
		{
			$this->abort();
		}

		if ( ! $archived && Config::get('todo.defaultList') == $name)
		{
			$this->abort('Cannot rename default list');
		}

		// Prompt for new list name
		$newName = $this->askForListId(false, true, $archived);
		if (is_null($name))
		{
			$this->abort();
		}

		// Load existing list, save with new name
		$list = Todo::get($name, $archived);
		$newList = clone $list;
		$newList->set('id', $newName);
		$newList->save();

		// Delete existing list and we're done
		$list->delete();
		$listType = ($archived) ? 'Archived list' : 'List';
		$this->info($listType . " '$name' renamed to '$newName'");
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
			array('archived', 'a', InputOption::VALUE_NONE, 'Use archived lists?', null),
		);
	}

}
