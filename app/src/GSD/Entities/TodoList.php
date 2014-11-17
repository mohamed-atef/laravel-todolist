<?php namespace GSD\Entities;

use GSD\Repositories\TodoRepositoryInterface;
/**
*
*/
class TodoList implements ListInterface
{
	protected static $validAttribs = array('id', 'archived', 'subtitle', 'title');
	protected $repository;
	protected $tasks;
	protected $attributes;
	protected $isDirty;

	/**
	* Inject the dependencies during construction
	* @param TodoRepositoryInterface $repo The repository
	* @param TaskCollectionInterface $collection The task collection
	*/
	function __construct(TodoRepositoryInterface $repo, TaskCollectionInterface $collection){
		$this->repository = $repo;
		$this->tasks = $collection;
		$this->attributes = array();
		$this->isDirty = false;
	}

	// List attributes

	/**
	 * @return list's id (basename)
	 */
	public function id()
	{
		return $this->get('id');
	}

	/**
	 * is the list archived?
	 * @return boolean
	 */
	public function isArchived()
	{
		return !! $this->get('archived');
	}

	/**
	 * is the list dirty?
	 * @return boolean
	 */
	public function isDirty()
	{
		return $this->isDirty;
	}

	/**
	 * @return list's title
	 */
	public function title()
	{
		return $this->get('title');
	}

	/**
	* Return a list attribute
	* @param string $name id|archived|subtitle|title
	* @return mixed
	* @throws InvalidArgumentException If $name is invalid
	*/
	public function get($name)
	{
		if (!in_array($name, static::$validAttribs)) {
			throw new \InvalidArgumentException('invalid attribut named $name');
		}//if (!in_array($name, static::$validAttribs))
		if (array_key_exists($name, $this->attributes)) {
			return $this->attributes[$name];
		}//if (array_key_exists($name, $this->attributes))
		return null;
	}

	/**
	* Set a list attribute
	* @param string $name id|archived|subtitle|title
	* @param mixed $value Attribute value
	* @return $this for method chaining
	* @throws InvalidArgumentException If $name is invalid
	*/
	public function set($name, $value)
	{
		if (!in_array($name, static::$validAttribs)) {
			throw new \InvalidArgumentException('invalid attribut named $name');
		}//if (!in_array($name, static::$validAttribs))

		if($name == 'archived') $value = !! $value;
		$this->attributes[$name] = $value;
		$this->isDirty = true;
		return $this;
	}

	// List operations

	/**
	 * archive the list. this make the list available only from archive.
	 * @return ListInterface for method chaining
	 * @throws RuntimeException if cannot save.
	 */
	public function archive()
	{
		// If already archived , then return this
		if ($this->isArchived()) {
			return $this;
		}//if ($this->isArchived())

		if (! array_key_exists('id', $this->attributes)) {
			throw new \RuntimeException('Cannot archived if id not set');
		}//if (! array_key_exists('id', $this->attributes))
		$id = $this->attributes['id'];
		// delete existing, unarchived list
		if ($this->repository->exists($id, false) and ! $this->repository->delete($id, false)) {
			throw new \RuntimeException("Repository failed deleting unarchived list");
		}//if ($this->repository->exists($id, false) and ! $this->repository->delete($id, false))
		$this->set('archived', true);
		return $this->save();
	}

	/**
	* Save the list
	* @return $this For method chaining
	* @throws RuntimeException If cannot save.
	*/
	public function save($force=false)
	{
		if ($this->isDirty || $force) {
			if (!array_key_exists('id', $this->attributes)) {
				throw new \RuntimeException("Cannot save if id not set");
			}//if (!array_key_exists('id', $this->attributes))
			if (!$this->repository->save($this)) {
				throw new \RuntimeException("Repository couldn't save");
			}//if (!$this->repository->save($id, $this, $archived))
			$this->isDirty = false;
		}//if ($this->isDirty)
		return $this;
	}

	/**
	 * Delete the task list
	 * @return boolean TRUE on success
	 */
	public function delete()
	{
		return $this->repository->delete($this->id(), $this->isArchived());
	}

	// Task operations

	/**
	* Add a new task to the collection
	* @param string|TaskInterface $task Either a TaskInterface or a string we can construct one from.
	* @return $this for method chaining
	*/
	public function taskAdd($task)
	{
		$this->tasks->add($task);
		$this->isDirty = true;
		return $this;
	}

	/**
	* Return number of tasks
	* @return integer
	*/
	public function taskCount($type='all')
	{
		$count = 0;

		foreach ($this->tasks->getAll() as $task) {
			switch ($type) {
				case 'done':
					if ($task->isComplete()) {
						$count++;
					}
					break;
				case 'todo':
					if (!$task->isComplete() and !$task->isNextAction()) {
						$count++;
					}
					break;
				case 'next':
					if (!$task->isComplete() and $task->isNextAction()) {
						$count++;
					}
					break;
				
				default:
					$count++;
			}//switch ($type)
		}//foreach ($this->tasks->getAll() as $task)

		return $count;
	}

	/**
	* Return a task
	* @param integer $index Task index #
	* @return TaskInterface
	* @throws OutOfBoundsException If $index outside range
	*/
	public function task($index)
	{
		return $this->tasks->get($index);
	}

	/**
	* Return a task attribute
	* @param integer $index Task index #
	* @param string $name Attribute name
	* @return mixed
	* @throws OutOfBoundsException If $index outside range
	* @throws InvalidArgumentException If $name is invalid
	*/
	public function taskGet($index, $name)
	{
		$task = $this->tasks->get($index);
		return $task->get($name);
	}

	/**
	* Return all tasks as an array.
	* @return array All the TaskInterface objects
	*/
	public function tasks()
	{
		return $this->tasks->getAll();
	}

	/**
	* Set a task attribute
	* @param integer $index Task index
	* @param string $name Attribute name
	* @param mixed $value Attribute value
	* @return $this for method chaining
	* @throws OutOfBoundsException If $index outside range
	* @throws InvalidArgumentException If $name is invalid
	*/
	public function taskSet($index, $name, $value)
	{
		$task = $this->tasks->get($index);
		$task->set($name, $value);
		return $this;
	}

	/**
	* Remove the specified task
	* @return $this for method chaining
	* @throws OutOfBoundsException If $index outside range
	*/
	public function taskRemove($index)
	{
		$this->tasks->remove($index);
		return $this;
	}
	// Not yet implemented
}
