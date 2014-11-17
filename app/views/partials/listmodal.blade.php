<div class="modal fade" id="listbox">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" type="button" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="listbox-title">title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="list-id" class="col-lg-3 control-label">List Name</label>
                        <div class="col-lg-9"><input type="text" class="form-control" id="list-id"></div>
                    </div>
                    <div class="form-group">
                        <label for="list-title" class="col-lg-3 control-label">List Title</label>
                        <div class="col-lg-9"><input type="text" class="form-control" id="list-title"></div>
                    </div>
                    <div class="form-group">
                        <label for="list-subtitle" class="col-lg-3 control-label">List Subtitle</label>
                        <div class="col-lg-9"><input type="text" class="form-control" id="list-subtitle"></div>
                    </div>
                </form>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">Close</button>
                    <button class="btn btn-primary" type="button" onclick="gsd.listboxSave()">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>