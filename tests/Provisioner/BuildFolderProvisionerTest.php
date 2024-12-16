<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\BuildFolderProvisioner;
use DigipolisGent\Domainator9k\SockBundle\Provisioner\BuildProvisioner;
use Doctrine\Common\Collections\ArrayCollection;

class BuildFolderProvisionerTest extends AbstractBuildProvisionerTest
{

    public function testDoCreate()
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
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($folders);

        $templateService
            ->expects($this->atLeastOnce())
            ->method('replaceKeys')
            ->willReturn('/path/to/my/location');

        $provisioner = new BuildFolderProvisioner(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $path = escapeshellarg('/path/to/my/location');
        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->atLeastOnce())
            ->method('exec')
            ->with($this->equalTo('mkdir -p ' . $path . ' && chmod 750 ' . $path));


        $applicationEnvironment = new ApplicationEnvironment();

        $this->invokeProvisionerMethod($provisioner, 'doBuild', $ssh, $applicationEnvironment, true);
    }

    protected function getProvisionerClass()
    {
        return BuildFolderProvisioner::class;
    }

}
