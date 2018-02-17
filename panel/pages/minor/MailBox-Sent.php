<?php \Utils\Front::printPageDesc("Skrzynka nadawcza", "Wysłane wiadomości"); ?>

<div class="row">
    <div class="col-md-3">
        <a href="?tab=mailbox&minor=send" class="btn btn-primary btn-block margin-bottom">Utwórz nową wiadomość</a>

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
                    <li class="active"><a href="?tab=mailbox&minor=sent"><i class="fa fa-envelope-o"></i> Wysłane</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Wiadomości wysłane</h3>

                <!--<div class="box-tools pull-right">
                    <div class="has-feedback">
                        <input type="text" class="form-control input-sm" placeholder="Search Mail">
                        <span class="glyphicon glyphicon-search form-control-feedback"></span>
                    </div>
                </div>-->
                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
                <div class="mailbox-controls">
                    <!-- Check all button -->
                    <!--<button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i>
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
                    </div>-->
                    <!-- /.btn-group -->
                    <!--<button type="button" class="btn btn-default btn-sm" onclick="window.location.reload()"><i class="fa fa-refresh"></i></button>-->
                </div>
                <div class="table-responsive mailbox-messages">
                    <table class="table table-hover table-striped">
                        <tbody>
                        <?php

                        try
                        {
                            $messages = \User\Message::getAllSentMessages($_SESSION["userid"]);
                            if (empty($messages)) echo "<div class='alert alert-warning block-btn'>Nie masz aktualnie żadnych wiadomości!</div>";

                            foreach ($messages as $message)
                            {
                                echo "<tr>";
                                echo "<td><input type='checkbox'></td>";
                                echo "<td class='mailbox-star'><i class='fa ".($message["message_read"] == "1" ? "fa-star-o" : "fa-star")." text-yellow'></i></td>";
                                echo "<td class='mailbox-name'><a href='?tab=mailbox&minor=message&msgid=".$message["message_id"]."'>".$message["user_name"]." ".$message["user_surname"]."</a>";
                                echo "<td class='mailbox-subject'>".$message["message_title"]."</td>";
                                echo "<td class='mailbox-attachment'></td>";
                                echo "<td class='mailbox-date'>".$message["message_sent"]."</td>";
                                echo "</tr>";
                            }
                        }
                        catch (\Exception $e)
                        {
                            \Utils\Front::error($e->getMessage());
                        }

                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer no-padding">
                <div class="mailbox-controls">
                    <button type="button" class="btn btn-default btn-sm" onclick="window.location.reload()"><i class="fa fa-refresh"></i></button>
                    <!-- Check all button
                    <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i>
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
                    </div>
                    <button type="button" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i></button>
                    <div class="pull-right">
                        1-50/200
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm"><i class="fa fa-chevron-left"></i></button>
                            <button type="button" class="btn btn-default btn-sm"><i class="fa fa-chevron-right"></i></button>
                        </div>
                        <!-- /.btn-group
                    </div>
                    <!-- /.pull-right -->
                </div>
            </div>
        </div>
        <!-- /. box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
