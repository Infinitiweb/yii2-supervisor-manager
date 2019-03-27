<?php

namespace infinitiweb\supervisorManager\components\supervisor\control;

use infinitiweb\supervisorManager\components\supervisor\Supervisor;

/**
 * Class MainProcess
 *
 * @package infinitiweb\supervisorManager\components\supervisor\control
 */
class MainProcess extends Supervisor
{
    /**
     * Get version of current supervisor XML RPC API.
     *
     * @return mixed
     */
    public function getAPIVersion()
    {
        return $this->_connection->callMethod('supervisor.getAPIVersion');
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->_connection->callMethod('supervisor.getState');
    }

    /**
     * Get process id of main supervisor process.
     *
     * @return mixed
     */
    public function getProcessId()
    {
        return $this->_connection->callMethod('supervisor.getPID');
    }

    /**
     * Restart all supervisors child processes.
     *
     * @return mixed
     */
    public function restart()
    {
        return $this->_connection->callMethod('supervisor.restart');
    }

    /**
     * Stop main supervisor process.
     *
     * @return mixed
     */
    public function shutdown()
    {
        return $this->_connection->callMethod('supervisor.shutdown');
    }

    /**
     * Get full info about all supervisors child processes.
     *
     * @return mixed
     */
    public function getAllProcessInfo()
    {
        return $this->_connection->callMethod('supervisor.getAllProcessInfo');
    }

    /**
     * Sort all available processes by relative group.
     *
     * @return array
     */
    public function getAllProcessesByGroup(): array
    {
        $processList = $this->getAllProcessInfo();

        $groups = [];

        foreach ($processList as $process) {

            $groupName = $process['group'];

            if (!in_array($groupName, array_keys($groups))) {
                $groups[$groupName] = [];
            }

            if (!isset($process['priority'])) {
                $process['priority'] = Process::getProcessPriority($groupName);
            }

            $groups[$groupName]['processList'][] = $process;
        }

        return $groups;
    }

    /**
     * Start all supervisors child processes.
     *
     * @return mixed
     */
    public function start()
    {
        return $this->_connection->callMethod('supervisor.startAllProcesses');
    }

    /**
     * Stop all supervisor processes
     *
     * @return mixed
     */
    public function stop()
    {
        return $this->_connection->callMethod('supervisor.stopAllProcesses');
    }

    /**
     * Start main supervisor process from console.
     *
     * @param int $delay
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public static function forceStart($delay = 1000): bool
    {
        exec('supervisord -n  > /dev/null &');

        usleep($delay);

        return true;
    }
}
