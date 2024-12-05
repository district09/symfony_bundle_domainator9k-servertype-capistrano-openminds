<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoFileFormType;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoFolderFormType;

class CapistranoFolderFormTypeTest extends AbstractFormTypeTest
{

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $childs = [
            'name',
            'location',
        ];

        $index = 0;

        foreach ($childs as $child) {
            $formBuilder
                ->expects($this->at($index))
                ->method('add')
                ->with($child);

            $index++;
        }

        $formType = new CapistranoFolderFormType();
        $formType->buildForm($formBuilder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->getOptionsResolverMock();
        $resolver
            ->expects($this->at(0))
            ->method('setDefaults')
            ->with(['data_class' => CapistranoFolder::class]);

        $formType = new CapistranoFolderFormType();
        $formType->configureOptions($resolver);
    }
}
