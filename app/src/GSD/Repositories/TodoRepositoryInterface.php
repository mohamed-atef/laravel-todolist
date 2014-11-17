<?php namespace GSD\Repositories;

// File: app/src/GSD/Repositories/TodoRepositoryInterface.php

use GSD\Entities\ListInterface;

interface TodoRepositoryInterface{

	/**
	 * Does the todo list exist?
	 * @param string $id ID of the list
	 * @return boolean
	 */
	public function exists($id, $archived=false);

	/**
	 * [load todo list from it's id]
	 * @param  [string] $id [id from the list]
	 * @return TodoListInterface the list
	 * @throws InvalidArgumentException if $id isn't found
	 */
	public function load($id, $archived=false);

	/**
	 * save todo list
	 * @param string $id ID from the list
	 * @param object TodoListInterface $list the list
	 */
	public function save(ListInterface $list);

	/**
	 * Return ids of all lists
	 * @param boolean $archived
	 * @return array list of ids
	 */
	public function getAll($archived=false);

	/**
	 * Delete the todo list
	 * @param string $id ID of the list
	 * @return boolean True if successful
	 */
	public function delete($id, $archived = false);
}
