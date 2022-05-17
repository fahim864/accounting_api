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
    public function selectData($admin_id, $cols, $table_name, $where = null, $limit = null)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $cols = filter_var($cols, FILTER_SANITIZE_STRING);
        $table_name = filter_var($table_name, FILTER_SANITIZE_STRING);
        $date = date("Y-m-d H:i:s");
        $qry_ins_std = "SELECT " . $cols . "  FROM " . $table_name . " WHERE `admin_id` = ?";
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
    public function customerList($admin_id, $data)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $customer_type = $data['c_type'];
        if (!empty($customer_type) && ($customer_type === "C" || $customer_type === "S")) {
            $date = date("Y-m-d H:i:s");
            $qry_ins_std = "SELECT * FROM `customer` WHERE `admin_id` = ? AND `customer_type` = ?";
            $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
            if ($res_ins_std->execute([$admin_id, $customer_type])) {
                $row_fet_batch = $res_ins_std->fetchAll();
                $data['error']  = false;
                $data['msg']  = "Course list fetched Successfully";
                $data["data"] = $row_fet_batch;
                return $data;
            } else {
                $data['error']  = true;
                $data['msg']  = "Customer is not found";
                return $data;
            }
        } else {
            $data['error']  = true;
            $data['msg']  = "Invalid Parameter";
            return $data;
        }
    }
    public function customerAdd($admin_id, $data)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $customer_name = $data['c_name'];
        $customer_email = $data['c_email'];
        $customer_phone = $data['c_phone'];
        $customer_type = $data['c_type'];
        if (!empty($customer_type) && ($customer_type === "C" || $customer_type === "S")) {
            $date = date("Y-m-d H:i:s");
            $qry_ins_std = "INSERT INTO `customer`(`admin_id`, `customer_id`, `customer_type`, `customer_name`, `customer_email`, `customer_phone`, `customer_creation`, `customer_eff_sdc_start_date`, `customer_eff_sdc_end_date`) VALUES ()";
            $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
            if ($res_ins_std->execute([$admin_id, $customer_type])) {
                $row_fet_batch = $res_ins_std->fetchAll();
                $data['error']  = false;
                $data['msg']  = "Course list fetched Successfully";
                $data["data"] = $row_fet_batch;
                return $data;
            } else {
                $data['error']  = true;
                $data['msg']  = "Customer is not found";
                return $data;
            }
        } else {
            $data['error']  = true;
            $data['msg']  = "Invalid customer_type";
            return $data;
        }
    }
}
