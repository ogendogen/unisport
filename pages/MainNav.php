<body>
<nav id="nav" class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Nawigacja</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Nawigacja</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li id=page0><a href="<?php echo $CONF["site"]; ?>">Strona główna</a></li>
                <?php
                // todo: if logged in than "Zalogowany" else "Zaloguj"
                if (isset($_SESSION["userid"]))
                {
                    echo "<li id=page1><a href=\"#\">Zalogowany jako: ".$_SESSION["login"]."</a></li>";
                }
                else
                {
                    echo "<li id=page1><a href=\"".$CONF["site"]."?tab=login\">Logowanie</a></li>";
                }
                ?>

                <li id=page2><a href="<?php echo $CONF["site"]; ?>?tab=register">Rejestracja</a></li>

                <?php

                if (isset($_SESSION["userid"]))
                {
                    echo "<li id=page3><a href=\"".$CONF["site"]."?tab=login&logout=1\">Wyloguj się</a></li>";
                }

                ?>
            </ul>
        </div>
    </div>
</nav>