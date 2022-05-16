<?php

namespace App\Services\Database;

use Psr\Log\LoggerAwareInterface;
use PDO;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class PdoServiceProvider implements ServiceProviderInterface
{
    
    
    public function register(Container $pimple)
    {
        
        $config = $pimple['settings']['pdo'];
        
        $dsn = "{$config['engine']}:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $username = $config['username'];
        $password = $config['password'];
        
        $pdo = new PDO($dsn, $username, $password, $config['options']);
        
        $pimple['dbh'] = function($c) use ($pdo){
            return $pdo;
        };

    }
}