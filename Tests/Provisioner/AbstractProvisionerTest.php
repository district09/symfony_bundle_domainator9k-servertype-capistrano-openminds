<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\AbstractProvisioner;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\BuildProvisioner;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Fixtures\FooApplication;
use DigipolisGent\SettingBundle\Service\DataValueService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use phpseclib\Net\SSH2;
use PHPUnit\Framework\TestCase;

abstract class AbstractProvisionerTest extends TestCase
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

    /**
     * Call a protected/private method of an event listener.
     *
     * @param AbstractProvisioner &$object
     *   The event listener.
     * @param string $methodName
     *   Name of the method to call.
     * @param array $args,..
     *  Arguments to pass to method.
     *
     * @return mixed
     *   Method return.
     */
    protected function invokeProvisionerMethod(AbstractProvisioner $listener, $methodName)
    {
        $environment = new Environment();
        $environment->setName('test');

        $application = new FooApplication();

        $applicationEnvironment = new ApplicationEnvironment();
        $applicationEnvironment->setEnvironment($environment);
        $applicationEnvironment->setApplication($application);

        $task = new Task();
        $task->setType($listener instanceof BuildProvisioner ? Task::TYPE_BUILD : Task::TYPE_DESTROY);
        $task->setStatus(Task::STATUS_NEW);
        $task->setApplicationEnvironment($applicationEnvironment);

        $reflection = new \ReflectionClass(get_class($listener));

        $property = $reflection->getProperty('task');
        $property->setAccessible(true);
        $property->setValue($listener, $task);

        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        $args = func_get_args();
        $args = array_splice($args, 2);

        return $method->invokeArgs($listener, $args);
    }
}
