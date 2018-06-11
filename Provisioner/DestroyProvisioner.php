<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use phpseclib\Net\SSH2;

/**
 * Class DestroyProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
class DestroyProvisioner extends AbstractProvisioner
{

    /**
     * @param Task $task
     */
    public function run(Task $task)
    {
        $this->task = $task;

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

                $this->removeSymlinks($ssh, $applicationEnvironment);
                $this->removeCrontab($ssh, $applicationEnvironment);
                $this->removeFiles($ssh, $applicationEnvironment);
                $this->removeFolders($ssh, $applicationEnvironment);

                $this->taskLoggerService->addSuccessLogMessage($this->task, 'Server cleaned.');
            } catch (\Exception $ex) {
                if (empty($ssh)) {
                    $this->taskLoggerService->addErrorLogMessage($this->task, $ex->getMessage());
                }

                $this->taskLoggerService->addFailedLogMessage($this->task, 'Cleanup failed.');
                $this->task->setFailed();
                return;
            }
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    protected function removeFiles(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Removing files', 1);

        if (!$capistranoFiles = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_file')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No files present.', 2);
            return;
        }

        $templateEntities = [
            'application_environment' => $applicationEnvironment,
            'application' => $applicationEnvironment->getApplication(),
        ];

        try {
            foreach ($capistranoFiles as $capistranoFile) {
                $path = $capistranoFile->getLocation();
                $path .= '/' . $capistranoFile->getFilename();
                $path .= '.' . $capistranoFile->getExtension();
                $path = $this->templateService->replaceKeys($path, $templateEntities);

                $this->taskLoggerService->addInfoLogMessage(
                    $this->task,
                    sprintf('Removing "%s".', $path),
                    2
                );

                $this->executeSshCommand($ssh, 'rm -f ' . escapeshellarg($path));
            }

            $this->taskLoggerService->addSuccessLogMessage($this->task, 'Files removed.', 2);
        } catch (\Exception $ex) {
            $this->taskLoggerService
                ->addErrorLogMessage($this->task, $ex->getMessage(), 2)
                ->addFailedLogMessage($this->task, 'Removing files failed.', 2);

            throw $ex;
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    protected function removeSymlinks(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Removing symlinks', 1);

        if (!$capistranoSymlinks = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_symlink')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No symlinks present.', 2);
            return;
        }

        $templateEntities = [
            'application_environment' => $applicationEnvironment,
            'application' => $applicationEnvironment->getApplication(),
        ];

        try {
            foreach ($capistranoSymlinks as $capistranoSymlink) {
                $source = $this->templateService->replaceKeys(
                    $capistranoSymlink->getSourceLocation(),
                    $templateEntities
                );

                $this->taskLoggerService->addInfoLogMessage(
                    $this->task,
                    sprintf('Removing "%s".', $source),
                    2
                );

                $this->executeSshCommand($ssh, 'rm ' . escapeshellarg($source));
            }

            $this->taskLoggerService->addSuccessLogMessage($this->task, 'Symlinks removed.', 2);
        } catch (\Exception $ex) {
            $this->taskLoggerService
                ->addErrorLogMessage($this->task, $ex->getMessage(), 2)
                ->addFailedLogMessage($this->task, 'Removing symlinks failed.', 2);

            throw $ex;
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    protected function removeFolders(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Removing directories', 1);

        if (!$capistranoFolders = $this->dataValueService->getValue($applicationEnvironment, 'capistrano_folder')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No directories present.', 2);
        }

        $templateEntities = [
            'application_environment' => $applicationEnvironment,
            'application' => $applicationEnvironment->getApplication(),
        ];

        try {
            foreach ($capistranoFolders as $capistranoFolder) {
                $path = $this->templateService->replaceKeys($capistranoFolder->getLocation(), $templateEntities);

                $this->taskLoggerService->addInfoLogMessage(
                    $this->task,
                    sprintf('Removing "%s".', $path),
                    2
                );

                $this->executeSshCommand($ssh, 'rm -rf ' . escapeshellarg($path));
            }

            $this->taskLoggerService->addSuccessLogMessage($this->task, 'Directories removed.', 2);
        } catch (\Exception $ex) {
            $this->taskLoggerService
                ->addErrorLogMessage($this->task, $ex->getMessage(), 2)
                ->addFailedLogMessage($this->task, 'Removing directories failed.', 2);

            throw $ex;
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $applicationEnvironment
     */
    protected function removeCrontab(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Removing crontab', 1);

        if (!$this->dataValueService->getValue($applicationEnvironment, 'capistrano_crontab_line')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No crontab present.', 2);
            return;
        }

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
        $command = '(' . $command . ') | crontab -';

        // Remove the crontab lines.
        try {
            $this->executeSshCommand($ssh, $command);
            $this->taskLoggerService->addSuccessLogMessage($this->task, 'Crontab removed.', 2);
        } catch (\Exception $ex) {
            $this->taskLoggerService
                ->addErrorLogMessage($this->task, $ex->getMessage(), 2)
                ->addFailedLogMessage($this->task, 'Removing crontab failed.', 2);

            throw $ex;
        }
    }

    public function getName()
    {
        return 'Capistrano files and folders';
    }

}
