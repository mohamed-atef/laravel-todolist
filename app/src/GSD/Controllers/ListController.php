<?php namespace GSD\Controllers;

use GSD\Entities\ListInterface;
use GSD\Repositories\TodoRepositoryInterface;
use Input;
use Response;
use Todo;
use App;
use Carbon\Carbon;
use Config;

class ListController extends \Controller {

    protected $repository;

    function __construct(TodoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $archived = !!Input::get('archived');

        $lists = Todo::allLists($archived);
        
        $return = array(
            'lists'=>array()
        );

        foreach ($lists as $listId) {
            $list = Todo::get($listId, $archived);
            $return['lists'][] = array(
                'name'           => $listId,
                'title'          => $list->get('title'),
                'subtitle'       => $list->get('subtitle'),
                'isArchived'     => $list->isArchived(),
                'numNextActions' => $list->taskCount('next'),
                'numNormal'      => $list->taskCount('todo'),
                'numCompleted'   => $list->taskCount('done'),
            );
        }
        return Response::json($return);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        try{
            $name = strtolower(Input::get("name"));
            $title = Input::get("title");
            if (!$title) $title = ucfirst($name);
            $subtitle = Input::get("subtitle");
    
            if (empty($name))
                throw new \RuntimeException("List Name $name is required");
            if ($this->repository->exists($name, false))
                throw new \RuntimeException("List '$name' already exists");
    
            $list = Todo::makeList($name, $title);
            if ($subtitle)
            {
                $list->set('subtitle', $subtitle)->save();
            }
    
            $result = array('success' => true,'name' => $name);
        }catch(\Exception $e){
            $result = array('error' => $e->getMessage());
        }
        return Response::json($result);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $archived = !!Input::get('archived');
        
        try {
            $list = Todo::get($id, $archived);
            $result = $this->toAssoc($list);
        } catch (\RuntimeException $e) {
            $result = array('error'=>$e->getMessage());
        }

        return Response::json($result);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $input = Input::get('list');

        if ($id != $input['name']) {
            return Response::json(array('error'=>$input['name']));
        }

        // Build list with new inputs
        $list = App::make('GSD\Entities\ListInterface');
        $list->set('id', $id);
        $list->set('title', $input['title']);
        $list->set('subtitle', $input['subtitle']);
        $list->set('archived', str2bool($input['archived']));

        // Add tasks from values passed       
        $tasks = array_key_exists('tasks', $input) ? $input['tasks'] : array();
        foreach ($tasks as $task) {
            $newTask = App::make('GSD\Entities\TaskInterface');
            $descript = $task['descript'];
            if ($task['dateDue']) {
                $d = Carbon::createFromTimestamp($task['dateDue']/1000);
                $descript .= ' :due:'.$d->format('Y-m-d');
            }
            $newTask->setDescription($descript);
            if (str2bool($task['isCompleted'])) {
                $newTask->setIsComplete(true, Carbon::createFromTimestamp($task['dateCompleted']/1000));
            }
            if (str2bool($task['isNext'])) {
                $newTask->setIsNextAction(true);
            }

            $list->taskAdd($newTask);
        }

        // save list
        $list->save();
        
        return Response::json(array('success'=>true));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $archived = Input::get('archived');
        // dd($archived);
        try {
            // default list throw exception
            if ($id == Config::get('todo.gsd.defaultList')) {
                throw new \RuntimeException("Default list can't be deleted");
            }
            $list = Todo::get($id, $archived);

            if($list->delete()){
                $result = array('success'=>true);
            }
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage());
        }
        return Response::json($result);
        
    }

    /**
     * Archive the specified list
     *
     * @param string $id The list name
     */
    public function archive($id)
    {
        try{
            // Throws error if list doesn't exist
            $list = Todo::get($id);

            // Can't archive default list
            if ($id == Config::get('todo.gsd.defaultList')) {
                throw new \RuntimeException("Cannot archive default list");
            }

            // Throw error if archived list exist
            if ($this->repository->exists($id, true)) {
                throw new \RuntimeException("Archive list '$id' exists. Try renaming first.");
            }
        }catch(\Exception $e){
            return Response::json(array('error'=>$e->getMessage()));
        }

        $list->archive();
        return Response::json(array('success'=>true));
    }

    /**
     * Unarchive the specified list
     *
     * @param string $id The list name
     */
    public function unarchive($id)
    {
        try{
            // Throws error if list doesn't exist
            $list = Todo::get($id, true);

            // Throw error if list active already exist
            if ($this->repository->exists($id, false)) {
                throw new \RuntimeException("Active list '$id' exists. Try renaming first.");
            }
            
            $list->set('archived', false);
            $list->save();

            // Delete existing archive
            if (!$this->repository->delete($id, true)) {
                throw new \RuntimeException("ERROR deleting archived version.");
            }

        }catch(\Exception $e){
            return Response::json(array('error'=>$e->getMessage()));
        }

        return Response::json(array('success'=>true));
    }

    /**
     * Rename $source list to $dest
     * @param string $source The source list name
     * @param string $dest
     *The destination list name
     */
    public function rename($source, $dest)
    {
        $archived = !! Input::get('archived');
        $source = trim($source);
        $dest = trim($dest);

        try{
            if (empty($source))
                throw new \RuntimeException("Source list name is required");
            if (empty($dest))
                throw new \RuntimeException("Destination list name required");
            if ($source == Config::get('todo.gsd.defaultList') && ! $archived)
                throw new \RuntimeException("Cannot rename default list");
            if ($this->repository->exists($dest, $archived))
                throw new \RuntimeException("Destination list exists");

            // Load existing list, save with new name, then delete old one
            $list = Todo::get($source, $archived);
            $newList = clone $list;
            $newList->set('id', $dest);
            $newList->save();
            $list->delete();

            $result = array('success' => true);
        }catch(\Exception $e){
            $result = array('error'=>$e->getMessage());
        }
        return Response::json($result);
    }

    public function moveTask($source, $index, $dest)
    {
        try {
            // throw Exception for the same list
            if ($source == $dest) {
                throw new \RuntimeException("can't move to same list");
            }

            // throw exception if source is null
            if (is_null($source)) {
                throw new \RuntimeException("Invalid source list"); 
            }
            $sourceList = Todo::get($source);

            // throw exception if dest is null
            if (is_null($dest)) {
                throw new \RuntimeException("Invalid destination list"); 
            }
            $destList = Todo::get($dest);

            // throw exception if destination archive
            if ($destList->isArchived()) {
                throw new \RuntimeException("can't move to archive list");
            }

            $task = $sourceList->task($index);
            if (is_null($task)) {
                throw new \RuntimeException("Invalid task"); 
            }
            
            $sourceList->taskRemove($index);
            $destList->taskAdd($task)->save();
            $sourceList->save();

            $return = array('success'=>true);
        } catch (Exception $e) {
            $return = array('error'=>$e->getMessage());
        }
        return Response::json($return);
    }

    public function toAssoc(ListInterface $list)
    {
        $return = array(
            'list'=>array(
                'name'=> $list->get('id'),
                'title'=> $list->get('title'),
                'subtitle'=> $list->get('subtitle'),
                'archived'=> $list->get('archived'),
                'tasks'=>array()
            )
        );

        foreach ($list->tasks() as $task) {
            $array = array(
                'isNext' => $task->isNextAction(),
                'isCompleted' => $task->isComplete(),
                'descript' => $task->description(),
                'dateDue' => $task->dateDue(),
                'dateCompleted' => $task->dateCompleted()
            );

            if ($array['dateDue']) {
                $array['dateDue'] = $array['dateDue']->timestamp * 1000;
            }

            if ($array['dateCompleted'])
            {
                $array['dateCompleted'] = $array['dateCompleted']->timestamp * 1000;
            }
            $return['list']['tasks'][] = $array;
        }

        return $return;
    }
}
