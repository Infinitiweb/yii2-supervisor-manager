<?php

namespace infinitiweb\supervisorManager\components\supervisor;

use infinitiweb\supervisorManager\components\supervisor\exceptions\AuthenticationException;
use infinitiweb\supervisorManager\components\supervisor\exceptions\ConnectionException;
use infinitiweb\supervisorManager\components\supervisor\exceptions\SupervisorException;
use yii\base\Component;
use Zend\Http\Client\Adapter\Exception\RuntimeException;
use Zend\XmlRpc\Client as XmlRpcClient;
use Zend\XmlRpc\Client\Exception\FaultException;
use Zend\XmlRpc\Client\Exception\HttpException;

/**
 * Class Connection
 *
 * @package infinitiweb\supervisorManager\components\supervisor
 */
class Connection extends Component implements ConnectionInterface
{
    /** @var string */
    public $url;
    /** @var string */
    public $user;
    /** @var string */
    public $password;

    /** @var XmlRpcClient */
    private $connection;

    /**
     * Connection constructor.
     *
     * @param XmlRpcClient $client
     * @param array $config
     * @throws AuthenticationException
     * @throws ConnectionException
     * @throws SupervisorException
     */
    public function __construct(XmlRpcClient $client, array $config = [])
    {
        parent::__construct($config);

        $this->connection = $client;

        $this->initConnection();
        $this->checkConnection();
    }

    /**
     * @return \Zend\Http\Client|XmlRpcClient
     */
    private function initConnection()
    {
        return $this->connection->getHttpClient()->setAuth($this->user, $this->password);
    }

    /**
     * @return XmlRpcClient
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param string $method
     * @param array $params
     *
     * @return mixed
     * @throws AuthenticationException
     * @throws ConnectionException
     * @throws SupervisorException
     */
    public function callMethod($method, array $params = [])
    {
        try {
            return $this->connection->call($method, $params);
        } catch (RuntimeException $error) {
            throw new ConnectionException('Unable to connect to supervisor XML RPC server.');
        } catch (HttpException $error) {
            throw new AuthenticationException('Authentication failed. Check user name and password.');
        } catch (FaultException $error) {
            throw new SupervisorException($error->getMessage());
        }
    }

    /**
     * @return int
     * @throws AuthenticationException
     * @throws ConnectionException
     * @throws SupervisorException
     */
    public function checkConnection()
    {
        return (int)$this->callMethod('supervisor.getAPIVersion');
    }
}
