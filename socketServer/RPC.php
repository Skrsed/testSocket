<?php

namespace app\socketServer;

class RPC
{
    const PROTOCOL = [
        "jsonrpc" => "2.0"
    ];

    /**
     * Method which using for encode jsonrpc requst string  
     *
     * @return json {"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}
     */
    public static function requestJSON($method, $params, $id = null)
    {
        $method = [
            'method' => $method
        ];
        $params = [
            'params' => $params
        ];
        $id = [
            'id' => null
        ];
        $request = array_merge(self::PROTOCOL, $method, $params, $id);
        return json_encode($request);
    } 
    /**
     * Method which using for encode jsonrpc response string  
     *
     * @return json {"jsonrpc": "2.0", "result": 19, "id": 1}
     */
    public static function responseJSON($result)
    {
        $result = [
            'result' => $result
        ];
        $id = [
            'id' => null
        ];

        $response = array_merge(self::PROTOCOL, $result, $id);
        return json_encode($response);
    }
}

  