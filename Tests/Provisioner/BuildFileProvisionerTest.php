<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner\BuildFileProvisioner;
use Doctrine\Common\Collections\ArrayCollection;

class BuildFileProvisionerTest extends AbstractBuildProvisionerTest
{

    public function testDoCreate()
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
            ->willReturn('/path/to/my/location');

        $provisioner = new BuildFileProvisioner(
            $dataValueService,
            $templateService,
            $taskLoggerService,
            $entityManager
        );

        $path = escapeshellarg($file->getLocation());
        $ssh = $this->getSsh2Mock();
        $ssh->expects($this->any())
            ->method('exec')
            ->with($this->stringContains($path));


        $applicationEnvironment = new ApplicationEnvironment();

        $this->invokeProvisionerMethod($provisioner, 'doCreate', $ssh, $applicationEnvironment);
    }

    protected function getProvisionerClass()
    {
        return BuildFileProvisioner::class;
    }

}
