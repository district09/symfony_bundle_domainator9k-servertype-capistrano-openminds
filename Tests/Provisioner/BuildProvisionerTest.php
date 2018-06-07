<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\BuildProvisioner;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Fixtures\FooApplication;
use Doctrine\Common\Collections\ArrayCollection;

class BuildProvisionerTest extends AbstractProvisionerTest
{

    public function testOnBuild()
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
        $task->setType(Task::TYPE_BUILD);
        $task->setStatus(Task::STATUS_NEW);
        $task->setApplicationEnvironment($applicationEnvironment);

        $dataValueService = $this->getDataValueServiceMock([true,'user']);
        $templateService = $this->getTemplateServiceMock();
        $taskService = $this->getTaskServiceMock();
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

        $arguments = [$dataValueService, $templateService, $taskService, $entityManager];
        $methods = [
            'getSshCommand' => function () {
                return $this->getSsh2Mock();
            },
            'createFolders' => function () {
                return null;
            },
            'createSymlinks' => function () {
                return null;
            },
            'createFiles' => function () {
                return null;
            }
        ];

        $provisioner = $this->getProvisionerMock($arguments, $methods);
        $provisioner->run($task);
    }

    public function testCreateFolders()
    {
        $dataValueService = $this->getDataValueServiceMock([]);
        $templateService = $this->getTemplateServiceMock();
        $taskService = $this->getTaskServiceMock();
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

        $provisioner = new BuildProvisioner(
            $dataValueService,
            $templateService,
            $taskService,
            $entityManager
        );

        $path = escapeshellarg('/path/to/my/location');
        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
            ->method('exec')
            ->with($this->equalTo('mkdir -p -m 750 ' . $path));


        $applicationEnvironment = new ApplicationEnvironment();

        $this->invokeProvisionerMethod($provisioner, 'createFolders', $ssh, $applicationEnvironment);
    }

    public function testCreateSymlinks()
    {
        $dataValueService = $this->getDataValueServiceMock([]);
        $templateService = $this->getTemplateServiceMock();
        $taskService = $this->getTaskServiceMock();
        $entityManager = $this->getEntityManagerMock();

        $symlinks = new ArrayCollection();
        $symlink = new CapistranoSymlink();
        $symlink->setDestinationLocation('/path/to/my/destination');
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

        $templateService
            ->expects($this->at(1))
            ->method('replaceKeys')
            ->willReturn('/path/to/my/destination');

        $provisioner = new BuildProvisioner(
            $dataValueService,
            $templateService,
            $taskService,
            $entityManager
        );

        $destination = escapeshellarg('/path/to/my/destination');
        $source = escapeshellarg('/path/to/my/source');

        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
            ->method('exec')
            ->with($this->equalTo('ln -sfn ' . $destination . ' ' . $source));

        $applicationEnvironment = new ApplicationEnvironment();

        $this->invokeProvisionerMethod($provisioner, 'createSymlinks', $ssh, $applicationEnvironment);
    }

    public function testCreateFiles()
    {
        $dataValueService = $this->getDataValueServiceMock([]);
        $templateService = $this->getTemplateServiceMock();
        $taskService = $this->getTaskServiceMock();
        $entityManager = $this->getEntityManagerMock();

        $files = new ArrayCollection();
        $file = new CapistranoFile();
        $file->setLocation('/path/to/location');
        $file->setFilename('file');
        $file->setExtension('ext');
        $file->setContent('my content');
        $files->add($file);

        $dataValueService
            ->expects($this->at(0))
            ->method('getValue')
            ->willReturn($files);

        $templateService
            ->expects($this->at(0))
            ->method('replaceKeys')
            ->willReturn('/path/to/location/file.ext');

        $templateService
            ->expects($this->at(1))
            ->method('replaceKeys')
            ->willReturn('my-content');

        $provisioner = new BuildProvisioner(
            $dataValueService,
            $templateService,
            $taskService,
            $entityManager
        );

        $path = escapeshellarg('/path/to/location/file.ext');
        $content = escapeshellarg('my-content');
        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
            ->method('exec')
            ->with($this->equalTo(
                "[[ ! -f $path ]] || MOD=$(stat --format '%a' $path "
              . "&& chmod 600 $path) "
              . "&& echo $content > $path "
              . "&& [[ -z \"\$MOD\" ]] || chmod \$MOD $path"));

        $applicationEnvironment = new ApplicationEnvironment();

        $this->invokeProvisionerMethod($provisioner, 'createFiles', $ssh, $applicationEnvironment);
    }

    private function getProvisionerMock(array $arguments, array $methods)
    {
        $mock = $this
            ->getMockBuilder(BuildProvisioner::class)
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
