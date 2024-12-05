<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\DestroyFolderProvisioner;
use Doctrine\Common\Collections\ArrayCollection;

class DestroyFolderProvisionerTest extends AbstractDestroyProvisionerTest
{

    public function testDoRemove()
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

        $provisioner = new DestroyFolderProvisioner(
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

        $this->invokeProvisionerMethod($provisioner, 'doDestroy', $ssh, $applicationEnvironment);
    }

    protected function getProvisionerClass()
    {
        return DestroyFolderProvisioner::class;
    }

}
