<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType;


use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationType;
use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationTypeEnvironment;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoFile;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Form\Type\CapistranoFileFormType;
use DigipolisGent\SettingBundle\Entity\SettingDataValue;
use DigipolisGent\SettingBundle\FieldType\AbstractFieldType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Class CapistranoFileFieldType
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\FieldType
 */
class CapistranoFileFieldType extends AbstractFieldType
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
        $options['entry_type'] = CapistranoFileFormType::class;
        $options['allow_add'] = true;
        $options['allow_delete'] = true;
        $options['by_reference'] = false;
        $options['prototype'] = true;
        $options['prototype_data'] = new CapistranoFile();

        $ids = json_decode($value, true);

        $originEntity = $this->getOriginEntity();
        $capistranoFileRepository = $this->entityManager->getRepository(CapistranoFile::class);

        $data = [];

        if (!is_null($ids)) {
            foreach ($ids as $id) {
                $data[] = $capistranoFileRepository->find($id);
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

            if(!is_null($ids)){
                foreach ($ids as $id){
                    $data[] = clone $capistranoFileRepository->find($id);
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
        return 'capistrano_file';
    }

    /**
     * @param $value
     * @return string
     */
    public function encodeValue($value): string
    {
        $capistranoFileIds = [];

        foreach ($value as $capistranoFile) {
            $this->entityManager->persist($capistranoFile);
            $capistranoFileIds[] = $capistranoFile->getId();
        }

        return json_encode($capistranoFileIds);
    }
}