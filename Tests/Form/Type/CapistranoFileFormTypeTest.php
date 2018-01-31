<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoFileFormType;

class CapistranoFileFormTypeTest extends AbstractFormTypeTest
{

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $childs = [
            'name',
            'filename',
            'extension',
            'location',
            'content',
        ];

        $index = 0;

        foreach ($childs as $child) {
            $formBuilder
                ->expects($this->at($index))
                ->method('add')
                ->with($child);

            $index++;
        }

        $formType = new CapistranoFileFormType();
        $formType->buildForm($formBuilder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->getOptionsResolverMock();
        $resolver
            ->expects($this->at(0))
            ->method('setDefaults')
            ->with(['data_class' => CapistranoFile::class]);

        $formType = new CapistranoFileFormType();
        $formType->configureOptions($resolver);
    }
}
