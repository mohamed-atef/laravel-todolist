<?php namespace GSD\Entities;

interface ListInterface{

	/**
	 * save task list
	 */
	public function save($force=false);

	/**
	 * Delete the task list
	 * @return boolean TRUE on success
	 */
	public function delete();

	/**
	 * return id
	 * @return type
	 */
	public function id();

	/**
	 * is the list dirty?
	 * @return boolean
	 */
	public function isDirty();

	/**
	 * is the list archived?
	 * @return boolean
	 */
	public function isArchived();

	/**
	 * set task
	 * @param type $name
	 * @param type $id
	 * @return type
	 */
	public function set($name, $id);

	/**
	 * return list attribute
	 * @param string $name
	 */
	public function get($name);

	/**
	* Set a task attribute
	* @param integer $index Task index
	* @param string $name Attribute name
	* @param mixed $value Attribute value
	* @return $this for method chaining
	* @throws OutOfBoundsException If $index outside range
	* @throws InvalidArgumentException If $name is invalid
	*/
	public function taskSet($index, $name, $value);

	/**
	 * return the title (alias for get)
	 */
	public function title();

	/**
	* Archive the list. This makes the list only available from the archive.
	* @return ListInterface For method chaining
	*/
	public function archive();

	/**
	 * add new task to the collection
	 * @param TodoTaskInterface $task
	 */
	public function taskAdd($task);

	/**
	* Return a task attribute
	* @param integer $index Task index #
	* @param string $name Attribute name
	* @return mixed
	* @throws OutOfBoundsException If $index outside range
	* @throws InvalidArgumentException If $name is invalid
	*/
	public function taskGet($index, $name);

	/**
	* Return number of tasks
	* @return integer
	*/
	public function taskCount($type='all');

	/**
	* Return a task
	* @param integer $index Task index #
	* @return TaskInterface
	* @throws OutOfBoundsException If $index outside range
	*/
	public function task($index);

	/**
	 * return all tasks as an array
	 */
	public function tasks();

	/**
	* Remove the specified task
	* @return $this for method chaining
	* @throws OutOfBoundsException If $index outside range
	*/
	public function taskRemove($index);
}
