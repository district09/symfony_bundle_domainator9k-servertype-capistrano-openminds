<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use phpseclib3\Net\SSH2;

/**
 * Class DestroyFolderProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
class DestroyFolderProvisioner extends AbstractDestroyProvisioner
{

    public function getName()
    {
        return 'Capistrano folders';
    }

    protected function doDestroy(SSH2 $ssh, ApplicationEnvironment $appEnv)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Removing directories', 1);

        if (!$capistranoFolders = $this->dataValueService->getValue($appEnv, 'capistrano_folder')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No directories present.', 2);
            return;
        }

        $templateEntities = [
            'application_environment' => $appEnv,
            'application' => $appEnv->getApplication(),
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
}
