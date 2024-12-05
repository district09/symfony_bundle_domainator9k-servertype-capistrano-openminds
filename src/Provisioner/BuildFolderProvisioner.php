<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use phpseclib3\Net\SSH2;

/**
 * Class BuildFolderProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
class BuildFolderProvisioner extends AbstractBuildProvisioner
{

    public function getName()
    {
        return 'Capistrano folders';
    }

    protected function doBuild(SSH2 $ssh, ApplicationEnvironment $appEnv, $isTaskServer)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Creating directories', 1);

        if (!$capistranoFolders = $this->dataValueService->getValue($appEnv, 'capistrano_folder')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No directories specified.', 2);
            return;
        }

        $templateEntities = [
            'application_environment' => $appEnv,
            'application' => $appEnv->getApplication(),
            'environment' => $appEnv->getEnvironment(),
        ];

        try {
            foreach ($capistranoFolders as $capistranoFolder) {
                $path = $this->templateService->replaceKeys($capistranoFolder->getLocation(), $templateEntities);

                $this->taskLoggerService->addLogHeader(
                    $this->task,
                    sprintf('Creating "%s".', $path),
                    2
                );
                $cmd = 'mkdir -p ' . escapeshellarg($path)
                    . ' && chmod ' . $capistranoFolder->getChmod() . ' ' . escapeshellarg($path);
                $this->executeSshCommand($ssh, $cmd, 3);
            }

            $this->taskLoggerService->addSuccessLogMessage($this->task, 'Directories created.', 2);
        } catch (\Exception $ex) {
            $this->taskLoggerService
                ->addErrorLogMessage($this->task, $ex->getMessage(), 2)
                ->addFailedLogMessage($this->task, 'Creating directories failed.', 2);

            throw $ex;
        }
    }
}
