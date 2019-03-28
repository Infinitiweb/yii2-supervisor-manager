<?php

namespace infinitiweb\supervisorManager\components\supervisor\control;

use infinitiweb\supervisorManager\components\supervisor\ConnectionInterface;
use infinitiweb\supervisorManager\components\supervisor\exceptions\ProcessException;
use infinitiweb\supervisorManager\components\supervisor\Supervisor;
use supervisormanager\components\supervisor\config\ProcessConfig;

/**
 * Class Process
 *
 * @package infinitiweb\supervisorManager\components\supervisor\control
 */
class Process extends Supervisor
{
    /** @var string */
    private $processName;

    /**
     * Process constructor.
     *
     * @param $processName
     * @param ConnectionInterface $connection
     */
    public function __construct($processName, ConnectionInterface $connection)
    {
        $this->processName = $processName;

        parent::__construct($connection);
    }

    /**
     * @return mixed
     */
    public function stopProcess()
    {
        return $this->connection->callMethod('supervisor.stopProcess', [$this->processName]);
    }

    /**
     * @return mixed
     */
    public function startProcess()
    {
        return $this->connection->callMethod('supervisor.startProcess', [$this->processName]);
    }

    /**
     * @return mixed
     */
    public function getProcessInfo()
    {
        return $this->connection->callMethod('supervisor.getProcessInfo', [$this->processName]);
    }

    /**
     * @param $outputType
     * @return false|string
     * @throws ProcessException
     */
    public function getProcessOutput($outputType)
    {
        if (!in_array($outputType, ['stderr_logfile', 'stdout_logfile'])) {
            throw new ProcessException('Specified incorrect type of process output.');
        }

        return file_get_contents($this->getProcessInfo()[$outputType]);
    }

    /**
     * @param $processName
     * @return int
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public static function getProcessPriority($processName)
    {
        return (new ProcessConfig($processName))->getPriority();
    }
}
