<?php

use Illuminate\Console\Command;

/**
 * Return true if every arg is null
 * @usage all_null($arg1, arg2, ...)
 * @return bool
 */
function all_null()
{
	foreach (func_get_args() as $arg) {
		if (!is_null($arg)) {
			return false;
		}
	}
	return true;
}

/**
 * Return true if a value is between two other values.
 */
function between($value, $min, $max, $inclusive=true)
{
	if ($inclusive) {
		return ($value >= $min and $value <= $max);
	}
	return ($value > $min and $value < $max);
}

/**
 * [pick_from_list description]
 * @param  Command $command [description]
 * @param  [type]  $title   [description]
 * @param  array   $choices [description]
 * @param  integer $default [description]
 * @param  [type]  $abort   [description]
 * @return [type]           [description]
 */
function pick_from_list(Command $command, $title, array $choices, $default=0, $abort=null){
	
	if ($abort) {

		$choices[] = $abort;

	}//if ($abort)

	$numChoices = count($choices);

	if (!$numChoices) {

		throw new \InvalidArgumentException("Must have at least one choice");

	}//if (!$numChoices)

	if ($default == -1 && empty($abort)) {

		throw new \InvalidArgumentException("Cannot use default=-1 without $abort option");

	}//if ($default > $numChoices || $default < 0)

	if ( ! between($default, -1, $numChoices))
	{
		throw new \InvalidArgumentException("Invalid value, default=$default");
	}

	$question = "Please enter a number between 1-$numChoices";

	if ($default > 0 ) {

		$question .= 'default is $default';

	}//if ($default > 0 )

	elseif ($default < 0) {

		$question .= "(enter to abort)";
		$default = $numChoices;

	}//elseif ($default < 0)

	$question .= ":";

	while (1) {
		$command->line('');
		$command->info($title);
		$command->line('');

		for ($i=0; $i < $numChoices; $i++) { 

			$command->line(($i + 1).". ".$choices[$i]);

		}//for ($i=0; $i < $numChoices; $i++)

		$command->line('');
		$answer = $command->ask($question);

		if ($answer == '') {

			$answer = $default;

		}//if ($answer = '')

		if (between($answer, 1, $numChoices)) {

			if ($abort and $answer == $numChoices) {

				$answer = -1;

			}//if ($abort and $answer == $numChoices)

			return (int)$answer;

		}//if (between($answer, 1, $numChoices))

	}//while (1)

	// Output wrong choice
	$command->line('');
	$formatter = $command->getHelperSet()->get('formatter');
	$block = $formatter->formatBlock('Invalid entry!', 'error', true);
	$command->line($block);

}

function str2bool($value)
{
	return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}
