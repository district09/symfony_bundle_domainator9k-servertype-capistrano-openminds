<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\DestroyProvisioner;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Fixtures\FooApplication;
use Doctrine\Common\Collections\ArrayCollection;

class DestroyProvisionerTest extends AbstractProvisionerTest
{

    public function testOnDestroy()
    {
        $application = new FooApplication();

        $prodEnvironment = new Environment();
        $prodEnvironment->setName('prod');

        $uatEnvironment = new Environment();
        $uatEnvironment->setName('uat');

        $applicationEnvironment = new ApplicationEnvironment();
        $applicationEnvironment->setEnvironment($prodEnvironment);
        $applicationEnvironment->setApplication($application);

        $servers = new ArrayCollection();
        $server = new VirtualServer();
        $server->setEnvironment($prodEnvironment);
        $servers->add($server);
        $server = new VirtualServer();
        $server->setEnvironment($uatEnvironment);
        $servers->add($server);

        $task = new Task();
        $task->setType(Task::TYPE_DESTROY);
        $task->setStatus(Task::STATUS_NEW);
        $task->setApplicationEnvironment($applicationEnvironment);

        $dataValueService = $this->getDataValueServiceMock([true,'username']);
        $templateService = $this->getTemplateServiceMock();
        $taskLoggerService = $this->getTaskLoggerServiceMock();
        $entityManager = $this->getEntityManagerMock();

        $serverRepository = $this->getRepositoryMock();

        $serverRepository
            ->expects($this->at(0))
            ->method('findAll')
            ->willReturn($servers);

        $entityManager
            ->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo(VirtualServer::class))
            ->willReturn($serverRepository);

        $arguments = [$dataValueService, $templateService, $taskLoggerService, $entityManager];
        $methods = [
            'getSshCommand' => function () {
                return $this->getSsh2Mock();
            },
            'removeFiles' => function () {
                return null;
            },
            'removeSymlinks' => function () {
                return null;
            },
            'removeFolders' => function () {
                return null;
            },
            'createFiles' => function () {
                return null;
            }
        ];

        $provisioner = $this->getProvisionerMock($arguments, $methods);
        $provisioner->run($task);
    }


    public function testRemoveSymlinks()
    {
        $dataValueService = $this->getDataValueServiceMock([]);
        $templateService = $this->getTemplateServiceMock();
        $taskLoggerService = $this->getTaskLoggerServiceMock();
        $entityManager = $this->getEntityManagerMock();

        $symlinks = new ArrayCollection();
        $symlink = new CapistranoSymlink();
        $symlink->setSourceLocation('/path/to/my/source');
        $symlinks->add($symlink);

        $dataValueService
            ->expects($this->at(0))
            ->method('getValue')
            ->willReturn($symlinks);

        $templateService
            ->expects($this->at(0))
            ->method('replaceKeys')
            ->willReturn('/path/to/my/source');

        $provisioner = new DestroyProvisioner(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $source = escapeshellarg('/path/to/my/source');
        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
            ->method('exec')
            ->with($this->equalTo('rm ' . $source));

        $applicationEnvironment = new ApplicationEnvironment();

        $this->invokeProvisionerMethod($provisioner, 'removeSymlinks', $ssh, $applicationEnvironment);
    }

    public function testRemoveFolders()
    {
        $dataValueService = $this->getDataValueServiceMock([]);
        $templateService = $this->getTemplateServiceMock();
        $taskLoggerService = $this->getTaskLoggerServiceMock();
        $entityManager = $this->getEntityManagerMock();

        $folders = new ArrayCollection();
        $folder = new CapistranoFolder();
        $folder->setLocation('/path/to/my/location');
        $folders->add($folder);

        $dataValueService
            ->expects($this->at(0))
            ->method('getValue')
            ->willReturn($folders);

        $templateService
            ->expects($this->at(0))
            ->method('replaceKeys')
            ->willReturn('/path/to/my/location');

        $provisioner = new DestroyProvisioner(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $path = escapeshellarg('/path/to/my/location');
        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
            ->method('exec')
            ->with($this->equalTo('rm -rf ' . $path));


        $applicationEnvironment = new ApplicationEnvironment();

        $this->invokeProvisionerMethod($provisioner, 'removeFolders', $ssh, $applicationEnvironment);
    }

    private function getProvisionerMock(array $arguments, array $methods)
    {
        $mock = $this
            ->getMockBuilder(DestroyProvisioner::class)
            ->setMethods(array_keys($methods))
            ->setConstructorArgs($arguments)
            ->getMock();

        foreach ($methods as $method => $callback) {
            $mock
                ->method($method)
                ->willReturnCallback($callback);
        }

        return $mock;
    }
}
