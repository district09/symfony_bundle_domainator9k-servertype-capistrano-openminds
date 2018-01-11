<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType;


use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFolder;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoFileFormType;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoFolderFormType;
use DigipolisGent\SettingBundle\Entity\SettingDataValue;
use DigipolisGent\SettingBundle\FieldType\AbstractFieldType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Class CapistranoFolderFieldType
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType
 */
class CapistranoFolderFieldType extends AbstractFieldType
{

    private $entityManager;

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
     * @return array
     */
    public function getOptions($value): array
    {
        $options = [];
        $options['entry_type'] = CapistranoFolderFormType::class;
        $options['allow_add'] = true;
        $options['allow_delete'] = true;
        $options['by_reference'] = false;
        $options['prototype'] = true;
        $options['prototype_data'] = new CapistranoFolder();

        $ids = json_decode($value, true);

        $originEntity = $this->getOriginEntity();
        $capistranoFolderRepository = $this->entityManager->getRepository(CapistranoFolder::class);

        $data = [];

        if (!is_null($ids)) {
            foreach ($ids as $id) {
                $data[] = $capistranoFolderRepository->find($id);
            }
        }

        if ($originEntity instanceof ApplicationEnvironment && is_null($originEntity->getId())) {
            $applicationType = $this->entityManager->getRepository(ApplicationType::class)
                ->findOneBy(['type' => $originEntity->getApplication()->getType()]);

            $criteria = [
                'applicationType' => $applicationType,
                'environment' => $originEntity->getEnvironment(),
            ];

            $applicationTypeEnvironment = $this->entityManager
                ->getRepository(ApplicationTypeEnvironment::class)->findOneBy($criteria);

            $settingDataValue = $this->entityManager->getRepository(SettingDataValue::class)
                ->findOneByKey($applicationTypeEnvironment, self::getName());

            $ids = null;

            if (!is_null($settingDataValue)) {
                $ids = json_decode($settingDataValue->getValue(), true);
            }

            if (!is_null($ids)) {
                foreach ($ids as $id) {
                    $originalCapistranoFolder = $capistranoFolderRepository->find($id);
                    $newCapistranoFolder = clone $originalCapistranoFolder;
                    $data[] = $newCapistranoFolder;
                }
            }
        }

        $options['data'] = $data;

        return $options;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'capistrano_folder';
    }

    /**
     * @param $value
     * @return string
     */
    public function encodeValue($value): string
    {
        $capistranoFolderIds = [];

        foreach ($value as $capistranoFolder) {
            $this->entityManager->persist($capistranoFolder);
            $capistranoFolderIds[] = $capistranoFolder->getId();
        }

        return json_encode($capistranoFolderIds);
    }

    /**
     * @param $value
     * @return array
     */
    public function decodeValue($value)
    {
        $capistranoFolderRepository = $this->entityManager->getRepository(CapistranoFolder::class);

        $ids = [];

        if ($value == '' || is_null($ids)) {
            return [];
        }

        $capistranoFolders = [];
        $ids = json_decode($value, true);

        foreach ($ids as $id) {
            $capistranoFolders[] = $capistranoFolderRepository->find($id);
        }

        return $capistranoFolders;
    }
}