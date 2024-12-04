<?php

namespace App\Extensions\Servers\HestiaCP;

use App\Classes\Extensions\Server;
use App\Helpers\ExtensionHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HestiaCP extends Server
{
    public function getMetadata()
    {
        return [
            'display_name' => 'HestiaCP',
            'version' => '0.0.1',
            'author' => 'HestiaCP Team',
            'website' => 'https://hestiacp.com',
        ];
    }

    private function request($data = [])
    {
        $host = rtrim(ExtensionHelper::getConfig('HestiaCP', 'host'), '/');
        $port = rtrim(ExtensionHelper::getConfig('HestiaCP', 'port'), '/');
        $accesskey = ExtensionHelper::getConfig('HestiaCP', 'accesskey'); 
        $secretkey = ExtensionHelper::getConfig('HestiaCP', 'secretkey'); 

        $data['hash'] = $accesskey.':'.$secretkey;

        $response = Http::post($host . ':'. $port . '/api/', $data);
        if ($response->failed()) {
            dd($response->body(), $response->status());
            throw new \Exception('Error while requesting API');
        }
        return $response;
    }

    public function getConfig()
    {
        return [
            [
                'name' => 'host',
                'type' => 'text',
                'friendlyName' => 'Hostname',
                'validation' => 'url:http,https',
                'required' => true,
            ],
            [
                'name' => 'port',
                'type' => 'text',
                'friendlyName' => 'Port',
                'validation' => 'numeric',
                'required' => true,
            ],
            [
                'name' => 'accesskey',
                'type' => 'text',
                'friendlyName' => 'Access Key',
                'required' => true,
            ],
            [
                'name' => 'secretkey',
                'type' => 'text',
                'friendlyName' => 'Secret key',
                'required' => true,
            ],
        ];
    }
    
    public function getProductConfig($options)
    {
        // Get all the packages
        $response = $this->request(['cmd' => 'v-list-user-packages', 'arg1' => 'json']); 
        $packages = $response->json();
        $packageOptions = [];
        foreach ($packages as $package => $options) {
            $packageOptions[] = [
                'value' => $package,
                'name' => $package,
            ];
        }

        return [
            [
                'name' => 'package',
                'type' => 'dropdown',
                'friendlyName' => 'Package',
                'options' => $packageOptions,
                'required' => true,
            ],
        ];
    }

    public function getUserConfig()
    {
        return [
            [
                'name' => 'domain',
                'type' => 'text',
                'validation' => 'domain',
                'friendlyName' => 'Domain',
                'required' => true,
            ]
        ];
    }

    public function createServer($user, $params, $order, $orderProduct, $configurableOptions)
    {
        $username = strtolower(Str::random());
        $password = Str::random();
        // If first one is a number, add a letter
        if (is_numeric($username[0])) {
            $username = 'a' . substr($username, 1);
        }
        $this->request([
            'cmd' => 'v-add-user', 
            'arg1' => $username,
            'arg2' => $password,
            'arg3' => $user->email,
            'arg4' => $params['package'],
            'arg5' => $user -> name,
        ]);

        $this -> request([
            'cmd' => 'v-add-domain', 
            'arg1' => $username,
            'arg2' => $params['config']['domain'],
        ]);

        ExtensionHelper::setOrderProductConfig('username', strtolower($username), $orderProduct->id);
        ExtensionHelper::setOrderProductConfig('password', $password, $orderProduct->id);
        
        return true;
    }

    public function suspendServer($user, $params, $order, $orderProduct, $configurableOptions)
    {
        $this->request([
            'cmd' => 'v-suspend-user', 
            'arg1' => $params['config']['username'],
        ]);

        return true;
    }

    public function unsuspendServer($user, $params, $order, $orderProduct, $configurableOptions)
    {
       
        $this->request([
            'cmd' => 'v-unsuspend-user', 
            'arg1' => $params['config']['username'],
        ]);

        return true;
    }

    public function terminateServer($user, $params, $order, $orderProduct, $configurableOptions)
    {
        
        $this->request([
            'cmd' => 'v-delete-user', 
            'arg1' => $params['config']['username'],
        ]);

        return true;
    }
    

}