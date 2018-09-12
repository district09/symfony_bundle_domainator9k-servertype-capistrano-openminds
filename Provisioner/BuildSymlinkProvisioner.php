<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use phpseclib\Net\SSH2;

/**
 * Class BuildSymlinkProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
class BuildSymlinkProvisioner extends AbstractBuildProvisioner
{

    public function getName()
    {
        return 'Capistrano symlinks';
    }

    protected function doBuild(SSH2 $ssh, ApplicationEnvironment $appEnv)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Creating symlinks', 1);

        if (!$capistranoSymlinks = $this->dataValueService->getValue($appEnv, 'capistrano_symlink')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No symlinks specified.', 2);
            return;
        }

        $templateEntities = [
            'application_environment' => $appEnv,
            'application' => $appEnv->getApplication(),
            'environment' => $appEnv->getEnvironment(),
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
}
