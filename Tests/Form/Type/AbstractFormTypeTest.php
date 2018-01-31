<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFormTypeTest extends TestCase
{

    protected function getOptionsResolverMock()
    {
        $mock = $this
            ->getMockBuilder(OptionsResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    protected function getFormBuilderMock()
    {
        $mock = $this
            ->getMockBuilder(FormBuilderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}
