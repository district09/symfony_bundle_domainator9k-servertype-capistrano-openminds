<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\DependencyInjection;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\DependencyInjection\DigipolisGentDomainator9kServerTypesCapistranoOpenmindsExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DigipolisGentDomainator9kServerTypesCapistranoOpenmindsExtensionTest extends TestCase
{

    public function testLoad()
    {
        $container = $this->getContainerBuilderMock();
        $container
            ->expects($this->at(0))
            ->method('fileExists');


        $configs = [];

        $extension = new DigipolisGentDomainator9kServerTypesCapistranoOpenmindsExtension();
        $extension->load($configs, $container);
    }

    private function getContainerBuilderMock()
    {
        $mock = $this
            ->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}
