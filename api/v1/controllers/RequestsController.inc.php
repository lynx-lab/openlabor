<?php

class RequestsController extends openLaborController {
    
    /**
     * 
     * @abstract jobs search
     * @param type $request
     * @eturn $jobResult
     */
    public function get001Action($request,$format,$url_parameters) {
//        print_r($request);
//    public function getActionSearch($request) {
        require_once(__DIR__ .'/../include/config.inc.php');
//        echo "<br />Siamo nel posto giusto (getActionSearch): ";
//        print_r($request);
        if (!isset($request['jobID'])) {
            $toSearch = array();
            $toSearch['keywords'] = $request['keywords'];
            $toSearch['cityCompany'] = $request['city'];
            $toSearch['qualificationRequired'] = $request['qualification'];
            $jobResult = $this->searchJobs($toSearch,'cityCompany');
            $NumResult = count($jobResults);
            
        } else {
            $jobResult = $this->getJob($request['jobID']);
        }
        $this->LogRequest($_REQUEST,$_SERVER,$NumResult);
        if ($format == 'xml') {
                $jobResult = openLaborController::array2xml($jobResult,'job');
                $jobResult= str_replace('&', '&amp;',$jobResult);
//                print_r($jobResult);
                header ("Content-type: text/xml");
            } else {
            $jobResult = json_encode($jobResult);
        }
        echo $jobResult;
    }

    /**
     *  @abstract job report
     *  @param array $request contains data of job report
     *  @param string $format contains type of format (xml, json)
     *  @param 
     */
    public function post002Action($dataAr,$format,$url_parameters) {
//        print_r($request);
        require_once(__DIR__ .'/../include/config.inc.php');
//        echo "<br />Siamo nel posto giusto (getActionSearch): ";
//        print_r($dataAr);
//        $dataAr['professionalProfile'] = 'informatico';
//        print_r(array('son qui',$professionalProfile));
//        print_r($dataAr);
        $dataAr = $this->dataValidator($dataAr);
        $this->LogReport($_REQUEST,$_SERVER,$dataAr['position']);

//        echo $dataAr['position'];
        if ($dataAr['position'] != false && ($dataAr['positionCode'] == '' || $dataAr['positionCode'] == false))  {
            $dataAr['positionCode'] = $this->getJobCode($dataAr['position']);
        }
//        print_r($jobResult['job_types']['categories'][0]['category']);
        $InsertId = $this->reportJob($dataAr);
        if ($format == 'xml') {
                $jobResult = openLaborController::array2xml($jobResult,'job');
                $jobResult= str_replace('&', '&amp;',$jobResult);
//                print_r($jobResult);
                header ("Content-type: text/xml");
            } else {
            $idResult = json_encode($InsertId);
        }
        echo $idResult;
    }        

    
      
    public function getJob($jobId) {
        $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
        $dh = $GLOBALS['dh'];
        $jobOffer = $dh->getJobFromId($jobId);
//        $jobOfferJson = json_encode($jobOffer);
        return $jobOffer;
        //echo $jobOfferJson;
    }    
    
    /**
     * @abstract return the code of position
     * @param string $position
     * @return string $positionCode
     * 
     */
    public function getJobCode($position) {
        $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
        $dh = $GLOBALS['dh'];
        
        $urlSemanticApi = URL_LAVORI4.$position;
        $keywords = $position;
        //$curlHeader = array("Content-Type: application/x-www-form-urlencoded");
        $curlHeader = '';
        $jobResult = REST_request::sendRequest($keywords,$curlHeader,$urlSemanticApi,$curlPost);
        $jobResult = json_decode($jobResult,TRUE);
        $positionCode = $jobResult['job_types']['categories'][0]['category'];        
//        return $jobsCode['job_types']['categories'][0]['category'];
        return $positionCode;
    }
    
