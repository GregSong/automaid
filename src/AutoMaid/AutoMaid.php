<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/21
 * Time: 00:03
 */
namespace AutoMaid;

//use AppKernel;
use AutoMaid\Annotation\Father;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Monolog\Handler\StreamHandler;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Monolog\Logger;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Yaml\Yaml;

class AutoMaid
{
    const SA                = 'AutoMaid\Annotation\ServiceAnnotation';
    const CA                = 'AutoMaid\Annotation\ControllerAnnotation';
    const DepA              = 'AutoMaid\Annotation\DepOn';
    const TA                = 'AutoMaid\Annotation\Tags';
    const AA                = 'AutoMaid\Annotation\Arguments';
    const SCA               = 'AutoMaid\Annotation\Scope';
    const FA                = 'AutoMaid\Annotation\Factory';
    const FTA               = 'AutoMaid\Annotation\Father';
    const SERVICE_FILE_NAME = 'am_services.yml';

    /**
     * @var bool
     */
    public static $useTraits   = true;
    PUBLIC static $initSysConf = false;
    public static $YAML_LEVEL  = 4;

    /**
     * @var boolean
     */
    protected $lazy;

    protected $projectDir;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Service[]
     */
    protected $definedServices = array();

    /**
     * @var Service[]
     */
    protected $generateServices = array();

    /**
     * @var AMController[]
     */
    protected $controllers = array();
    /**
     * @var AppKernel
     */
    protected $kernel;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    /**
     * @var Reader
     */
    protected $annotationReader;

    protected $amConfigFiles = array();
    /**
     * @var ContainerBuilder
     */
    protected $builder;

    function __construct()
    {
        $this->projectDir = __DIR__ . '/../../../../..';
        $this->logger     = new Logger('AutoMaid');
        $this->logger->pushHandler(
            new StreamHandler('php://stdin', Logger::INFO)
        );
    }

    public function init()
    {
        $this->amConfigFiles = $this->getConfigurationLocation();

        if (self::$initSysConf) {
            $this->initConfigurationFiles($this->amConfigFiles);
        }

        if (class_exists('AppKernel')) {
            $this->kernel = new \AppKernel('dev', true);
            //TODO Greg: below statement will cause a exception will booting kernel, a duplication raised. I may check it later
            $this->kernel->boot();
            $this->container = $this->kernel->getContainer();
            $this->parseDefinedServices();
        }

        $this->annotationReader = new Reader();

    }

    /**
     * @return \string[]
     */
    public function getConfigurationLocation()
    {
        $filePathes = array();

        // Step 1. app/config/config.yml
        $filePathes[] = $this->projectDir . '/app/config/config.yml';

        // Step 2. any other service.yml under src
        foreach (new RecursiveIteratorIterator (
                     new RecursiveDirectoryIterator (
                         $this->projectDir . '/src'
                     ),
                     RecursiveIteratorIterator::CHILD_FIRST
                 ) as $x) {
            if ($x->getFileName() == 'services.yml') {
                $filePathes[] = $x->getPathName();
            }
        }

        return $filePathes;
    }

    /**
     * @param \string[] $locations
     */
    public function initConfigurationFiles(array $locations)
    {
        foreach ($locations as $location) {
            $config = Yaml::parse($location);
            if (isset($config['imports'])) {
                $imports = $config['imports'];
                foreach ($imports as $index => $import) {
                    if ($import['resource'] == self::SERVICE_FILE_NAME) {
                        unset($imports[$index]);
                    }
                }
            } else {
                $config['imports'] = array();
            }
            $config['imports'][] = array(
                'resource'      => self::SERVICE_FILE_NAME,
                'ignore_errors' => true,
            );

            // backup old service.yml and config.yml
            rename($location, $location . '.' . posix_getpid() . '.bak');
            // write back to yaml file
            file_put_contents(
                $location,
                Yaml::dump($config),
                self::$YAML_LEVEL
            );
            // backup old configuration if there is any
            $amService = dirname(
                    $location
                ) . DIRECTORY_SEPARATOR . self::SERVICE_FILE_NAME;
            if (file_exists($amService)) {
                rename($amService, $amService . '.' . posix_getpid() . '.bak');
            }
        }

    }

