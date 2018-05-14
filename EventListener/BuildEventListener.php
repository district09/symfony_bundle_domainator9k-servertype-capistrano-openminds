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

        // Get the application specific string to wrap arround the crontab lines.
        $wrapper = '### DOMAINATOR:';
        $wrapper .= $applicationEnvironment->getApplication()->getNameCanonical() . ':';
        $wrapper .= $applicationEnvironment->getEnvironment()->getName() . ' ###';

        // Build the command to strip the current crontab lines.
        $command = 'crontab -l | ';
        $command .= 'tr -s [:cntrl:] \'\r\' | ';
        $command .= 'sed -e \'s/' . $wrapper . '.*' . $wrapper . '\r*//\' | ';
        $command .= 'sed -e \'s/#\s\+Edit this file[^\r]\+\r\(#\(\s[^\r]*\)\?\r\)*//\' |';
        $command .= 'tr -s \'\r\' \'\n\'';

        // Get the crontab lines.
        $crontabLines = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_crontab_line');

        if (!$crontabLines || $ssh->host !== $applicationEnvironment->getWorkerServerIp()) {
            // Remove the crontab lines.
            $ssh->exec('(' . $command . ') | crontab -');
        }
        else {
            // Build the application crontab lines.
            $crontab = '';
            /** @var CapistranoCrontabLine $crontabLine */
            foreach ($crontabLines as $crontabLine) {
                $crontab .= $this->templateService->replaceKeys((string) $crontabLine, $templateEntities);
                $crontab .= "\n";
            }

            // Wrap and escape them.
            $crontab = $wrapper . "\n" . $crontab . $wrapper;
            $crontab = escapeshellarg($crontab);

            // Apply the changes on the server.
            $ssh->exec('(echo ' . $crontab . ' && ' . $command . ') | crontab -');
        }
    }

}
