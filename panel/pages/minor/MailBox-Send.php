<?php

\Utils\Front::printPageDesc("Nowa wiadomość", "Tworzenie nowej wiadomości prywatnej");

if (isset($_POST["receiver"]))
{
    try
    {
        \Utils\Validations::validatePostArray($_POST);
        \Utils\Validations::validateWholeArray($_POST);

        $user = new \User\User();
        $credentials = explode(" ", $_POST["receiver"]);
        $receiver = $user->findUsersByCredentials($credentials[0], $credentials[1])[0];
        $receiver_id = $receiver->getUserId();

        if (strlen($_POST["title"]) > 32) throw new \Exception("Za długi temat wiadomości! (max. 32)");

        \User\Message::sendMessage($_SESSION["userid"], $receiver_id, $_POST["title"], $_POST["message"]);
        \Utils\Front::success("Wiadomość wysłana poprawnie!");
    }
    catch (\Exception $e)
    {
        \Utils\Front::error($e->getMessage());
    }
}

?>

<div class="col-md-3">
    <a href="?tab=mailbox&minor=send" class="btn btn-primary btn-block margin-bottom">Wróć do skrzynki nadawczej</a>

    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title">Foldery</h3>
            <div class="box-tools">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body no-padding">
            <ul class="nav nav-pills nav-stacked">
                <li><a href="?tab=mailbox&minor=main"><i class="fa fa-inbox"></i> Odebrane
                        <span class="label label-primary pull-right"><?php $count = \User\Message::countUnreadMessages($_SESSION["userid"]); if ($count > 0) echo $count;?></span></a></li>
                <li><a href="?tab=mailbox&minor=sent"><i class="fa fa-envelope-o"></i> Wysłane</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="col-md-9">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Utwórz nową wiadomość</h3>
        </div>
        <!-- /.box-header -->
        <form method="post">
            <div class="box-body">
                <div class="form-group">
                    <input type="text" name="receiver" class="form-control" placeholder="Do: (imię i nazwisko)">
                </div>
                <div class="form-group">
                    <input type="text" name="title" class="form-control" placeholder="Temat:">
                </div>
                <div class="form-group">
                    <textarea class="form-control" name="message" placeholder="Zawartość wiadomości..." id="message"></textarea>
                </div>
            </div>
            <div class="box-footer">
                <div class="pull-right">
                    <!--<button type="button" class="btn btn-default"><i class="fa fa-pencil"></i> Draft</button>-->
                    <button type="submit" class="btn btn-primary"><i class="fa fa-envelope-o"></i> Wyślij</button>
                </div>
                <button type="reset" onclick="clearCKE()" class="btn btn-default"><i class="fa fa-times"></i> Wyczyść</button>
            </div>
        </form>
        <script>
            $(function () {
                CKEDITOR.replace("message");
            });

            function clearCKE()
            {
                CKEDITOR.instances.message.setData('');
            }
        </script>
    </div>
    <!-- /. box -->
</div>