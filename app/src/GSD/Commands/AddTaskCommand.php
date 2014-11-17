<?php namespace GSD\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App;
use Todo;

class AddTaskCommand extends CommandBase {

    protected $name = 'gsd:add';

    protected $description = 'Add a new task to a list.';

    protected $nameArgumentDescription = "List name to add the task";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $name = $this->getListId('Select list to add the task to:');

        if(is_null($name))
        {
            $this->abort();
        }

        $list = Todo::get($name);

        $task = App::make('GSD\Entities\TaskInterface');

        if (!$task->setFromString($this->argument('task'))) {
            throw new \InvalidArgumentException('Cannot parse task string');
        }

        $type = 'Todo';

        if ($this->option('action')) {
            $task->setIsNextAction(true);
            $type = 'Next Action';
        }

        $list->taskAdd($task);
        $list->save();
        $this->info("$type successfully added to $name");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(array(
            array('task', InputArgument::REQUIRED, 'the task description.'),
        ), parent::getArguments());
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(array(
            array('action', 'a', InputOption::VALUE_NONE, 'Make task next action.', null),
        ), parent::getOptions());
    }

}
