<div class="modal fade" id="crudModelViewAccount" data-backdrop="false" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modelHeading">Show Transaction Account</h4>
                <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="name" class="control-label">Name</label>
                    <input type="text" class="form-control" value="{{$account?->name}}" disabled readonly>
                </div>
                <div class="col-sm-offset-2 col-sm-10">
                    <button class="btn btn-default close-modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>