<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoCrontabLine;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\BuildCrontabProvisioner;
use Doctrine\Common\Collections\ArrayCollection;

class BuildCrontabProvisionerTest extends AbstractBuildProvisionerTest
{

    public function testDoCreate()
    {
        $dataValueService = $this->getDataValueServiceMock([]);
        $templateService = $this->getTemplateServiceMock();
        $taskLoggerService = $this->getTaskLoggerServiceMock();
        $entityManager = $this->getEntityManagerMock();

        $lines = new ArrayCollection();
        $line = new CapistranoCrontabLine();
        $line->setName('crontab');
        $line->setMinute('*');
        $line->setHour('*');
        $line->setDayOfMonth('*');
        $line->setMonthOfYear('*');
        $line->setDayOfWeek('*');
        $line->setCommand('mycommand');
        $lines->add($line);

        $dataValueService
            ->expects($this->at(0))
            ->method('getValue')
            ->willReturn($lines);

        $provisioner = new BuildCrontabProvisioner(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $path = escapeshellarg('/path/to/my/location');
        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->any())
            ->method('exec')
            ->with($this->stringContains('### DOMAINATOR:'));


        $applicationEnvironment = new ApplicationEnvironment();
        $application = $this->getMockBuilder(AbstractApplication::class)->getMock();
        $applicationEnvironment->setApplication($application);
        $env = new Environment();
        $server = new VirtualServer();
        $server->setTaskServer(true);
        $server->setHost('localhost');
        $env->addVirtualServer($server);
        $applicationEnvironment->setEnvironment($env);

        $this->invokeProvisionerMethod($provisioner, 'doBuild', $ssh, $applicationEnvironment);
    }

    protected function getProvisionerClass()
    {
        return BuildCrontabProvisioner::class;
    }

}
