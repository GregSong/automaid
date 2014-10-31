<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/31
 * Time: 14:56
 */

namespace Greg\ATC\TestBundle\Common;

use AutoMaid\Annotation\ServiceAnnotation;
use Greg\ATC\TestBundle\Common\Service\DummyServiceTwoTraits;

/**
 * Class Controller
 * @package Greg\ATC\TestBundle\Common
 * @ServiceAnnotation("base_controller")
 */
class Controller
{
    use DummyServiceTwoTraits;
} 