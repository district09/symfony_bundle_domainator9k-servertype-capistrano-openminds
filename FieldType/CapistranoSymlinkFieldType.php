<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType;


use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoSymlink;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoSymlinkFormType;
use DigipolisGent\SettingBundle\Entity\SettingDataValue;
use DigipolisGent\SettingBundle\FieldType\AbstractFieldType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Class CapistranoSymlinkFieldType
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType
 */
class CapistranoSymlinkFieldType extends AbstractFieldType
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
        $options['entry_type'] = CapistranoSymlinkFormType::class;
        $options['allow_add'] = true;
        $options['allow_delete'] = true;
        $options['by_reference'] = false;
        $options['prototype'] = true;
        $options['prototype_data'] = new CapistranoSymlink();

        $ids = json_decode($value, true);

        $originEntity = $this->getOriginEntity();
        $capistranoSymlinkRepository = $this->entityManager->getRepository(CapistranoSymlink::class);

        $data = [];

        if (!is_null($ids)) {
            foreach ($ids as $id) {
                $data[] = $capistranoSymlinkRepository->find($id);
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
                    $originalCapistranoSymlink = $capistranoSymlinkRepository->find($id);
                    $newCapistranoSymlink = clone $originalCapistranoSymlink;
                    $data[] = $newCapistranoSymlink;
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
        return 'capistrano_symlink';
    }

    /**
     * @param $value
     * @return string
     */
    public function encodeValue($value): string
    {
        $capistranoSymlinkIds = [];

        foreach ($value as $capistranoSymlink) {
            $this->entityManager->persist($capistranoSymlink);
            $capistranoSymlinkIds[] = $capistranoSymlink->getId();
        }

        return json_encode($capistranoSymlinkIds);
    }

    public function decodeValue($value)
    {
        $capistranoSymlinkRepository = $this->entityManager->getRepository(CapistranoSymlink::class);

        $ids = [];

        if ($value == '' || is_null($ids)) {
            return [];
        }

        $capistranoSymlinks = [];
        $ids = json_decode($value, true);

        foreach ($ids as $id) {
            $capistranoSymlinks[] = $capistranoSymlinkRepository->find($id);
        }

        return $capistranoSymlinks;
    }
}