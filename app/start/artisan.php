<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/
Artisan::add(new GSD\Commands\ListAllCommand);

Artisan::add(new GSD\Commands\CreateCommand);

Artisan::add(new GSD\Commands\EditListCommand);

Artisan::add(new GSD\Commands\UncreateCommand);

Artisan::add(new GSD\Commands\AddTaskCommand);

Artisan::add(new GSD\Commands\DoTaskCommand);

Artisan::add(new GSD\Commands\ListTasksCommand);

Artisan::add(new GSD\Commands\EditTaskCommand);

Artisan::add(new GSD\Commands\ArchiveListCommand);

Artisan::add(new GSD\Commands\UnArchiveListCommand);

Artisan::add(new GSD\Commands\RenameListCommand);

Artisan::add(new GSD\Commands\RemoveTaskCommand);

Artisan::add(new GSD\Commands\MoveTaskCommand);

