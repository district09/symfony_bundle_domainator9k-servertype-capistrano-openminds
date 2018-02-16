<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\EventListener\BuildEventListener;
use DigipolisGent\SettingBundle\Service\DataValueService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use phpseclib\Net\SSH2;
use PHPUnit\Framework\TestCase;

abstract class AbstractEventistenerTest extends TestCase
{

    protected function getSsh2Mock()
    {
        $mock = $this
            ->getMockBuilder(SSH2::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->method('getLog')
            ->willReturn('');

        return $mock;
    }

    protected function getRepositoryMock(string $repositoryClass = EntityRepository::class)
    {
        $mock = $this
            ->getMockBuilder($repositoryClass)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    protected function getEntityManagerMock()
    {
        $mock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    protected function getTaskLoggerServiceMock()
    {
        $mock = $this
            ->getMockBuilder(TaskLoggerService::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    protected function getTemplateServiceMock()
    {
        $mock = $this
            ->getMockBuilder(TemplateService::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    protected function getDataValueServiceMock($values)
    {
        $mock = $this
            ->getMockBuilder(DataValueService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $index = 0;
        foreach ($values as $value) {
            $mock
                ->expects($this->at($index))
                ->method('getValue')
                ->willReturn($value);

            $index++;
        }

        return $mock;
    }
}
