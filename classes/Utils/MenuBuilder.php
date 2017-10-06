<?php

namespace Utils
{
    class MenuBuilder
    {
        public function __construct()
        {
            if (!isset($_SESSION["active"])) throw new \Exception("User niezalogowany"); // potrzebne aby zdefiniowac aktywną kartę użytkownika
            $this->createEmptyMenu();
        }

        public function __destruct()
        {
            echo "</ul>";
        }

        private function createEmptyMenu()
        {
            echo '<ul class="sidebar-menu" data-widget="tree">
                <li class="header">Główna nawigacja</li>';
        }

        public function addSingleTab(string $name, string $tab, string $icon="fa fa-link")
        {
            echo '<li id="'.$tab.'"><a href="?tab='.$tab.'"><i class="'.$icon.'"></i> <span>'.$name.'</span></a></li>';
        }

        public function addMultiTab(string $name, string $tab, array $subpages, string $icon="fa fa-link") // subpages array map: "name" => "nazwa", "tab" => "strona"
        {
            echo '<li class="treeview">
                    <a href="#"><i class="'.$icon.'"></i>
                        <span>'.$name.'</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">';

                    foreach ($subpages as $subpage)
                    {
                        echo '<li id="'.$subpage["tab"].'"><a href="?tab=' .$subpage["tab"]. '">'.$subpage["name"].'</a></li>';
                    }

                    echo '</ul>
                </li>';
        }
    }
}