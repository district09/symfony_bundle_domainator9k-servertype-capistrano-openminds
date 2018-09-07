<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use phpseclib\Net\SSH2;

/**
 * Class BuildCrontabProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
class BuildCrontabProvisioner extends AbstractBuildProvisioner
{

    public function getName()
    {
        return 'Capistrano crontab';
    }

    protected function doCreate(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment) {
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

}
