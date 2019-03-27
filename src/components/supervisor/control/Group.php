<?php

namespace infinitiweb\supervisorManager\components\supervisor\control;

use infinitiweb\supervisorManager\components\supervisor\ConnectionInterface;
use infinitiweb\supervisorManager\components\supervisor\Supervisor;

/**
 * Class Group
 *
 * @package infinitiweb\supervisorManager\components\supervisor\control
 */
class Group extends Supervisor
{
    /**
     * @var string
     */
    private $_groupName;

    /**
     * Group constructor.
     *
     * @param string $groupName
     * @param ConnectionInterface $connection
     * @param array $config
     */
    public function __construct($groupName, ConnectionInterface $connection, $config = [])
    {
        $this->_groupName = $groupName;

        parent::__construct($connection, $config);
    }

    /**
     * @return mixed
     */
    public function startProcessGroup()
    {
        return $this->_connection->callMethod(
            'supervisor.startProcessGroup', [$this->_groupName]
        );
    }

    /**
     * @return mixed
     */
    public function stopProcessGroup()
    {
        return $this->_connection->callMethod(
            'supervisor.stopProcessGroup', [$this->_groupName]
        );
    }
}
