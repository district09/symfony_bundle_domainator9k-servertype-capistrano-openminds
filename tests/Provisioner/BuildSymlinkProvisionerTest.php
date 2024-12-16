<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\BuildSymlinkProvisioner;
use Doctrine\Common\Collections\ArrayCollection;

class BuildSymlinkProvisionerTest extends AbstractBuildProvisionerTest
{

    public function testDoCreate()
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
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($symlinks);

        $templateService
            ->expects($this->any())
            ->method('replaceKeys')
            ->willReturnOnConsecutiveCalls('/path/to/my/source', '/path/to/my/destination');


        $provisioner = new BuildSymlinkProvisioner(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $destination = escapeshellarg('/path/to/my/destination');
        $source = escapeshellarg('/path/to/my/source');

        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->atLeastOnce())
            ->method('exec')
            ->with($this->equalTo('ln -sfn ' . $destination . ' ' . $source));

        $applicationEnvironment = new ApplicationEnvironment();

        $this->invokeProvisionerMethod($provisioner, 'doBuild', $ssh, $applicationEnvironment, true);
    }

    protected function getProvisionerClass()
    {
        return BuildSymlinkProvisioner::class;
    }

}
