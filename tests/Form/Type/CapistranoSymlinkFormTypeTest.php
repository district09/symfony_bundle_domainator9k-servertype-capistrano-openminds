<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoSymlinkFormType;

class CapistranoSymlinkFormTypeTest extends AbstractFormTypeTest
{

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $children = [
            'name',
            'sourceLocation',
            'destinationLocation',
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

        $formType = new CapistranoSymlinkFormType();
        $formType->buildForm($formBuilder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->getOptionsResolverMock();
        $resolver
            ->expects($this->atLeastOnce())
            ->method('setDefaults')
            ->with(['data_class' => CapistranoSymlink::class]);

        $formType = new CapistranoSymlinkFormType();
        $formType->configureOptions($resolver);
    }
}
