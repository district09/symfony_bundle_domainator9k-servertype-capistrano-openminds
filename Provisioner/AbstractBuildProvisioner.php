<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Task;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\CoreBundle\Exception\LoggedException;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Entity\CapistranoCrontabLine;
use phpseclib\Net\SSH2;

/**
 * Class AbstractBuildProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
abstract class AbstractBuildProvisioner extends AbstractProvisioner
{

    public function doRun()
    {
        $appEnv = $this->task->getApplicationEnvironment();
        $environment = $appEnv->getEnvironment();

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
                $user = $this->dataValueService->getValue($appEnv, 'sock_ssh_user');
                $ssh = $this->getSshCommand($server, $user);

                $this->doBuild($ssh, $appEnv);

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
     * @param ApplicationEnvironment $appEnv
     */
    abstract protected function doBuild(SSH2 $ssh, ApplicationEnvironment $appEnv);
}
