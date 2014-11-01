<?php
use \UnitTester;

class ServiceInjectionCest
{
    /**
     * @var DITestMagicService
     */
    protected $service;

    public function _before(UnitTester $I)
    {
        $this->service = new DITestMagicService();
    }

    public function _after(UnitTester $I)
    {
    }

    // tests
    public function inject_service(UnitTester $I)
    {

        $this->service->setContainer(
            array(
                'name' => 'service_container',
            )
        );
        $I->assertEquals(
            'Array
(
    [Container] => Array
        (
            [name] => service_container
        )

)
',
            $this->service->dump()
        );

        $I->assertEquals(
            'Array
(
    [name] => service_container
)
',
            $this->service->useService('Container')
        );
//        print_r($this->service['Container'], true);
    }

    public function inject_services(UnitTester $I)
    {
        $this->service->setAmServices(
            array(
                '@service_container' => array(
                    'name' => 'service_container',
                )
            )
        );
        $I->assertEquals(
            'Array
(
    [@service_container] => Array
        (
            [name] => service_container
        )

)
',
            $this->service->dump()
        );
        $container = $this->service->get('@service_container');
    }

    public function inject_set_property(UnitTester $I){
        DITestMagicService::setPropertySet(true);
        $this->service->setMagic('Play Magic');

        $I->assertEquals('Play Magic', $this->service->getMagic());
    }
}