    /**
     * Get all already defined services
     */
    public function parseDefinedServices()
    {
        if (!empty($this->container)) {
            // TODO Greg: below code is questionable as getServiceIds is not part of ContainerInterface
            // Use reflection to get services and alias of container
            $containerClazz    = new \ReflectionClass($this->container);
            $servicesProperty  = $containerClazz->getProperty('services');
            $aliasesProperty   = $containerClazz->getProperty('aliases');
            $methodMapProperty = $containerClazz->getProperty('methodMap');
            $servicesProperty->setAccessible(true);
            $aliasesProperty->setAccessible(true);
            $methodMapProperty->setAccessible(true);
            $aliases   = $aliasesProperty->getValue($this->container);
            $methodMap = $methodMapProperty->getValue($this->container);

            foreach ($methodMap as $serviceName => $method) {
                $alias = '';
                foreach ($aliases as $a => $n) {
                    if ($n == $serviceName) {
                        $alias = $a;
                        break;
                    }
                }
                $this->definedServices[] = new Service(
                    $serviceName, '', $alias
                );
            }

        }
    }

    /**
     * Search a dir recursively and
     * @param string $path
     * @return int number of php files
     */
    public function loadFiles($path = '../../src')
    {
        $phpFiles = array();
        foreach (new RecursiveIteratorIterator (
                     new RecursiveDirectoryIterator ($path),
                     RecursiveIteratorIterator::CHILD_FIRST
                 ) as $x) {
            if (!empty($x) && preg_match('/^.+\.php$/', $x->getFileName())) {

                $this->logger->info('Loading file ' . $x->getFileName());

                // There are a few files with php suffix but they are html acutally. I need to filter out them.
                if (preg_match('/^.+\.html\.php$/', $x->getFileName())) {
                    continue;
                }

                /** @noinspection PhpIncludeInspection */
                include_once $x->getPathname();
                $phpFiles[] = $x->getPathname();
            }
        }

        return sizeof($phpFiles);
    }

    public function loadClass()
    {
        return get_declared_classes();
    }

    /**
     * Parse 'Factory' annotation
     * @param ReflectionClass $clazz
     * @return array
     * TODO Currently, not consider inheritance
     */
    public function parseFactory(\ReflectionClass $clazz)
    {
        $factory = array();
        if (empty($clazz)) {
            return $factory;
        }

        $factoryAnnotation = $this->annotationReader->getClassAnnotation(
            $clazz,
            self::FA
        );

        if (!empty($factoryAnnotation)) {
            $factory = array(
                'class'  => $factoryAnnotation->getClass(),
                'method' => $factoryAnnotation->getMethod()
            );
        }
        return $factory;
    }
    /**
     * @param \ReflectionClass|null $clazz
     * @return array
     */
    public function parseDepOn($clazz)
    {
        if (empty($clazz)) {
            return array();
        }
        $depOns = array();

        // Parse parents
        $depOns = array_merge(
            $depOns,
            $this->parseDepOn($clazz->getParentClass())
        );

        $depOnAnnotation = $this->annotationReader->getClassAnnotation(
            $clazz,
            self::DepA
        );
        if (!empty($depOnAnnotation)) {
            $depOns = array_merge($depOns, $depOnAnnotation->getServices());
        }

        // Parse trait
        foreach ($clazz->getTraits() as $trait) {
            $depOns = array_merge($depOns, $this->parseDepOn($trait));
        }


        return $depOns;
    }

    /**
     * Parse "Arguments Annotation"
     * This function is a little complex as we need to find closest parent or the class itself
     * which has arguments annotation and compare it with its __construct
     * @param ReflectionClass $clazz
     * @return array
     */
    public function parseArguments(\ReflectionClass $clazz)
    {
        $arguments = array();
        if (empty($clazz)) {
            return $arguments;
        }
        try {
            // Step 1. get constructor
            $constructor = $clazz->getMethod('__construct');
            do {
                $argumentAnnotation = $this->annotationReader->getClassAnnotation(
                    $clazz,
                    self::AA
                );
                if (!empty($argumentAnnotation)) {
                    $arguments = $argumentAnnotation->getArguments();

                    // TODO: Validate if constructor has same value numbers as @Arguments declared

                    break;
                }
                $clazz = $clazz->getParentClass();
            } while (!empty($clazz));
        } catch (\Exception $e){
            // Greg: no constructor, no need to trigger constructor injection, just return
        }

        return $arguments;
    }

    public function parseParent(\ReflectionClass $clazz)
    {
        $parent = '';
        if(empty($clazz)){
            return $parent;
        }

        /* @var $parentAnnotation Father */
        $parentAnnotation = $this->annotationReader->getClassAnnotation(
            $clazz,
            self::FTA
        );

        if(!empty($parentAnnotation)){
            $parent = $parentAnnotation->getParent();
        }

//        if ($this->builder->hasDefinition($parent)) {
//            $parent = '';
//        }
        return $parent;

    }

