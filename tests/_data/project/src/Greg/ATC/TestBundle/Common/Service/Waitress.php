<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/22
 * Time: 11:39
 */
namespace Greg\ATC\TestBundle\Common\Service;



/**
 * Class Waitress
 * @\AutoMaid\Annotation\ServiceAnnotation("waitress")
 * @\AutoMaid\Annotation\DepOn({
 * "dummyService":"@dummy_service"
 * })
 */
class Waitress {
    protected $dummyService;

    /**
     * @param mixed $dummyService
     */
    public function setDummyService($dummyService)
    {
        $this->dummyService = $dummyService;
    }



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