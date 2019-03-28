<?php

namespace infinitiweb\supervisorManager\components\supervisor\config;

use yii\base\Component;

/**
 * Class ConfigFileHandler
 *
 * @package infinitiweb\supervisorManager\components\supervisor\config
 */
class ConfigFileHandler extends Component
{
    const BACKUP_FILE_NAME = 'config_backup.zip';
    const DS = DIRECTORY_SEPARATOR;

    /** @var string Path to supervisor configuration dir */
    private $configDir;
    /** @var string Current group name working with */
    private $processName;
    /** @var string Path to process configuration file */
    private $configPath;
    /** @var string Source string with supervisor configuration for specified group. */
    private $configSource;

    /**
     * ConfigFileHandler constructor.
     *
     * @param string $processName
     * @param string $configDir
     * @param array $config
     */
    public function __construct($processName = null, $configDir = null, $config = [])
    {
        $this->processName = $processName;

        $this->setConfigDir($configDir);
        $this->checkConfigDir();

        parent::__construct($config);
    }

    /**
     * @param null $backupName
     * @return bool
     */
    public function backupConfig($backupName = null): bool
    {
        $zip = new \ZipArchive();

        $archiveName = $backupName ?: self::BACKUP_FILE_NAME;
        $archivePath = sprintf("%s%s%s", $this->configDir, self::DS, $archiveName);

        if (!$zip->open($archivePath, \ZipArchive::CREATE)) {
            return false;
        }

        $filesList = $this->getConfigFilesPaths();

        foreach ($filesList as $filePath) {
            $zip->addFile(sprintf("%s%s%s", $this->configDir, self::DS, $filePath), $filePath);
        }

        return $zip->close();
    }

    /**
     * @param null $backupName
     * @return bool
     */
    public function restoreFromBackup($backupName = null): bool
    {
        $zip = new \ZipArchive;

        $archiveName = $backupName ?: self::BACKUP_FILE_NAME;
        $archivePath = sprintf("%s%s%s", $this->configDir, self::DS, $archiveName);

        if (!$zip->open($archivePath)) {
            return false;
        }

        $currentConfigFiles = $this->getConfigFilesPaths();

        foreach ($currentConfigFiles as $filePath) {
            if (!strpos($filePath, 'zip')) {
                unlink(sprintf("%s%s%s", $this->configDir, self::DS, $filePath));
            }
        }

        $zip->extractTo($this->configDir);

        return $zip->close();
    }

    /**
     * @param $processData
     * @return bool
     */
    public function saveConfig($processData): bool
    {
        if (!$this->processName) {
            return false;
        }

        $replacementCallback = function ($matches) use ($processData) {
            return sprintf("%s\n%s\n%s", $matches[1], $processData, $matches[3]);
        };

        $pattern = "/(\[.*:{$this->processName}\])([\s-\S]+?)(\Z|\[)/";
        $configString = preg_replace_callback($pattern, $replacementCallback, $this->configSource);

        return $this->saveFileConfig($configString);
    }

    /**
     * @param $groupName
     * @param $processData
     * @return bool|int
     */
    public function createConfig($groupName, $processData)
    {
        $this->backupConfig();

        $processData = "[program:$groupName]\n{$processData}";
        $fileName = "{$groupName}.conf";

        return file_put_contents(sprintf("%s%s%s", $this->configDir, self::DS, $fileName), $processData);
    }

    /**
     * @param bool $backup
     * @return bool
     */
    public function deleteGroup($backup = false): bool
    {
        if ($backup) {
            $this->backupConfig();
        }

        $pattern = "/(\[.*:{$this->processName}\])([\s-\S]+?)(\Z|\[)/";
        $configString = preg_replace_callback($pattern, function ($matches) {
            return '' . $matches[3];
        }, $this->configSource);

        return $this->saveFileConfig($configString);
    }

    /**
     * @param null $processName
     * @return bool|string
     */
    public function getProcessConfig($processName = null)
    {
        if (!$this->processName) {
            $this->processName = $processName;
        }

        if (!$this->processName) {
            return false;
        }

        $filesList = $this->getConfigFilesPaths();

        foreach ($filesList as $fileConfig) {
            $configPath = $this->configDir . '/' . $fileConfig;
            $configData = file_get_contents($configPath);

            if (!strpos($configData, ":$this->processName]")) {
                continue;
            }

            $this->configPath = $configPath;
            $this->configSource = $configData;

            $pattern = "/\[.*:{$this->processName}\]([\s-\S]+?)(\Z|\[)/";
            preg_match($pattern, $configData, $result);

            if (!isset($result[1])) {
                return false;
            }

            return trim($result[1]);
        }

        return false;
    }

    /**
     * @return bool
     */
    private function checkConfigDir(): bool
    {
        if (!is_dir($this->configDir)) {
            return mkdir($this->configDir, 777);
        }

        return true;
    }

    /**
     * @param string $configDir
     */
    private function setConfigDir(string $configDir): void
    {
        if (!$configDir) {
            $this->configDir = \Yii::$app->params['supervisorConfiguration']['configDir'];
        } else {
            $this->configDir = $configDir;
        }
    }

    /**
     * @return array
     */
    private function getConfigFilesPaths(): array
    {
        $filesFilterCallback = function ($item) {
            $configFileIsDir = is_dir(sprintf("%s%s%s", $this->configDir, self::DS, $item));
            $configFileIsZip = strpos($item, '.zip') !== false;

            return !$configFileIsDir && !$configFileIsZip;
        };

        return array_filter(scandir($this->configDir), $filesFilterCallback);
    }

    /**
     * @param $configData
     * @return bool
     */
    private function saveFileConfig($configData): bool
    {
        if (!$this->configPath) {
            return false;
        }

        if ($configData) {
            file_put_contents($this->configPath, $configData);
        } else {
            unlink($this->configPath);
        }

        return true;
    }
}
