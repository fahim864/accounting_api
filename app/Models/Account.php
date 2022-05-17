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
        $qry_ins_std = "SELECT " . $cols . "  FROM " . $table_name . " WHERE `admin_id` = ? ";
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
    public function customerList($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $customer_type = $params['c_type'];
        if (!empty($customer_type) && ($customer_type === "C" || $customer_type === "S")) {
            $date = date("Y-m-d H:i:s");
            $qry_ins_std = "SELECT * FROM `customer` WHERE `admin_id` = ? AND `customer_type` = ? AND `customer_eff_sdc_end_date` IS NULL";
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
    public function customerAdd($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $customer_name = $params['c_name'];
        $customer_email = $params['c_email'];
        $customer_phone = $params['c_phone'];
        $customer_type = $params['c_type'];
        if (!empty($customer_type) && ($customer_type === "C" || $customer_type === "S")) {
            $date = date("Y-m-d H:i:s");
            if ($this->customer_phone_exists($customer_phone)) {
                $data['error']  = true;
                $data['msg']  = "Phone Number is exists or not valid number";
                return $data;
            }
            $customer_id = $this->customer_id($customer_type);
            try {
                //code...
                $qry_ins_std = "INSERT INTO `customer`(`admin_id`, `customer_id`, `customer_type`, `customer_name`, `customer_email`, `customer_phone`, `customer_creation`, `customer_eff_sdc_start_date`, `customer_eff_sdc_end_date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
                if ($res_ins_std->execute([$admin_id, $customer_id, $customer_type, $customer_name, $customer_email, $customer_phone, $date, $date, null])) {
                    $data['error']  = false;
                    $data['msg']  = "Customer added Successfully";
                    return $data;
                } else {
                    $data['error']  = true;
                    $data['msg']  = "Customer could not add to storage";
                    return $data;
                }
            } catch (\Throwable $th) {
                if ($th->getCode() === "23000") {
                    $customer_id = $this->customer_id($customer_type);
                    $qry_ins_std = "INSERT INTO `customer`(`admin_id`, `customer_id`, `customer_type`, `customer_name`, `customer_email`, `customer_phone`, `customer_creation`, `customer_eff_sdc_start_date`, `customer_eff_sdc_end_date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
                    if ($res_ins_std->execute([$admin_id, $customer_id, $customer_type, $customer_name, $customer_email, $customer_phone, $date, $date, null])) {
                        $data['error']  = false;
                        $data['msg']  = "Customer added Successfully";
                        return $data;
                    } else {
                        $data['error']  = true;
                        $data['msg']  = "Customer could not add to storage";
                        return $data;
                    }
                } else {
                    $data['error']  = true;
                    $data['msg']  = "Customer could not be able to enter";
                    return $data;
                }
            }
        } else {
            $data['error']  = true;
            $data['msg']  = "Invalid customer_type";
            return $data;
        }
    }
    public function customerDelete($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $customer_id = $params['c_id'];
        try {
            //code...
            $qry_upd_cust = "UPDATE `customer` SET `customer_eff_sdc_end_date`= CURRENT_TIMESTAMP() WHERE `customer_id` = ? AND `customer_eff_sdc_end_date` IS NULL";
            $res_upd_cust = $this->dbhandler->prepare($qry_upd_cust);
            if ($res_upd_cust->execute([$customer_id])) {
                $data['error']  = false;
                $data['msg']  = "Customer Deleted Successfully";
                return $data;
            } else {
                $data['error']  = true;
                $data['msg']  = "Customer could not delete";
                return $data;
            }
        } catch (\Throwable $th) {
            $data['error']  = true;
            $data['msg']  = "Customer could not be able to delete";
            return $data;
        }
    }
    public function customerEdit($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $customer_id = $params['c_id'];
        $customer_name = $params['c_name'];
        $customer_email = $params['c_email'];
        $customer_phone = $params['c_phone'];
        $customer_type = $params['c_type'];
        if (!empty($customer_type) && ($customer_type === "C" || $customer_type === "S")) {
            $date = date("Y-m-d H:i:s");

            if ($this->customer_phone_exists($customer_phone)) {
                $data['error']  = true;
                $data['msg']  = "Phone Number is exists or not valid number";
                return $data;
            }
            try {
                //code...
                $qry_upd_cust = "UPDATE `customer` SET `customer_eff_sdc_end_date`= CURRENT_TIMESTAMP WHERE `customer_id` = ? AND `customer_eff_sdc_end_date` IS NULL";
                $res_upd_cust = $this->dbhandler->prepare($qry_upd_cust);
                if ($res_upd_cust->execute([$customer_id])) {
                    $qry_ins_std = "INSERT INTO `customer`(`admin_id`, `customer_id`, `customer_type`, `customer_name`, `customer_email`, `customer_phone`, `customer_creation`, `customer_eff_sdc_start_date`, `customer_eff_sdc_end_date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
                    if ($res_ins_std->execute([$admin_id, $customer_id, $customer_type, $customer_name, $customer_email, $customer_phone, $date, $date, null])) {
                        $data['error']  = false;
                        $data['msg']  = "Customer edited Successfully";
                        return $data;
                    } else {
                        $data['error']  = true;
                        $data['msg']  = "Customer could not add to storage";
                        return $data;
                    }
                } else {
                    $data['error']  = true;
                    $data['msg']  = "Customer could not add to storage";
                    return $data;
                }
            } catch (\Throwable $th) {
                $data['error']  = true;
                $data['msg']  = "Customer could not be able to enter";
                return $data;
            }
        } else {
            $data['error']  = true;
            $data['msg']  = "Invalid customer_type";
            return $data;
        }
    }

    private function customer_phone_exists($c_phone_number)
    {
        if (strlen($c_phone_number) !== 10) {
            return true;
        }
        $qry_ins_std = "SELECT * FROM `customer` WHERE `customer_phone` = ? AND `customer_eff_sdc_end_date` IS NULL";
        $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
        $res_ins_std->execute([$c_phone_number]);
        if ($res_ins_std->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function customer_id($customer_type)
    {
        $qry_ins_std = "SELECT * FROM `customer` WHERE `customer_type` = ? ORDER BY `customer_id` DESC LIMIT 1";
        $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
        $res_ins_std->execute([$customer_type]);
        if ($res_ins_std->rowCount() < 1) {
            return $customer_type . "10001";
        } else {
            $row_ins_std = $res_ins_std->fetch();
            $customer_id = filter_var($row_ins_std['customer_id'], FILTER_SANITIZE_NUMBER_INT) + 1;
            return $customer_type . $customer_id;
        }
    }
}
