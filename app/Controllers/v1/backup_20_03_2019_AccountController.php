<?php

namespace App\Controllers\v1;

use Interop\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;
use App\Models\Account;
use RR\Shunt\Parser;
use RR\Shunt\Context;
use vermotr\Math\Matrix;
use DateTime;
use RecursiveIteratorIterator;
use RecursiveArrayIterator;

class AccountController {

    protected $auth;
    protected $account;

    public function __construct(ContainerInterface $container, Account $accountModel) {
        $this->auth = $container->get('auth');
        $this->account = $accountModel;
    }

    /**
     * Return List of Accounts
     * @param \Slim\Http\Request    $request
     * @param \Slim\Http\Response   $response
     * @param array                 $args
     * 
     * @return \Slim\Http\Response
     */
    public function index(Request $request, Response $response, array $args) {

        $requestUser = $this->auth->requestUser($request);

        //$jwt = $request->getHeaders();
        //var_dump($requestUser);
        //var_dump($token);
        //die();
    }

    /**
     * Create an account
     * @param \Slim\Http\Request    $request
     * @param \Slim\Http\Response   $response
     * @return  \Slim\Http\Response
     */
    public function CreateAccount(Request $request, Response $response) {

        $requestUser = $this->auth->requestUser($request);
        if (is_null($requestUser)) {
            return $response->withJson([], 401);
        }
        $data = $request->getParsedBody();
        $project = $this->account->InsertAccount($data);
        if (!empty($project)) {
            var_dump($project);
        }
    }

