<?php

namespace App\Models;

use Monolog\Logger;

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
                $qry_ins_std = "INSERT INTO `customer`(`admin_id`, `customer_id`, `customer_type`, `customer_name`, `customer_email`, `customer_phone`, `customer_creation`, `customer_eff_sdc_start_date`, `customer_eff_sdc_end_date`) VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
                    $qry_ins_std = "INSERT INTO `customer`(`admin_id`, `customer_id`, `customer_type`, `customer_name`, `customer_email`, `customer_phone`, `customer_creation`, `customer_eff_sdc_start_date`, `customer_eff_sdc_end_date`) VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
                    $qry_ins_std = "INSERT INTO `customer`(`admin_id`, `customer_id`, `customer_type`, `customer_name`, `customer_email`, `customer_phone`, `customer_creation`, `customer_eff_sdc_start_date`, `customer_eff_sdc_end_date`) VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?)";
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

    //User Completed info
    public function userList($admin_id)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $qry_ins_std = "SELECT * FROM `user` WHERE `adminid` = ? AND `effective_date` IS NULL";
        $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
        if ($res_ins_std->execute([$admin_id])) {
            $row_fet_batch = $res_ins_std->fetchAll();
            $data['error']  = false;
            $data['msg']  = "User list fetched Successfully";
            $data["data"] = $row_fet_batch;
            return $data;
        } else {
            $data['error']  = true;
            $data['msg']  = "Customer is not found";
            return $data;
        }
    }
    public function userAdd($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);

        $user_fname = $params["u_fname"];
        $user_lname = $params["u_lname"];
        $user_email = $params["u_email"];
        $user_status = $params["u_status"];
        $user_role = $params["u_role"];
        $user_password = md5($params["u_password"]);
        if (!empty($user_role) && !empty($user_email) && (!empty($user_status) && ($user_status === "0" || $user_status === "1")) && !empty($user_password) && !empty($user_fname) && !empty($user_lname)) {
            $date = date("Y-m-d H:i:s");
            if ($this->user_email_exists($user_email)) {
                $data['error']  = true;
                $data['msg']  = "Email is exists or not valid number";
                return $data;
            }
            try {
                //code...
                $qry_ins_std = "INSERT INTO `user`(`adminid`, `first_name`, `last_name`, `email`, `password`, `status`, `role`, `hash`, `created_on`) VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
                if ($res_ins_std->execute([$admin_id, $user_fname, $user_lname, $user_email, $user_password, $user_status, $user_role, RAND(9999, 99999), $date])) {
                    $data['error']  = false;
                    $data['msg']  = "User added Successfully";
                    return $data;
                } else {
                    $data['error']  = true;
                    $data['msg']  = "User could not add to storage";
                    return $data;
                }
            } catch (\Throwable $th) {

                $data['error']  = true;
                $data['msg']  = "User could not be able to enter";
                return $data;
            }
        } else {
            $data['error']  = true;
            $data['msg']  = "Invalid User Cred";
            return $data;
        }
    }
    public function userDelete($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $customer_id = $params['u_id'];
        try {
            //code...
            $qry_upd_cust = "UPDATE `user` SET `effective_date`= CURRENT_TIMESTAMP() WHERE `id` = ?  AND `adminid` = ?";
            $res_upd_cust = $this->dbhandler->prepare($qry_upd_cust);
            if ($res_upd_cust->execute([$customer_id, $admin_id])) {
                $data['error']  = false;
                $data['msg']  = "User Deleted Successfully";
                return $data;
            } else {
                $data['error']  = true;
                $data['msg']  = "User could not delete";
                return $data;
            }
        } catch (\Throwable $th) {
            $data['error']  = true;
            $data['msg']  = "User could not be able to delete";
            return $data;
        }
    }
    public function userEdit($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $user_id = $params["u_id"];
        $user_fname = $params["u_fname"];
        $user_lname = $params["u_lname"];
        $user_email = $params["u_email"];
        $user_status = $params["u_status"];
        $user_role = $params["u_role"];
        $user_password = $params["u_password"];
        $date = date("Y-m-d H:i:s");
        if ($this->user_email_exists($user_email, $user_id)) {
            $data['error']  = true;
            $data['msg']  = "Invalid Creds.";
            return $data;
        }

        if ($this->usr_role_exists($user_role)) {
            $data['error']  = true;
            $data['msg']  = "Invalid Role";
            return $data;
        }

        if ((strlen($user_status) > 0) && ($user_status === "0" || $user_status === "1")) {
            try {
                //code...
                if (empty($user_password)) {
                    $qry_upd_cust = "UPDATE `user` SET `first_name`= ?,`last_name`= ?,`email`= ?,`status`= ?,`role`= ?,`hash`= ?,`updated_at`= ?,`effective_date`= ? WHERE `id` = ?";
                    $res_upd_cust = $this->dbhandler->prepare($qry_upd_cust);
                    if ($res_upd_cust->execute([$user_fname, $user_lname, $user_email, $user_status, $user_role, random_int(9999999, 99999999), $date, NULL,  $user_id])) {
                        $data['error']  = false;
                        $data['msg']  = "User edited Successfully";
                        return $data;
                    } else {
                        $data['error']  = true;
                        $data['msg']  = "User could not add to storage";
                        return $data;
                    }
                } else {
                    $message = $this->password_validate($user_password);
                    if (!$message) {
                        $qry_upd_cust = "UPDATE `user` SET `first_name`= ?,`last_name`= ?,`email`= ?,`password` = ?, `status`= ?,`role`= ?,`hash`= ?,`updated_at`= ?,`effective_date`= ? WHERE `id` = ?";
                        $res_upd_cust = $this->dbhandler->prepare($qry_upd_cust);
                        if ($res_upd_cust->execute([$user_fname, $user_lname, $user_email, md5($user_password), $user_status, $user_role, random_int(9999999, 99999999), $date, NULL,  $user_id])) {
                            $data['error']  = false;
                            $data['msg']  = "User edited Successfully";
                            return $data;
                        } else {
                            $data['error']  = true;
                            $data['msg']  = "User could not add to storage";
                            return $data;
                        }
                    } else {
                        $data['error']  = true;
                        $data['msg']  = $message;
                        return $data;
                    }
                }
            } catch (\Throwable $th) {
                $data['error']  = true;
                $data['msg']  = "User could not be able to enter";
                return $data;
            }
        } else {
            $data['error']  = true;
            $data['msg']  = "Invalid status";
            return $data;
        }
    }

    public function settingsModel($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        try {
            //code...
            if (empty($params)) {
                $data['error']  = true;
                $data['msg']  = "Invalid Creds";
                return $data;
            }
            $d = [];
            foreach ($params as $k => $v) {
                $d[$k] = filter_var(htmlentities(strip_tags($v)), 513);
            }

            $params = $d;

            $set_s_name = $params['s_name'];
            $set_s_company_name = $params['s_company_name'];
            $set_s_com_phone = $params['s_com_phone'];
            $set_s_com_tin = $params['s_com_tin'];
            $set_s_com_address = $params['s_com_address'];
            $set_s_language = $params['s_language'];
            $set_s_com_logo = $params['s_com_logo'];



            $qry_upd_cust = "SELECT `setting_name` FROM `settings` WHERE `admin_id` = ?";
            $res_upd_cust = $this->dbhandler->prepare($qry_upd_cust);
            $res_upd_cust->execute([$admin_id]);
            if ($res_upd_cust->rowCount() > 0) {
                $qry_set_upd = "UPDATE `settings` SET `setting_name`=?, `company_name`=?, `address`=?, `gsttin`=?, `company_logo`=?, `phone_number`=?  WHERE `admin_id` = ?";
                $res_set_upd = $this->dbhandler->prepare($qry_set_upd);
                if ($res_set_upd->execute([$set_s_name, $set_s_company_name, $set_s_com_address, $set_s_com_tin, $set_s_com_logo, $set_s_com_phone, $admin_id])) {
                    $data['error']  = false;
                    $data['msg']  = "Settings updated Successfully";
                    return $data;
                } else {
                    $data['error']  = true;
                    $data['msg']  = "Settings could not update";
                    return $data;
                }
            } else {
                $qry_set_ins = "INSERT INTO `settings`(`admin_id`, `setting_name`, `company_name`, `address`, `gsttin`, `company_logo`, `phone_number`) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $res_set_ins = $this->dbhandler->prepare($qry_set_ins);
                if ($res_set_ins->execute([$admin_id, $set_s_name, $set_s_company_name, $set_s_com_address, $set_s_com_tin, $set_s_com_logo, $set_s_com_phone])) {
                    $data['error']  = false;
                    $data['msg']  = "Settings added Successfully";
                    return $data;
                } else {
                    $data['error']  = true;
                    $data['msg']  = "Settings could not add";
                    return $data;
                }
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
            $data['error']  = true;
            $data['msg']  = "Settings could not be able to update";
            return $data;
        }
    }

    public function productslist($admin_id)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        try {
            //code...
            $qry_upd_cust = "SELECT * FROM `goods_master` WHERE `admin_id` = ? AND `effective_end_date` IS NULL";
            $res_upd_cust = $this->dbhandler->prepare($qry_upd_cust);
            if ($res_upd_cust->execute([$admin_id])) {
                $row_upd_cust = $res_upd_cust->fetchAll();
                $data['error']  = false;
                $data['msg']  = "Product fetched Successfully";
                $data['data'] = $row_upd_cust;
                return $data;
            } else {
                $data['error']  = true;
                $data['msg']  = "Product could not delete";
                return $data;
            }
        } catch (\Throwable $th) {
            $data['error']  = true;
            $data['msg']  = "Product could not be able to delete";
            return $data;
        }
    }

    public function productsAdd($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $p_name = $params["p_name"];
        $gst_cata = $params["gst_cata"];
        $hsn = $params["hsn"];
        $gst_appli = $params["gst_appli"];
        if (!empty($p_name) && !empty($gst_cata) && !empty($hsn) && !empty($gst_appli)) {
            $date = date("Y-m-d H:i:s");
            if ($this->product_exists($p_name)) {
                $data['error']  = true;
                $data['msg']  = "Product Already exists.";
                return $data;
            }
            $qry_ins_std = "INSERT INTO `goods_master`(`admin_id`, `goods_name`, `gst_category`, `hsn_code`, `gst_applicable`, `effective_start_date`, `effective_end_date`, `tracking`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
            if ($res_ins_std->execute([$admin_id, $p_name, $gst_cata, $hsn, $gst_appli, $date, NULL, NULL])) {
                $data['error']  = false;
                $data['msg']  = "Product added Successfully";
                return $data;
            } else {
                $data['error']  = true;
                $data['msg']  = "Product could not add to storage";
                return $data;
            }
        } else {
            $data['error']  = true;
            $data['msg']  = "Invalid Product Cred";
            return $data;
        }
    }

    public function productsEdit($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $e_id = $params["e_id"];
        $p_name = $params["p_name"];
        $gst_cata = $params["gst_cata"];
        $hsn = $params["hsn"];
        $gst_appli = $params["gst_appli"];
        $date = date("Y-m-d H:i:s");
        if ($this->product_exists($p_name, $e_id)) {
            $data['error']  = true;
            $data['msg']  = "Product already exists.";
            return $data;
        }

        try {
            //code...
            $qry_select_cust = "SELECT `tracking` FROM `goods_master` WHERE `id` = ?";
            $res_select_cust = $this->dbhandler->prepare($qry_select_cust);
            $res_select_cust->execute([$e_id]);
            $row_select_cust = $res_select_cust->fetch();
            $qry_upd_cust = "UPDATE `goods_master` SET `effective_end_date`= CURRENT_TIMESTAMP() WHERE `effective_end_date` IS NULL AND `id` = ?";
            $res_upd_cust = $this->dbhandler->prepare($qry_upd_cust);
            if ($res_upd_cust->execute([$e_id])) {
                $qry_ins_std = "INSERT INTO `goods_master`(`admin_id`, `goods_name`, `gst_category`, `hsn_code`, `gst_applicable`, `effective_start_date`, `effective_end_date`, `tracking`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
                if ($res_ins_std->execute([$admin_id, $p_name, $gst_cata, $hsn, $gst_appli, $date, NULL, $row_select_cust['tracking']])) {
                    $data['error']  = false;
                    $data['msg']  = "Product edited Successfully";
                    return $data;
                } else {
                    $data['error']  = true;
                    $data['msg']  = "Product could not add to storage";
                    return $data;
                }
            } else {
                $data['error']  = true;
                $data['msg']  = "Product could not add to storage";
                return $data;
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
            $data['error']  = true;
            $data['msg']  = "Product could not be able to enter";
            return $data;
        }
    }

    public function realisationSearch($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $invoice_num = $params['srch'];
        try {
            //code...
            $qry_upd_cust = "SELECT `invoice_no`,`invoice_date`,`amount_paid`,`amount_due` FROM `invoice_master` WHERE `admin_id`= ? AND `invoice_no`= ?";
            $res_upd_cust = $this->dbhandler->prepare($qry_upd_cust);
            if ($res_upd_cust->execute([$admin_id, $invoice_num])) {
                $row_upd_cust = $res_upd_cust->fetchAll();
                $data['error']  = false;
                $data['msg']  = "Realisation fetched Successfully";
                $data['data'] = $row_upd_cust;
                return $data;
            } else {
                $data['error']  = true;
                $data['msg']  = "Realisation could not delete";
                return $data;
            }
        } catch (\Throwable $th) {
            $data['error']  = true;
            $data['msg']  = "Realisation could not be able to delete";
            return $data;
        }
    }

    public function paymenttosupplierSearch($admin_id, $params)
    {
        $admin_id = filter_var($admin_id, FILTER_SANITIZE_NUMBER_INT);
        $invoice_num = $params['srch'];
        try {
            //code...
            $qry_upd_cust = "SELECT `bill_no`,`bill_date`,`amt_paid`,`amt_due` FROM `bill_master` WHERE `admin_id` = ? AND `bill_no` = ? ";
            $res_upd_cust = $this->dbhandler->prepare($qry_upd_cust);
            if ($res_upd_cust->execute([$admin_id, $invoice_num])) {
                $row_upd_cust = $res_upd_cust->fetchAll();
                $data['error']  = false;
                $data['msg']  = "Realisation fetched Successfully";
                $data['data'] = $row_upd_cust;
                return $data;
            } else {
                $data['error']  = true;
                $data['msg']  = "Realisation could not delete";
                return $data;
            }
        } catch (\Throwable $th) {
            $data['error']  = true;
            $data['msg']  = "Realisation could not be able to delete";
            return $data;
        }
    }

    private function product_exists($product_name, $p_id = null)
    {
        if ($p_id === NULL) {
            $qry_ins_std = "SELECT `goods_name` FROM `goods_master` WHERE `goods_name` = ? AND `effective_end_date` IS NULL;";
        } else {
            $qry_ins_std = "SELECT `goods_name` FROM `goods_master` WHERE `goods_name` = ? AND `effective_end_date` IS NULL     AND `id`<> '$p_id'";
        }
        $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
        $res_ins_std->execute([$product_name]);
        if ($res_ins_std->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function usr_role_exists($role)
    {
        $qry_ins_std = "SELECT `role_name` FROM `user_role` WHERE `id` = ? AND `status` = 0";
        $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
        $res_ins_std->execute([$role]);
        if ($res_ins_std->rowCount() > 0) {
            return false;
        } else {
            return true;
        }
    }

    private function password_validate($password)
    {
        if (strlen($password) < 8) {
            return "Passowrd is too short";
        }

        return false;
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
    private function user_email_exists($c_email_number, $user_id = null)
    {
        if (!filter_var($c_email_number, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        if ($user_id === NULL) {
            $qry_ins_std = "SELECT `first_name` FROM `user` WHERE `email` = ? AND `effective_date` IS NULL";
        } else {
            $qry_ins_std = "SELECT `first_name` FROM `user` WHERE `email` = ? AND `effective_date` IS NULL AND `id`<> '$user_id'";
        }
        $res_ins_std = $this->dbhandler->prepare($qry_ins_std);
        $res_ins_std->execute([$c_email_number]);
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
