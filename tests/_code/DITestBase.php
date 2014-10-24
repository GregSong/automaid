<?php
//use AutoMaid\DIServiceTraits;
use AutoMaid\Annotation\ServiceAnnotation;
use AutoMaid\Annotation\DepOn;

/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/21
 * Time: 12:25
 */

/**
 * Class DITestBase
 * @ServiceAnnotation("base_service")
 * @DepOn({
 * "testServiceOne": "@test_service1",
 * "testServiceTwo":"@test_service2",
 * "waitress": "@waitress",
 * "url":"%%url%%"
 * })
 */
class DITestBase
{
    protected $waitress;

    /**
     * @param mixed $waitTress
     */
    public function setWaitress($waitTress)
    {
        $this->waitress = $waitTress;
    }

    use DITestServiceOneTraits, DITestServiceTwoTraits;


    public function useService($name)
    {
        return print_r($this->services[$name], true);
    }
} 