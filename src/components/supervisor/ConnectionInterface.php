<?php

namespace infinitiweb\supervisorManager\components\supervisor;

/**
 * Interface ConnectionInterface
 *
 * @package infinitiweb\supervisorManager\components\supervisor
 */
interface ConnectionInterface
{
    /**
     * @return mixed
     */
    public function getConnection();

    /**
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     */
    public function callMethod($method, array $params = []);
}
