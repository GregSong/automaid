<?php
use AutoMaid\AutoMaid;

class APTCest
{
    protected $service;
    /**
     * @var AutoMaid
     */
    protected $automaid;

    public function _before(UnitTester $I)
    {
        $this->service  = new DITestBase();
        $this->automaid = new AutoMaid();
    }

    public function _after(UnitTester $I)
    {
    }

    public function parse_services(UnitTester $I)
    {
        $I->amGoingTo('Parse service annotation of a class');
        $this->automaid->setProjectDir(__DIR__ . '/../_data/project');
        $this->automaid->init();
        $this->automaid->loadFiles(__DIR__ . '/../_code');
        $this->automaid->loadFiles(__DIR__ . '/../_data');
        $this->automaid->parseServices();
        $services = $this->automaid->getGenServices();

        $baseService    = $services[2];
        $testServiceOne = $services[0];
        $testServiceTwo = $services[1];

        $I->assertEquals('base_service', $baseService->getName());
        $I->assertEquals('DITestBase', $baseService->getClazz());
        $I->assertEquals(4, sizeof($baseService->getDepends()));
        $I->assertEquals(
            $testServiceOne->getName(),
            $baseService->getDepends()['testServiceOne']['service']
        );
        $I->assertEquals(
            $testServiceTwo->getName(),
            $baseService->getDepends()['testServiceTwo']['service']
        );

        $test_controller_service = $services[7];
        $I->assertEquals(
            2,
            sizeof($test_controller_service->getDepends()),
            'Test controller has two dependencies which introduced by trait and parent class'
        );
    }

    public function load_php_files(UnitTester $I)
    {
        $I->amGoingTo('Load all php files');
        $this->automaid->setProjectDir(__DIR__ . '/../_data/project');
        $this->automaid->init();
        $num = $this->automaid->loadFiles(__DIR__ . '/../_code');

        $I->assertEquals(6, $num);

    }

    public function _init_configuration_files(UnitTester $I)
    {
        $I->amGoingTo('Add am_service.yml to services configuration files');
        $this->automaid->init();
        $this->automaid->initConfigurationFiles(
            array(
                __DIR__ . '/../_data/project/app/config/config.yml',
                __DIR__ . '/../_data/project/src/Greg/ATC/TestBundle/Resources/config/services.yml',
            )
        );
        $I->openFile(__DIR__ . '/../_data/project/app/config/config.yml');
        $I->canSeeInThisFile(
            '- { resource: ' . AutoMaid::SERVICE_FILE_NAME . ', ignore_errors: true'
        );
        $I->openFile(
            __DIR__ . '/../_data/project/src/Greg/ATC/TestBundle/Resources/config/services.yml'
        );
        $I->canSeeInThisFile(
            '- { resource: ' . AutoMaid::SERVICE_FILE_NAME . ', ignore_errors: true'
        );
    }

    public function write_service_configuration_file(UnitTester $I)
    {
        $I->amGoingTo(
            'check if automaid can get configuration file location of service'
        );

        $this->automaid->setProjectDir(__DIR__ . '/../_data/project');
        $this->automaid->init();
        $this->automaid->loadFiles(__DIR__ . '/../_data/project');
        $this->automaid->parseServices();

        $this->automaid->writeServiceConfiguration();

        $globalConfigPath = __DIR__ . '/../_data/project/app/config/am_services.yml';
        $bundleConfigPath = __DIR__ . '/../_data/project/src/Greg/ATC/TestBundle/Resources/config/am_services.yml';
        $I->seeFileFound($globalConfigPath);

        $I->seeFileFound($bundleConfigPath);

        $I->runShellCommand(
            "diff $globalConfigPath " . __DIR__ . '/../_data/project/app/config/sample.am_services.yml'
        );
        $I->dontSeeInShellOutput('differ');

        $I->runShellCommand(
            "diff $bundleConfigPath " . __DIR__ . '/../_data/project/src/Greg/ATC/TestBundle/Resources/config/sample.am_services.yml'
        );
        $I->dontSeeInShellOutput('differ');

        $I->deleteFile($globalConfigPath);
        $I->deleteFile($bundleConfigPath);
    }
}