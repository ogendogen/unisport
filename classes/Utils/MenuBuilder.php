<?php
/**
 * Created by PhpStorm.
 * User: Marcin
 * Date: 2017-10-06
 * Time: 12:03
 */

namespace Utils
{
    class MenuBuilder
    {
        public function __construct()
        {
            if (!isset($_SESSION["active"])) throw new \Exception("User niezalogowany"); // potrzebne aby zdefiniowac aktywną kartę użytkownika
            $this->createEmptyMenu();
        }

        public function finishBuilding()
        {
            echo "</ul>";
        }

        private function createEmptyMenu()
        {
            echo '<ul class="sidebar-menu" data-widget="tree">
                <li class="header">Główna nawigacja</li>';
        }

        public function addSingleTab(string $name, string $tab, string $fa_icon="fa-link", bool $active=false)
        {
            echo '<li id="'.$tab.'" '.($active ? 'class="active"' : '').'><a href="?tab='.$tab.'"><i class="fa '.$fa_icon.'"></i> <span>'.$name.'</span></a></li>';
        }

        public function addMultiTab(string $name, string $tab, array $subpages, string $fa_icon="fa-link", bool $active=false) // subpages array map: "name" => "nazwa", "tab" => "strona"
        {
            echo '<li class="treeview">
                    <a href="#"><i class="fa fa-link"></i>
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

        /*public function setActiveTab(string $tab, string $parent=null)
        {
            echo '<script>$("#'.$tab.'").addClass("active");</script>';
            if (!is_null($parent))
            {
                ?>

                <?php
            }
        }*/
    }
}