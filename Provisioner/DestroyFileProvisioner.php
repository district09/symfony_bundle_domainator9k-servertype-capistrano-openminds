<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use phpseclib\Net\SSH2;

/**
 * Class DestroyFileProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
class DestroyFileProvisioner extends AbstractDestroyProvisioner
{

    public function getName()
    {
        return 'Capistrano files';
    }

    protected function doDestroy(SSH2 $ssh, ApplicationEnvironment $appEnv)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Removing files', 1);

        if (!$capistranoFiles = $this->dataValueService->getValue($appEnv, 'capistrano_file')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No files present.', 2);
            return;
        }

        $templateEntities = [
            'application_environment' => $appEnv,
            'application' => $appEnv->getApplication(),
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
}
