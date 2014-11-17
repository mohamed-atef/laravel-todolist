<?php namespace GSD\Entities;

/**
*
*/
class TaskCollection implements TaskCollectionInterface
{
	protected $tasks; // array of TaskInterfaces

	function __construct()
	{
		$this->tasks = array();
	}

	/**
	 * Add new task to the collection
	 * @param string|TaskInterface $task Either TaskInterface or string.
	 * @throws InvalidArgumentException if $task not string or TaskInterface.
	 */
	public function add($task)
	{
		if (!($task instanceof TaskInterface)) {
			if (!(is_string($task))) {
				throw new \InvalidArgumentException('$task must be string or TaskInterface');
			}//if (!(is_string($task)))
			$newTask = \App::make('GSD\Entities\TaskInterface');
			if (! $newTask->setFromString($task)) {
				throw new \InvalidArgumentException('cannot parse task string');
			}//if (! $newTask->setFormString($task))
			$task = $newTask;
		}//if (!($task instanceof TaskInterface))
		$this->tasks[] = $task;
		$this->sortTasks();
	}

	/**
	 * Return task based on index
	 * @param  integer $index 0 is the first item in the collection
	 * @return TaskInterface of the Todo task
	 * @throws OutOfBoundsException if $index out of the range
	 */
	public function get($index)
	{
		$this->sortTasks();

		if ($index < 0 || $index >= count($this->tasks)) {
			throw new \OutOfBoundsException('$index is outside range');
		}//if ($index < 0 || $index >= count($this->tasks))

		return $this->tasks[$index];
	}

	/**
	 * Return array containing all tasks
	 * @return array
	 */
	public function getAll()
	{
		$this->sortTasks();
		
		return $this->tasks;
	}

	/**
	 * Remove the specified task
	 * @param integer $index the task to remove
	 * @throws OutOfBoundsException if the $index outside the range
	 */
	public function remove($index){
		if ($index < 0 || $index >= count($this->tasks)) {
			throw new \OutOfBoundsException('$index is outside range');
		}//if ($index < 0 || $index >= count($this->tasks))
		unset($this->tasks[$index]);
		$this->sortTasks();
	}

	/**
	 * sort tasks where:
	 * 1) Next actions are alpabetically first.
	 * 2) Normal actions are alpabetically first.
	 * 3) Completed Tasks are sorted by date completed , descendengly
	 */
	public function sortTasks()
	{
		$next = array();
		$normal = array();
		$completed = array();
		foreach ($this->tasks as $task) {
			if ($task->isComplete()) {
				$completed[] = $task;
			}//if ($task->isComplete())
			elseif ($task->isNextAction()) {
				$next[] = $task;
			}//elseif ($task->isNextAction())
			else{
				$normal[] = $task;
			}
		}//foreach ($this->tasks as $tasks)
		usort($next, 'static::cmpDescription');
		usort($normal, 'static::cmpDescription');
		usort($completed, 'static::cmpCompleted');
		$this->tasks = array_merge($next, $normal, $completed);
	}

	/**
	 * Compare two tasks by description
	 */
	public function cmpDescription($a, $b)
	{
		return strnatcmp($a->description(), $b->description());
	}

	/**
	 * compare two tasks by completion date.
	 */
	public function cmpCompleted($a, $b)
	{
		$stamp1 = $a->dateCompleted()->timestamp;
		$stamp2 = $b->dateCompleted()->timestamp;
		if ($stamp1 == $stamp2) {
			return strnatcmp($a->description(), $b->description());
		}//if ($stamp1 == $stamp2)
		return $stamp1 - $stamp2;
	}
}
