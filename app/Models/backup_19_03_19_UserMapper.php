<?php

namespace App\Models;

use PDO;
use Monolog\Logger;
class UserMapper 
{   
   
    protected $logger;
    protected $dbhandler;
    public $userdata;

    public function __construct(Logger $logger, \PDO $db)
    {
        $this->logger = $logger;
        $this->dbhandler = $db;
    }
   
    /**
     * Fetch all authors
     *
     * @return [Author]
     */
    public function fetchAll()
    {
        $sql = "SELECT * FROM users ORDER BY id ASC";
        $stmt = $this->dbhandler->query($sql);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = $row;
        }

        return $results;
    }
    
    /**
     * Load a single author
     *
     * @return Author|false
     */
    public function loadById($id)
    {
        $sql = "SELECT * FROM user WHERE id = :user_id";
        $stmt = $this->dbhandler->prepare($sql);
        $stmt->execute(['user_id' => $id]);
        $data = $stmt->fetch();

        if ($data) {
            return $data;
        }

        return false;
    }
    
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM user WHERE `email_id` = :email";
        $stmt = $this->dbhandler->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        $data = $stmt->fetch(PDO::FETCH_OBJ);
        
        if($data){
            
           return $this->userdata = $data;
           
        }
        return FALSE;
    }
    
   
    
}