    /**
     * @param $clazz
     * @return Service
     */
    public function parseClass($clazz)
    {
        $service           = null;
        $reflectionClass   = new \ReflectionClass($clazz);
        $serviceAnnotation = $this->annotationReader->getClassAnnotation(
            $reflectionClass,
            self::SA
        );

        if (!empty($serviceAnnotation)) {
            $this->logger->info(
                'Found service ' . $serviceAnnotation->getName()
            );
            $service = new Service($serviceAnnotation->getName(), $clazz);
            $service->setTop($serviceAnnotation->isTop());
            $service->setAbstract($serviceAnnotation->isAbstract());
            $service->setLazy($serviceAnnotation->isLazy());

            // Greg: process DepOn

            $service->addDeps(
                $this->parseDepOn($reflectionClass)
            );

            // Greg: process arguments
            $service->setArguments($this->parseArguments($reflectionClass));

            // Greg: process Tags
            $service->addTags(
                $this->parseTags($reflectionClass)
            );

            // Greg process Scope
            $service->setScope(
                $this->parseScope($reflectionClass)
            );

            // Greg process factory
            $service->setFactory(
                $this->parseFactory($reflectionClass)
            );

            // TODO process parent
            $service->setParent(
                $this->parseParent($reflectionClass)
            );
            // Get File path of service
            $path  = $reflectionClass->getFileName();
            $dir   = dirname($path);
            $match = array();

            // This is a bundle service so put it under @Bundle/Common/Service
            if (!$service->isTop() && preg_match(
                    '/(^.+\/.+Bundle)\//',
                    $dir,
                    $match
                )
            ) {
                $cfgPath = $match[1] . DIRECTORY_SEPARATOR . 'Resources/config/am_services.yml';
                $service->setCfgPath($cfgPath);
            } else {
                if (!$service->isTop() && preg_match(
                        '/(^.+\/.+Bundle)\/Controller/',
                        $dir,
                        $match
                    )
                ) // Controller? @Bundle/Controller, we may need to parse route from annotation
                {
                    // TODO Greg: I think it is better to have a separate file for controller
                    $cfgPath = $match[1] . DIRECTORY_SEPARATOR . 'Resources/config/am_services.yml';
                    $service->setCfgPath($cfgPath);

                    // TODO Greg: Get routes
                } else {
                    // A global service, should be under Common/Service
                    $service->setCfgPath(
                        $this->projectDir . '/app/config/am_services.yml'
                    );
                }
            }
        }

        return $service;
    }

    /**
     * @throws \Exception
     */
    public function parseServices()
    {
        $classNames = $this->loadClass();
        foreach ($classNames as $clazz) {
            $service = $this->parseClass($clazz);
            if (!empty($service)) {
                $this->generateServices[] = $service;
            }
        }
        $this->logger->info('Start to validate service');
        foreach ($this->generateServices as $service) {
            $this->validateService($service);
        }

    }

