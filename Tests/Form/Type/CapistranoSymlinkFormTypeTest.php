<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoFileFormType;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoSymlinkFormType;

class CapistranoSymlinkFormTypeTest extends AbstractFormTypeTest
{

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $childs = [
            'name',
            'sourceLocation',
            'destinationLocation',
        ];

        $index = 0;

        foreach ($childs as $child) {
            $formBuilder
                ->expects($this->at($index))
                ->method('add')
                ->with($child);

            $index++;
        }

        $formType = new CapistranoSymlinkFormType();
        $formType->buildForm($formBuilder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->getOptionsResolverMock();
        $resolver
            ->expects($this->at(0))
            ->method('setDefaults')
            ->with(['data_class' => CapistranoSymlink::class]);

        $formType = new CapistranoSymlinkFormType();
        $formType->configureOptions($resolver);
    }
}
