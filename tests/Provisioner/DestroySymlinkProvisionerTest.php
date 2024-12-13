<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\DestroySymlinkProvisioner;
use Doctrine\Common\Collections\ArrayCollection;

class DestroySymlinkProvisionerTest extends AbstractDestroyProvisionerTest
{

    public function testDoRemove()
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
            ->expects($this->any())
            ->method('getValue')
            ->willReturn($symlinks);

        $templateService
            ->expects($this->atLeastOnce())
            ->method('replaceKeys')
            ->willReturn('/path/to/my/source');

        $provisioner = new DestroySymlinkProvisioner(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $source = escapeshellarg('/path/to/my/source');
        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->atLeastOnce())
            ->method('exec')
            ->with($this->equalTo('rm ' . $source));

        $applicationEnvironment = new ApplicationEnvironment();

        $this->invokeProvisionerMethod($provisioner, 'doDestroy', $ssh, $applicationEnvironment);
    }

    protected function getProvisionerClass()
    {
        return DestroySymlinkProvisioner::class;
    }

}