    /**
     * @param ReflectionClass $clazz
     * @return string
     */
    public function parseScope(\ReflectionClass $clazz)
    {
        if (empty($clazz)) {
            return '';
        }
        $scopeAnnotation = $this->annotationReader->getClassAnnotation(
            $clazz,
            self::SCA
        );
        $scope = '';
        if (!empty($scopeAnnotation)) {
            $scope = $scopeAnnotation->getScope();
        }
        return $scope;
    }
    /**
     * @param string $projectDir
     */
    public function setProjectDir($projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function getGenServices()
    {
        return $this->generateServices;
    }

    public function writeServiceConfiguration()
    {
        $configs = array();
        foreach ($this->generateServices as $service) {
            $filePath = $service->getCfgPath();
            if (!isset($configs[$filePath])) {
                $configs[$filePath] = array('services' => array());
            }
            $services                      = &$configs[$filePath]['services'];
            $services[$service->getName()] = array(
                'class' => $service->getClazz(),
                'calls' => array()
            );

            $serviceConf                   = &$services[$service->getName()];
            foreach ($service->getDepends() as $name => $depOn) {
                $serviceConf['calls'][] = array(
                    $depOn['setter'],
                    array($depOn['depend']),
                );
            }

            foreach ($service->getTags() as $tag) {
                $serviceConf['tags'][] = $tag;
            }

            foreach ($service->getArguments() as $argument) {
                $serviceConf['arguments'][] = $argument;
            }
            $scope = $service->getScope();
            if (!empty($scope)) {
                $serviceConf['scope'] = $scope;
            }

            $parent = $service->getParent();
            if (!empty($parent)) {
                $serviceConf['parent'] = $parent;
            }

            list($factoryClass, $factoryMethod) = $service->getFactory();
            if (!empty($factoryClass) && !empty($factoryMethod)) {
                $serviceConf['factory_class'] = $factoryClass;
                $serviceConf['factory_method'] = $factoryMethod;
            }

            $serviceConf['abstract'] = $service->isAbstract();
            $lazy = $service->isLazy();
            if(isset($this->lazy)){
                $lazy = $this->lazy;
            } elseif (isset($lazy)){
                $lazy = false;
            }
            $serviceConf['lazy'] = $lazy;

            if (empty($serviceConf['arguments'])) {
                unset($serviceConf['arguments']);
            }

            if (empty($serviceConf['tags'])) {
                unset($serviceConf['tags']);
            }

            if (empty($serviceConf['calls'])) {
                unset($serviceConf['calls']);
            }
            if (!$serviceConf['abstract']) {
                unset($serviceConf['abstract']);
            }
        }
        foreach ($configs as $path => $config) {
            file_put_contents($path, Yaml::dump($config, self::$YAML_LEVEL));
        }
    }

    /**
     * Validate service
     * 1. check if depended service is already in repo
     * 2. check if depended service's setter is defined;
     *
     * @param Service $service
     * @throws \Exception
     */
    private function validateService(Service $service)
    {
        // Check depended service
        $dependencies = &$service->getDepends();
        foreach ($dependencies as $serviceName => &$val) {
            $this->logger->log('debug', "Validating service : $serviceName");
            if ($val['type'] == Service::SERVICE) {
                foreach ($this->generateServices as $s) {
                    if ($s->getName() == $val['service'] || $s->getAlias(
                        ) == $val['service']
                    ) {
                        $depOn = $s;
                        break;
                    }
                }

                if (empty($depOn)) {
                    foreach ($this->definedServices as $s) {
                        if ($s->getName() == $val['service'] || $s->getAlias(
                            ) == $val['service']
                        ) {
                            $depOn = $s;
                            break;
                        }
                    }
                }


                if (empty($depOn)) {
                    throw new \InvalidArgumentException(
                        "$serviceName is not defined"
                    );
                }
            }


            // Check setter
            $clazz = new \ReflectionClass($service->getClazz());
            foreach ($clazz->getMethods() as $method) {
                if (strtolower($method->getName()) == strtolower(
                        $val['setter']
                    )
                ) {
                    $found         = true;
                    $val['setter'] = $method->getName();
                    break;
                }
            }
            if (empty($found)) {
                // if setter is not found, check if DIServiceTrait is defined and if there is a property with this name
                foreach ($clazz->getProperties() as $p) {
                    if (strtolower($p->getName()) == strtolower(
                            $val['property']
                        )
                    ) {
                        $propertyClazz   = $p;
                        $val['property'] = $p->getName();
                        $val['setter']   = 'set' . preg_replace_callback(
                                '/^(\\w)/',
                                function ($a) {
                                    return strtoupper($a[0]);
                                },
                                $p->getName()
                            );
                        break;
                    }
                }

                if (!empty($propertyClazz)) {
                    foreach ($clazz->getTraits() as $trait) {
                        if ($trait->getName() == 'DIServiceTrait') {
                            $found = true;
                            break;
                        }
                    }
                    if (empty($found)) {
                        continue;
                    }
                }

                throw new \Exception(
                    'A proper setter should be defined in traits namely set[ServiceName](Big Camel) as ' . $val['setter']
                );
            }
        }
    }

    /**
     * @param string|null $serviceName
     * @return Definition|null
     */
    public function getServiceInfo($serviceName)
    {
        if (empty($this->builder)) {
            // TODO Greg: just leave below here, I may move them to separate method latter.
            $clazz  = new \ReflectionClass($this->kernel);
            $getter = $clazz->getMethod('buildContainer');
            $getter->setAccessible(true);
            $this->builder = $getter->invoke($this->kernel);
            $this->builder->compile();
        }

        if ($this->builder->hasDefinition($serviceName)) {
            // Found service
            $definition = $this->builder->getDefinition($serviceName);
        } elseif ($this->builder->hasAlias($serviceName)) {
            // Found service
            $alias = $this->builder->getAlias($serviceName);

            $definition = $this->builder->getDefinition((string)$alias);
        } else {
            echo "No such service" . PHP_EOL;
        }

        return empty($definition) ? null : $definition;
    }

    /**
     * @return Service[]
     */
    public function getDefinedServices()
    {
        return $this->definedServices;
    }

    /**
     * @param ReflectionClass $clazz
     * @return array
     */
    private function parseTags(ReflectionClass $clazz)
    {
        if (empty($clazz)) {
            return array();
        }
        $tags = array();
        $ta   = $this->annotationReader->getClassAnnotation($clazz, self::TA);
        if (!empty($ta)) {
            $tags = $ta->getTags();
        }

        return $tags;
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
