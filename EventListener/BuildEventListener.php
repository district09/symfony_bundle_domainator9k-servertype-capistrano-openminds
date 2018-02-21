<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\CoreBundle\Event\BuildEvent;
use phpseclib\Net\SSH2;

/**
 * Class BuildEventListener
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\EventListener
 */
class BuildEventListener extends AbstractEventListener
{

    /**
     * @param BuildEvent $event
     */
    public function onBuild(BuildEvent $event)
    {
        $applicationEnvironment = $event->getTask()->getApplicationEnvironment();
        $environment = $applicationEnvironment->getEnvironment();

        $servers = $this->entityManager->getRepository(VirtualServer::class)->findAll();

        foreach ($servers as $server) {

            $manageCapistrano = $this->dataValueService->getValue($server, 'manage_capistrano');

            if (!$manageCapistrano) {
                continue;
            }

            if ($server->getEnvironment() != $environment) {
                continue;
            }

            $this->taskLoggerService->addLine(
                sprintf(
                    'Switching to server "%s"',
                    $server->getName()
                )
            );

            try {
                $user = $this->dataValueService->getValue($applicationEnvironment, 'sock_ssh_user');
                $ssh = $this->getSshCommand($server, $user);
            } catch (\Exception $exception) {
                $this->taskLoggerService->addLine($exception->getMessage());
                continue;
            }

            $this->taskLoggerService->addLine('Creating directories');
            $this->createFolders($ssh, $applicationEnvironment);
            $this->taskLoggerService->addLine('Creating files');
            $this->createFiles($ssh, $applicationEnvironment);
            $this->taskLoggerService->addLine('Creating symlinks');
            $this->createSymlinks($ssh, $applicationEnvironment);
            $this->taskLoggerService->addLine($ssh->getLog());
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    public function createFolders(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
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
            $ssh->exec('mkdir -p ' . $location);
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    public function createSymlinks(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
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

            $destination = $this->templateService->replaceKeys(
                $capistranoSymlink->getDestinationLocation(),
                $templateEntities
            );

            $ssh->exec('ln -sfn ' . $destination . ' ' . $source);
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    public function createFiles(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $templateEntities = [
            'application_environment' => $applicationEnvironment,
            'application' => $applicationEnvironment->getApplication(),
        ];

        $capistranoFiles = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_file');
        foreach ($capistranoFiles as $capistranoFile) {
            $path = $capistranoFile->getLocation();
            $path .= '/' . $capistranoFile->getFilename();
            $path .= '.' . $capistranoFile->getExtension();

            $path = escapeshellarg(
                $this->templateService->replaceKeys($path, $templateEntities)
            );

            $content = escapeshellarg(
                $this->templateService->replaceKeys($capistranoFile->getContent(), $templateEntities)
            );

            $ssh->exec('echo ' . $content . ' > ' . $path);
        }
    }

}