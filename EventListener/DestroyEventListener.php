<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Server;
use DigipolisGent\Domainator9k\CoreBundle\Event\DestroyEvent;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\LoginFailedException;
use DigipolisGent\SettingBundle\Service\DataValueService;
use Doctrine\ORM\EntityManagerInterface;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

/**
 * Class DestroyEventListener
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\EventListener
 */
class DestroyEventListener extends AbstractEventListener
{

    /**
     * @param DestroyEvent $event
     */
    public function onDestroy(DestroyEvent $event)
    {

        $applicationEnvironment = $event->getTask()->getApplicationEnvironment();
        $environment = $applicationEnvironment->getEnvironment();

        $servers = $this->entityManager->getRepository(Server::class)->findAll();

        foreach ($servers as $server) {
            $ssh = $this->getSshCommand($server);

            if ($server->getEnvironment() != $environment) {
                continue;
            }

            $this->taskLoggerService->addLine(
                sprintf(
                    'Switching to server "%s"',
                    $server->getName()
                )
            );

            $this->taskLoggerService->addLine('Removing symlinks');
            $this->removeSymlinks($ssh, $applicationEnvironment);
            $this->taskLoggerService->addLine('Removing directories');
            $this->removeFolders($ssh, $applicationEnvironment);
            $this->taskLoggerService->addLine($ssh->getLog());
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    public function removeSymlinks(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $templateEntities = [
            'application_environment' => $applicationEnvironment,
            'application' => $applicationEnvironment->getApplication(),
        ];

        $capistranoSymlinks = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_symlink');

        foreach ($capistranoSymlinks as $capistranoSymlink) {
            $source = $this->templateService->replaceKeys(
                $capistranoSymlink->getSourceLocation(),
                $templateEntities
            );

            $ssh->exec('rm ' . $source);
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    public function removeFolders(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $templateEntities = [
            'application_environment' => $applicationEnvironment,
            'application' => $applicationEnvironment->getApplication(),
        ];

        $locations = [];
        $capistranoFolders = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_folder');

        foreach ($capistranoFolders as $capistranoFolder) {
            $locations[] = $this->templateService->replaceKeys($capistranoFolder->getLocation(), $templateEntities);
        }

        foreach ($locations as $location) {
            $ssh->exec('rm -rf ' . $location);
        }
    }
}
