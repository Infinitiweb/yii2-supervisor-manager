<?php

namespace supervisormanager\components\supervisor\config;

use infinitiweb\supervisorManager\components\supervisor\exceptions\ProcessConfigException;
use yii\base\Component;

/**
 * Class ProcessConfig
 *
 * @package supervisormanager\components\supervisor\config
 */
class ProcessConfig extends Component
{
    /** @var ConfigFileHandler */
    private $config;
    /** @var string */
    private $command;
    /** @var string */
    private $processName = '%(program_name)s_%(process_num)02d';
    /** @var int */
    private $numprocs = 1;
    /** @var int */
    private $numprocsStart = 0;
    /** @var int */
    private $priority = 999;
    /** @var bool */
    private $autostart = true;
    /** @var int */
    private $startretries = 3;
    /** @var string */
    private $autorestart = 'unexpected';
    /** @var string */
    private $exitcodes = '0,2';
    /** @var string */
    private $stopsignal = 'TERM';
    /** @var int */
    private $stopwaitsecs = 10;
    /** @var int */
    private $startsecs = 1;
    /** @var string */
    private $programName;
    /** @var string */
    private $state = 'update';
    /** @var array */
    private $allowedConfigOptions = [
        'command',
        'process_name',
        'numprocs',
        'numprocs_start',
        'priority',
        'autostart',
        'startsecs',
        'startretries',
        'autorestart',
        'exitcodes',
        'stopsignal',
        'stopwaitsecs',
    ];

    /**
     * ProcessConfig constructor.
     *
     * @param string $programName
     * @param array $config
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function __construct($programName, $config = [])
    {
        $this->programName = $programName;
        $this->config = new ConfigFileHandler($this->programName);

        $this->prepareProcessConfig();

        parent::__construct($config);
    }

    /**
     * @param ConfigFileHandler $configFileHandler
     */
    public function setConfigHandler(ConfigFileHandler $configFileHandler)
    {
        $this->config = $configFileHandler;
    }

    /**
     * @return void
     */
    public function prepareProcessConfig()
    {
        $processConfigData = $this->config->getProcessConfig($this->programName);

        if (!$processConfigData) {
            $this->state = 'create';
        } else {
            $configInArray = preg_split('/\n/', $processConfigData);

            foreach ($configInArray as $configParam) {
                list($optionName, $optionValue) = explode('=', $configParam);

                $optionName = trim($optionName);
                $optionValue = trim($optionValue);

                if ($this->hasProperty($optionName)) {
                    $this->$optionName = $optionValue;
                }
            }
        }
    }

    /**
     * @param bool $backup
     * @return bool
     */
    public function deleteProcess(bool $backup = false): bool
    {
        return $this->config->deleteGroup($backup);
    }

    /**
     * @return bool|int
     */
    public function saveProcessConfig()
    {
        $configInArray = [];

        foreach ($this->allowedConfigOptions as $optionName) {
            $configInArray[] = $optionName . '=' . $this->$optionName;
        }

        $configString = implode("\n", $configInArray);

        if ($this->state == 'create') {
            return $this->config->createConfig(
                $this->programName, $configString
            );
        } else {
            return $this->config->saveConfig($configString);
        }
    }

    /**
     * @param array $processData
     * @return bool
     */
    public function createGroup(array $processData): bool
    {
        foreach ($processData as $optionName => $optionValue) {
            if ($this->hasProperty($optionName)) {
                $this->$optionName = $optionValue;
            }
        }

        return $this->saveProcessConfig() ? true : false;
    }

    /**
     * @return bool
     */
    public function addNewGroupProcess(): bool
    {
        $this->setNumprocs($this->getNumprocs() + 1);

        return $this->saveProcessConfig() ? true : false;
    }

    /**
     * @return bool
     */
    public function deleteGroupProcess(): bool
    {
        $currentProcessNumber = $this->getNumprocs();

        if ($currentProcessNumber === 1) {
            return false;
        }

        $this->setNumprocs($currentProcessNumber - 1);

        return $this->saveProcessConfig() ? true : false;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getProcess_name(): string
    {
        return $this->processName;
    }

    /**
     * @param string $processName
     */
    public function setProcess_name(string $processName)
    {
        $this->processName = $processName;
    }

    /**
     * @return int
     */
    public function getNumprocs(): int
    {
        return $this->numprocs;
    }

    /**
     * @param int $numprocs
     */
    public function setNumprocs(int $numprocs)
    {
        $this->numprocs = $numprocs;
    }

    /**
     * @return int
     */
    public function getNumprocs_start(): int
    {
        return $this->numprocsStart;
    }

    /**
     * @param int $numprocsStart
     */
    public function setNumprocs_start(int $numprocsStart)
    {
        $this->numprocsStart = $numprocsStart;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param $priority
     * @throws ProcessConfigException
     */
    public function setPriority($priority)
    {
        if ((int)$priority > 999) {
            throw new ProcessConfigException('Invalid process priority param.');
        }

        $this->priority = (int)$priority;
    }

    /**
     * @return boolean
     */
    public function getAutostart(): bool
    {
        return $this->autostart;
    }

    /**
     * @param boolean $autoStart
     */
    public function setAutostart($autoStart)
    {
        $this->autostart = (int)$autoStart;
    }

    /**
     * @return int
     */
    public function getStartretries(): int
    {
        return $this->startretries;
    }

    /**
     * @param int $startRetries
     */
    public function setStartretries(int $startRetries)
    {
        $this->startretries = $startRetries;
    }

    /**
     * @return string
     */
    public function getAutorestart(): string
    {
        return $this->autorestart;
    }

    /**
     * @param string $autoRestart
     *
     * @throws ProcessConfigException
     */
    public function setAutorestart(string $autoRestart)
    {
        if (!in_array($autoRestart, ['false', 'unexpected', 'true'])) {
            throw new ProcessConfigException('Invalid process auto restart param.');
        }

        $this->autorestart = $autoRestart;
    }

    /**
     * @return string
     */
    public function getExitcodes(): string
    {
        return $this->exitcodes;
    }

    /**
     * @param string $exitCodes
     */
    public function setExitcodes(string $exitCodes)
    {
        $this->exitcodes = $exitCodes;
    }

    /**
     * @return string
     */
    public function getStopsignal(): string
    {
        return $this->stopsignal;
    }

    /**
     * @param string $stopSignal
     *
     * @throws ProcessConfigException
     */
    public function setStopsignal(string $stopSignal)
    {
        if (!in_array($stopSignal, ['TERM', 'HUP', 'INT', 'QUIT', 'KILL', 'USR1', 'USR2'])) {
            throw new ProcessConfigException('Invalid stop signal value.');
        }

        $this->stopsignal = $stopSignal;
    }

    /**
     * @return int
     */
    public function getStopwaitsecs(): int
    {
        return $this->stopwaitsecs;
    }

    /**
     * @param int $stopWaitSecs
     */
    public function setStopwaitsecs(int $stopWaitSecs)
    {
        $this->stopwaitsecs = $stopWaitSecs;
    }

    /**
     * @return int
     */
    public function getStartsecs(): int
    {
        return $this->startsecs;
    }

    /**
     * @param int $startSecs
     */
    public function setStartsecs(int $startSecs)
    {
        $this->startsecs = $startSecs;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }
}