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
     * @return mixed
     */
    public function getProcessId()
    {
        return $this->_connection->callMethod('supervisor.getPID');
    }

    /**
     * @return mixed
     */
    public function restart()
    {
        return $this->_connection->callMethod('supervisor.restart');
    }

    /**
     * @return mixed
     */
    public function shutdown()
    {
        return $this->_connection->callMethod('supervisor.shutdown');
    }

    /**
     * @return mixed
     */
    public function getAllProcessInfo()
    {
        return $this->_connection->callMethod('supervisor.getAllProcessInfo');
    }

    /**
     * @return array
     * @throws \Exception
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
     * @return mixed
     */
    public function start()
    {
        return $this->_connection->callMethod('supervisor.startAllProcesses');
    }

    /**
     * @return mixed
     */
    public function stop()
    {
        return $this->_connection->callMethod('supervisor.stopAllProcesses');
    }

    /**
     * @param int $delay
     * @return bool
     */
    public static function forceStart($delay = 1000): bool
    {
        exec('supervisord -n  > /dev/null &');
        usleep($delay);

        return true;
    }
}
