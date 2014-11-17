<?php namespace GSD\Entities;

use Carbon\Carbon;

/**
*
*/
class Task implements TaskInterface
{

	protected $complete;	// is the task complete?
	protected $description; // task description
	protected $due; // null or Carbon
	protected $whenCompleted; // null or Carbon;
	protected $nextAction; // is this a next action?

	function __construct(){
		$this->clear();
	}

	/**
	 * Clear all task attributes
	 */
	protected function clear(){
		$this->complete = false;
		$this->description = '';
		$this->due = null;
		$this->whenCompleted = null;
		$this->nextAction = false;
	}

	/**
	 * [isComplete has the task been completed?]
	 * @return boolean
	 */
	public function isComplete(){
		return $this->complete;
	}

	/**
	 * Description of the task
	 * @return string
	 */
	public function description(){
		return $this->description;
	}

	/**
	 * when the task is due?
	 * @return mixed Either null if no due date or carbon object
	 */
	public function dateDue(){
		return $this->due;
	}

	/**
	 * when was the task completed
	 * @return mixed Either null if not completed or carbon object
	 */
	public function dateCompleted(){
		return $this->whenCompleted;
	}

	/**
	 * is the task next action?
	 * @return boolean
	 */
	public function isNextAction(){
		return $this->nextAction;
	}

	/**
	* Set whether task is complete. Automatically updates dateCompleted.
	* @param bool $complete
	*/
	public function setIsComplete($complete, $when=null){
		$this->complete = !! $complete;
		if ($this->complete) {
			if ($when == null) {
				$when = new Carbon;
			}
			elseif (is_string($when)) {
				$when = new Carbon($when);
			}
			$this->whenCompleted = $when;
		}else{
			$this->whenCompleted = null;
		}
	}

	/**
	* Set task description
	* @param string $description
	*/
	public function setDescription($description){
		$this->description = $description;
	}

	/**
	* Set date due
	* @param null|string|Carbon $date null to clear, otherwise stores Carbon
	*date internally.
	*/
	public function setDateDue($date){
		if (!(is_null($date)) and ! ($date instanceof Carbon)) {
			throw new \InvalidArgumentException('$date is not null or Carbon');
		}
		$this->due = $date;
	}

	/**
	* Set whether task is a next action
	* @param bool $nextAction
	*/
	public function setIsNextAction($nextAction){
		$this->nextAction = !!$nextAction;
	}

	/**
	* Set a property. (Ends up calling specific setter)
	* @param string $name isComplete|description|dateDue|isNextAction
	* @param mixed $value The value to set
	* @throws InvalidArgumentException If $name is invalid
	*/
	public function set($name, $value){
		switch ($name) {
			case 'isComplete':
				$this->setIsComplete($value);
				break;
			case 'description':
				$this->setDescription($value);
				break;
			case 'dateDue':
				$this->setDateDue($value);
				break;
			case 'isNextAction':
				$this->setIsNextAction($value);
				break;
			default:
				throw new \InvalidArgumentException("Invalid attribute $name");
		}
	}

	/**
	* Get a property.
	* @param string $name isComplete|description|dateDue|isNextAction|dateCompleted
	* @return mixed
	* @throws InvalidArgumentException If $name is invalid
	*/
	public function get($name){
		switch ($name) {
			case 'isComplete':
				return $this->isComplete();
				break;
			case 'description':
				return $this->description();
				break;
			case 'dateDue':
				return $this->dateDue();
				break;
			case 'isNextAction':
				return $this->isNextAction();
				break;
			case 'dateCompleted':
				return $this->dateCompleted();
				break;

			default:
				throw new \InvalidArgumentException("Invalid attribute $name");
				break;
		}
	}

	/**
	 * Set all the tasks attributes from a string.
	 * @param string $info The task info
	 * @return bool True on success, false otherwise
	 */
	public function setFromString($info){
		$this->clear();

		// Remove dup space and convert into words
		$info = preg_replace('/\s\s+/', ' ', $info);
		$words = explode(' ', trim($info));
		if (count($words) == 1 && $words[0] == '') {
			return false;
		}// if (count($words) == 0)

		// Completed task
		if ($words[0] == 'x') {
			$this->complete = true;
			array_shift($words);
			try {
				$this->whenCompleted = new Carbon(array_shift($words));
			}//try
			catch (\Exception $e) {
				return false;
			}//catch (\Exception $e)
		}//if ($words[0] == 'x')
		elseif ($words[0] == '*') {
			$this->nextAction = true;
			array_shift($words);
		}//elseif ($words[0] == '*')
		elseif ($words[0] == '-') {
			array_shift($words);
		}//elseif ($words[0] == '-')

		// Look for a due date
		for ($i = 0; $i < count($words); $i++)
		{
			if (substr($words[$i], 0, 5) == ':due:')
			{
				$this->due = new Carbon(substr($words[$i], 5));
				unset($words[$i]);
				break;
			}//if (substr($words[$i], 0, 5) == ':due:')
		}//for ($i = 0; $i < count($words); $i++)
		$this->description = join(' ', $words);
		return true;
	}

	/**
	 * Return the task as a string
	 */
	public function __toString(){
		$build = array();
		if ($this->complete){
		$build[] = 'x';
		$build[] = $this->whenCompleted->format('Y-m-d');
		}//if ($this->complete)
		elseif ($this->nextAction){
			$build[] = '*';
		}//elseif ($this->nextAction)
		else{
			$build[] = '-';
		}//else

		$build[] = $this->description;
		if ($this->due){
			$build[] = ':due:' . $this->due->format('Y-m-d');
		}//if ($this->due)
		return join(' ', $build);
	}
}
