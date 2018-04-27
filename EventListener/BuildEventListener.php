<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\CoreBundle\Event\BuildEvent;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoCrontabLine;
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
        if ($event->getTask()->getStatus() == Task::STATUS_FAILED) {
            return;
        }

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
                $this->taskLoggerService->endTask();

                return;
            }

            $this->taskLoggerService->addLine('Creating directories');
            $this->createFolders($ssh, $applicationEnvironment);
            $this->taskLoggerService->addLine('Creating files');
            $this->createFiles($ssh, $applicationEnvironment);
            $this->taskLoggerService->addLine('Creating symlinks');
            $this->createSymlinks($ssh, $applicationEnvironment);
            $this->taskLoggerService->addLine('Creating crontab');
            $this->createCrontab($ssh, $applicationEnvironment);

            $log = $ssh->getLog();
            if ($log) {
                $this->taskLoggerService->addLine('SSH log:');
                $this->taskLoggerService->addLine($log);
            }
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
            'environment' => $applicationEnvironment->getEnvironment(),
        ];

        $locations = [];
        $capistranoFolders = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_folder');

        foreach ($capistranoFolders as $capistranoFolder) {
            $locations[] = $this->templateService->replaceKeys($capistranoFolder->getLocation(), $templateEntities);
        }

        foreach ($locations as $location) {
            $command = 'mkdir -p -m 750 ' . $location;
            $this->executeSshCommand($ssh, $command);
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
            'environment' => $applicationEnvironment->getEnvironment(),
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

            $command = 'ln -sfn ' . $destination . ' ' . $source;
            $this->executeSshCommand($ssh, $command);
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
            'environment' => $applicationEnvironment->getEnvironment(),
        ];

        $capistranoFiles = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_file');
        foreach ($capistranoFiles as $capistranoFile) {
            $path = $capistranoFile->getLocation();
            $path .= '/' . $capistranoFile->getFilename();
            $path .= '.' . $capistranoFile->getExtension();

            $path = escapeshellarg(
                $this->templateService->replaceKeys($path, $templateEntities)
            );

            $content = $this->templateService->replaceKeys($capistranoFile->getContent(), $templateEntities);
            $content = str_replace(["\r\n", "\r"], "\n", $content);
            $content = escapeshellarg($content);

            $command = "[[ ! -f $path ]] || MOD=$(stat --format '%a' $path && chmod 600 $path)";
            $command .= ' && echo ' . $content . ' > ' . $path;
            $command .= ' && [[ -z "$MOD" ]] || chmod $MOD ' . $path;

            $this->executeSshCommand($ssh, $command);
        }
    }

    public function createCrontab(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $templateEntities = [
            'application_environment' => $applicationEnvironment,
            'application' => $applicationEnvironment->getApplication(),
            'environment' => $applicationEnvironment->getEnvironment(),
        ];

        $crontabLines = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_crontab_line');
        $crontab = '';

        if ($crontabLines) {
            $crontab .= "### DOMAINATOR START ###\n";

            /** @var CapistranoCrontabLine $crontabLine */
            foreach ($crontabLines as $crontabLine) {
                $crontab .= $this->templateService->replaceKeys((string) $crontabLine, $templateEntities);
                $crontab .= "\n";
            }

            $crontab .= '### DOMAINATOR END ###';
            $crontab = escapeshellarg($crontab);
        }

        $ssh->exec('(echo ' . $crontab . ' && (crontab -l | tr -s [:cntrl:] \'\r\' | sed -e \'s/### DOMAINATOR START ###.*### DOMAINATOR END ###\r*//\' | tr -s \'\r\' \'\n\')) | crontab -');
    }

}
