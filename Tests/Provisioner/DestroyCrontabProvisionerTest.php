<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\AbstractApplication;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Environment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoCrontabLine;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\DestroyCrontabProvisioner;
use Doctrine\Common\Collections\ArrayCollection;

class DestroyCrontabProvisionerTest extends AbstractDestroyProvisionerTest
{

    public function testDoRemove()
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

        $provisioner = new DestroyCrontabProvisioner(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
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

        $this->invokeProvisionerMethod($provisioner, 'doRemove', $ssh, $applicationEnvironment);
    }

    protected function getProvisionerClass()
    {
        return DestroyCrontabProvisioner::class;
    }

}