    /**
     * 
     * @abstract add job reported to table jobs
     *           if positionCode is not a valid code it ask to semantic api to have the right code
     *          
     * @param array $dataAr
     */
    public function reportJob($dataAr=array()) {
        $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
        $dh = $GLOBALS['dh'];
        
        if ($dataAr['positionCode'] == '') {
            $urlSemanticApi = URL_LAVORI4.$dataAr['professionalProfile'];
            $keywords = $dataAr['professionalProfile'];
            //$curlHeader = array("Content-Type: application/x-www-form-urlencoded");
            $curlHeader = '';
            $jobsCode = REST_request::sendRequest($keywords,$curlHeader,$urlSemanticApi,$curlPost);
            //$resultAR = json_decode($jobsCode, TRUE);
        }
        $InsertId = $dh->addJobOffer($dataAr);
        return $InsertId;
        
    }
    
     /**
      * @abstract search engine
      * 
      * @param $toSearch
      * @param $keyMandatory
      * @return $jobOffers
      */
    public function searchJobs($toSearch=array(),$keyMandatory=null) {
        $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
        $dh = $GLOBALS['dh'];
        
        $today_date = today_dateFN();
        $todayUT = Abstract_AMA_DataHandler::date_to_ts($today_date);

        $clause = 'where jobexpiration >= '.$todayUT;
        
        /**
         * case 1: keywords set, professional code match with keywords
         *          look for other parameters
         * case 2: keywords set, no professional code match with keywords
         *          no look for other parameters
         * case 3: keywords not set
         *          look for other parameters
         * 
         */
        
        $foundKeywords = true;
        if ($toSearch['keywords'] != '' && $toSearch['keywords'] != null) {

            $foundKeywords = false;

            $curlPost = false;
            $urlSemanticApi = URL_LAVORI4.$toSearch['keywords'];
            $keywords = $toSearch['keywords'];
            //$curlHeader = array("Content-Type: application/x-www-form-urlencoded");
            $curlHeader = '';
            $jobsCode = REST_request::sendRequest($keywords,$curlHeader,$urlSemanticApi,$curlPost);
            
            $resultAR = json_decode($jobsCode, TRUE);
            if (count($resultAR) > 0) {
                $professionalCodes = $resultAR['job_types']['categories'];
                if (count($professionalCodes) > 0) {
                    $foundKeywords = true;
                    
                    $clause .= ' AND ';

                    $numberCode = NUMBER_CODE;
                    if ((count($professionalCodes))  <= NUMBER_CODE) {
                        $numberCode = count($professionalCodes);
                    }
                    $clauseKey = NULL;
                    
                    /**
                     * @todo find the way to order the results by professional code order in the array $professionalCode
                     */
                    for ($i=0; $i<$numberCode;$i++) {
                        $professionalCode = $professionalCodes[$i]['category'];
                        switch ($i) {
                            case 0:
                                $clauseKey = '(positionCode like \''.$professionalCode.'%\'';
                                break;
                            /*
                            case $numberCode:
                                $clause .= ' OR positionCode like \''.$professionalCode.'%\')';
                                break;
                             * 
                             */
                            default:
                                $clauseKey .= ' OR positionCode like \''.$professionalCode.'%\'';
                                break;
                        }
                    }
                    if ($clauseKey != NULL) {
                        $clause .= ' '. $clauseKey.')';
                    }
                }
                
            }
        }
        if ($foundKeywords) {
            
            if ($toSearch['cityCompany'] != '' && $toSearch['cityCompany'] != null) {
                $clause .= ' AND cityCompany = \''. $toSearch['cityCompany'].'\'';
            }

            if ($toSearch['qualificationRequired'] != '' && $toSearch['qualificationRequired'] != null) {
                $clause .= ' AND '. constant($toSearch['qualificationRequired']);
            }        

            $jobOffers = $dh->listOffers($clause);
        }
        return $jobOffers;
    }
    /**
     * @abstract validate data sended by user
     * @param array $dataAr contains the data POSTED
     * @return array  $dataAr contains the data validated
     */
    private function dataValidator($dataAr) {
        $dataAr['api_key'] = DataValidator::validate_string($dataAr['api_key']);
        $dataAr['jurisdiction_id'] = DataValidator::validate_string($dataAr['jurisdiction_id']);
        $dataAr['locale'] = DataValidator::validate_string($dataAr['locale']);
        $dataAr['professionalProfile'] = DataValidator::validate_string($dataAr['professionalProfile']);
        $dataAr['positionCode'] = DataValidator::validate_string($dataAr['positionCode']);
        $dataAr['position'] = DataValidator::validate_string($dataAr['position']);
        $dataAr['workersRequired'] = DataValidator::is_uinteger($dataAr['workersRequired']);
        $dataAr['jobExpiration'] = DataValidator::is_uinteger($dataAr['jobExpiration']);
        $dataAr['qualificationRequired'] = DataValidator::validate_string($dataAr['qualificationRequired']);
        $dataAr['descriptionQualificationRequired'] = DataValidator::validate_string($dataAr['descriptionQualificationRequired']);
        $dataAr['professionalTrainingRequired'] = DataValidator::validate_string($dataAr['professionalTrainingRequired']);
        $dataAr['address'] = DataValidator::validate_string($dataAr['address']);
        $dataAr['zipCode'] = DataValidator::validate_string($dataAr['zipCode']);
        $dataAr['cityCompany'] = DataValidator::validate_string($dataAr['cityCompany']);
        $dataAr['nation'] = DataValidator::validate_string($dataAr['nation']);
        $dataAr['experienceRequired'] = DataValidator::validate_string($dataAr['experienceRequired']);
        $dataAr['durationExperience'] = DataValidator::is_uinteger($dataAr['durationExperience']);
        $dataAr['minAge'] = DataValidator::is_uinteger($dataAr['minAge']);
        $dataAr['maxAge'] = DataValidator::is_uinteger($dataAr['maxAge']);
        $dataAr['remuneration'] = DataValidator::is_uinteger($dataAr['remuneration']);
        $dataAr['rewards'] = DataValidator::validate_string($dataAr['rewards']);
        $dataAr['reservedForDisabled'] = $dataAr['reservedForDisabled']; //boolean type 
        $dataAr['favoredCategoryRequests'] = DataValidator::validate_string($dataAr['favoredCategoryRequests']);
        $dataAr['ownVehicle'] = $dataAr['ownVehicle']; //boolean type 
        $dataAr['notes'] = DataValidator::validate_string($dataAr['notes']);
        $dataAr['linkMoreInfo'] = DataValidator::validate_url($dataAr['linkMoreInfo']);
        $dataAr['j_latitude'] = DataValidator::validate_string($dataAr['j_latitude']);
        $dataAr['j_longitude'] = DataValidator::validate_string($dataAr['j_longitude']);
        $dataAr['source'] = DataValidator::validate_string($dataAr['sourceJob']);
        unset($dataAr['sourceJob']);
        $dataAr['media_url'] = DataValidator::validate_url($dataAr['media_url']);
        $dataAr['sourceJobName'] = DataValidator::validate_string($dataAr['sourceJobName']);
        $dataAr['sourceJobSurname'] = DataValidator::validate_string($dataAr['sourceJobSurname']);
        $dataAr['published'] = $dataAr['published']; //boolean type 
        return $dataAr;

    } 

    public function getActionSimple($request) {
        echo "<br />Siamo nel posto giusto: $request";
        $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
        $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
        $cpiAr = $cpiObj->contents;
        $remoteJobs->search_data($toSearch,'ComuneAzienda',$cpiAr);
    }

    public function postAction($request) {
//        print_r($request);
        $this->LogReport($_REQUEST,$_SERVER,'postAction');
        echo "postAction<br />Siamo nel posto giusto ". $request;
        //print_r($request);
        $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
        $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
        $cpiAr = $cpiObj->contents;
        $remoteJobs->search_data($toSearch,'ComuneAzienda',$cpiAr);
    }
      
}

/*
        $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
        $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
        $cpiAr = $cpiObj->contents;
        $remoteJobs->search_data($toSearch,'ComuneAzienda',$cpiAr);
//        print_r($remoteJobs->results);
 * 
 */



?>
