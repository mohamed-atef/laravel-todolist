<?php namespace GSD\Entities;

interface TaskCollectionInterface{

	/**
	 * add new task to the collection
	 * @param TaskInterface $task
	 */
	public function add($task);

	/**
	 * Return task based on index
	 * @param  integer $index 0 is the first item in the collection
	 * @return TodoTaskInterface the todo task
	 * @throws OutOfBoundsException if the $index outside the range
	 */
	public function get($index);

	/**
	 * return array containing all tasks
	 * @return array
	 */
	public function getAll();

	/**
	 * Remove the specified task
	 * @param integer $index the task to remove
	 * @throws OutOfBoundsException if the $index outside the range
	 */
	public function remove($index);
}
