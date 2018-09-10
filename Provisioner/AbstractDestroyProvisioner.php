<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\CoreBundle\Exception\LoggedException;
use phpseclib\Net\SSH2;

/**
 * Class AbstractDestroyProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
abstract class AbstractDestroyProvisioner extends AbstractProvisioner
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

                $this->doRemove($ssh, $appEnv);

                $this->taskLoggerService->addSuccessLogMessage($this->task, 'Server cleaned.');
            } catch (\Exception $ex) {
                if (empty($ssh)) {
                    $this->taskLoggerService->addErrorLogMessage($this->task, $ex->getMessage());
                }

                $this->taskLoggerService->addFailedLogMessage($this->task, 'Cleanup failed.');
                throw new LoggedException('', 0, $ex);
            }
        }
    }

    /**
     * @param SSH2 $ssh
     * @param ApplicationEnvironment $appEnv
     */
    abstract protected function doRemove(SSH2 $ssh, ApplicationEnvironment $appEnv);
}
