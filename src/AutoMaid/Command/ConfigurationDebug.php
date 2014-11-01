<?php
/**
 * Created by PhpStorm.
 * User: kuroro2121
 * Date: 14/10/31
 * Time: 22:55
 */

namespace AutoMaid\Command;


use AutoMaid\AutoMaid;
use AutoMaid\DirTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Yaml\Yaml;

class ConfigurationDebug extends Command
{
    use DirTrait;

    // TODO Greg: not sure if I need to make it configurable
    public static $YAML_LEVEL = 4;

    protected function configure()
    {
        $this->setDefinition(
            array(
                new InputOption(
                    'service',
                    's',
                    InputOption::VALUE_REQUIRED,
                    'Service name'
                ),
                new InputOption(
                    'dependency',
                    'd',
                    InputOption::VALUE_NONE,
                    'dependency'
                )
            )
        );
    }

    public function getDescription()
    {
        return "Debug service information";
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectProjectDir();
        $automaid = new AutoMaid();
        $automaid->setProjectDir($this->projectDir);
        $automaid->init();

        $automaid->loadFiles($this->projectSrc);
        $automaid->parseServices();

        $serviceNames = array();
        if ($input->getOption('service')) {
            $serviceNames[] = $input->getOption('service');
        } else {
            foreach ($automaid->getDefinedServices() as $service) {
                $serviceNames[] = $service->getName();
            }
        }


        foreach ($serviceNames as $serviceName) {
            if (null ===
                $definition = $automaid->getServiceInfo($serviceName)
            ) {
                continue;
            }

            // TODO Greg: by default just dump the detail of definition
            echo Yaml::dump(
                array(
                    $serviceName =>
                        array(
                            $this->dumpDefinition($definition)
                        )
                ),
                self::$YAML_LEVEL
            );
        }


    }

    /**
     * Dump information of service defintion
     * TODO I think I can add a few plugin to parse certain information
     * @param $definition
     *
     * @return array
     */
    private function dumpDefinition(Definition $definition)
    {
        $data = array(
            'class'     => (string)$definition->getClass(),
            'scope'     => $definition->getScope(),
            'public'    => $definition->isPublic(),
            'synthetic' => $definition->isSynthetic(),
            'file'      => $definition->getFile(),
        );

        $data['tags'] = array();
        if (count($definition->getTags())) {
            foreach ($definition->getTags() as $tagName => $tagData) {
                foreach ($tagData as $parameters) {
                    $data['tags'][] = array(
                        'name'       => $tagName,
                        'parameters' => $parameters
                    );
                }
            }
        }

        // TODO Greg: parse arguments
        $data['args'] = array();
        if (count($definition->getArguments())) {
            $data['args'] = $this->parseArguments($definition->getArguments());
        }

        $data['calls'] = array();
        if (count($definition->getMethodCalls())) {
            foreach ($definition->getMethodCalls(
            ) as $methodName => $arguments) {
                $data['calls'][] = array(
                    $arguments[0] => $this->parseArguments($arguments[1])
                );
            }

        }

        return $data;
    }

    /**
     * @param array|null $arguments
     *
     * @return array
     */
    private function parseArguments($arguments)
    {
        $args = array();
        foreach ($arguments as $argument) {
            $args[] = $this->parseArgument($argument);
        }

        return $args;
    }

    /**
     * @param $argument
     * @return string
     */
    private function parseArgument($argument)
    {
        switch (true) {
            case $argument instanceof Definition:
                $result = 'Definition: ' . $argument->getClass();
                break;
            case $argument instanceof Reference:
                $result = 'Reference: ' . (string)$argument;
                break;
            case $argument instanceof Expression:
                $result = 'Expression: ' . (string)$argument;
                break;
            case is_array($argument):
                $a      = $this->parseArguments($argument);
                $result = 'array: ' . Yaml::dump($a, 0);
                break;
            default:
                $result = gettype($argument) . ": " . $argument;
                break;
        }

        return $result;
    }
}