<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p><?php echo $logged_user->getUserCredentials(); ?></p>
                <!-- Status -->
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- search form (Optional) -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
              <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
              </button>
            </span>
            </div>
        </form>
        <!-- /.search form -->

        <!-- Sidebar Menu -->
        <?php

        $sidemenu = new \Utils\MenuBuilder();
        $sidemenu->addSingleTab("Pulpit", "home", "fa fa-home");
        $sidemenu->addSingleTab("Moje drużyny", "teams", "fa fa-users");
        $sidemenu->addSingleTab("Moje mecze", "matches", "glyphicon glyphicon-list-alt");

        $multisub = array();

        $multisub[0]["name"] = "Multi #1";
        $multisub[0]["tab"] = "tab1";

        $multisub[1]["name"] = "Multi #2";
        $multisub[1]["tab"] = "tab2";

        $sidemenu->addMultiTab("Multitab", "multi", $multisub);
        unset($sidemenu);

        ?>

        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>