<?php

namespace App\Models;

use PDO;
use Monolog\Logger;

class UserMapper
{

    protected $logger;
    protected $dbhandler;
    static protected $userID;
    public function __construct(Logger $logger, \PDO $db)
    {
        $this->logger = $logger;
        $this->dbhandler = $db;
    }

    public function set_User_Id($value)
    {
        self::$userID = $value;
    }
    public function get_User_Id()
    {
        return self::$userID;
    }

    /**
     * Fetch all authors
     *
     * @return [Author]
     */
    public function fetchAll()
    {
        $sql = "SELECT * FROM `admin` ORDER BY id ASC";
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
        $sql = "SELECT * FROM `admin` WHERE id = :user_id";
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
        $sql = "SELECT `name`,`id`, `email`,`password` FROM `admin` WHERE `email` = :email";
        $stmt = $this->dbhandler->prepare($sql);
        $stmt->execute([':email' => $email]);

        $data = $stmt->fetch(PDO::FETCH_OBJ);
        if ($data) {
            return $data;
        }
        return false;
    }
    public function findByStudent_id($academic_id, $student_id, $dob)
    {
        $sql = "SELECT `student_name`,`batch` AS `email`,`id` FROM `student` WHERE `id` = :student_id AND `admin_id` =:academic_id AND `dob` =:dob";
        $stmt = $this->dbhandler->prepare($sql);
        $stmt->execute([':academic_id' => $academic_id, ':student_id' => $student_id, ':dob' => $dob]);

        $data = $stmt->fetch(PDO::FETCH_OBJ);

        if ($data) {
            return $data;
        }
        return FALSE;
    }

    public function findByTeacher_id($teacher_id, $teacher_name, $teacher_num)
    {
        $sql = "SELECT `id`,`staff_name` AS `staff_name`,`mob_num` FROM `staff_manager` WHERE `id` = :teacher_id AND `mob_num` = :mob_num AND `staff_name` = :teacher_name";
        $stmt = $this->dbhandler->prepare($sql);
        $stmt->execute([':teacher_id' => $teacher_id, ':teacher_name' => $teacher_name, ':mob_num' => $teacher_num]);

        $data = $stmt->fetch(PDO::FETCH_OBJ);

        if ($data) {
            return $data;
        }
        return FALSE;
    }

    public function setToken_by_id($u_id, $jti)
    {
        $sql = "UPDATE `admin` SET `token`=? WHERE `id` = ?";
        $stmt = $this->dbhandler->prepare($sql);

        if ($stmt->execute([$jti, $u_id])) {
            return TRUE;
        }
        return false;
    }
    public function getToken_by_id($u_id)
    {
        $sql = "SELECT `token` FROM `admin` WHERE `id` =  ?";
        $stmt = $this->dbhandler->prepare($sql);

        if ($stmt->execute([$u_id])) {
            return $stmt->fetch();
        }
        return false;
    }

    public function createAdminmain($email, $password, $name)
    {
        $enc_pass = md5($password);
        $date = date("Y-m-d H:i:s");
        $phone = "+8801700000000";

        $sql = "INSERT INTO `admin`(`name`, `password`, `email`, `phone`, `created_on`) VALUES (:name, :password, :email, :phone, :date)";
        $stmt = $this->dbhandler->prepare($sql);

        if ($stmt->execute([':name' => $name, ':password' => $enc_pass, ':email' => $email, ':phone' => $phone, ':date' => $date])) {
            return TRUE;
        }
        return FALSE;
    }
}
