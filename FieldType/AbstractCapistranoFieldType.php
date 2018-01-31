<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\SettingBundle\Entity\SettingDataValue;
use DigipolisGent\SettingBundle\FieldType\AbstractFieldType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

abstract class AbstractCapistranoFieldType extends AbstractFieldType
{

    protected $entityManager;

    /**
     * CapistranoFileFieldType constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return string
     */
    public function getFormType(): string
    {
        return CollectionType::class;
    }


    /**
     * @param $value
     * @return string
     */
    protected function encodeCapistranoValue($value): string
    {
        $ids = [];

        foreach ($value as $entity) {
            $this->entityManager->persist($entity);
            $ids[] = $entity->getId();
        }

        return json_encode($ids);
    }

    /**
     * @param $value
     * @return array
     */
    protected function decodeCapistranoValue($className, $value)
    {
        $repository = $this->entityManager->getRepository($className);

        $entities = [];
        $ids = json_decode($value, true);

        if (is_array($ids)) {
            foreach ($ids as $id) {
                $entities[] = $repository->find($id);
            }
        }

        return $entities;
    }

    protected function getCapistranoOptions($formTypeClass, $entityClass, $key, $value)
    {
        $cRepository = $this->entityManager->getRepository($entityClass);
        $atRepository = $this->entityManager->getRepository(ApplicationType::class);
        $ateRepository = $this->entityManager->getRepository(ApplicationTypeEnvironment::class);
        $sdvRepository = $this->entityManager->getRepository(SettingDataValue::class);

        $options = [];
        $options['entry_type'] = $formTypeClass;
        $options['allow_add'] = true;
        $options['allow_delete'] = true;
        $options['by_reference'] = false;
        $options['prototype'] = true;
        $options['prototype_data'] = new $entityClass();

        $ids = json_decode($value, true);

        $originEntity = $this->getOriginEntity();

        $data = [];

        if (is_array($ids)) {
            foreach ($ids as $id) {
                $data[] = $cRepository->find($id);
            }
        }

        if ($originEntity instanceof ApplicationEnvironment && is_null($originEntity->getId())) {
            $criteria = [
                'type' => $originEntity->getApplication()->getType()
            ];

            $applicationType = $atRepository->findOneBy($criteria);

            $criteria = [
                'applicationType' => $applicationType,
                'environment' => $originEntity->getEnvironment(),
            ];

            $applicationTypeEnvironment = $ateRepository->findOneBy($criteria);
            $settingDataValue = $sdvRepository->findOneByKey($applicationTypeEnvironment, $key);

            $ids = [];

            if (!is_null($settingDataValue)) {
                $ids = json_decode($settingDataValue->getValue(), true);
            }

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $originalEntity = $cRepository->find($id);
                    $newEntity = clone $originalEntity;
                    if ($newEntity instanceof CapistranoFile) {
                        $newEntity->setOriginalCapistranoFile($originalEntity);
                    }
                    $data[] = $newEntity;
                }
            }
        }

        $options['data'] = $data;

        return $options;
    }
}
