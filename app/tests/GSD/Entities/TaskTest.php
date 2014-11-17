<?php
// File: app/tests/GSD/Entities/TaskTest.php
/**
*
*/
class TaskTest extends TestCase
{

	public function newTask()
	{
		return App::make('GSD\Entities\TaskInterface');
	}

	public function testGetters()
	{
		$task = $this->newTask();

		// Use specific getters
		$this->assertFalse($task->isComplete());
		$this->assertEquals('', $task->description());
		$this->assertNull($task->dateDue());
		$this->assertNull($task->dateCompleted());
		$this->assertFalse($task->isNextAction());

		// Use generic getters
		$this->assertFalse($task->get('isComplete'));
		$this->assertEquals('', $task->get('description'));
		$this->assertNull($task->get('dateDue'));
		$this->assertNull($task->get('dateCompleted'));
		$this->assertFalse($task->get('isNextAction'));
	}

	public function testSettingCompleteUpdatesWhenComplete()
	{
		$task = $this->newTask();

		$task->setIsComplete(true);
		$this->assertInstanceOf('Carbon\Carbon', $task->dateCompleted());
		$this->assertEquals(date('Y-m-d'), $task->dateCompleted()->format('Y-m-d'));
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage is not null or Carbon
	 */
	public function testSetDueThrowsException()
	{
		$task = $this->newTask();
		$task->setDateDue(123);
	}

	public function testOtherSetters()
	{
		$task = $this->newTask();

		$test1 = 'test description';
		$test2 = 'another test';

		$task->setDescription($test1);
		$this->assertEquals($test1, $task->description());
		$task->set('description', $test2);
		$this->assertEquals($test2, $task->description());

		$test1 = new Carbon\Carbon('1/1/2013');
		$task->setDateDue($test1);
		$this->assertEquals($test1, $task->dateDue());
		$task->set('dateDue', null);
		$this->assertNull($task->dateDue());

		$task->setIsNextAction(true);
		$this->assertTrue($task->isNextAction());
		$task->set('isNextAction', false);
		$this->assertFalse($task->isNextAction());
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid attribute
	 */
	public function testGetWithBadNameThrowsException()
	{
		$task = $this->newTask();
		$task->get('something');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Invalid attribute
	 */
	public function testSetWithBadNameThrowsException()
	{
		$task = $this->newTask();
		$task->set('badName', 'badValue');
	}

	/**
	 * @dataProvider stringTests
	 */
	public function testStringVariation($string, $valid, $stringSame)
	{
		$task = $this->newTask();

		$result = $task->setFromString($string);

		if ($valid) {
			$this->assertTrue($result);
			if ($stringSame) {
				$this->assertEquals($string, (string)$task);
			}//if ($stringSame)
			else{
				$this->assertNotEquals($string, (string)$task);
			}
		}//if ($valid)
		else{
			$this->assertFalse($result);
		}
	}

	public function stringTests()
	{
		return array(
			array('', false, false),
			array('* Simple next action', true, true),
			array('* Next with due date :due:2013-09-14', true, true),
			array('- Task with  an extra space', true, false),
			array('x bad', false, false),
			array('- Due date :due:2013-09-14 in middle', true, false),
			array('x 2013-08-03 Start Laravel Book: Getting Stuff Done', true, true)
		);
	}
}
