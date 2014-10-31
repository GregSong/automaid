<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/22
 * Time: 12:32
 */

namespace Greg\ATC\TestBundle\Common\Service;

use AutoMaid\Annotation\DepOn;

/**
 * Class WaitressTrait
 * @package Greg\ATC\Common\Service
 * @DepOn({
 * "waitress":"@waitress"
 * })
 */
trait WaitressTrait {
    public  $waitress;

    /**
     * @param mixed $waitress
     */
    public function setWaitress($waitress)
    {
        $this->waitress = $waitress;
    }

} 