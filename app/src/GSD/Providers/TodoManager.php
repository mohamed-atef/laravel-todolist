<?php namespace GSD\Providers;

use App;
/**
*
*/
class TodoManager
{
	/**
	 * stupid method
	 */
	public function imATeapot()
	{
		return "I'm a teapot";
	}

	/**
	* Create a new Todo List
	* @param string $id The basename of the list
	* @param string $title The title of the list
	* @return ListInterface The newly created list
	* @throws InvalidArgumentException If the list already exists
	*/
	public function makeList($id, $title)
	{
		$repository = App::make('GSD\Repositories\TodoRepositoryInterface');
		if ($repository->exists($id))
		{
			throw new \InvalidArgumentException("A list with id=$id already exists");
		}
		$list = App::make('GSD\Entities\ListInterface');
		$list->set('id', $id)->set('title', $title)->save();
		return $list;
	}

	/**
	 * Return a list of all lists
	 * @param  boolean $archived return archived list?
	 * @return array list ids
	 */
	public function allLists($archived=false)
	{
		$repository = App::make('GSD\Repositories\TodoRepositoryInterface');
		return $repository->getAll($archived);
	}

	/**
	 * get specific list
	 * @param integer $id
	 * @param boolean $archived
	 * @return array
	 */
	public function get($id, $archived=false)
	{
		$repository = App::make('GSD\Repositories\TodoRepositoryInterface');
		if (!$repository->exists($id, $archived)) {
			throw new \RuntimeException("List id=$id not found");
		}
		return $repository->load($id, $archived);
	}
}
