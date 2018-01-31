<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Event\BuildEvent;
use DigipolisGent\Domainator9k\CoreBundle\Event\DestroyEvent;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\EventListener\BuildEventListener;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\EventListener\DestroyEventListener;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Fixtures\FooApplication;
use DigipolisGent\SettingBundle\Service\DataValueService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use phpseclib\Net\SSH2;

class DestroyEventistenerTest extends AbstractEventistenerTest
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
        $server = new Server();
        $server->setEnvironment($prodEnvironment);
        $servers->add($server);
        $server = new Server();
        $server->setEnvironment($uatEnvironment);
        $servers->add($server);

        $task = new Task();
        $task->setType(Task::TYPE_DESTROY);
        $task->setStatus(Task::STATUS_NEW);
        $task->setApplicationEnvironment($applicationEnvironment);

        $event = new DestroyEvent($task);

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

        $eventListener = $this->getEventListenerMock($arguments, $methods);
        $eventListener->onDestroy($event);
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

        $eventListener = new DestroyEventListener(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
            ->method('exec')
            ->with($this->equalTo('rm /path/to/my/source'));


        $applicationEnvironment = new ApplicationEnvironment();

        $eventListener->removeSymlinks($ssh, $applicationEnvironment);
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

        $eventListener = new DestroyEventListener(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
            ->method('exec')
            ->with($this->equalTo('rm -rf /path/to/my/location'));


        $applicationEnvironment = new ApplicationEnvironment();

        $eventListener->removeFolders($ssh, $applicationEnvironment);
    }

    private function getEventListenerMock(array $arguments, array $methods)
    {
        $mock = $this
            ->getMockBuilder(DestroyEventListener::class)
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
