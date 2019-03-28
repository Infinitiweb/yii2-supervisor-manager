<?php

namespace infinitiweb\supervisorManager\components\supervisor;

use yii\base\Component;

/**
 * Class Supervisor
 *
 * @package infinitiweb\supervisorManager\components\supervisor
 */
class Supervisor extends Component
{
    /** @var string */
    const EVENT_CONFIG_CHANGED = 'configChangedEvent';

    /** @var ConnectionInterface */
    public $connection;

    /**
     * Supervisor constructor.
     *
     * @param ConnectionInterface $connection
     * @param array $config
     */
    public function __construct(ConnectionInterface $connection, array $config = [])
    {
        $this->connection = $connection;

        parent::__construct($config);
    }

    /**
     * @return mixed
     */
    public function configChangedEvent()
    {
        exec('supervisorctl update', $output, $status);

        return $status;
    }
}
