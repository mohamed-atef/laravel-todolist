/*Getting stuff Done javascript*/

/**
 * Fire after page load
 */
jQuery(window).load(function($){
    gsd.initialize();
    scrollUp();
});

function scrollUp () {
    // Scroll Up Button

    $(window).scroll(function()
    {
        var scrolltop=$(this).scrollTop();
        if(scrolltop>=500)
        {
            $("#elevator_item").show();
        }
        else { $("#elevator_item").hide();
        }
    });
    $("#elevator").click(function()
    {
        $("html,body").animate({scrollTop: 0}, 500);
    });

}

/**
 * gsd object
 */
var gsd = (function () {
    // private variables and functions
    var alertTimer = null;

    var currentList = null;

    var activeLists = null;

    var archivedLists = null;

    function commonBox (msgType, message) {
        clearTimeout(alertTimer);

        $("#message-area").html(
            $("#" + msgType + "-message")
            .html()
            .replace(msgType + '-message-text', message)
            .replace(msgType + '-message-id', 'alert-id')
        );
        alertTimer = setTimeout(function()
        {
        $("#alert-id").slideUp();
        alertTimer = null;
        }, 8000);
    }

    /**
     * Handle topnav menu click on archive/unarchive button
     */
    function menuArchiveClick () {
        var msg = '';
        var flag = false;
        var url = '/lists/'+currentList.name;
        if (currentList.archived) {
            url += '/unarchive';
        }else{
           url += '/archive'; 
        }

        $.ajax({
            url: url,
            type:'POST',
            error: function (hdr, status, error) {
                gsd.errorMessage('menuArchiveClick error: '+status+' - '+error);
            },
            success: function (data) {
                if (data && data.error) {
                    gsd.errorMessage("archive error: "+data.error);
                    return;
                };
                if (currentList.archived) {
                    flag = false;
                    msg = 'unarchived.';
                }else{
                    flag = true;
                    msg = 'archived.';
                }
                gsd.loadList(currentList.name, flag);
                gsd.successMessage("List successfully "+msg);
            }
        });
        return false;
    }

    /**
     * Handle click on top nav menu rename option
     */
    function menuRenameClick () {
        $(".dropdown-menu").dropdown("toggle");
        var dest = prompt("New name for list '" + currentList.name + "'?");
        if (!dest)
        {
            gsd.errorMessage("Rename canceled");
            return false;
        }
        var url = '/lists/' + currentList.name + '/rename/' + dest;
        if (currentList.archived) url += '?archived=1';
        $.ajax({
            url: url,
            method: "POST",
            error: function(hdr, status, error){
                gsd.errorMessage("menuRenameClick " + status + ' - ' + error);
            },
            success: function(data)
            {
                if (data && data.error)
                {
                    gsd.errorMessage(data.error);
                    return;
                }
                gsd.loadList(dest, currentList.archived);
                gsd.successMessage("Rename successful.");
            }
        });
    }

    /**
     * Handle click on topnav menu delete option
     */
    function menuDeleteClick () {
        $(".dropdown-menu").dropdown("toggle");
        var choose = confirm('Are you sure you want to delete '+currentList.name);
        if (!choose) {
            gsd.errorMessage("Deleting list process canceled.");
            return false;
        };
        // Confirm if list has tasks
        if(currentList.tasks.length > 0){
            var verify = confirm("This list has tasks do you still want to delete it ?");
            if (!verify) {
                gsd.errorMessage("Deleting list process canceled.");
                return false;
            };
        };
        var url = '/lists/'+currentList.name
        if(currentList.archived){url += '?archived=1'};

        $.ajax({
            url: url,
            type: 'POST',
            data:{_method: 'DELETE'},
            error:function(hdr, status, error) {
                gsd.errorMessage("menuDeleteClick " + status + ' - ' + error);
            },
            success:function(data) {
                if (data && data.error) {
                    gsd.errorMessage(data.error);
                    return false;
                };
                gsd.loadList(gsd.defaultList, false);
                gsd.successMessage("List has been deleted.");
            }
        });
        
    }

    /**
     * Handle click on top nav menu create option
     */
    function menuCreateClick () {
        $(".dropdown-menu").dropdown("toggle");
        $("#listbot-title").html("Create New List");
        $("#list-id").val('');
        $("#list-title").val('');
        $("#list-subtitle").val('');
        $("#listbox").modal('show').on('shown.bs.modal', function() {
            $("#list-id").focus().select();
        });
        return false;
    }

    /**
     * Display the task modal box
     * @param string title Title of the modal box
     * @param integer index Task index, -1 for new task
     */
    function taskboxShow (title, index) {
        var task = (index == -1) ? {} : currentList.tasks[index];
        $("#task-index").val(index);
        $("#taskbox-title").text(title);
        $("#task-next").prop("checked", (task.isNext === true));
        $("#task-descript").val(task.descript);
        if (task.dateDue)
        {
            var d = new Date(task.dateDue);
            $("#task-due").val(d.toDateString());
        }
        else
        {
            $("#task-due").val("");
        }
        $("#taskbox").modal("show").on("shown.bs.modal", function()
        {
            $("#task-descript").focus().select();
        });
    }

    /**
     * Handle click on top nav menu add option
     */
    function buttonAddClick () {
        taskboxShow("Add New Task", -1);
        return false;
    }

    /**
     * Update nav bar for the current list
     */
    function updateNavBar () {
        $("#list-name").html("+"+currentList.name);
        $("#menu-archive-text").html(currentList.archived ? "Unarchive" : "Archive");
        $("#button-add").prop("disabled", currentList.archived);
    }

    /**
     * Show one of the list of the lists on sidebar
     */
    function showSideBarList (archived)
    {
        var list = (archived) ? archivedLists : activeLists;

        var ul = (archived) ? $("#archived-lists") : $("#active-lists");

        var build = [];

        // No item in list of lists?
        if (list.length == 0) 
        {
            ul.html('<li>No archived lists</li>');
            return;
        }

        // loop through each item, bulid items for li
        for (var i = 0; i < list.length; i++) {
            var html = '<li';
            var l = list[i];
            var numTasks = l.numNextActions + l.numNormal;
            if (archived == currentList.archived && l.name == currentList.name){html += ' class="active"';};
            html += '><a href="javascript:gsd.loadList(\''+l.name+'\',';
            html += archived + ')">';
            html += l.name;
            if (!archived && numTasks > 0) {html += ' <span class="badge">' + numTasks + '</span>'};
            html += '</a></li>';
            build.push(html);
        };
        ul.html(build.join("\n"));
    }

    /**
     * Load the list of lists
     * @param bool archived Load the archived lists?
     */
    function loadLists (archived) {
        var url = '/lists';
        if (archived) {url+='?archived=1'};
        $.ajax({
            url: url,
            error: function (hdr, status, error) {
                gsd.errorMessage("loadLists "+status+'-'+error);
            },
            success: function (data) {
                if (data && data.error) {
                    gsd.errorMessage("loadLists error: "+data.error);
                    return;
                };
                if (archived)
                {
                    archivedLists = data.lists;
                }else{
                    activeLists = data.lists;
                }
                showSideBarList(archived);
            }
        });
    }

    /**
     * Build table row html for complete task
     * @param object task Task object
     * @param int index Index of task within currentList.tasks
     * @return string HTML for a table row representing the task
     */
    function buildCompletedTask (task, index) {
        var html = [];
        html.push('<tr>');
        html.push('<td><span class="label label-default">finished ');
        var d = new Date(task.dateCompleted);
        html.push(d.toDateString() + '</span></td><td>');
        html.push($('<div/>').text(task.descript).html());
        if (task.dateDue)
        {
            d = new Date(task.dateDue);
            html.push(' <span class="label label-info">');
            html.push('due ' + d.toDateString());
            html.push('</span>');
        }
        html.push('</td><td>');

        if ( ! currentList.archived)
        {
            html.push('<a href="javascript:void(0)" onclick="gsd.doDone(' + index);
            html.push(')" class="btn btn-default btn-xs" title="Mark not complete">');
            html.push('<span class="glyphicon glyphicon-ok"></span></a>');
            html.push(' <a href="javascript:void(0)" onclick="gsd.doDelete(' + index);
            html.push(')" class="btn btn-danger btn-xs" title="Delete task">');
            html.push('<span class="glyphicon glyphicon-remove-circle"></span></a>');
        }
        html.push('</td>');
        html.push('</tr>');
        return html.join('');
    }

    /**
     * Build table row html for open task
     * @param object task Task object
     * @param int index Index of task within currentList.tasks
     * @return string HTML for a table row representing the task
     */
    function buildOpenTask (task, index) {
        var html = [];
        html.push('<tr>');
        html.push('<td>');
        if (task.isNext) {html.push('<span class="label label-success">next</span>')};
        html.push('</td>');
        html.push('<td>');
        html.push($('<div/>').text(task.descript).html());
        if (task.dateDue) {
            var d = new Date(task.dateDue);
            html.push(' <span class="label label-primary">');
            html.push('Due '+ d.toDateString());
            html.push('</span>');
        };
        html.push('</td>');
        html.push('<td>');
        if (! currentList.archived) {
            html.push('<a href="javascript:void(0)" onclick="gsd.doDone('+index);
            html.push(')" class="btn btn-success btn-xs" title="Mark complete">');
            html.push('<span class="glyphicon glyphicon-ok"></span></a>');
            html.push(' <a href="javascript:void(0)" onclick="gsd.doEdit(' + index);
            html.push(')" class="btn btn-info btn-xs" title="Edit task">');
            html.push('<span class="glyphicon glyphicon-pencil"></span></a>');
            html.push(' <a href="javascript:void(0)" onclick="gsd.doMove(' + index);
            html.push(')" class="btn btn-warning btn-xs" title="Move task">');
            html.push('<span class="glyphicon glyphicon-transfer"></span></a>');
            html.push(' <a href="javascript:void(0)" onclick="gsd.doDelete(' + index);
            html.push(')" class="btn btn-danger btn-xs" title="Delete task">');
            html.push('<span class="glyphicon glyphicon-remove-circle"></span></a>');
        };
        html.push('</td>');
        html.push('</tr>');
        return html.join('');
    }


    function showTasks () {
        var open = [];
        var completed = [];
        for (var i = 0; i < currentList.tasks.length; i++) {
            var task = currentList.tasks[i];
            if(task.isCompleted){
                completed.push(buildCompletedTask(task, i));
            }else{
                open.push(buildOpenTask(task, i));
            }
        };
        if (open.length === 0) {open.push('<tr><td colspan="3">No open tasks</td></tr>');};
        if (completed.length === 0) {completed.push('<tr><td colspan="3">No completed tasks</td></tr>');};
        $("#open-tasks").html(open.join("\n"));
        $("#completed-tasks").html(completed.join("\n"));
    }

    function saveCurrentList (success_message, from) {
        $.ajax({
            url: "/lists/"+currentList.name,
            type: 'post',
            data: {_method: 'put', list:currentList},
            error:function(hdr, status, error) {
                gsd.errorMessage("saveCurrentList " + status + ' - '+error+" , from: "+from);
            },
            success:function (data) {
                if (data && data.error) {
                    console.log(data);
                    gsd.errorMessage("saveCurrentList error: "+data.error+" , from: "+from);
                    return;
                };
                gsd.loadList(currentList.name, currentList.archived);
                gsd.successMessage(success_message);
            }

        });
    }

    return {
        // public vars
        // public functions
        defaultList: null,
        
        /**
         * initialization
         */
        initialize: function () {
            $("#menu-archive").click(menuArchiveClick);
            $("#menu-rename").click(menuRenameClick);
            $("#menu-create").click(menuCreateClick);
            $("#menu-delete").click(menuDeleteClick)
            $("#button-add").click(buttonAddClick);

            // Load default list
            this.loadList(this.defaultList, false);
        },

        successMessage: function(message){commonBox('success', message);},

        errorMessage: function(message){commonBox("error", message);},

        loadList: function (name, archived) 
        {
            var url = "/lists/"+name;
            if(archived) url+="?archived=1";
            $.ajax({
                url: url,
                error: function (hdr, status, error) {
                    gsd.errorMessage("loadList " + status + "-" + error);
                },
                success: function (data) {
                    if (data && data.error) {
                        gsd.errorMessage("loadList error " + data.error);
                        return;
                    };
                    currentList = data.list;
                    updateNavBar();
                    showTasks();

                    // Reload Lists
                    loadLists(false);
                    loadLists(true);
                }
            });            
        },

        /**
         * Mark task as completed
         */
        doDone: function (index)
        {
            // Toggle completion status
            if (currentList.tasks[index].isCompleted)
            {
                currentList.tasks[index].isCompleted = false;
            }
            else
            {
                var d = new Date();
                currentList.tasks[index].isCompleted = true;
                currentList.tasks[index].dateCompleted = d.valueOf();
            }
            saveCurrentList("Task completion updated.", "doDone");
        },

        /**
         * Edit task
         */
        doEdit: function (index) {
            taskboxShow("Edit task", index);
        },

        /**
         * Move task
         */
        doMove: function(index)
        {
            var dest = prompt("Name of destination list :");
            if (!dest)
            {
                gsd.errorMessage("Move canceled");
                return false;
            };
            var url = '/lists/'+currentList.name+'/'+index+'/move/'+dest;
            
            $.ajax({
                url: url,
                type: 'POST',
            })
            .done(function(data) {
                if (data && data.error) {
                    gsd.errorMessage(data.error);
                    return false;
                };
                gsd.loadList(currentList.name, false);
                gsd.successMessage("Task has been moved successfully.");
            })
            .fail(function(hdr, status, error) {
                gsd.errorMessage("MoveTask " + status + " - " + error);
            });
            
        },

        /**
         * Delete a task
         */
        doDelete: function(index)
        {
            if (! confirm("This will permanently destroy the task. Are you sure?")) {return};
            
            // Remove the item from the current list
            currentList.tasks.splice(index, 1);
            
            // And save the list
            saveCurrentList("Task successfully deleted.", "doDelete");
        },

        taskboxSave: function () {
            var index = parseInt($("#task-index").val());
            var dueDate = $("#task-due").val();
            var task = {
                isNext: $("#task-next").prop("checked"),
                isCompleted: false,
                dateCompleted: null,
                descript: $("#task-descript").val()
            };
            if (dueDate === "")
            {
                dueDate = null;
            }
            else
            {
            try {
                dueDate = Date.parse(dueDate);
            } catch (err) {
                dueDate = null;
            }
            if (isNaN(dueDate))
                dueDate = null;
            }
            task.dateDue = dueDate;
            if (index < 0)
            {
                currentList.tasks.push(task);
            }
            else
            {
                currentList.tasks[index] = task;
            }
            $("#taskbox").modal("hide");
            saveCurrentList("Task successfully saved.", "taskboxSave");
        }, // as always the trailing comma of every method or property

        listboxSave: function() {
            var data = {
                name: $("#list-id").val(),
                title: $("#list-title").val(),
                subtitle: $("#list-subtitle").val()
            };

            $.ajax({
                url: '/lists',
                type: 'POST',
                data: data,
                error: function(hdr, status, error) {
                    gsd.errorMessage("listboxSave " + status + ' - ' + error);
                    $("#listbox").modal('hide');
                },
                success: function(data) {
                    console.log(data);
                    $("#listbox").modal('hide');
                    if (data && data.error) {
                        gsd.errorMessage(data.error);
                        return;
                    };
                    gsd.loadList(data.name, false);
                    gsd.successMessage("List successfully created.");
                }
            })
        }
    };
})();
