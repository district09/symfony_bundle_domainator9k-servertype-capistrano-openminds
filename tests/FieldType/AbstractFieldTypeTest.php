<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\FieldType;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Tests\Fixtures\FooApplication;
use DigipolisGent\SettingBundle\Entity\Repository\SettingDataValueRepository;
use DigipolisGent\SettingBundle\Entity\SettingDataValue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

abstract class AbstractFieldTypeTest extends TestCase
{

    protected function getEntityManagerMock(array $repositories = array())
    {
        $mock = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($repositories) {
            $mock->expects($this->atLeastOnce())
                ->method('getRepository')
                ->willReturnCallback(function($class) use ($repositories) { return $repositories[$class]; });
        }

        return $mock;
    }

    protected function setEntityId($entity, $id)
    {
        $reflectionObject = new \ReflectionObject($entity);
        $property = $reflectionObject->getProperty('id');
        $property->setAccessible(true);
        $property->setValue(
            $entity,
            $id
        );

        return $entity;
    }

    protected function getRepositoryMock(string $repositoryClass = EntityRepository::class)
    {
        $mock = $this
            ->getMockBuilder($repositoryClass)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    protected function generalTestEncodeValue($fieldTypeClass, $entityClass)
    {
        $entityManager = $this->getEntityManagerMock();

        $entities = [];
        $entities[] = $this->setEntityId(new $entityClass, 1);
        $entities[] = $this->setEntityId(new $entityClass, 2);

        $fieldType = new $fieldTypeClass($entityManager);
        $result = $fieldType->encodeValue($entities);
        $this->assertEquals('[1,2]', $result);
    }

    protected function generalTestDecodeValue($fieldTypeClass, $entityClass)
    {
        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->any())
            ->method('find')
            ->willReturnCallback(fn ($id) => $this->setEntityId(new $entityClass, $id));


        $entityManager = $this->getEntityManagerMock([$entityClass => $repository]);

        $fieldType = new $fieldTypeClass($entityManager);
        $result = $fieldType->decodeValue('[1,2]');
        $this->assertCount(2, $result);

        $fieldType = $fieldType = new $fieldTypeClass($entityManager);
        $result = $fieldType->decodeValue('');
        $this->assertCount(0, $result);
    }

    protected function generalTestGetOptions($fieldTypeClass, $entityClass)
    {
        $cRepository = $this->getRepositoryMock();
        $atRepository = $this->getRepositoryMock();
        $ateRepository = $this->getRepositoryMock();
        $sdvRepository = $this->getRepositoryMock(SettingDataValueRepository::class);

        $cRepository
            ->expects($this->any())
            ->method('find')
            ->willReturnCallback(fn ($id) => $this->setEntityId(new $entityClass, $id));

        $repositories = [
            $entityClass => $cRepository,
            ApplicationType::class => $atRepository,
            ApplicationTypeEnvironment::class => $ateRepository,
            SettingDataValue::class => $sdvRepository,
        ];

        $entityManagerMock = $this->getEntityManagerMock($repositories);

        $value = '[1,2]';
        $fieldType = new $fieldTypeClass($entityManagerMock);
        $options = $fieldType->getOptions($value);

        $keys = [
            'entry_type',
            'allow_add',
            'allow_delete',
            'by_reference',
            'prototype',
            'prototype_data',
            'data',
        ];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $options);
        }
    }

    public function generalTestGetOptionsWithApplicationEnvironment($fieldTypeClass, $entityClass)
    {
        $cRepository = $this->getRepositoryMock();
        $atRepository = $this->getRepositoryMock();
        $ateRepository = $this->getRepositoryMock();
        $sdvRepository = $this->getRepositoryMock(SettingDataValueRepository::class);

        $application = new FooApplication();
        $applicationEnvironment = new ApplicationEnvironment();
        $applicationEnvironment->setApplication($application);

        $applicationType = new ApplicationType();
        $applicationType->setName('foo');

        $applicationTypeEnvironment = new ApplicationTypeEnvironment();
        $applicationTypeEnvironment->setApplicationType($applicationType);

        $settingDataValue = new SettingDataValue();
        $settingDataValue->setValue('[1,2]');

        $atRepository
            ->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->willReturn($applicationType);

        $ateRepository
            ->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->willReturn($applicationTypeEnvironment);

        $sdvRepository
            ->expects($this->atLeastOnce())
            ->method('findOneByKey')
            ->willReturn($settingDataValue);

        $cRepository
            ->expects($this->any())
            ->method('find')
            ->willReturnCallback(fn ($id) => $this->setEntityId(new $entityClass, $id));

        $repositories = [
            $entityClass => $cRepository,
            ApplicationType::class => $atRepository,
            ApplicationTypeEnvironment::class => $ateRepository,
            SettingDataValue::class => $sdvRepository,
        ];

        $entityManagerMock = $this->getEntityManagerMock($repositories);

        $value = '';
        $fieldType = new $fieldTypeClass($entityManagerMock);
        $fieldType->setOriginEntity($applicationEnvironment);
        $options = $fieldType->getOptions($value);

        $keys = [
            'entry_type',
            'allow_add',
            'allow_delete',
            'by_reference',
            'prototype',
            'prototype_data',
            'data',
        ];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $options);
        }
    }
}
