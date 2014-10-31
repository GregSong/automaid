<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/21
 * Time: 15:54
 */

namespace AutoMaid;


class Service
{
    const SERVICE    = 0;
    const EXPRESSION = 1;
    const CONSTANT   = 2;

    protected $name;
    protected $alias;
    protected $clazz;
    protected $setter;
    /**
     * @var array
     */
    protected $depends = array();
    /**
     * @var string
     */
    protected $cfgPath;

    /**
     * @param $name
     * @param string $clazz Only generated service needs to know class name
     */
    function __construct($name, $clazz = '', $alias = '')
    {
        $this->name  = $name;
        $this->clazz = $clazz;
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getSetter()
    {
        return $this->setter;
    }

    /**
     * @param string $setter
     */
    public function setSetter($setter)
    {
        $this->setter = $setter;
    }


    /**
     * @return string
     */
    public function getClazz()
    {
        return $this->clazz;
    }

    /**
     * @return \string[]
     */
    public function &getDepends()
    {
        return $this->depends;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param array $depends
     */
    public function add($depends)
    {
        $serviceName = '';
        foreach ($depends as $name => $depend) {
            // Parse type, Service, Expression, Constants
//            $pos  = 0;
            $type = self::CONSTANT;
            // TODO Greg: I don't know how to distinct false and 0
            if (false !== strpos($depend, '@=') && strpos($depend, '@=') == 0) {
                $type = self::EXPRESSION;
            } elseif ($depend[0] == '@') {
                if (strlen($depend) >= 2 && $depend[1] != '@') {
                    $serviceName = substr($depend, 1);
                    $type        = self::SERVICE;
                } else {
                    $type = self::CONSTANT;
                }
            }

            $this->depends[$name] = array(
                'service' => $serviceName,
                'type'    => $type,
                'setter'  => 'set' . ucwords($name),
                'depend'  => $depend,
            );
        }
    }

    public function setCfgPath($cfgPath)
    {
        $this->cfgPath = $cfgPath;
    }

    public function getCfgPath()
    {
        return $this->cfgPath;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }
}