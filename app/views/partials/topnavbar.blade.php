<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="/">Laravel Todo List</a>
        </div>
        <ul class="nav navbar-nav">
            <li class="active">
                <a href="/" data-toggle="dropdown" data-target="#">
                    <span id="list-name">Actions</span> list <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="/" id="menu-archive">
                        <span id="menu-archive-text">Archive</span> list</a>
                    </li>
                    <li><a href="/" id="menu-rename">Rename list</a></li>
                    <li><a href="/" id="menu-delete">Delete list</a></li>
                    <li class="divider"></li>
                    <li><a href="/" id="menu-create">Create new list</a></li>
                </ul>
            </li>
        </ul>
        <div class="navbar-form navbar-right">
            <button type="button" class="btn btn-success" id="button-add">
                <span class="glyphicon glyphicon-plus-sign"></span>
                Add Task
            </button>
        </div>
    </div>
</div>