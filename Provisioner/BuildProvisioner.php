<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\CoreBundle\Exception\LoggedException;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoCrontabLine;
use phpseclib\Net\SSH2;

/**
 * Class BuildProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
class BuildProvisioner extends AbstractProvisioner
{

    public function doRun()
    {
        $applicationEnvironment = $this->task->getApplicationEnvironment();
        $environment = $applicationEnvironment->getEnvironment();

        /** @var VirtualServer[] $servers */
        $servers = $this->entityManager->getRepository(VirtualServer::class)->findAll();

        foreach ($servers as $server) {
            if ($server->getEnvironment() != $environment) {
                continue;
            }

            if (!$this->dataValueService->getValue($server, 'manage_capistrano')) {
                continue;
            }

            $this->taskLoggerService->addLogHeader(
                $this->task,
                sprintf('Capistrano server "%s"', $server->getName())
            );

            try {
                $user = $this->dataValueService->getValue($applicationEnvironment, 'sock_ssh_user');
                $ssh = $this->getSshCommand($server, $user);

                $this->createFolders($ssh, $applicationEnvironment);
                $this->createFiles($ssh, $applicationEnvironment);
                $this->createSymlinks($ssh, $applicationEnvironment);
                $this->createCrontab($ssh, $applicationEnvironment);

                $this->taskLoggerService->addSuccessLogMessage($this->task, 'Server provisioned.');
            } catch (\Exception $ex) {
                if (empty($ssh)) {
                    $this->taskLoggerService->addErrorLogMessage($this->task, $ex->getMessage());
                }

                $this->taskLoggerService->addFailedLogMessage($this->task, 'Provisioning failed.');
                throw new LoggedException('', 0, $ex);
            }
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    protected function createFolders(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Creating directories', 1);

        if (!$capistranoFolders = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_folder')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No directories specified.', 2);
            return;
        }

        $templateEntities = [
            'application_environment' => $applicationEnvironment,
            'application' => $applicationEnvironment->getApplication(),
            'environment' => $applicationEnvironment->getEnvironment(),
        ];

        try {
            foreach ($capistranoFolders as $capistranoFolder) {
                $path = $this->templateService->replaceKeys($capistranoFolder->getLocation(), $templateEntities);

                $this->taskLoggerService->addLogHeader(
                    $this->task,
                    sprintf('Creating "%s".', $path),
                    2
                );

                $this->executeSshCommand($ssh, 'mkdir -p -m 750 ' . escapeshellarg($path), 3);
            }

            $this->taskLoggerService->addSuccessLogMessage($this->task, 'Directories created.', 2);
        } catch (\Exception $ex) {
            $this->taskLoggerService
                ->addErrorLogMessage($this->task, $ex->getMessage(), 2)
                ->addFailedLogMessage($this->task, 'Creating directories failed.', 2);

            throw $ex;
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    protected function createSymlinks(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Creating symlinks', 1);

        if (!$capistranoSymlinks = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_symlink')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No symlinks specified.', 2);
            return;
        }

        $templateEntities = [
            'application_environment' => $applicationEnvironment,
            'application' => $applicationEnvironment->getApplication(),
            'environment' => $applicationEnvironment->getEnvironment(),
        ];

        try {
            foreach ($capistranoSymlinks as $capistranoSymlink) {
                $source = $this->templateService->replaceKeys(
                    $capistranoSymlink->getSourceLocation(),
                    $templateEntities
                );

                $destination = $this->templateService->replaceKeys(
                    $capistranoSymlink->getDestinationLocation(),
                    $templateEntities
                );

                $this->taskLoggerService->addLogHeader(
                    $this->task,
                    sprintf('Symlinking "%s" to "%s".', $source, $destination),
                    2
                );

                $command = 'ln -sfn ' . escapeshellarg($destination) . ' ' . escapeshellarg($source);

                $this->executeSshCommand($ssh, $command, 3);
            }

            $this->taskLoggerService->addSuccessLogMessage($this->task, 'Symlinks created.', 2);
        } catch (\Exception $ex) {
            $this->taskLoggerService
                ->addErrorLogMessage($this->task, $ex->getMessage(), 2)
                ->addFailedLogMessage($this->task, 'Creating symlinks failed.', 2);

            throw $ex;
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    protected function createFiles(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Creating files', 1);

        if (!$capistranoFiles = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_file')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No files specified.', 2);
            return;
        }

        $templateEntities = [
            'application_environment' => $applicationEnvironment,
            'application' => $applicationEnvironment->getApplication(),
            'environment' => $applicationEnvironment->getEnvironment(),
        ];

        try {
            foreach ($capistranoFiles as $capistranoFile) {
                $path = $capistranoFile->getLocation();
                $path .= '/' . $capistranoFile->getFilename();
                $path .= '.' . $capistranoFile->getExtension();
                $path = $this->templateService->replaceKeys($path, $templateEntities);

                $this->taskLoggerService->addLogHeader(
                    $this->task,
                    sprintf('Creating "%s"', $path),
                    2
                );

                $tmpPath = escapeshellarg($path . uniqid('.', true) . '.tmp');
                $path = escapeshellarg($path);

                $content = $this->templateService->replaceKeys($capistranoFile->getContent(), $templateEntities);
                $content = str_replace(["\r\n", "\r"], "\n", $content);

                // 8192 bytes is the max length supported by escapeshellarg.
                if (strlen($content) > 8192) {
                    $length = mb_strlen($content, 'UTF-8');
                    $maxI = (int) ceil($length / 2048) - 1;

                    for ($i = 0; $i <= $maxI; $i++) {
                        $part = mb_substr($content, $i * 2048, 2048, 'UTF-8');
                        $part = escapeshellarg($part);

                        $command = 'echo ' . ($i === $maxI ? '' : '-n ') . $part . ($i ? ' >> ' : ' > ') . $tmpPath;

                        if ($i === $maxI) {
                            $command .= " && ([[ ! -f $path ]] || chmod \$(stat --format '%a' $path) $tmpPath)";
                            $command .= ' && mv -f ' . $tmpPath . ' ' . $path;
                        }

                        $this->executeSshCommand($ssh, $command, 3);
                    }

                    continue;
                }

                $content = escapeshellarg($content);
                $command = 'echo ' . $content . ' > ' . $tmpPath;
                $command .= " && ([[ ! -f $path ]] || chmod \$(stat --format '%a' $path) $tmpPath)";
                $command .= ' && mv -f ' . $tmpPath . ' ' . $path;

                $this->executeSshCommand($ssh, $command, 3);
            }

            $this->taskLoggerService->addSuccessLogMessage($this->task, 'Files created.', 2);
        } catch (\Exception $ex) {
            $this->taskLoggerService
                ->addErrorLogMessage($this->task, $ex->getMessage(), 2)
                ->addFailedLogMessage($this->task, 'Creating files failed.', 2);

            throw $ex;
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    protected function createCrontab(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Creating crontab', 1);

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
        $command .= 'sed -e \'s/#\s\+Edit this file[^\r]\+\r\(#\(\s[^\r]*\)\?\r\)*//\' | ';
        $command .= 'tr -s \'\r\' \'\n\'';

        // Get the crontab lines.
        $crontabLines = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_crontab_line');

        try {
            if (!$crontabLines || $ssh->host !== $applicationEnvironment->getWorkerServerIp()) {
                // Remove the crontab lines.
                $command = '(' . $command . ') | crontab -';

                $this->executeSshCommand($ssh, $command);
                $this->taskLoggerService->addSuccessLogMessage($this->task, 'Crontab cleared.', 2);
                return;
            }

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
            $command = '(echo ' . $crontab . ' && ' . $command . ') | crontab -';

            $this->executeSshCommand($ssh, $command);

            $this->taskLoggerService->addSuccessLogMessage($this->task, 'Crontab created.', 2);
        } catch (\Exception $ex) {
            $this->taskLoggerService
                ->addErrorLogMessage($this->task, $ex->getMessage(), 2)
                ->addFailedLogMessage($this->task, 'Creating crontab failed.', 2);

            throw $ex;
        }
    }

    public function getName()
    {
        return 'Capistrano files and folders';
    }
}
