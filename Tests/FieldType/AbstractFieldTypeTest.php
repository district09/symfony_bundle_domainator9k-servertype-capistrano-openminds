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

        $index = 0;

        foreach ($repositories as $class => $repository) {
            $mock
                ->expects($this->at($index))
                ->method('getRepository')
                ->with($this->equalTo($class))
                ->willReturn($repository);

            $index++;
        }

        return $mock;
    }

    protected function setEntitytId($entity, $id)
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
        $entities[] = $this->setEntitytId(new $entityClass, 1);
        $entities[] = $this->setEntitytId(new $entityClass, 2);

        $fieldType = new $fieldTypeClass($entityManager);
        $result = $fieldType->encodeValue($entities);
        $this->assertEquals('[1,2]', $result);
    }

    protected function generalTestDecodeValue($fieldTypeClass, $entityClass)
    {
        $repository = $this->getRepositoryMock();
        $repository
            ->expects($this->at(0))
            ->method('find')
            ->with($this->equalTo(1))
            ->willReturn($this->setEntitytId(new $entityClass, 1));

        $repository
            ->expects($this->at(1))
            ->method('find')
            ->with($this->equalTo(2))
            ->willReturn($this->setEntitytId(new $entityClass, 2));

        $entityManager = $this->getEntityManagerMock();
        $entityManager
            ->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo($entityClass))
            ->willReturn($repository);

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
            ->expects($this->at(0))
            ->method('find')
            ->with($this->equalTo(1))
            ->willReturn($this->setEntitytId(new $entityClass, 1));

        $cRepository
            ->expects($this->at(1))
            ->method('find')
            ->with($this->equalTo(2))
            ->willReturn($this->setEntitytId(new $entityClass, 2));

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
        $applicationType->setType('foo');

        $applicationTypeEnvironment = new ApplicationTypeEnvironment();
        $applicationTypeEnvironment->setApplicationType($applicationType);

        $settingDataValue = new SettingDataValue();
        $settingDataValue->setValue('[1,2]');

        $atRepository
            ->expects($this->at(0))
            ->method('findOneBy')
            ->willReturn($applicationType);

        $ateRepository
            ->expects($this->at(0))
            ->method('findOneBy')
            ->willReturn($applicationTypeEnvironment);

        $sdvRepository
            ->expects($this->at(0))
            ->method('findOneByKey')
            ->willReturn($settingDataValue);

        $cRepository
            ->expects($this->at(0))
            ->method('find')
            ->with($this->equalTo(1))
            ->willReturn($this->setEntitytId(new $entityClass, 1));

        $cRepository
            ->expects($this->at(1))
            ->method('find')
            ->with($this->equalTo(2))
            ->willReturn($this->setEntitytId(new $entityClass, 2));

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