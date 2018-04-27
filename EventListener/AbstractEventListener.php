<?php


namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\EventListener;

use DigipolisGent\Domainator9k\CoreBundle\Entity\VirtualServer;
use DigipolisGent\Domainator9k\CoreBundle\Service\TaskLoggerService;
use DigipolisGent\Domainator9k\CoreBundle\Service\TemplateService;
use DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Exception\LoginFailedException;
use DigipolisGent\SettingBundle\Service\DataValueService;
use Doctrine\ORM\EntityManagerInterface;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

abstract class AbstractEventListener
{

    protected $dataValueService;
    protected $templateService;
    protected $taskLoggerService;
    protected $entityManager;

    /**
     * BuildEventListener constructor.
     * @param DataValueService $dataValueService
     * @param TemplateService $templateService
     */
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
    public function getSshCommand(VirtualServer $server, string $user): SSH2
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
     *   False on failure, output on success.
     */
    protected function executeSshCommand(SSH2 $ssh, $command)
    {
        $this->taskLoggerService->addLine(sprintf('Executing %s.', $command));
        $output = '';
        $result = $ssh->exec($command, function ($tmp) use ($output) {
            $output .= $tmp;
        });
        if ($result === false) {
            $this->taskLoggerService->addLine(sprintf('Command %s failed.', $command));
        }
        if ($output) {
            $this->taskLoggerService->addLine('SSH output:');
            $this->taskLoggerService->addLine($output);
        }
        return $result !== false;
    }
}
