<?php namespace GSD\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Todo;

class EditListCommand extends CommandBase {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'gsd:editlist';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Edit list title or subtitle.';

	protected $nameArgumentDescription = 'Listname to edit';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$name = $this->getListId('Select list to edit:');
		
		if(is_null($name))
		{
			$this->abort();
		}//if (is_null($name))

		$list = Todo::get($name);

		$title = $this->option('title');

		$subtitle = $this->option('subtitle');

		if(all_null($title, $subtitle))
		{
			$this->info(sprintf("Editing '%s'", $name));
			$this->line('');

			$title = $this->ask("Enter list title(enter to skip)?");

			$subtitle = $this->ask("Enter list subtitle(enter to skip)?");
			$this->line('');

			if (all_null($title, $subtitle)) {
				$this->comment("Nothing to change. List not updated");
				return;
			}//if (all_null($title, $subtitle))

		}//if (all_null($title, $subtitle))

		if ($title) {
			$list->set('title', $title);
		}

		if ($subtitle) {
			$list->set('subtitle', $subtitle);
		}

		$list->save();

		$this->info(sprintf("List '%s' updated", $name));
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array_merge(parent::getOptions(), array(
			array('title', 't', InputOption::VALUE_REQUIRED, 'Title of the list.', null),
			array('subtitle', 's', InputOption::VALUE_REQUIRED, 'Subtitle of the list.', null)
		));
	}

}
