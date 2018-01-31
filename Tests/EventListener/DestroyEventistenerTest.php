<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Event\BuildEvent;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\EventListener\BuildEventListener;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Fixtures\FooApplication;
use DigipolisGent\SettingBundle\Service\DataValueService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use phpseclib\Net\SSH2;

class BuildEventistenerTest extends AbstractEventistenerTest
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
        $server = new Server();
        $server->setEnvironment($prodEnvironment);
        $servers->add($server);
        $server = new Server();
        $server->setEnvironment($uatEnvironment);
        $servers->add($server);

        $task = new Task();
        $task->setType(Task::TYPE_BUILD);
        $task->setStatus(Task::STATUS_NEW);
        $task->setApplicationEnvironment($applicationEnvironment);

        $event = new BuildEvent($task);

        $dataValueService = $this->getDataValueServiceMock([]);
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
            ->with($this->equalTo(Server::class))
            ->willReturn($serverRepository);

        $taskLoggerService
            ->expects($this->at(0))
            ->method('addLine');

        $arguments = [$dataValueService, $templateService, $taskLoggerService, $entityManager];
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

        $eventListener = $this->getEventListenerMock($arguments, $methods);
        $eventListener->onBuild($event);
    }

    public function testCreateFolders()
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

        $eventListener = new BuildEventListener(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
            ->method('exec')
            ->with($this->equalTo('mkdir -p /path/to/my/location'));


        $applicationEnvironment = new ApplicationEnvironment();

        $eventListener->createFolders($ssh, $applicationEnvironment);
    }

    public function testCreateSymlinks()
    {
        $dataValueService = $this->getDataValueServiceMock([]);
        $templateService = $this->getTemplateServiceMock();
        $taskLoggerService = $this->getTaskLoggerServiceMock();
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

        $eventListener = new BuildEventListener(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
            ->method('exec')
            ->with($this->equalTo('ln -sfn /path/to/my/destination /path/to/my/source'));


        $applicationEnvironment = new ApplicationEnvironment();

        $eventListener->createSymlinks($ssh, $applicationEnvironment);
    }

    public function testCreateFiles()
    {
        $dataValueService = $this->getDataValueServiceMock([]);
        $templateService = $this->getTemplateServiceMock();
        $taskLoggerService = $this->getTaskLoggerServiceMock();
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

        $eventListener = new BuildEventListener(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
            ->method('exec')
            ->with($this->equalTo("echo 'my-content' > '/path/to/location/file.ext'"));

        $applicationEnvironment = new ApplicationEnvironment();

        $eventListener->createFiles($ssh, $applicationEnvironment);
    }

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

    protected function getEventListenerMock(array $arguments, array $methods)
    {
        $mock = $this
            ->getMockBuilder(BuildEventListener::class)
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
