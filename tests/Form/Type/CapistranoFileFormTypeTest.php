<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoFileFormType;

class CapistranoFileFormTypeTest extends AbstractFormTypeTest
{

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $children = [
            'name',
            'filename',
            'extension',
            'location',
            'chmod',
            'content',
        ];

        $index = 0;
        $self = $this;

        $formBuilder
              ->expects($this->any())
              ->method('add')
              ->willReturnCallback(function($child) use ($children, &$index, $self, $formBuilder) {
                  $self->assertEquals($children[$index], $child);
                  $index++;

                  return $formBuilder;
              });

        $formType = new CapistranoFileFormType();
        $formType->buildForm($formBuilder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->getOptionsResolverMock();
        $resolver
            ->expects($this->atLeastOnce())
            ->method('setDefaults')
            ->with(['data_class' => CapistranoFile::class]);

        $formType = new CapistranoFileFormType();
        $formType->configureOptions($resolver);
    }
}
