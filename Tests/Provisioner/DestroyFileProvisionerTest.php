<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\DestroyFileProvisioner;
use Doctrine\Common\Collections\ArrayCollection;

class DestroyFileProvisionerTest extends AbstractDestroyProvisionerTest
{

    public function testDoRemove()
    {
        $dataValueService = $this->getDataValueServiceMock([]);
        $templateService = $this->getTemplateServiceMock();
        $taskLoggerService = $this->getTaskLoggerServiceMock();
        $entityManager = $this->getEntityManagerMock();

        $files = new ArrayCollection();
        $file = new CapistranoFile();
        $file->setLocation('/path/to/my/location');
        $file->setContent('testcontent');
        $file->setExtension('php');
        $file->setFilename('testname');
        $file->setName('testname.php');
        $files->add($file);

        $dataValueService
            ->expects($this->at(0))
            ->method('getValue')
            ->willReturn($files);

        $templateService
            ->expects($this->at(0))
            ->method('replaceKeys')
            ->willReturn($file->getLocation() . '/' . $file->getFilename() . '.' . $file->getExtension());

        $provisioner = new DestroyFileProvisioner(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $path = escapeshellarg($file->getLocation() . '/' . $file->getFilename() . '.' . $file->getExtension());
        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->at(0))
            ->method('exec')
            ->with($this->stringContains('rm -f ' . $path));


        $applicationEnvironment = new ApplicationEnvironment();

        $this->invokeProvisionerMethod($provisioner, 'doDestroy', $ssh, $applicationEnvironment);
    }

    protected function getProvisionerClass()
    {
        return DestroyFileProvisioner::class;
    }

}
