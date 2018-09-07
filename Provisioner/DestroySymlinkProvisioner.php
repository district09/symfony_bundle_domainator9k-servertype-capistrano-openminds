<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use phpseclib\Net\SSH2;

/**
 * Class DestroySymlinkProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
class DestroySymlinkProvisioner extends AbstractDestroyProvisioner
{

    public function getName()
    {
        return 'Capistrano symlinks';
    }

    protected function doRemove(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
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
}
