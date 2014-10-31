<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/31
 * Time: 11:42
 */

namespace Greg\ATC\TestBundle\Contorller;

use AutoMaid\Annotation\ServiceAnnotation;
use Greg\ATC\TestBundle\Common\Service\WaitressTrait;
use Greg\ATC\TestBundle\Common\Controller;

/**
 * Class TestController
 * @package Greg\ATC\TestBundle\Contorller
 * @ServiceAnnotation("test_controller")
 */
class TestController extends Controller{
    use WaitressTrait;
} 