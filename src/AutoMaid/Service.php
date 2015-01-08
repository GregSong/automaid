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
    protected $tags = array();
    protected $top = false;
    protected $lazy;
    /**
     * @var array
     */
    protected $depends = array();
    /**
     * @var string
     */
    protected $cfgPath;
    /**
     * @var array
     */
    protected $arguments;
    /**
     * @var string
     */
    protected $scope;

    /**
     * @var bool
     */
    protected $abstract;

    /**
     * @var array
     * should be:
     * {
     *  "class": factory_class
     *  "method": factory_method
     * }
     */
    protected $factory;
    protected $parent;

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param $name
     * @param string $clazz Only generated service needs to know class name
     * @param string $alias
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
    public function & getDepends()
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
    public function addDeps($depends)
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
                'property' => $name,
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

    public function addTags($tags)
    {
        $this->tags = $tags;
    }

    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return boolean
     */
    public function isTop()
    {
        return $this->top;
    }

    /**
     * @param boolean $top
     */
    public function setTop($top)
    {
        $this->top = $top;
    }

    /**
     * @param array $parseArguments
     */
    public function setArguments($parseArguments)
    {
        $this->arguments = $parseArguments;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return array
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param array $parseFactory
     */
    public function setFactory($parseFactory)
    {
        $this->factory = $parseFactory;
    }

    /**
     * @return boolean
     */
    public function isAbstract()
    {
        return $this->abstract;
    }

    /**
     * @param boolean $abstract
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    public function setParent($parseParent)
    {
        $this->parent = $parseParent;
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return boolean
     */
    public function isLazy()
    {
        return $this->lazy;
    }

    /**
     * @param boolean $lazy
     */
    public function setLazy($lazy)
    {
        $this->lazy = $lazy;
    }
}
