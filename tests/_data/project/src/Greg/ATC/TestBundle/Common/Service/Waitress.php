<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/22
 * Time: 11:39
 */
namespace Greg\ATC\Common\Service;



/**
 * Class Waitress
 * @\AutoMaid\Annotation\ServiceAnnotation("waitress")
 */
class Waitress {
    public static $orders = array();
    public function order($name)
    {
        self::$orders[] = $name;
    }

    public function listOrders()
    {
        return json_encode(self::$orders);
    }

} 