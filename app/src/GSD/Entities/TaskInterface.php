<?php namespace GSD\Entities;

// File:: app/src/GSD/Entities/TodoTaskInterface.php

interface TaskInterface{

	/**
	 * [isComplete has the task been completed?]
	 * @return boolean
	 */
	public function isComplete();

	/**
	 * Description of the task
	 * @return string
	 */
	public function description();

	/**
	 * when the task is due?
	 * @return mixed Either null if no due date or carbon object
	 */
	public function dateDue();

	/**
	 * when was the task completed
	 * @return mixed Either null if not completed or carbon object
	 */
	public function dateCompleted();

	/**
	 * is the task next action?
	 * @return boolean
	 */
	public function isNextAction();

	/**
	* Set whether task is complete. Automatically updates dateCompleted.
	* @param bool $complete
	*/
	public function setIsComplete($complete, $when=null);

	/**
	* Set task description
	* @param string $description
	*/
	public function setDescription($description);

	/**
	* Set date due
	* @param null|string|Carbon $date null to clear, otherwise stores Carbon
	*date internally.
	*/
	public function setDateDue($date);

	/**
	* Set whether task is a next action
	* @param bool $nextAction
	*/
	public function setIsNextAction($nextAction);

	/**
	* Set a property. (Ends up calling specific setter)
	* @param string $name isComplete|description|dateDue|isNextAction
	* @param mixed $value The value to set
	* @throws InvalidArgumentException If $name is invalid
	*/
	public function set($name, $value);

	/**
	* Get a property.
	* @param string $name isComplete|description|dateDue|isNextAction|dateCompleted
	* @return mixed
	* @throws InvalidArgumentException If $name is invalid
	*/
	public function get($name);

	/**
	 * Set all the tasks attributes from a string.
	 * @param string $info The task info
	 * @return bool True on success, false otherwise
	 */
	public function setFromString($info);

	/**
	 * Return the task as a string
	 */
	public function __toString();
}
