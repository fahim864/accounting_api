<?php

namespace App\Models;

use PDO;
use Monolog\Logger;
use DateTime;

class Account {

    protected $logger;
    protected $dbhandler;
    protected $lastInsertId;

    public function __construct(Logger $logger, \PDO $db) {

        $this->logger = $logger;
        $this->dbhandler = $db;
    }

    public function InsertAccount($data = []) {
        if (!empty($data)) {

            $projectType = $this->getTypeofProject($data);
            if (!empty($projectType)) {

                return $lastId = $this->InitializeAccountData($data, $projectType);
            }
        }
    }

    /**
     * 
     * @param type $data['applicanttype', 'empbusinessType', 'loanAmount', 'bank']
     * @return array project ID, Project Name
     */
    public function getTypeofProject($bankId, $applicantType, $empBusiType, $loanAmt) {

        $qry = "SELECT r1.project_id, r1.project_name
                FROM
                (SELECT
                b.bank_name,
                pc.project_id,
                p.project_name,
                ap.apt_short_name as applicantType,
                ebt.emp_busi_short_name as employerType,
                lt.short_name as loanType,
                rl.range_min_value as lowValue,
                rl.range_max_value as highValue
                FROM
                project_combination pc
                INNER JOIN project  p ON pc.project_id = p.id
                INNER JOIN bank b ON pc.bank_id = b.id
                INNER JOIN applicant_type ap ON pc.applicant_type_id = ap.applicant_type_id
                INNER JOIN emp_business_type ebt ON pc.emp_business_id = ebt.emp_busi_id
                INNER JOIN loan_type lt ON pc.loan_type_id = lt.loan_type_id
                INNER JOIN loan_range_values rl ON pc.loan_range_low_id = rl.loan_range_id 
                where pc.bank_id=:bankId) r1
                Where (r1.applicantType=:appType) AND (r1.employerType=:empType) AND (:loanAmt BETWEEN r1.lowValue AND r1.highValue)";
        $stmt = $this->dbhandler->prepare($qry);
        $stmt->bindParam(':bankId', $bankId, PDO::PARAM_INT);
        $stmt->bindParam(':appType', $applicantType, PDO::PARAM_STR);
        $stmt->bindParam(':empType', $empBusiType, PDO::PARAM_STR);
        $stmt->bindParam(':loanAmt', $loanAmt, PDO::PARAM_INT);
        $stmt->execute();
        // $stmt->queryString;
        $num_rows = $stmt->rowCount();
        if ($num_rows == 1) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $result = array(
                    'project_id' => $row['project_id'],
                    'project_name' => $row['project_name']
                );
            }
        }
        return null;
    }

    /**
     * @param $data request params and project details
     */
    public function InitializeAccountData($data = [], $projectType = null, $userId=null, $bankId=null) {

        //Insert Account first
        if (!empty($projectType)) {

            $date = new DateTime();
            $currentDate = $date->format("Y-m-d");
            $is_api=1;
            $qry_addAccount = "INSERT INTO accounts(project_id, bank_id, user_id, comapny_name, date_added, date_updated, is_api)"
                    . " VALUES (:project_id, :bank_id, :user_id, :comapny_name, :date_added, :date_updated, :isAPI)";
            $stmt = $this->dbhandler->prepare($qry_addAccount);
            $stmt->bindParam(':project_id', $projectType);
            $stmt->bindParam(':bank_id', $bankId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':comapny_name', $data['LAN']);
            $stmt->bindParam(':date_added', $currentDate);
            $stmt->bindParam(':date_updated', $currentDate);
            $stmt->bindParam(':isAPI', $is_api);
            $stmt->execute();
            $this->lastInsertId = $this->dbhandler->lastInsertId();
        }
        
        /**
         * Injecting Calculations and Prediction Fields
         */
        $calc_pred = $this->InsertingCalcPredFields($projectType);
        
        /**
         * Merging Arrays Here for totak 88 fields
         */
        return $totalFields = array_merge_recursive($data, $calc_pred);
        
        /**
         * 
         * @param type array
         * Preparing Data for Transaction
         */
        
        foreach ($totalFields AS $fieldSN => $fData){
            //As Field id is most Important for storing Data for future use
            if($fieldID = self::getShortNameIDByprojectID($fieldSN, $projectType)){
                $transData[] = array($this->lastInsertId, $userId, $fieldID, $fData, $currentDate);
            }
            
        }
        
        
        
        /**
         * 
         * @param type $transData
         * Begin Transaction for field data insert
         */    
        $stmt = $this->dbhandler->prepare("INSERT INTO `data` (`account_id`,`user_id`,`field_id`,`field_data`, `created_on`) VALUES (?,?,?,?,?)");
        try
        {
            $this->dbhandler->beginTransaction();
            foreach ($transData AS $data){
                $stmt->execute($data);
            }
            $this->dbhandler->commit();
        }catch(Exception $e)
        {
            $this->dbhandler->rollBack();
            throw $e;
        }
        
        
    }

    public function CheckAccountExists($accountNumber) {
        
    }

    public function getAccountInfo($projectId=null) {
        
        $qry_projData = "SELECT
                    accounts.comapny_name,
                    accounts.count_prediction,
                    accounts.prediction_status,
                    project.project_name,
                    `user`.email_id,
                    bank.bank_name,
                    project.loan_type,
                    project.client_type
                    FROM
                    accounts
                    INNER JOIN bank ON accounts.bank_id = bank.id
                    INNER JOIN project ON project.bank_id = bank.id AND accounts.project_id = project.id
                    INNER JOIN `user` ON accounts.user_id = `user`.id
                    WHERE project.id=? AND accounts.id=?
                    ";
        $stmt = $this->dbhandler->prepare($qry_projData);
        $exeArray = [$projectId, $this->lastInsertId];
        $stmt->execute($exeArray);
        while ($row = $stmt->fetch(PDO::FETCH_OBJ))
        {
            return $row;
        }
        return false;
    }

    public function getProjectFormFields($projectID) {

        $query = "SELECT `short_name`, `id`, `field_type`, `svm`, `field_value` FROM `form_fields` WHERE `project_id`=:projectID AND field_type NOT IN ('prediction','columbreak','tab_start','tab_end','heading')";
        $stmt = $this->dbhandler->prepare($query);
        $stmt->bindParam(':projectID', $projectID);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $mydata[] = array(
                'SN' => $row['short_name'],
                'id' => $row['id'],
                'type' => $row['field_type'],
                'fieldValue' => $row['field_value'],
                'is_svm' => $row['svm']
            );
        }

        return (!empty($mydata)) ? $mydata : NULL;
    }

    public function BuildExpression($formFields = [], $projectId) {

        $calculation = [];
        for ($i = 0; $i < count($formFields); $i++) {
            if ($formFields[$i]['type'] == 'calculation') {
                $step = 1;
                $calStr = '';
                //        echo $myData[$i]['SN'] . "<br>";
                //Get the field name and query the database
                $qryC = "select * from calculation where project_id = :projectID and derived_field = :derivedField order by step";
                $stmt = $this->dbhandler->prepare($qryC);
                $stmt->bindParam(':projectID', $projectId);
                $stmt->bindParam(':derivedField', $formFields[$i]['SN']);
                $stmt->execute();
                $num_rowsC = $stmt->rowCount();
                if ($num_rowsC) {
                    while ($rowC = $stmt->fetch(PDO::FETCH_ASSOC)) {

                        if (strlen($rowC['pre_symbol']) == 0) {
                            $calStr = '(';
                        } else {
                            $calStr .= $rowC['pre_symbol'] . '(';
                        }
                        if (strlen($rowC['variable1']) > 0) {
                            $calStr .= $rowC['variable1'];
                            $dependentVars[$formFields[$i]['SN']][] = $rowC['variable1'];
                        }
                        if (strlen($rowC['middle_symbol']) > 0) {
                            $calStr .= $rowC['middle_symbol'];
                        }
                        if (strlen($rowC['varible2']) > 0) {
                            $calStr .= $rowC['varible2'];
                            $dependentVars[$formFields[$i]['SN']][] = $rowC['varible2'];
                        }
                        if (strlen($rowC['post_symbol']) == 0) {
                            $calStr .= ')';
                        } else {
                            $calStr .= $rowC['post_symbol'];
                        }

                        $calculation[$rowC['derived_field']] = $calStr;
                    }
                }
            }
        }
        return $myarray = array(
            'calculation' => $calculation,
            'dependentVars' => $dependentVars
        );
    }

    public function getProjectProperties($projectId = null) {

        //get Project SVM Dimension
        $qryDM = "SELECT `bias`,`svm_row`, `svm_col`, `project_name` FROM `project` WHERE id=:projectId";
        if ($stmt = $this->dbhandler->prepare($qryDM)) {
            $stmt->bindParam(':projectId', $projectId);
            $stmt->execute();
            $num_rows = $stmt->rowCount();
            if ($num_rows === 0) {
                return false;
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $projectProp = array(
                    'bias' => $row['bias'],
                    'row' => $row['svm_row'],
                    'col' => $row['svm_col'],
                    'name' => $row['project_name']
                );
            }
        }
        return (!empty($projectProp)) ? $projectProp : null;
    }

    public function getSVMConstant($projectId = null) {

        $qry_const = "SELECT `svm_field`, `const_a`, `const_b` FROM `pre_process` WHERE `project_id`=:projectID";
        if ($stmt = $this->dbhandler->prepare($qry_const)) {
            $svm_constant = [];
            $stmt->bindParam(':projectID', $projectId);
            $stmt->execute();
            $num_of_rows = $stmt->rowCount();
            if ($num_of_rows === 0) {
                return false;
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $svm_constant[$row['svm_field']] = array($row['const_a'], $row['const_b']);
            }
        }
        return (!empty($svm_constant)) ? $svm_constant : null;
    }

    public function getSVMTitles($projectId) {
        $qry = "SELECT `svm_col`, `svm_val` FROM `tbl_svm_titles` WHERE svm_proj_id=:projectId ORDER BY svm_col";
        if ($stmt = $this->dbhandler->prepare($qry)) {
            $stmt->bindParam(':projectId', $projectId);
            $stmt->execute();
            $num_of_rows = $stmt->rowCount();
            if ($num_of_rows === 0) {
                return false;
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $svm_matrix_titles[$row['svm_val']] = $row['svm_col'];
            }
        }
        return (!empty($svm_matrix_titles)) ? $svm_matrix_titles : NULL;
    }

    public function getSVMValues($projectId) {

        $qry_svm = "SELECT svm_row,svm_col,svm_val FROM tbl_svm_values WHERE svm_proj_id=:projectId";
        if ($stmt = $this->dbhandler->prepare($qry_svm)) {
            $stmt->bindParam(':projectId', $projectId);
            $stmt->execute();
            $rowCount = $stmt->rowCount();
            if ($rowCount === 0) {
                return false;
            }
            while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $svmVals[$rows['svm_row']][$rows['svm_col']] = $rows['svm_val'];
            }
        }
        return (!empty($svmVals)) ? $svmVals : NULL;
    }

    public function getRegressionValues($projectId) {

        $qry_REG = "SELECT `reg_row`, `reg_col`, `reg_value` FROM `regression` WHERE `project_id`=:projectID ORDER BY id";
        if ($stmt = $this->dbhandler->prepare($qry_REG)) {
            $stmt->bindParam(':projectID', $projectId);
            $stmt->execute();
            $num_of_rows = $stmt->rowCount();
            if ($num_of_rows === 0) {
                return FALSE;
            }
            while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $regressionVal[$rows['reg_row']][$rows['reg_col']] = $rows['reg_value'];
            }
        }
        return (!empty($regressionVal)) ? $regressionVal : NULL;
    }

    /**
     * LADA V2.0 Specific Functions
     */
    public function getCalculationsParamters() {

        /**
     * Track down Derived fields as Key and field Dependency and values
     */
    //Getting Derived Calculations list from database
    $generateInputData = [];
    $calSql = "SELECT * FROM `calculation` ORDER BY id";
    $stmt = $this->dbhandler->prepare($calSql);
    $stmt->execute();
    $rowCount = 0;
    while ($rowC = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if (self::is_json($rowC['func_signature'])) {
            $dataJson = json_decode($rowC['func_signature'], true);

            //Association[12] Primary Dependency[1] json_variable presents
            if ($dataJson['association'] == '5') {
                $generateInputData[$rowCount] = array(
                    $dataJson['custom_func_name'] => array(
                        "derivedField" => $rowC['derived_field'],
                        "association" => $dataJson['association'],
                        "is_score" => $dataJson['is_score_considerd'],
                ));
                //if Input Varibales are josn Format Or not

                if (self::is_json($rowC['variable1']) && self::is_json($rowC['variable2']) && self::is_json($rowC['variable3'])) {
                    /**
                      $generateInputData[$rowCount] = array(
                      $dataJson['custom_func_name'] => array(
                      "inputs" => array(self::is_json($rowC['variable1'], true), self::is_json($rowC['variable2'], true))
                      ));
                     * 
                     */
                    $myarr = array(self::is_json($rowC['variable2'], true), self::is_json($rowC['variable3'], true), self::is_json($rowC['variable4'], true), self::is_json($rowC['variable5'], true), self::is_json($rowC['variable6'], true));
                    $generateInputData[$rowCount][$dataJson['custom_func_name']]['inputs'] = $myarr;
                } else {


                    $generateInputData[$rowCount][$dataJson['custom_func_name']]['inputs'] = array($rowC['variable2'] => null, $rowC['variable3'] => null, $rowC['variable4'] => null, $rowC['variable5'] => null, $rowC['variable6'] => null);
                }
                if ($dataJson['is_cat'] == '1') {
                    $generateInputData[$rowCount][$dataJson['custom_func_name']]['is_cat'] = array('categorical' => $dataJson['is_cat']);
                }

                if ($dataJson['Primary_method'] !== '0') {

                    $generateInputData[$rowCount][$dataJson['custom_func_name']]['primary_method'] = array('method' => $dataJson['Primary_method']);
                } else {


                    $generateInputData[$rowCount][$dataJson['custom_func_name']]['primary_method'] = array();
                }
            } else {
                $generateInputData[$rowCount] = array(
                    $dataJson['custom_func_name'] => array(
                        "derivedField" => $rowC['derived_field'],
                        "association" => $dataJson['association'],
                        "is_score" => $dataJson['is_score_considerd'],
                    //"inputs" => array(self::is_json($rowC['variable1'], true), self::is_json($rowC['variable2'], true))
                ));
                //if Input Varibales are josn Format Or not

                if (self::is_json($rowC['variable1']) && self::is_json($rowC['variable2'])) {
                    /**
                      $generateInputData[$rowCount] = array(
                      $dataJson['custom_func_name'] => array(
                      "inputs" => array(self::is_json($rowC['variable1'], true), self::is_json($rowC['variable2'], true))
                      ));
                     * 
                     */
                    $myarr = array(self::is_json($rowC['variable1'], true), self::is_json($rowC['variable2'], true), self::is_json($rowC['variable3'], true), self::is_json($rowC['variable4'], true), self::is_json($rowC['variable5'], true), self::is_json($rowC['variable6'], true));
                    $generateInputData[$rowCount][$dataJson['custom_func_name']]['inputs'] = $myarr;
                } else {


                    $generateInputData[$rowCount][$dataJson['custom_func_name']]['inputs'] = array($rowC['variable1'] => NULL, $rowC['variable2'] => NULL, $rowC['variable3'] => NULL, $rowC['variable4'] => NULL, $rowC['variable5'] => NULL, $rowC['variable6'] => NULL);
                }

                if ($dataJson['is_cat'] == '1') {
                    $generateInputData[$rowCount][$dataJson['custom_func_name']]['is_cat'] = array('categorical' => $dataJson['is_cat']);
                }

                if ($dataJson['Primary_method'] !== '0') {

                    $generateInputData[$rowCount][$dataJson['custom_func_name']]['Primary_method'] = array('method' => $dataJson['Primary_method']);
                } else {

                    $generateInputData[$rowCount][$dataJson['custom_func_name']]['Primary_method'] = array();
                }
            }
        }

        // return $myManipulate;
        $rowCount++;
    }

    return $generateInputData;
    }

    public function getScorefromEndUsageLoan($ploan, $pType) {
        if (!empty($ploan) && !empty($pType)) {
            $combID = sprintf("%d%d", $ploan, $pType);
            $qry = "SELECT `score` FROM end_usage_combination WHERE `combination_id` IN(:combinationID)";
            $stmt = $this->dbhandler->prepare($qry);
            $stmt->bindParam(':combinationID', $combID);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                return $row['score'];
            }
            
        }
        return 0;
    }
    
    private static function is_json($string, $return_data = false) {
    $data = json_decode($string, true);
    return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
    }

    public function getScoreRelationship($snno) {

        if (!empty($snno)) {
            $qry = "SELECT `rel_score` FROM `relationship_master` WHERE `rel_id`=:relID";
            $stmt = $this->dbhandler->prepare($qry);
            $stmt->bindParam(':relID', $snno, PDO::PARAM_STR);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['rel_score'];
            }
            
        }
        return 0;
    }

    public function getTypeofEmployerProc($norgID, $vorgYears) {
        if (!empty($this->dbhandler)) {
            if (!empty($norgID) && !empty($vorgYears)) {
                //Calling Proceeduer here
                //$res = mysqli_query($conn, "CALL norg_proc($norgID,$vorgYears)");
                $qry = "select 
                    (case
                    when is_vintage_validated = '1' and  :highValue >= vintage_high_value then vintage_high_score  
                    when is_vintage_validated = '1' and  :lowValue <= vintage_low_value then vintage_low_score
                    else global_score
                    end) score
                    from norg_master 
                    where norg_id = :norgID";
                $stmt = $this->dbhandler->prepare($qry);
                $stmt->bindParam(':highValue', $vorgYears, PDO::PARAM_STR);
                $stmt->bindParam(':lowValue', $vorgYears, PDO::PARAM_STR);
                $stmt->bindParam(':norgID', $norgID, PDO::PARAM_STR);
                $stmt->execute();
                /* execute prepared statement */
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    return $row['score'];
                }
                

                printf("Failed to run query: Number(%s) Reference(%s)", $this->dbhandler->errno, $this->dbhandler->error);
                return 0;
            }
            return 0;
        }
        printf("Failed to connect to database: %s", $this->dbhandler->connect_error);
    }

    public function updatingCalculationFields($data = [], $projectID = null) {
        if(!empty($data)){
            $date = new DateTime();
            $currentDate = $date->format("Y-m-d");
            $transData=[];
            $qry = "UPDATE data foo SET foo.field_data=?,foo.updated_on=? WHERE foo.field_id=? AND foo.account_id=?";
            for($i=0; $i<count($data);$i++)
            {
                if($fieldID = self::getShortNameIDByprojectID($data[$i][0], $projectID))
                        $transData[] = array($data[$i][1],$currentDate, $fieldID, $this->lastInsertId);
                        
            }
            
            
        $stmt = $this->dbhandler->prepare($qry);
        try
        {
            $this->dbhandler->beginTransaction();
            foreach ($transData AS $data){
                $stmt->execute($data);
            }
            $this->dbhandler->commit();
        }catch(Exception $e)
        {
            $this->dbhandler->rollBack();
            throw $e;
        }
        }
    }
    
    public function getShortNameIDByprojectID($shortKey=null, $projectID=null)
    {
        if(!empty($shortKey)){
            $param1= $shortKey;
            $param2= "% $shortKey";
            $param3= "$shortKey %";
            $param4= "% $shortKey %";
        }
        
        $qry = "SELECT `id` FROM `form_fields` WHERE `project_id`=:projID "
                . "AND short_name = :param1 OR short_name LIKE :param2 OR short_name LIKE :param3 OR short_name LIKE :param4";
        $paramArray = [
            ':projID'   => $projectID,
            ':param1'   => $param1,
            ':param2'   => $param2,
            ':param3'   => $param3,
            ':param4'   => $param4
        ];
        $stmt = $this->dbhandler->prepare($qry);
        $stmt->execute($paramArray);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            return $row['id'];
        }
        return false;
    }
    
    public function InsertingCalcPredFields($projectID=null)
    {
        $qry = "SELECT `short_name` FROM `form_fields` WHERE `project_id`=? AND `field_type` IN ('calculation', 'prediction')";
        $stmt = $this->dbhandler->prepare($qry);
        $stmt->execute([$projectID]);
        //$resArr = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $resArr[$row['short_name']] = "";
        }
        return (!empty($resArr)) ? $resArr : FALSE;
    }
    
    public function GetConsolidatedData($project_id=null, $svm_mark=1)
    {
        $qry = "SELECT
                form_fields.short_name,
                form_fields.field_value,
                project.project_name,
                `data`.user_id,
                `data`.field_data,
                `data`.field_id
                FROM
                form_fields
                INNER JOIN project ON form_fields.project_id = project.id
                INNER JOIN `data` ON `data`.field_id = form_fields.id
                INNER JOIN accounts ON `data`.account_id = accounts.id
                WHERE project.id=? AND accounts.id=? AND form_fields.svm=?";
        $exeArray = [$project_id, $this->lastInsertId, $svm_mark];
        $stmt = $this->dbhandler->prepare($qry);
        $stmt->execute($exeArray);
        while($rows = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $fieldData[] = [
                                        'id'    => $rows['field_id'],
                                        'short_name' => $rows['short_name'],
                                        'field_value'=> $rows['field_value'],
                                        'value'     => $rows['field_data']
            ];
        }
                        
                        return  (!empty($fieldData)) ? $fieldData : false;
    }
    
    public function updateCalculationResult($data=[], $projectID=null, $userId=null)
    {
        if(!empty($data)){
            $date = new DateTime();
            $currentDate = $date->format("Y-m-d");
            $qry = "UPDATE `data` foo SET foo.field_data=?,foo.updated_on=? WHERE foo.field_id=? AND foo.account_id=? AND foo.user_id=?";
            foreach ($data AS $key  => $val)
            {
                if($fieldId = self::getShortNameIDByprojectID($key, $projectID))
                        $transData[] = array($val,$currentDate, $fieldId, $this->lastInsertId,$userId);
                        
            }
        }
        
        /**
         * 
         * @param type $transData
         * Begin Transaction for field data update
         */    
        if(!empty($transData)){
            $stmt = $this->dbhandler->prepare($qry);
        try
        {
            $this->dbhandler->beginTransaction();
            foreach ($transData AS $data){
                $stmt->execute($data);
            }
            $this->dbhandler->commit();
        }catch(Exception $e)
        {
            $this->dbhandler->rollBack();
            throw $e;
        }
            
        }
        
        
    }
}
