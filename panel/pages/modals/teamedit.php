<p class="text-center">
    <button class="btn btn-default" data-toggle="modal" data-target="#loginModal">Edycja</button>
</p>

<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="Login" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title">Edycja drużyny</h5>
            </div>

            <div class="modal-body">
                <!-- The form is placed inside the body of modal -->
                <form id="loginForm" method="post" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-xs-3 control-label">Nazwa drużyny</label>
                        <div class="col-xs-5">
                            <input type="text" name="editTeamName" class="form-control" value=""/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-3 control-label">Opis drużyny</label>
                        <div class="col-xs-5">
                            <textarea name="editTeamdesc" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-5 col-xs-offset-3">
                            <input type="submit" class="btn btn-success" value="Zatwierdź"/>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Anuluj</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
