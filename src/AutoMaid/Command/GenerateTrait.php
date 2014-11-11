<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/11/2
 * Time: 00:45
 */

namespace AutoMaid\Command;


use AutoMaid\AutoMaid;
use AutoMaid\DirTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateTrait
 * 1. Generate trait for service;
 * 2. By default, do not replace;
 * 3. Can force to replace;
 * @package AutoMaid\Command
 */
class GenerateTrait extends Command
{
    use DirTrait;

    protected $force   = false;
    protected $excludes = array();
    protected $services = array();
    protected $paths = array();
    protected $interfaces = array();
    protected $scope = 'am';

    protected function configure()
    {
        $this->setDefinition(
            array(
                new InputOption(
                    'force',
                    'f',
                    InputOption::VALUE_NONE,
                    'Force to replace previous trait'
                ),
                new InputOption(
                    'service',
                    's',
                    InputOption::VALUE_REQUIRED,
                    'Service name which needs a trait'
                ),
                new InputOption(
                    'exclude',
                    'e',
                    InputOption::VALUE_REQUIRED,
                    'Exclude key word, which can help to bypass a few service (use comma as delimiter)'
                ),
                new InputOption(
                    'path',
                    'p',
                    InputOption::VALUE_REQUIRED,
                    'Source file path which contains source code of service (based on project path)'
                ),
                new InputOption(
                    'interface',
                    'i',
                    InputOption::VALUE_REQUIRED,
                    'Interface used by other service'
                ),
                new InputOption(
                    'scope',
                    'c',
                    InputOption::VALUE_REQUIRED,
                    'Scope of service (sometimes you may have same service under different namespace',
                    'am'
                )
            )
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectProjectDir();
        $this->scope = $input->getOption('scope');
        $this->force   = $input->getOption('force');
        $paths    = $input->getOption('path');

        if (!empty($paths)) {
            $this->paths = preg_split('/[ ]*,[ ]*/', $paths);
        } else {
            $this->paths[] = $this->getProjectSrc();
        }
        $services = $input->getOption('service');
        if (!empty($services)) {
            $this->services = preg_split('/[ ]*,[ ]*/', $services);
        } else {
            // All services
        }
        $excludes = $input->getOption('exclude');
        if (!empty($excludes)) {
            $this->excludes = preg_split('/[ ]*,[ ]*/', $excludes);
        }


        $interfaces = $input->getOption('interface');
        if (!empty($interfaces)) {
            $this->interfaces = preg_split('/[ ]*,[ ]*/', $interfaces);
        }

        $automaid = new AutoMaid();
        $automaid->setProjectDir($this->projectDir);
        $automaid->init();

        // Load files
        foreach ($this->paths as $path) {
            $automaid->loadFiles($this->projectDir . DIRECTORY_SEPARATOR . $path);
        }
        $automaid->parseServices();

        foreach ($this->services as $service) {
            // First of all, get definition of service
            $definition = $automaid->getServiceInfo($service);
            if (empty($definition)) {
                throw new \Exception("No such service $service");
            }
            $clazz = new \ReflectionClass($definition->getClass());

            // namespace
            $origNamespace = $clazz->getNamespaceName();
            $newNamespace = $origNamespace . '\Traits';

            // class name
            $origClazzName = $clazz->getShortName();
            $newClazzName = $origClazzName . 'Trait';

            // file name
            $origFile = $clazz->getFileName();
            $path = dirname($origFile) . '/Traits';
//            $newFile = $path . '/' . $newClazzName . '.php';

            // interface
            /**
             * Greg: This is a little bit tricky, in the array returned by getInterfaces,
             * interfaces implemented by parent class is placed before ones introduced by
             * the class. IMO, if user didn't indicate interface, it is better to use interface
             * closer.
             */
            $interfaces = $clazz->getInterfaces();
            $interfaceThisClass = array();
            $parentClazz = $clazz->getParentClass();
            if (!empty($parentClazz)) {
                $interfaceThisClass = array_diff($interfaces,
                                                 $clazz->getParentClass()
                                                     ->getInterfaces());
            }

            if (!empty($this->interfaces)) {
                // with --interface option
                foreach ($interfaces as $i) {
                    if ($i->getShortName() == $this->interfaces[0]) {
                        // TODO Greg: by now, we only process 1 interface per service.
                        $interface = $i->getShortName();
                        $interfaceFullPath = $i->getName();
                        break;
                    }
                }
                if (empty($interface) && $this->interfaces[0] == $origClazzName ) {
                    $interface = $origClazzName;
                    $interfaceFullPath = $clazz->getName();
                }
            } else {
                // without --interface option
                if(empty($interfaces)){
                    $interface = $origClazzName;
                    $interfaceFullPath = $clazz->getName();
                } else {
                    $interfaces = empty($interfaceThisClass)?$interfaces:$interfaceThisClass;
                    $interface = current($interfaces)->getShortName();
                    $interfaceFullPath = current($interfaces)->getName();

                }
            }
            $newFile = $path . '/' . $interface . 'Trait.php';
            if(empty($interface)){
                throw new \Exception(
                    'Failed to find provided interface from service'
                );
            }

            // service name
            $id = trim(shell_exec('sed -n -e \'s/[ ]*\*[ ]*@ServiceAnnotation[ ]*([ ]*"\(.*\)"[ ]*)[ ]*/\1/p\' ' . $origFile));

            // field name, convert interface name into camelCase
            $field = lcfirst($this->scope) . $interface;

            // setter and getter
            $setter = 'set' . ucfirst($field);
            $getter = 'get' . ucfirst($field);

            // backup procedure
            $genFile = false;
            if (file_exists($newFile) ) {
                // backup file
                if ($this->force) {
                    rename($newFile, $newFile . '.' . posix_getpid() . '.bak' );
                    $genFile = true;
                }
            } else {
                // File doesn't exist
                $genFile = true;
            }

            if ($genFile) {
                $content = str_replace('%%NAMESPACE%%', $newNamespace, self::$template);
                $content = str_replace('%%INTERFACE%%', $interface, $content);
                $content = str_replace('%%FIELD%%', $field, $content);
                $content = str_replace('%%SETTER%%', $setter, $content);
                $content = str_replace('%%GETTER%%', $getter, $content);
                $content = str_replace('%%SERVICE%%', $id, $content);
                $content = str_replace(
                    '%%INTERFACEFULLPATH%%',
                    $interfaceFullPath,
                    $content
                );

                if (!file_exists($path)) {
                    mkdir($path);
                }
                file_put_contents($newFile, $content);
            }
        }

    }


    public function getDescription()
    {
        return "Generate trait for service to make service easier to use";
    }

    static $template = '<?php

namespace %%NAMESPACE%%;

use AutoMaid\Annotation\DepOn;
use %%INTERFACEFULLPATH%%;

/**
 * Trait of %%INTERFACE%%
 * @package %%NAMESPACE%%
 * @DepOn({
 * "%%FIELD%%":"@%%SERVICE%%"
 * })
 */
trait %%INTERFACE%%Trait
{
    /**
     * @var %%INTERFACE%%
     */
    protected $%%FIELD%%;

    /**
     * @return %%INTERFACE%%
     */
    public function %%GETTER%%()
    {
        return $this->%%FIELD%%;
    }

    /**
     * @param %%INTERFACE%% $%%FIELD%%
     */
    public function %%SETTER%%($%%FIELD%%)
    {
        $this->%%FIELD%% = $%%FIELD%%;
    }
}
    ';
} 