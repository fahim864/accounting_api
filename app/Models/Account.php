<?php

namespace App\Models;

use PDO;
use Monolog\Logger;
use DateTime;

class Account
{
    protected $logger;
    protected $dbhandler;
    protected $lastInsertId;
    static protected $acountNumber;

    public function __construct(Logger $logger, \PDO $db)
    {
        $this->logger = $logger;
        $this->dbhandler = $db;
    }

    public function set_Account_Number($value)
    {
        self::$acountNumber = $value;
    }

    public function get_Account_number()
    {
        return self::$acountNumber;
    }



    //Course Completed info
    public function courseCompletedModel($admin_id)
    {
        $date = date("Y-m-d H:i:s");
        $qry_ins_std = "SELECT a.`id`, a.`student_name`, a.`phone_number`, a.`end_date`, a.`basis`, b.`batch_name`, a.`batch`, ''  AS `class`  FROM `student` AS a LEFT JOIN `batch` AS b ON a.`batch` = b.`id` WHERE a.`admin_id` = ?  AND a.`basis` = 'Course Basis' AND a.`end_date` < CURRENT_DATE();";
        $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
        if ($res_ins_std->execute([$admin_id])) {
            $row_fet_batch = $res_ins_std->fetchAll();
            $data['error']  = false;
            $data['msg']  = "Course list fetched Successfully";
            $data["data"] = $row_fet_batch;
            return $data;
        } else {
            $data['error']  = true;
            $data['msg']  = "teacher data is not get from db";
            return $data;
        }
    }




    private function userExistCheckPhone($p = null)
    {
        if ($p !== null) {
            $phone_number = $p;
            $qry_usr = "SELECT * FROM `admin` WHERE `phone` = :phone";
            $res_usr = $this->dbhandler->prepare($qry_usr);
            $res_usr->execute([':phone' => $phone_number]);
            $row_usr_cnt = $res_usr->rowCount();
            if ($row_usr_cnt > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
