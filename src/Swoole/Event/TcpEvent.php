<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/11/14
 * Time: 15:32
 */

namespace One\Swoole\Event;

use One\Facades\Log;
use One\Http\Router;
use One\Http\RouterException;
use One\Protocol\TcpRouterData;

trait TcpEvent
{

    public function onConnect(\swoole_server $server, $fd, $reactor_id)
    {

    }


    public function __receive(\swoole_server $server, $fd, $reactor_id, $data)
    {
        if ($this->protocol) {
            $data = $this->protocol::decode($data);
        }
        $this->onReceive($server, $fd, $reactor_id, $data);
    }

    
    public function onReceive(\swoole_server $server, $fd, $reactor_id, $data)
    {


    }

    public function onBufferFull(\swoole_server $server, $fd)
    {


    }

    public function onBufferEmpty(\swoole_server $server, $fd)
    {


    }

    /**
     * @param \swoole_server $server
     * @param $fd
     * @param $reactor_id
     * @param TcpRouterData $data
     */
    protected function router(\swoole_server $server, $fd, $reactor_id, $data)
    {
        $data->uuid = $data->uuid . '.' . uuid();
        $data->fd = $fd;
        Log::setTraceId($data->uuid);
        try {
            $router = new Router();
            list($data->class, $data->method, $mids, $action, $data->args) = $router->explain('tcp', $data['url'], $data, $this);
            $f = $router->getExecAction($mids, $action, $data, $this);
            $res = $f();
        } catch (RouterException $e) {
            $res = $e->getMessage();
        } catch (\Throwable $e) {
            $res = $e->getMessage();
        }

        if ($res) {
            $server->send($fd, $res);
        }

    }

}