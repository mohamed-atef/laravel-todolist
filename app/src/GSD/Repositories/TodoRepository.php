<?php namespace GSD\Repositories;

use Config;
use GSD\Entities\ListInterface;
/**
*
*/
class TodoRepository implements TodoRepositoryInterface
{
	protected $path;
	protected $extension;

	/**
	 * we will throw exception if the path doesn't exist
	 */
	function __construct()
	{
		$this->path = str_finish(Config::get('todo.gsd.folder'), '/');

		if (!\File::isDirectory($this->path)) {
			throw new \RuntimeException("Directory doesn't exist: $this->path");
		}//if (!\File::isDirectory($this->path))

		if (!\File::isDirectory($this->path.'archived')) {
			throw new \RuntimeException("Directory doesn't exist: $this->path".'archived');
		}//if (!\File::isDirectory($this->path.'archived'))

		$this->extension = Config::get('todo.gsd.extension');
		if (!starts_with($this->extension, '.')) {
			$this->extension = '.'.$this->extension;
		}//if (!starts_with($this->extension))
	}

	/**
	 * Delete the todo list
	 * @param string $id ID of the list
	 * @return boolean True if successful
	 */
	public function delete($id, $archived = false){

		$file = $this->fullpath($id, $archived);

		if (\File::exists($file)) {
			return unlink($file);
		}//if (\File::exists($file))

		return false;
	}

	/**
	 * Does the todo list exist?
	 * @param string $id ID of the list
	 * @return boolean
	 */
	public function exists($id, $archived=false){

		$file = $this->fullpath($id, $archived);

		return \File::exists($file);
	}

	/**
	 * Return ids of all lists
	 * @param boolean $archived
	 * @return array list of ids
	 */
	public function getAll($archived=false){

		$match = $this->path;

		if ($archived) {
			$match .= 'archived/';
		}//if ($archived)

		$match .= '*'.$this->extension;

		$files = \File::glob($match);

		$ids = array();

		foreach ($files as $file) {
			$ids[] = basename($file, $this->extension);
		}//foreach ($files as $file)

		return $ids;
	}

	/**
	 * [load todo list from it's id]
	 * @param  [string] $id [id from the list]
	 * @return TodoListInterface the list
	 * @throws InvalidArgumentException if $id isn't found
	 */
	public function load($id, $archived=false){
		if (!$this->exists($id, $archived)) {
			throw new \InvalidArgumentException('List with id=$id, archived=$archived not found');
		}//if (!$this->exists($id, $archived))

		$lines = explode("\n", \File::get($this->fullpath($id, $archived)));

		// Pull title
		$title = array_shift($lines);
		$title = trim(substr($title, 1));

		// Pull subtitle
		if (count($lines) && starts_with($lines[0], '(')) {
			$subtitle = trim(array_shift($lines));
			$subtitle = ltrim($subtitle, '(');
			$subtitle = rtrim($subtitle, ')');
		}//if (count($lines) && $lines[0][0]=='(')

		// Setup the list
		$list = \App::make('GSD\Entities\ListInterface');
		$list->set('id', $id);
		$list->set('title', $title);
		if (!empty($subtitle)) {
			$list->set('subtitle', $subtitle);
		}//if (!empty($subtitle))
		$list->set('archived', $archived);

		// And add the tasks
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line) {
				$list->taskAdd($line);
			}//if ($line)
		}//foreach ($lines as $line)
		return $list;
	}

	/**
	 * save todo list
	 * @param string $id ID from the list
	 * @param object TodoListInterface $list the list
	 */
	public function save(ListInterface $list){
		$id = $list->get('id');

		$archived = $list->get('archived');

		$build = array();

		$build[] = '#'.$list->get('title');

		$subtitle = $list->get('subtitle');

		if ($subtitle) {
			$build[] = "($subtitle)";
		}//if ($subtitle)

		$lastType = '';

		$tasks = $list->tasks();

		foreach ($tasks as $task) {
			$task = (string)$task;
			$type = $task[0];
			if ($type != $lastType) {
				$build[] = ''; //Blank lines between types of tasks
				$lastType = $type;
			}//if ($type != $lastType)
			$build[] = $task;
		}//foreach ($tasks as $task)

		$content = join("\n", $build);

		$filename = $this->fullpath($id, $archived);

		$result = \File::put($filename, $content);

		return $result !== false;
	}

	public function fullpath($id, $archived)
	{
		$path = $this->path;
		if ($archived) {
			$path .= 'archived/';
		}//if ($archived)
		$path .= $id.$this->extension;
		return $path;
	}
}
