<?php

class RequestsController extends openLaborController {
    
    /**
     * 
     * class per jobs search
     * @param type $request
     * @eturn $jobResult
     */
    public function get001Action($request,$format='xml',$url_parameters) {
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

        /*
         * view result in correct format
         */
        if ($format == 'xml') {
            $jobResult = openLaborController::array2xml($jobResult,'job');
            $jobResult= str_replace('&', '&amp;',$jobResult);
            header ("Content-type: text/xml");
        } else {
            header('Content-Type: application/json; charset=utf8');
            $jobResult = json_encode($jobResult);
        }
        echo $jobResult;
    }

    /**
     *  method to job report
     *  @param array $request contains data of job report
     *  @param string $format contains type of format (xml, json)
     *  @param 
     */
    public function post002Action($dataAr,$format) {
        require_once(__DIR__ .'/../include/config.inc.php');

        $dataAr = $this->dataValidator($dataAr);
        $this->LogReport($_REQUEST,$_SERVER,$dataAr['position']);

        if ($dataAr['position'] != false && ($dataAr['positionCode'] == '' || $dataAr['positionCode'] == false))  {
            $dataAr['positionCode'] = $this->getJobCode($dataAr['position']);
        }
        $InsertId = $this->reportJob($dataAr);
        $inserIdAr['AddedJobId'] = $InsertId;
        
        /*
         * view result in correct format
         */
        if ($format == 'xml') {
                $jobResult = openLaborController::array2xml($inserIdAr,'job');
                $jobResult= str_replace('&', '&amp;',$jobResult);
                header ("Content-type: text/xml");
            } else {
                header('Content-Type: application/json; charset=utf8');
                $jobResult = json_encode($inserIdAr);
        }
        echo $jobResult;
    }        
    
    /**
     * method to comment a job offer
     * @param array $dataAr contains: authorId, jobId, NodeJobId (node parent), commentText
     * @param string $format the data format (xml,json)
     * @return string NodeId of comment
     * 
     */
    public function post003Action($dataAr = array(),$format = 'json',$url_parameters, $c_published = false) {
        require_once(__DIR__ .'/../include/config.inc.php');

        $dataAr = $this->dataCommentValidator($dataAr);
        $jobId = $dataAr['job_id'];
        $text4Report = 'comment to'. ' ' .$dataAr['job_id'];
        $this->LogReport($_REQUEST,$_SERVER,$text4Report);
        
        /*
         * get job data
         */
        $jobsData = $this->getJob($jobId);
        if (AMA_DB::isError($jobsData)) {
            $controller = new errorController();
            $errorMsg = translateFN('Error reading Job id') . ' '.  $jobId;
            $controller->printError('POST',$format,$errorMsg);
        } else {
            $jobData = $jobsData[0];
            $InsertId = $this->addCommentToJob($dataAr,$jobData);
            if (AMA_DB::isError($jobData)) {
                $controller = new errorController();
                $errorMsg = 'Error writing comment to Job' . ' '.  $jobId;
                $controller->printError('POST',$format,$errorMsg);
            } else {
                $inserIdAr['AddedCommentToJobId'] = $InsertId;

                /*
                 * view result in correct format
                 */
                if ($format == 'xml') {
                        $jobResult = openLaborController::array2xml($inserIdAr,'job_comment');
                        $jobResult= str_replace('&', '&amp;',$jobResult);
                        header ("Content-type: text/xml");
                    } else {
                        header('Content-Type: application/json; charset=utf8');
                        $jobResult = json_encode($inserIdAr);
                }
                echo $jobResult;
            }

        }
        
    }
    
    private function addCommentToJob($dataAr,$jobData, $serviceId = null, $instanceId= null) {
            $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
            $dh = $GLOBALS['dh'];
            $common_dh = $GLOBALS['common_dh'];
            $node_ha['parent_id'] = $jobData['j_idNode'];
            
            $node_ha['name'] = $jobData['position'];
            $node_ha['title'] = $jobData['positionCode'];
            $node_ha['text'] = $dataAr['comment_text'];
            $node_ha['type'] = ADA_NOTE_TYPE;
            $node_ha['creation_date'] = $dh->ts_to_date(time());
//            print_r($dh->get_node_info($node_ha['parent_id']));

            $node_ha['order'] = $node_ha['order'];
            $node_ha['level'] = '0';
            $node_ha['vesion'] = '0';
            $node_ha['n_contacts'] = 0;
            $node_ha['icon'] = '';

            $node_ha['bgcolor'] = '';
            $node_ha['color'] = '';
            $node_ha['correctness'] = '';
            $node_ha['copyright'] = '0';
            $node_ha['id_position'] = '';
            $node_ha['lingua'] = $dataAr['locale'];
            $node_ha['pubblicato'] = $published;

            if ($serviceId == null) {
                $node_ha['id_course'] = ADA_JOB_SERVICE_ID;
            } else {
                $node_ha['id_course'] = $serviceId;
            }

            if ($instanceId == null) {
                $node_ha['id_instance'] = ADA_JOB_INSTANCE_SERVICE_ID;
            } else {
                $node_ha['id_instance'] = $instanceId;
            }

            $node_ha['id_node_author'] = 1; // assuming ADMIN of platform
            if (is_int($dataAr['c_user']) && $dataAr['c_user'] > 0) {
                $node_ha['id_node_author'] = $dataAr['c_user'];
            } elseif (DataValidator::validate_email ($dataAr['c_user'])) {
                $id_node_author = $common_dh->find_user_from_email($dataAr['c_user']);
                if (!AMA_DB::isError($id_node_author)) $node_ha['id_node_author'] = $id_node_author;
            }            
        
            $InsertId = $dh->add_node($node_ha);
            return $InsertId;
    }
     /**
     * method to retrieve a job offer
     * @param int $jobId the id of job to retrieve
     * @return array $jobOffer 
     * 
     */
    private function getJob($jobId, $dataProvider = null) {
        if ($dataProvider == null) {
            $dataProvider = DATA_PROVIDER;
        }
        $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN($dataProvider));
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
    private function getJobCode($position) {
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
    private function reportJob($dataAr=array()) {
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
    private function searchJobs($toSearch=array(),$keyMandatory=null) {
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
     * validate data sended by user
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
    
    private function dataCommentValidator($dataAr) {
        $dataAr['api_key'] = DataValidator::validate_string($dataAr['api_key']);
        $dataAr['jurisdiction_id'] = DataValidator::validate_string($dataAr['jurisdiction_id']);
        $dataAr['locale'] = DataValidator::validate_string($dataAr['locale']);
        $dataAr['job_id'] = DataValidator::is_uinteger($dataAr['job_id']);
        $dataAr['comment_text'] = DataValidator::validate_string($dataAr['comment_text']);
        $dataAr['c_latitude'] = DataValidator::validate_string($dataAr['c_latitude']);
        $dataAr['c_longitude'] = DataValidator::validate_string($dataAr['c_longitude']);
        $dataAr['c_user'] = DataValidator::validate_string($dataAr['c_user']);
        $dataAr['media_url'] = DataValidator::validate_url($dataAr['media_url']);
        $dataAr['c_user_firstname'] = DataValidator::validate_string($dataAr['c_user_firstname']);
        $dataAr['c_user_surname'] = DataValidator::validate_string($dataAr['c_user_surname']);
        $dataAr['c_published'] = $dataAr['c_published']; //boolean type 
        return $dataAr;

    }     
    

}

