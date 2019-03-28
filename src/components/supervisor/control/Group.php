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
    /** @var string */
    private $groupName;

    /**
     * Group constructor.
     *
     * @param string $groupName
     * @param ConnectionInterface $connection
     * @param array $config
     */
    public function __construct(string $groupName, ConnectionInterface $connection, $config = [])
    {
        $this->groupName = $groupName;

        parent::__construct($connection, $config);
    }

    /**
     * @return mixed
     */
    public function startProcessGroup()
    {
        return $this->connection->callMethod('supervisor.startProcessGroup', [$this->groupName]);
    }

    /**
     * @return mixed
     */
    public function stopProcessGroup()
    {
        return $this->connection->callMethod('supervisor.stopProcessGroup', [$this->groupName]);
    }
}
