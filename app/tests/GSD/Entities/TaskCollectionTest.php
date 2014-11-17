<?php

/**
*
*/
class TaskCollectionTest extends TestCase
{

	protected function newCollection()
	{
		return App::make('GSD\Entities\TaskCollectionInterface');
	}

	public function testAddFromClassWork()
	{
		$tasks = $this->newCollection();
		$task = App::make('GSD\Entities\Task');
		$task->setDescription('a simple test');
		$tasks->add($task);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage $task must be string or TaskInterface
	 */
	public function testAddWithInvalidTypeThrowError()
	{
		$tasks = $this->newCollection();
		$tasks->add(1.0);
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage cannot parse task string
	 */
	public function testAddWithEmptyStringThrowException()
	{
		$tasks = $this->newCollection();
		$tasks->add('');
	}

	public function testAddWithValidString()
	{
		$tasks = $this->newCollection();
		$description = 'Something todo';
		$tasks->add($description);
		$this->assertEquals($description, $tasks->get(0)->description());
	}

	/**
	 * @expectedException OutOfBoundsException
	 * @expectedExceptionMessage $index is outside range
	 */
	public function testGetWhenEmptyThrowsException()
	{
		$tasks = $this->newCollection();
		$tasks->get(0);
	}

	public function testGetAll()
	{
		$tasks = $this->newCollection();
		$result = $tasks->getAll();
		$this->assertSame(array(), $result);

		$tasks->add('item 1');
		$tasks->add('item 2');
		$result = $tasks->getAll();
		$this->assertInternalType('array', $result);
		$this->assertCount(2, $result);
	}

	/**
	 * @expectedException OutOfBoundsException
	 * @expectedExceptionMessage $index is outside range
	 */
	public function testRemoveThrowsException()
	{
		$tasks = $this->newCollection();
		$tasks->add('item 1');
		$tasks->remove(1);
	}

	public function testAddSortRemove()
	{
		$tasks = $this->newCollection();
		$tasks->add('Zebra Paintaing');
		$tasks->add('Alligator wristling');
		$tasks->add('Monkey business');
		$this->assertEquals('Alligator wristling', $tasks->get(0)->description());

		$tasks->remove(0);
		$tasks->remove(1);
		$result = $tasks->getAll();
		$this->assertInternalType('array', $result);
		$this->assertCount(1, $result);
		$this->assertEquals('Monkey business', $result[0]->description());
	}
}
