<?php

namespace SoliDryTest\_data;


use Modules\V2\Entities\Menu;

class MenuFixture
{
    public static function createAndGet() : Menu
    {
        $mainMenu            = new Menu();
        $mainMenu->title     = 'Foo Bar Baz';
        $mainMenu->rfc       = '/main/menu';
        $mainMenu->parent_id = 0;
        $mainMenu->save();

        $subMenu            = new Menu();
        $subMenu->title     = 'Foo Bar Baz Sub';
        $subMenu->rfc       = '/main/sub_menu';
        $subMenu->parent_id = $mainMenu->id;
        $subMenu->save();

        $subSubMenu            = new Menu();
        $subSubMenu->title     = 'Foo Bar Baz Sub Sub';
        $subSubMenu->rfc       = '/main/sub_sub_menu';
        $subSubMenu->parent_id = $subMenu->id;
        $subSubMenu->save();

        return $mainMenu;
    }

    public static function truncate()
    {
        Menu::truncate();
    }
}