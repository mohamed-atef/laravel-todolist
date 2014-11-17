<?php namespace GSD\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Todo;

class CreateCommand extends CommandBase {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'gsd:create';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create new list.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		// Get options and arguments
		$name = $this->argument('+name');
		$title = $this->option('title');
		$subtitle = $this->option('subtitle');

		// prompt for every thing
		if (all_null($name, $title, $subtitle)) {

			if (! ($name = $this->askForListId(false, true))) {
				$this->abort();
			}//if (! ($name = $this->askForListId(false, true)))

			$title = $this->ask('Enter list title (enter to skip)?');
			$subtitle = $this->ask('Enter list subtitle (enter to skip)?');
			
		}//if (all_null($name, $title, $subtitle))

		// validate arguments
		elseif (is_null($name)) {
			$this->abort('Must specify +name if title or subtitle used');
		}//elseif (is_null($name))

		elseif ($name[0] != '+') {
			$this->abort('The list name must begin with a plus (+)');
		}//elseif ($name[0] != '+')

		else{
			$name = substr($name, 1);
			if ($this->repository->exists($name)) {
				throw new \InvalidArgumentException("List '$name' already exists");
			}//if ($this->repository->exists($name))
		}

		// Create list, defaulting title if needed
		$title = ($title)? : ucfirst($name);
		$list = Todo::makeList($name, $title);

		// Set subtitle if needed
		if ($subtitle) {
			$list->set('subtitle', $subtitle)->save();
		}//if ($subtitle)

		$this->info("List '$name' successfully created");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('+name', InputArgument::OPTIONAL, 'List name to create.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('title', 't', InputOption::VALUE_REQUIRED, 'Title of the list.', null),
			array('subtitle', 's', InputOption::VALUE_REQUIRED, 'Subtitle of the list.', null),
		);
	}

}
