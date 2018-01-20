<?php
namespace App;

class Menu {
    use Traits\Singleton;
    use Traits\GetSet;

    protected $items = array();

    public static function getItems() {
        $data = self::getInstance()->get('items');
        $items = array();

        $request = new HttpRequest();
        $page = $request->getParam('page');

        foreach($data as $display => $link) {
            $class = preg_match("/^(".rtrim($link, 's').")/", $page) ? ' active' : '';
            $items[] = "<li class='nav-item'>
                <a class='nav-link {$class}' href='/{$link}'>{$display}</a>
            </li>";
        }

        return join($items, "\n");
    }
}
?>