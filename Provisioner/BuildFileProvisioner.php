<?php

namespace DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner;

use DigipolisGent\Domainator9k\CoreBundle\Entity\ApplicationEnvironment;
use phpseclib\Net\SSH2;

/**
 * Class BuildFileProvisioner
 *
 * @package DigipolisGent\Domainator9k\ServerTypes\CapistranoOpenmindsBundle\Provisioner
 */
class BuildFileProvisioner extends AbstractBuildProvisioner
{

    public function getName()
    {
        return 'Capistrano files';
    }

    protected function doCreate(SSH2 $ssh, ApplicationEnvironment $appEnv)
    {
        $this->taskLoggerService->addLogHeader($this->task, 'Creating files', 1);

        if (!$capistranoFiles = $this->dataValueService->getValue($appEnv, 'capistrano_file')) {
            $this->taskLoggerService->addInfoLogMessage($this->task, 'No files specified.', 2);
            return;
        }

        $templateEntities = [
            'application_environment' => $appEnv,
            'application' => $appEnv->getApplication(),
            'environment' => $appEnv->getEnvironment(),
        ];

        try {
            foreach ($capistranoFiles as $capistranoFile) {
                $path = $capistranoFile->getLocation();
                $path .= '/' . $capistranoFile->getFilename();
                $path .= '.' . $capistranoFile->getExtension();
                $path = $this->templateService->replaceKeys($path, $templateEntities);

                $this->taskLoggerService->addLogHeader(
                    $this->task,
                    sprintf('Creating "%s"', $path),
                    2
                );

                $tmpPath = escapeshellarg($path . uniqid('.', true) . '.tmp');
                $path = escapeshellarg($path);

                $content = $this->templateService->replaceKeys($capistranoFile->getContent(), $templateEntities);
                $content = str_replace(["\r\n", "\r"], "\n", $content);

                // 8192 bytes is the max length supported by escapeshellarg.
                if (strlen($content) > 8192) {
                    $length = mb_strlen($content, 'UTF-8');
                    $maxI = (int) ceil($length / 2048) - 1;

                    for ($i = 0; $i <= $maxI; $i++) {
                        $part = mb_substr($content, $i * 2048, 2048, 'UTF-8');
                        $part = escapeshellarg($part);

                        $command = 'echo ' . ($i === $maxI ? '' : '-n ') . $part . ($i ? ' >> ' : ' > ') . $tmpPath;

                        if ($i === $maxI) {
                            $command .= ' && chmod ' . $capistranoFile->getChmod() . ' ' . $tmpPath;
                            $command .= ' && mv -f ' . $tmpPath . ' ' . $path;
                        }

                        $this->executeSshCommand($ssh, $command, 3);
                    }

                    continue;
                }

                $content = escapeshellarg($content);
                $command = 'echo ' . $content . ' > ' . $tmpPath;
                $command .= ' && chmod ' . $capistranoFile->getChmod() . ' ' . $tmpPath;
                $command .= ' && mv -f ' . $tmpPath . ' ' . $path;

                $this->executeSshCommand($ssh, $command, 3);
            }

            $this->taskLoggerService->addSuccessLogMessage($this->task, 'Files created.', 2);
        } catch (\Exception $ex) {
            $this->taskLoggerService
                ->addErrorLogMessage($this->task, $ex->getMessage(), 2)
                ->addFailedLogMessage($this->task, 'Creating files failed.', 2);

            throw $ex;
        }
    }
}
