<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Form\Type;

use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoFolderFormType;

class CapistranoFolderFormTypeTest extends AbstractFormTypeTest
{

    public function testBuildForm()
    {
        $formBuilder = $this->getFormBuilderMock();

        $children = [
            'name',
            'location',
            'chmod',
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

        $formType = new CapistranoFolderFormType();
        $formType->buildForm($formBuilder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->getOptionsResolverMock();
        $resolver
            ->expects($this->atLeastOnce())
            ->method('setDefaults')
            ->with(['data_class' => CapistranoFolder::class]);

        $formType = new CapistranoFolderFormType();
        $formType->configureOptions($resolver);
    }
}
