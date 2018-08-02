<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\CoreBundle\Provisioner\AbstractProvisioner as AbstractProvisionerCore;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Exception\LoginFailedException;
use DigipolisGent\SettingBundle\Service\DataValueService;
use Doctrine\ORM\EntityManagerInterface;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

abstract class AbstractProvisioner extends AbstractProvisionerCore
{

    protected $dataValueService;
    protected $templateService;
    protected $taskLoggerService;
    protected $entityManager;
    protected $task;

    public function __construct(
        DataValueService $dataValueService,
        TemplateService $templateService,
        TaskLoggerService $taskLoggerService,
        EntityManagerInterface $entityManager
    ) {
        $this->dataValueService = $dataValueService;
        $this->templateService = $templateService;
        $this->taskLoggerService = $taskLoggerService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Server $server
     * @return SSH2
     * @throws LoginFailedException
     */
    protected function getSshCommand(VirtualServer $server, string $user): SSH2
    {
        $passphrase = $this->dataValueService->getValue($server, 'capistrano_private_key_passphrase');
        $keyLocation = $this->dataValueService->getValue($server, 'capistrano_private_key_location');

        $ssh = new SSH2($server->getHost(), $server->getPort());
        $key = new RSA();

        if (!empty($passphrase)) {
            $key->setPassword($passphrase);
        }

        $key->loadKey(file_get_contents($keyLocation));

        if (!$ssh->login($user, $key)) {
            throw new LoginFailedException('Login failed.');
        }

        return $ssh;
    }

    /**
     * @param SSH2 $ssh
     * @param string $command
     *
     * @return bool|string
     *   The command output.
     */
    protected function executeSshCommand(SSH2 $ssh, $command)
    {
        $this->taskLoggerService
            ->addLogHeader($this->task, 'Executing command', 2)
            ->addInfoLogMessage($this->task, $command, 3);

        $output = '';
        $result = $ssh->exec($command, function ($tmp) use ($output) {
            $output .= $tmp;
        });

        if ($output !== '') {
            $type = $this->taskLoggerService::LOG_TYPE_INFO;

            if ($result === false) {
                $type = $this->taskLoggerService::LOG_TYPE_ERROR;
            }

            $this->taskLoggerService->addLogMessage($this->task, $type, $output, 3, false);
        }

        if ($result === false) {
            $this->taskLoggerService->addFailedLogMessage($this->task, 'Command failed.', 3);

            throw new \Exception('Could not execute command.');
        }

        $this->taskLoggerService->addSuccessLogMessage($this->task, 'Command executed.', 3);

        return $output;
    }
}
