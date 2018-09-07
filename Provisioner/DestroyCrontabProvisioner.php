<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use phpseclib\Net\SSH2;

/**
 * Class DestroyCrontabProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
class DestroyCrontabProvisioner extends AbstractDestroyProvisioner
{

    public function getName()
    {
        return 'Capistrano crontab';
    }

    protected function doRemove(SSH2 $ssh, ApplicationEnvironment $applicationEnvironment)
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
}