    /**
     *  Analyze An Account 
     */
    public function AnalyzeAccount(Request $request, Response $response) {

        $requestUser = $this->auth->requestUser($request);


        if (is_null($requestUser)) {
            return $response->withJson([], 401);
        }
        $data = $request->getParsedBody();

        //Get the model first
        $modelVariables = array('income_type', 'emp_busi_type', 'loan_amt');
        /**
         * Selection of Projects start here
         * TIAp = [1=>Salary, 2=>SENP, 3=>Non-earning];
         * TEAp = [
         *         1=>Private/Partnership/Proprietorship firms in existence since less than 5 years,
         *         2=>Private/Partnership/Proprietorship firms having existence between 5-10 years and having employee strength less than 50,
         *         3=>Private firm having existence between 10-15 years and having employee strength more than 50,
         *         4=>Publicly held private companies / Semi Govt/Autonomous Bodies with existence more than 15,
         *         5=>Govt Organizations(Central/State/Local/Municipal bodies etc
         *         ];
         */
        $appType = '';
        $empType = '';
        
        $TIAP_array = self::recursiveFind($data, ['TIAp', 'TICoAp1', 'TICoAp2', 'TICoAp3', 'TICoAp4', 'TICoAp5']);
        $TEAP_array = self::recursiveFind($data, ['TEAp', 'TECoAp1', 'TECoAp2', 'TECoAp3', 'TECoAp4', 'TECoAp5']);
        $salaried = false;
        $senp = false;
        $combo = false;
        $gov = false;
        $non_gov = false;

        for ($i = 0; $i < count($TIAP_array); $i++) {
            if (!empty($TIAP_array[$i])) {
                $current = $TIAP_array[$i];
                $next = $TIAP_array[$i + 1];

                if (!empty($TIAP_array)) {
                    if ($current !== $next) {
                        $combo = true;
                        break;
                    }
                }

                if ($next == 1) {
                    $salaried = true;
                }
                if ($next == 2) {
                    $senp = true;
                }
            }
        }
        for ($j = 0; $j < count($TEAP_array); $j++) {
            if (!empty($TEAP_array[$i])) {
                if ($TEAP_array[$j] == '5') {
                    $gov = true;
                    break;
                }
            }
        }
        

        if ($salaried) {
            if ($gov) {
                $appType = 'Salaried';
                $empType = 'Goverment';
            } else {
                    $appType = 'Salaried';
                    $empType = 'Non Gov';
            }
        } else if ($senp) {
            $appType = 'SENP';
            $empType = 'Misc';
        } else {
            $appType = 'Combo';
            $empType = 'Misc';
        }

        $selectedModel = $this->account->getTypeofProject($data['message_code'], $appType, $empType, $data['LoanData']['LoanAmnt']);


        if (!empty($selectedModel)) {

            $formFields = $this->account->getProjectFormFields($selectedModel['project_id']);

            if (!empty($formFields)) {

                $inputParameters = self::custom_filter($data);

                /**
                 * We will Initialize account Data here
                 */
                $acct_res = $this->account->InitializeAccountData($inputParameters, $selectedModel['project_id'], $requestUser['id'], $data['message_code']);

                /**
                 * Getting Calculation(s) parameters ready
                 */
                $calculationParams = $this->account->getCalculationsParamters();



                /**
                 * Injecting part starts here all calculation paramters with their value availbale
                 */
                array_walk_recursive($calculationParams, 'self::changeVal', $inputParameters);
                $funcResult = [];

                /**
                 * Calculations Begin here
                 */
                if (!empty($calculationParams)) {
                    for ($i = 0; $i < count($calculationParams); $i++) {
                        foreach ($calculationParams[$i] AS $k => $v) {
                            //Calling Function Dynamically
                            //echo $k . "<br>";
                            switch ($k) {
                                case 'calculateIncomeEMI':
                                    $funcResult[] = self::calculateIncomeEMI($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateBankBalEMI':
                                    $funcResult[] = self::calculateBankBalEMI($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateSHApCoAp':
                                    $funcResult[] = self::calculateSHApCoAp($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateCDApCoAp':
                                    $funcResult[] = self::calculateCDApCoAp($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateNYBApCoAp':
                                    $funcResult[] = self::calculateNYBApCoAp($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateBPApCoAp':
                                    $funcResult[] = self::calculateBPApCoAp($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateTBApCoAp':
                                    $funcResult[] = self::calculateTBApCoAp($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateNYJApCoAp':
                                    $project_properties = $this->account->getProjectProperties($selectedModel['project_id']);
                                    $funcResult[] = self::calculateNYJApCoAp($calculationParams[$i], $project_properties['name']);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateTEApCoAp':
                                    $funcResult[] = self::calculateTEApCoAp($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateRelationsApCoAp':
                                    $funcResult[] = self::calculateRelationsApCoAp($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateTJApCoAp':
                                    $funcResult[] = self::calculateTJApCoAp($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateHQCoAp':
                                    $funcResult[] = self::calculateHQCoAp($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;
                                case 'calculateBSAApCoAp':
                                    $funcResult[] = self::calculateBSAApCoAp($calculationParams[$i]);
                                    //echo $k . "----" .  $i . '<br>';
                                    break;

                                default :
                                    echo "Finsihed Calculations at $i";
                            }
                        }
                    }
                }

                /**
                 * Updating Calculation Fields
                 */
//                print_r($calculationParams);
//                print_r($funcResult);
//                exit;
                if (!empty($funcResult))
                    $UpdateData = $this->account->updatingCalculationFields($funcResult, $selectedModel['project_id']);


                //Calculated fields result are complete

                /**
                 * Getting Project related data with Constraints
                 * @project_id
                 * @accounts_id
                 * @user_id
                 */
                /**
                 * Generating Field Data
                 * Here we Convert fields to 1XSVM length
                 */
                #Quering Database for Form Fields with Data
                #Constraints Are @project_id, @account_id, @svm_mark=1
                $fieldData = $this->account->GetConsolidatedData($selectedModel['project_id']);

                //Making a 1D array from this fields
                //Preg_matching content field value shold be between 0-9

                $consolidate_fields = array();
                for ($i = 0; $i < count($fieldData); $i++) {

                    if (preg_match('/^[0-9,]+$/i', $fieldData[$i]['field_value'], $matches)) {
                        $keywords = preg_split("/[,]+/", $matches[0]);
                        foreach ($keywords as $val) {
                            $actualData = ($val == $fieldData[$i]['value']) ? 1 : 0;
                            $ele = $fieldData[$i]['short_name'] . $val;
                            $consolidate_fields[$ele] = $actualData;
                        }

                        /**
                          //Changed due to calculated fields expansion in the current version
                          switch ($fieldData[$i]['short_name']) {

                          case 'QApcal':
                          # code...
                          for ($j = 1; $j <= 5; $j++) {
                          $actualData = ($j == $fieldData[$i]['value']) ? 1 : 0;
                          $ele = $fieldData[$i]['short_name'] . $j;
                          $consolidate_fields[$ele] = $actualData;
                          }
                          break;
                          case 'QCoApcal':
                          # code...
                          for ($j = 1; $j <= 5; $j++) {
                          $actualData = ($j == $fieldData[$i]['value']) ? 1 : 0;
                          $ele = $fieldData[$i]['short_name'] . $j;
                          $consolidate_fields[$ele] = $actualData;
                          }
                          break;

                          case 'EUloan':
                          # code...
                          for ($j = 1; $j <= 5; $j++) {
                          $actualData = ($j == $fieldData[$i]['value']) ? 1 : 0;
                          $ele = $fieldData[$i]['short_name'] . $j;
                          $consolidate_fields[$ele] = $actualData;
                          }
                          break;
                          case 'TJApCoAp':
                          # code...
                          for ($j = 1; $j <= 5; $j++) {
                          $actualData = ($j == $fieldData[$i]['value']) ? 1 : 0;
                          $ele = $fieldData[$i]['short_name'] . $j;
                          $consolidate_fields[$ele] = $actualData;
                          }
                          break;
                          case 'TEApCoAp':
                          # code...
                          for ($j = 1; $j <= 5; $j++) {
                          $actualData = ($j == $fieldData[$i]['value']) ? 1 : 0;
                          $ele = $fieldData[$i]['short_name'] . $j;
                          $consolidate_fields[$ele] = $actualData;
                          }
                          break;
                          case 'TBApCoAp':
                          # code...
                          for ($j = 1; $j <= 5; $j++) {
                          $actualData = ($j == $fieldData[$i]['value']) ? 1 : 0;
                          $ele = $fieldData[$i]['short_name'] . $j;
                          $consolidate_fields[$ele] = $actualData;
                          }
                          break;
                          case 'BPApCoAp':
                          # code...
                          for ($j = 1; $j <= 5; $j++) {
                          $actualData = ($j == $fieldData[$i]['value']) ? 1 : 0;
                          $ele = $fieldData[$i]['short_name'] . $j;
                          $consolidate_fields[$ele] = $actualData;
                          }
                          break;
                          case 'RELApCoAp':
                          # code...
                          for ($j = 1; $j <= 5; $j++) {
                          $actualData = ($j == $fieldData[$i]['value']) ? 1 : 0;
                          $ele = $fieldData[$i]['short_name'] . $j;
                          $consolidate_fields[$ele] = $actualData;
                          }
                          break;
                          default:
                          # code...
                          $consolidate_fields[$fieldData[$i]['short_name']] = $fieldData[$i]['value'];
                          }
                         * */
                    } else {
                        $consolidate_fields[$fieldData[$i]['short_name']] = $fieldData[$i]['value'];
                    }
                }

                /**
                 * Error Logging for Later
                 */
                //Create two matrix from SVM Table and Input array
                //WE suppose project fields are always greater than svm fields
                //Get keys of the consolidated fields
                $consolidate_keys = array_keys($consolidate_fields);
                $svm_matrix_titles = $this->account->getSVMTitles($selectedModel['project_id']);
                $svm_keys = array_keys($svm_matrix_titles);

                for ($i = 0; $i < count($consolidate_keys); $i++) {

                    for ($j = 2; $j < count($svm_matrix_titles); $j++) {
                        if (stristr($consolidate_keys[$i], $svm_keys[$j]) !== false) {
                            //echo 'Match key are '. $consolidate_keys[$i] . ' With ' . $svm_keys[$j] . ' at Outer '. $i . ' Inner '. $j . "<br>";
                            $secondArr[$svm_keys[$j]] = $consolidate_fields[$consolidate_keys[$i]];
                        }
                    }
                }

                /**
                 * Rearranging keys to match svm excel order
                 */
                //$sorted = array_merge(array_flip($svm_keys), $secondArr);

                uksort($secondArr, function($key1, $key2) use ($svm_keys) {
                    return (array_search($key1, $svm_keys) > array_search($key2, $svm_keys));
                });

                //Getting the SVM constants
                $svm_constants = $this->account->getSVMConstant($selectedModel['project_id']);

                //Showing Dataset before riskScaling
                //print_r($secondArr);
                //Showing Risk Scaling Constants
                //print_r($svm_constants);
                //After Re-scalling
                foreach ($secondArr as $inputkey => $inputArr) {
                    foreach ($svm_constants as $constkey => $const) {
                        if (stristr($inputkey, $constkey) !== false) {
                            if (is_array($const)) {
                                for ($i = 0; $i < count($const); $i++) {

                                    if ($i == 0) {

                                        $result = ($inputArr - $const[$i]);
                                    } else {
                                        $result = ($result / $const[$i]);
                                    }
                                }
                                $secondArr[$inputkey] = $result;
                            }
                        }
                    }
                }
                /**
                 * Re-arranging data
                 */
                $arr = [];
                $i = 0;
                foreach ($secondArr AS $s) {
                    $arr[$i][] = $s;
                }

                //Get the project Properties
                $projectParams = $this->account->getProjectProperties($selectedModel['project_id']);
                if (is_null($projectParams)) {
                    return $response->withJson(['invalid model properties'], 407);
                }
                if (count($arr[0]) != $projectParams['col']) {
                    return $response->withJson(['SVM column and User Input not matched'], 401);
                }
                //Get SVM co-efficients
                $svmVals = $this->account->getSVMValues($selectedModel['project_id']);
                if (is_null($svmVals)) {
                    return $response->withJson(['invalid SVM coefficient'], 405);
                }
                $firstArray = [];
                for ($row_index = 0; $row_index < count($svmVals); $row_index++) {

                    for ($col_index = 2; $col_index < count($svmVals[$row_index]); $col_index++) {
                        $cur_col_index = ($col_index - 2);
                        $firstArr[$row_index][$cur_col_index] = $svmVals[$row_index][$col_index];
                    }
                }

                $matrixA = new Matrix($firstArr);
                $matrixB = new Matrix($arr);
                $matrixBT = $matrixB->transpose();
                $product_of_AB = $matrixA->multiply($matrixBT);

                //Getting YiAlphai from svm table
                //Product of (Yixalphai)
                $yalpha = array();
                for ($row_index = 0; $row_index < count($svmVals); $row_index++) {

                    for ($col_index = 0; $col_index < 2; $col_index++) {

                        if ($col_index == 1)
                        // echo 'At row '. $row_index . ' At col index '. $col_index . ' Value ' . $rows[$row_index][0]. 'X' .$rows[$row_index][1] . "<br>";
                            $yalpha[0][] = ($svmVals[$row_index][0] * $svmVals[$row_index][1]);
                    }
                }

                $matrix_YAlpha = new Matrix($yalpha);
                /**
                 * Multiply Yi and alphai with 
                 */
                $beforeBiasProduct = $matrix_YAlpha->multiply($product_of_AB);
                $addBias = ($beforeBiasProduct[0][0] + $projectParams['bias']);


                /**
                 * Calculating Regression
                 */
                $regArray = $this->account->getRegressionValues($selectedModel['project_id']);

                if (is_null($regArray)) {
                    return $response->withJson(['invalid regression coeficient'], 406);
                }
                $filterRegArray = [];
                for ($i = 0; $i < count($regArray); $i++) {
                    if ($i == 1)
                        continue;
                    for ($j = 0; $j < count($regArray[$i]); $j++) {
                        $filterRegArray[$regArray[$i][$j]] = (!empty($regArray[$i + 1][$j])) ? $regArray[$i + 1][$j] : 0;
                    }
                }


                $reg_keys = array_keys($filterRegArray);
                $multipliedArrReg = [];
                for ($i = 1; $i < count($reg_keys); $i++) {

                    for ($j = 0; $j < count($consolidate_keys); $j++) {
                        if (stristr($reg_keys[$i], $consolidate_keys[$j]) !== false) {
                            //echo 'Match key are '. $consolidate_keys[$j] . ' With ' . $reg_keys[$i] . ' at Outer '. $i . ' Inner '. $j . "<br>";
                            $multipliedArrReg[$reg_keys[$i]] = ($consolidate_fields[$consolidate_keys[$j]] * $filterRegArray[$reg_keys[$i]]);
                        }
                    }
                }
                $output = 0;
                //Adding All multiplied values
                foreach ($multipliedArrReg AS $arrEle) {
                    $output += $arrEle;
                }

                //Adding B0 Value separately
                $output = ($filterRegArray['B0'] + $output);

                //Predicted Status of loan
                $pred = (1 / (1 + exp(-($output))));
                $date = new DateTime();
                $currentDate = $date->format("Y-m-d H:i:s");

                $status = ($pred > 0.5 && $addBias < 0) ? 'Low Risk' : (($pred < 0.5 && $addBias > 0) ? 'High Risk' : 'Medium Risk');
                $svmFinal = $addBias;
                $lrFinal = $pred;
                /**
                 * Updating Calculation results to db
                 * @status
                 * @svmFinal
                 * @lrFinal
                 */
                $updateData = [
                    "PNPASVM" => $status,
                    "PSVM" => $svmFinal,
                    "PNPALR" => $lrFinal,
                    "DtP" => $currentDate
                ];

                $updateFields = $this->account->updateCalculationResult($updateData, $selectedModel['project_id'], $requestUser['id']);
                //Getting Account Info
                $acctInfo = $this->account->getAccountInfo($selectedModel['project_id']);

                //Generating response
                $rsp = array(
                    'error' => false,
                    'LAN' => $acctInfo->comapny_name,
                    'testedBy' => $acctInfo->email,
                    'analyzedOn' => $currentDate,
                    'PNPALR' => $lrFinal,
                    'PNPASVM' => $status,
                    'PSVM' => $svmFinal
                );
                return $response
                                ->withStatus(200)
                                ->withJson($rsp);
            }
        }
    }

    public function Calculatedfields($formData = array()) {
        if (!empty($formData)) {
            //print_r($formData);
            //Calling Database for project Related form Settings
            $myData = $this->account->getProjectFormFields($formData['project_id']);
            $myManipulate = array();
            for ($i = 0; $i < count($myData); $i++) {
                foreach ($formData as $k => $v) {
                    if ($myData[$i]['id'] == $k) {
                        if (!empty($v)) {

                            $myManipulate[$myData[$i]['SN']] = $v;
                        } else {
                            if ($v == 0) {
                                if ($myData[$i]['type'] == 'text') {
                                    $myManipulate[$myData[$i]['SN']] = 0;
                                } elseif ($myData[$i]['type'] == 'dropdown') {
                                    $myManipulate[$myData[$i]['SN']] = 1;
                                }
                            }
                        }
                    }
                }
            }
//print_r($myManipulate);
            return $myManipulate;
        }
    }

    private static function changeVal(&$v, $key, $mydata) {
        foreach ($mydata as $k => $val) {

            if ($key == $k) {
                $v = $val;
            }
        }
    }

    private static function calculateTJApCoAp($params = []) {
        $resultCategory = 0;
        $resultTJApCoAp = 0;
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateTJApCoAp') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 0; $i < count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0
                                    if ($i == 0) {

                                        $result[] = (!empty($val['TJAp'])) ? $val['TJAp'] : 0;
                                    } else {
                                        $result[] = (!empty($val["TJCoAp$i"])) ? $val["TJCoAp$i"] : 0;
                                    }
                                }
                            }
                            if ($inp == 'is_cat' && !empty($val)) {
                                $resultCategory = $val['categorical'];
                            }
                        }
                    }
                }
            }
            $resultTJApCoAp = (max($result)) ? max($result) : $resultCategory;
        }
        return array($derivedField, $resultTJApCoAp);
        //return $result;
    }

    private static function calculateHQCoAp($params = []) {
        $resultCategory = 0;
        $resultTJApCoAp = 0;
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateTJApCoAp') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 0; $i < count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0
                                    if ($i == 0) {

                                        $result[] = (!empty($val['TJAp'])) ? $val['TJAp'] : 0;
                                    } else {
                                        $result[] = (!empty($val["TJCoAp$i"])) ? $val["TJCoAp$i"] : 0;
                                    }
                                }
                            }
                            if ($inp == 'is_cat' && !empty($val)) {
                                $resultCategory = $val['categorical'];
                            }
                        }
                    }
                }
            }
            $resultTJApCoAp = (max($result)) ? max($result) : $resultCategory;
        }
        return array($derivedField, $resultTJApCoAp);
        //return $result;
    }

    private static function calculateBSAApCoAp($params = []) {
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateBSAApCoAp') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 0; $i < count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0

                                    if ($i == 0) {
                                        $result[] = (!empty($val['BSAAp'])) ? $val['BSAAp'] : 0;
                                    } else {
                                        $result[] = (!empty($val["BSACoAp$i"])) ? $val["BSACoAp$i"] : 0;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return array($derivedField, min(array_filter($result)));
        //return $result;
    }

    private static function calculateCDApCoAp($params = []) {
        $resultCategory = 0;
        $resultCDApCoAp = 0;
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateCDApCoAp') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 1; $i <= count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0


                                    $result[] = (!empty($val["CDCoAp$i"])) ? $val["CDCoAp$i"] : 0;
                                }
                            }
                            if ($inp == 'is_cat' && !empty($val)) {
                                $resultCategory = $val['categorical'];
                            }
                        }
                    }
                }
            }
            $resultCDApCoAp = (max($result)) ? max($result) : $resultCategory;
        }
        return array($derivedField, $resultCDApCoAp);
        //return $result;
    }

    private static function calculateNYBApCoAp($params = []) {
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateNYBApCoAp') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 0; $i < count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0
                                    if ($i == 0) {

                                        $result[] = (!empty($val['NYBAp'])) ? $val['NYBAp'] : 0;
                                    } else {
                                        $result[] = (!empty($val["NYBCoAp$i"])) ? $val["NYBCoAp$i"] : 0;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return array($derivedField, max($result));
        //return $result;
    }

    private static function calculateBPApCoAp($params = []) {
        $resultCategory = 0;
        $resultBPApCoAp = 0;
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateBPApCoAp') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 0; $i < count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0
                                    if ($i == 0) {
                                        $result[] = (!empty($val["BPAp"])) ? $val["BPAp"] : 0;
                                    } else {
                                        $result[] = (!empty($val["BPCoAp$i"])) ? $val["BPCoAp$i"] : 0;
                                    }
                                }
                            }

                            if ($inp == 'is_cat' && !empty($val)) {
                                $resultCategory = $val['categorical'];
                            }
                        }
                    }
                }
            }
            $resultBPApCoAp = (max($result)) ? max($result) : $resultCategory;
        }
        return array($derivedField, $resultBPApCoAp);
        //return $result;
    }

    private static function calculateTBApCoAp($params = []) {
        $resultCategory = 0;
        $resultTBApCoAp = 0;

        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateTBApCoAp') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 0; $i < count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0
                                    if ($i == 0) {
                                        $result[] = (!empty($val["TBAp"])) ? $val["TBAp"] : 0;
                                    } else {
                                        $result[] = (!empty($val["TBCoAp$i"])) ? $val["TBCoAp$i"] : 0;
                                    }
                                }
                            }
                            if ($inp == 'is_cat' && !empty($val)) {
                                $resultCategory = $val['categorical'];
                            }
                        }
                    }
                }
            }
            $resultTBApCoAp = (max($result)) ? max($result) : $resultCategory;
        }
        return array($derivedField, $resultTBApCoAp);
        //return $result;
    }

    private static function calculateNYJApCoAp($params = [], $project_name = null) {
        $avgNYJApCoAp = 0;
        $add_NYJAp_NYJCoAp = 0;
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateNYJApCoAp') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 0; $i < count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0
                                    if ($i == 0) {
                                        $result[] = (!empty($val["NYJAp"])) ? $val["NYJAp"] : 0;
                                    } else {
                                        $result[] = (!empty($val["NYJCoAp$i"])) ? $val["NYJCoAp$i"] : 0;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($project_name == 'Project B' || $project_name == 'Project C') {
            for ($i = 0; $i < count($result); $i++) {
                $add_NYJAp_NYJCoAp += $result[$i];
                if ($i >= 2)
                    break;
            }
            $avgNYJApCoAp = ($add_NYJAp_NYJCoAp / 2);
            return array($derivedField, $avgNYJApCoAp);
        }

        return array($derivedField, max($result));
        //return $result;
    }

    private static function calculateTEApCoAp($params = []) {
        $resultCategory = 0;
        $resultTEApCoAp = 0;
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateTEApCoAp') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 0; $i < count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0
                                    if ($i == 0) {
                                        $result[] = (!empty($val["TEAp"])) ? $val["TEAp"] : 0;
                                    } else {
                                        $result[] = (!empty($val["TECoAp$i"])) ? $val["TECoAp$i"] : 0;
                                    }
                                }
                            }
                            if ($inp == 'is_cat' && !empty($val)) {
                                $resultCategory = $val['categorical'];
                            }
                        }
                    }
                }
            }
            $resultTEApCoAp = (max($result)) ? max($result) : $resultCategory;
        }
        return array($derivedField, $resultTEApCoAp);
        //return $result;
    }

    private static function calculateRelationsApCoAp($params = []) {
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateRelationsApCoAp') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 1; $i <= count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0


                                    $result[] = (!empty($val["RELCoAp$i"])) ? $val["RELCoAp$i"] : 0;
                                }
                            }
                        }
                    }
                }
            }
        }
        return array($derivedField, max($result));
        //return $result;
    }

    private static function calculateSHApCoAp($params = []) {
        $pfApCoApTotal = 0;
        $nasApCoApTotal = 0;
        $naiApCoApTotal = 0;
        $resultShApCoap = 0;
        $result = [];
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateSHApCoAp') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 0; $i < count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0

                                    if ($i == 0) {
                                        $pfAp = (!empty($val[$i]['PFAp'])) ? $val[$i]['PFAp'] : 0;
                                        $nasAp = (!empty($val[$i]['NASAp'])) ? $val[$i]['NASAp'] : 0;
                                        $naiAp = (!empty($val[$i]['NAIAp'])) ? $val[$i]['NAIAp'] : 0;
                                        $pfApCoApTotal += $pfAp;
                                        $nasApCoApTotal += $nasAp;
                                        $naiApCoApTotal += $naiAp;
                                        /**
                                         * For Debug Purpose
                                         */
                                        // $result[] = "AT AP--" . $i ."(" . $pfApCoApTotal . ") / (" . $nasApCoApTotal . "+". $naiApCoApTotal .")";
                                    } else {
                                        $pfCoAp = (!empty($val[$i]["PFCoAp$i"])) ? $val[$i]["PFCoAp$i"] : 0;
                                        $nasCoAp = (!empty($val[$i]["NASCoAp$i"])) ? $val[$i]["NASCoAp$i"] : 0;
                                        $naiCoAp = (!empty($val[$i]["NAICoAp$i"])) ? $val[$i]["NAICoAp$i"] : 0;
                                        $pfApCoApTotal += $pfCoAp;
                                        $nasApCoApTotal += $nasCoAp;
                                        $naiApCoApTotal += $naiCoAp;
                                        /**
                                         * For Debug Purpose
                                         */
                                        //$result[] = "AT AP--" . $i ."(" . $pfApCoApTotal . ") / (" . $nasApCoApTotal . "+". $naiApCoApTotal .")";
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (($nasApCoApTotal + $naiApCoApTotal) === 0) {
                $resultShApCoap = 99;
            } else {
                $resultShApCoap = ($pfApCoApTotal / ($nasApCoApTotal + $naiApCoApTotal)) * 100;
            }
            $resultShApCoap = ($resultShApCoap !== 0) ? $resultShApCoap : 1;
        }
        //return $resultShApCoap;
        return array($derivedField, $resultShApCoap);
    }

    private static function calculateBankBalEMI($params = []) {
        $bankBalanceTotal = 0;
        $emiApCoApTotal = 0;
        $resultBankBalEmi = 0;
        $result = [];
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateBankBalEMI') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 0; $i < count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0

                                    if ($i == 0) {
                                        $balAp = (!empty($val[$i]['BalAp'])) ? $val[$i]['BalAp'] : 0;
                                        $emiAp = (!empty($val[$i]['EMIAp'])) ? $val[$i]['EMIAp'] : 0;
                                        $bankBalanceTotal += $balAp;
                                        $emiApCoApTotal += $emiAp;
                                        /**
                                         * For Debug Purpose
                                         */
                                        //$result[] = "AT AP--" . $i . "(" . $bankBalanceTotal . "/" . $emiApCoApTotal . ")";
                                    } else {
                                        $balCoap = (!empty($val[$i]["BalCoAp$i"])) ? $val[$i]["BalCoAp$i"] : 0;
                                        $emiCoap = (!empty($val[$i]["EMICoAp$i"])) ? $val[$i]["EMICoAp$i"] : 0;
                                        $bankBalanceTotal += $balCoap;
                                        $emiApCoApTotal += $emiCoap;
                                        /**
                                         * For Debug Purpose
                                         */
                                        //$result[] = "AT CoAP--" . $i . "(" . $bankBalanceTotal . "/" . $emiApCoApTotal . ")";
                                    }
                                }
                            }
                        }
                    }//
                }
            }
            if ($emiApCoApTotal === 0) {
                $resultBankBalEmi = 99;
            } else {
                $resultBankBalEmi = ($bankBalanceTotal / $emiApCoApTotal);
            }
            $resultBankBalEmi = ($resultBankBalEmi !== 0) ? $resultBankBalEmi : 1;
        }
        //return $resultBankBalEmi;
        return array($derivedField, $resultBankBalEmi);
        //return $result;
    }

    private static function calculateIncomeEMI($params = []) {
        $nasap_naiap = 0;
        $emiap_coap = 0;
        $totalIncome = 0;
        if (!empty($params)) {
            foreach ($params AS $cal => $param) {
                if ($cal == 'calculateIncomeEMI') {
                    //You can proceed
                    if (is_array($param)) {
                        foreach ($param AS $inp => $val) {
                            if ($inp == 'derivedField')
                                $derivedField = $val;
                            if ($inp == 'is_score')
                                $is_score_considered = $val;
                            if ($inp == 'inputs' && is_array($val)) {
                                for ($i = 0; $i < count($val); $i++) {
                                    //Building Combination
                                    //Applicant index=>0

                                    if ($i == 0) {
                                        $nasap = (!empty($val[$i]['NASAp'])) ? $val[$i]['NASAp'] : 0;
                                        $naiap = (!empty($val[$i]['NAIAp'])) ? $val[$i]['NAIAp'] : 0;
                                        $emiap = (!empty($val[$i]['EMIAp'])) ? $val[$i]['EMIAp'] : 0;
                                        $nasap_naiap += $nasap + $naiap;
                                        $emiap_coap += $emiap;
                                        /**
                                         * For Debug Purpose
                                         */
                                        $result[] = "AT AP--" . $i . "(" . $nasap_naiap . "/" . $emiap_coap . ")";
                                    } else {
                                        $nasCoap = (!empty($val[$i]["NASCoAp$i"])) ? $val[$i]["NASCoAp$i"] : 0;
                                        $naiCoap = (!empty($val[$i]["NAICoAp$i"])) ? $val[$i]["NAICoAp$i"] : 0;
                                        $emiCoap = (!empty($val[$i]["EMICoAp$i"])) ? $val[$i]["EMICoAp$i"] : 0;
                                        $nasap_naiap += $nasCoap + $naiCoap;
                                        $emiap_coap += $emiCoap;
                                        /**
                                         * For Debug Purpose
                                         */
                                        $result[] = "AT CoAP--" . $i . "(" . $nasap_naiap . "/" . $emiap_coap . ")";
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /**
             * Handing Devide by Zero
             */
            if ($emiap_coap === 0) {
                $totalIncome = 99;
            } else {

                $totalIncome = (($nasap_naiap / $emiap_coap) * 100);
            }
            $totalIncome = ($totalIncome !== 0) ? $totalIncome : 1;
        }
        //return $totalIncome;
        return array($derivedField, $totalIncome);
        //return $result;
    }

    public function array_flatten($array) {

        if (!is_array($array)) {
            return FALSE;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, array_flatten($value));
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    private static function custom_filter($array) {
        $temp = [];
        array_walk($array, function($item, $key) use (&$temp) {

            if (is_array($item))
                foreach ($item as $k => $value)
                    $temp[$k] = $value;
        });
        return $temp;
    }

    private static function recursiveFind(array $array, $needle) {
        $iterator = new \RecursiveArrayIterator($array);
        $recursive = new \RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        $aHitList = array();
        foreach ($recursive as $key => $value) {
            if (in_array($key, $needle)) {
                array_push($aHitList, $value);
            }
        }
        return $aHitList;
    }

}
