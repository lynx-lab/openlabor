<?php

class RequestsController extends openLaborController {
    
    /**
     * 
     * jobs search
     * @param type $request
     * @eturn $jobResult
     */
    public function get001Action($request,$format='xml',$url_parameters) {
        require_once(__DIR__ .'/../include/config.inc.php');
        if (!isset($request['jobID'])) {
            $toSearch = array();
            $toSearch['keywords'] = $request['keywords'];
            $toSearch['cityCompany'] = $request['city'];
            $toSearch['qualificationRequired'] = $request['qualification'];
            $jobResult = $this->searchJobs($toSearch,'cityCompany');
            $NumResult = count($jobResult);
            
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
            $httpStatus = '400';
            $controller = new errorController();
            $errorMsg = 'Error reading Job id' . ' '.  $jobId;
            $controller->printError('POST',$format,$errorMsg,$httpStatus);
        } else {
            $jobData = $jobsData[0];
            $InsertId = $this->addCommentToJob($dataAr,$jobData);
            if (AMA_DB::isError($InsertId)) {
                $httpStatus = '400';
                $controller = new errorController();
                $errorMsg = 'Error writing comment to Job' . ' '.  $jobId;
                $controller->printError('POST',$format,$errorMsg, $httpStatus);
            } else {
                $insertIdAr['AddedCommentToJobId'] = $InsertId;

                /*
                 * view result in correct format
                 */
                if ($format == 'xml') {
                        $jobResult = openLaborController::array2xml($insertIdAr,'job_comment');
                        $jobResult= str_replace('&', '&amp;',$jobResult);
                        header ("Content-type: text/xml");
                    } else {
                        header('Content-Type: application/json; charset=utf8');
                        $jobResult = json_encode($insertIdAr);
                }
                echo $jobResult;
            }

        }
        
    }

    /** *
     * 
     * comments search
     * @param string $request
     * @param string $format xml or json
     * @return array $result
     */
    public function get004Action($request,$format='xml',$url_parameters) {
        require_once(__DIR__ .'/../include/config.inc.php');
        if (!isset($request['jobID'])) {
            $httpStatus = '404';
            $controller = new errorController();
            $errorMsg = 'Job id malformed or null';
            $controller->printError('GET',$format,$errorMsg,$httpStatus);
        } else {
            $jobResult = $this->getJob($request['jobID']);
            $jobResult = $jobResult[0];
            $this->LogRequest($_REQUEST,$_SERVER);
            if(AMA_DB::isError($jobResult)) {
                $httpStatus = '404';
                $controller = new errorController();
                $errorMsg = 'Error while reading Job id ' . $request['jobID'];
                $controller->printError('GET',$format,$errorMsg,$httpStatus);
            }
            else {

                $idNode = $jobResult['j_idNode'];
                $positionCode = $jobResult['positionCode'];
                $id_instance = ADA_JOB_INSTANCE_SERVICE_ID;
                $nodeDataAr = $this->getComment($idNode, $id_instance);
                if(AMA_DB::isError($nodeDataAr)) {
                    $httpStatus = '404';
                    $controller = new errorController();
                    $errorMsg = 'Error while reading Comment ' . $idNode;
                    $controller->printError('GET',$format,$errorMsg,$httpStatus);
                }
                else 
                {
                    $commentResult = array();
                    for ($index = 0; $index < count($nodeDataAr); $index++) {
                        $idLanguage = $nodeDataAr['lingua'];
                        $commentResult[$index]['idComment'] = $nodeDataAr[$index]['id_nodo'];
                        $commentResult[$index]['idJob'] = $request['jobID'];
                        $commentResult[$index]['positionCode'] = $positionCode;
    //                  $commentResult['title'] = $nodeDataAr['titolo'];
                        $commentResult[$index]['name'] = $nodeDataAr[$index]['nome'];
                        $commentResult[$index]['text'] = $nodeDataAr[$index]['testo'];
                        $commentResult[$index]['creationDate'] = $nodeDataAr[$index]['data_creazione'];
                        $commentResult[$index]['locale'] = $this->languages[$idLanguage]['codice_lingua'];
                        $commentResult[$index]['authorId'] = $nodeDataAr[$index]['u_id_user'];
                        $commentResult[$index]['authorName'] = $nodeDataAr[$index]['u_name'];
                        $commentResult[$index]['authorSurName'] = $nodeDataAr[$index]['u_surname'];
                        $commentResult[$index]['authorEmail'] = $nodeDataAr[$index]['u_email'];
                    }

                    /*
                     * view result in correct format
                     */
                    if ($format == 'xml') {
                        $result = openLaborController::array2xml($commentResult,'jobComment');
                        $result= str_replace('&', '&amp;',$result);
                        header ("Content-type: text/xml");
                    } else {
                        header('Content-Type: application/json; charset=utf8');
                        $result = json_encode($commentResult);
                    }
                    echo $result;

                }
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
     * return the code of position or training name
     * It use the semantic API (see: http://openlabor.lynxlab.com/services/doc/server.html)
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
      * search engine
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

            $jobOffers = $dh->listJobOffers($clause);
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
    
    /*********************************************
     * TRAINING AREA
     */
    
    /**
     * 
     * @param type $dataAr
     * @return type
     */
        /**
     * 
     * Training search
     * @param type $request
     * @eturn $jobResult
     */
    public function get005Action($request,$format='xml',$url_parameters) {
        require_once(__DIR__ .'/../include/config.inc.php');
        if (!isset($request['ID'])) {
            $toSearch = array();
            $toSearch['keywords'] = $request['keywords'];
            $toSearch['cityCompany'] = $request['city'];
            $toSearch['qualificationRequired'] = $request['qualification'];
            $trainingResult = $this->searchTraining($toSearch,'cityCompany');
            $NumResult = count($trainingResults);
            
        } else {
            $trainingResult = $this->getTraining($request['ID']);
        }
        $this->LogRequest($_REQUEST,$_SERVER,$NumResult);

        /*
         * view result in correct format
         */
        if ($format == 'xml') {
            $trainingResult = openLaborController::array2xml($trainingResult,'training');
            $trainingResult = str_replace('&', '&amp;',$trainingResult);
            header ("Content-type: text/xml");
        } else {
            header('Content-Type: application/json; charset=utf8');
            $trainingResult = json_encode($trainingResult);
        }
        echo $trainingResult;
    }

         /**
      * @abstract search engine
      * 
      * @param $toSearch
      * @param $keyMandatory
      * @return $jobOffers
      */
    private function searchTraining($toSearch=array(),$keyMandatory=null) {
        $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(ADA_TRAINING_SERVICE_PROVIDER));
        $dh = $GLOBALS['dh'];
        
        $today_date = today_dateFN();
        $todayUT = Abstract_AMA_DataHandler::date_to_ts($today_date);

        $clause = 'where t_expiration >= '.$todayUT . ' OR  t_expiration = 0';
        
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
                $professionCodes = $resultAR['job_types']['categories'];
                if (count($professionCodes) > 0) {
                    $foundKeywords = true;
                    
                    $clause .= ' AND T.idTraining = I.id_TC_training AND';

                    $numberCode = NUMBER_CODE;
                    if ((count($professionCodes))  <= NUMBER_CODE) {
                        $numberCode = count($professionCodes);
                    }
                    $clauseKey = NULL;
                    
                    /**
                     * @todo find the way to order the results by professional code order in the array $professionalCode
                     */
                    for ($i=0; $i<$numberCode;$i++) {
                        $professionCode = $professionCodes[$i]['category'];
                        switch ($i) {
                            case 0:
                                $clauseKey = '(I.ISTATCode like \''.$professionCode.'%\'';
                                break;
                            /*
                            case $numberCode:
                                $clause .= ' OR positionCode like \''.$professionalCode.'%\')';
                                break;
                             * 
                             */
                            default:
                                $clauseKey .= ' OR I.ISTATCode like \''.$professionCode.'%\'';
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
                $clause .= ' AND company = \''. $toSearch['cityCompany'].'\'';
            }

            if ($toSearch['qualificationRequired'] != '' && $toSearch['qualificationRequired'] != null) {
                $clause .= ' AND '. constant($toSearch['qualificationRequired']);
                $clause = str_replace('qualificationRequired', 't_qualificationRequired', $clause);
            }
            

            $trainingOffers = $dh->listTrainingOffers($clause);
        }
        return $trainingOffers;
    }

    /**
     *  method to report a Training
     *  @param array $request contains data of Training report
     *  @param string $format contains type of format (xml, json)
     *  @param 
     */
    public function post006Action($dataAr,$format) {
        require_once(__DIR__ .'/../include/config.inc.php');

        $dataAr = $this->dataTrainingValidator($dataAr);
        $this->LogReport($_REQUEST,$_SERVER,$dataAr['nameTraining']);

        if ($dataAr['nameTraining'] != false && ($dataAr['trainingCode'] == '' || $dataAr['trainingCode'] == false))  {
            $dataAr['trainingCode'] = $this->getJobCode($dataAr['nameTraining']);
        }
        $InsertId = $this->reportTraining($dataAr);
        $inserIdAr['AddedTrainingId'] = $InsertId;
        
        /*
         * view result in correct format
         */
        if ($format == 'xml') {
            $trainingResult = openLaborController::array2xml($inserIdAr,'training');
            $trainingResult = str_replace('&', '&amp;',$trainingResult);
            header ("Content-type: text/xml");
        } else {
            header('Content-Type: application/json; charset=utf8');
            $trainingResult = json_encode($inserIdAr);
        }
        echo $trainingResult;

    }
    
    /**
     * method to comment a Training offer
     * @param array $dataAr contains: authorId, trainingId, NodeTrainingId (node parent), commentText
     * @param string $format the data format (xml,json)
     * @return string NodeId of comment
     * 
     */
    public function post007Action($dataAr = array(),$format = 'json',$url_parameters, $c_published = false) {
        require_once(__DIR__ .'/../include/config.inc.php');

        $dataAr = $this->dataCommentValidator($dataAr);
        $trainingId = $dataAr['training_id'];
        $text4Report = 'comment to'. ' ' .$dataAr['jtraining_id'];
        $this->LogReport($_REQUEST,$_SERVER,$text4Report);
        
        /*
         * get job data
         */
        $trainingData = $this->getTraining($trainingId);
        if (AMA_DB::isError($trainingData)) {
            $httpStatus = '400';
            $controller = new errorController();
            $errorMsg = 'Error reading Training id' . ' '.  $jobId;
            $controller->printError('POST',$format,$errorMsg,$httpStatus);
        } else {
            $trainingData = $trainingData[0];
            $InsertId = $this->addCommentToTraining($dataAr,$trainingData);
            if (AMA_DB::isError($InsertId)) {
                $httpStatus = '400';
                $controller = new errorController();
                $errorMsg = 'Error writing comment to training' . ' '.  $trainingId;
                $controller->printError('POST',$format,$errorMsg, $httpStatus);
            } else {
                $insertIdAr['AddedCommentToTrainingId'] = $InsertId;

                /*
                 * view result in correct format
                 */
                if ($format == 'xml') {
                        $trainingResult = openLaborController::array2xml($insertIdAr,'training_comment');
                        $trainingResult= str_replace('&', '&amp;',$trainingResult);
                        header ("Content-type: text/xml");
                    } else {
                        header('Content-Type: application/json; charset=utf8');
                        $trainingResult = json_encode($insertIdAr);
                }
                echo $trainingResult;
            }

        }
        
    }
    
        /**
     * 
     * @abstract add job reported to table jobs
     *           if positionCode is not a valid code it ask to semantic api to have the right code
     *          
     * @param array $dataAr
     */
    private function reportTraining($dataAr=array()) {
        $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
        $dh = $GLOBALS['dh'];
        
        if ($dataAr['TrainingCode'] == '') {
            $urlSemanticApi = URL_LAVORI4.$dataAr['nameTraining'];
            $keywords = $dataAr['nameTraining'];
            //$curlHeader = array("Content-Type: application/x-www-form-urlencoded");
            $curlHeader = '';
            $jobsCode = REST_request::sendRequest($keywords,$curlHeader,$urlSemanticApi,$curlPost);
            //$resultAR = json_decode($jobsCode, TRUE);
        }
//        print_r($dataAr);
        $InsertId = $dh->addTrainingOffer($dataAr);
        return $InsertId;
    }
     /**
     * method to retrieve a Training offer
     * @param int $trainingId the id of training to retrieve
     * @return array $trainingOffer 
     * 
     */
    private function getTraining($trainingId, $dataProvider = null) {
        if ($dataProvider == null) {
            $dataProvider = DATA_PROVIDER;
        }
        $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN($dataProvider));
        $dh = $GLOBALS['dh'];
        $trainingOffer = $dh->getTrainingFromId($trainingId);
//        $jobOfferJson = json_encode($jobOffer);
        return $trainingOffer;
        //echo $jobOfferJson;
    }    
    
    private function addCommentToTRaining($dataAr,$trainingData, $serviceId = null, $instanceId= null) {
            $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
            $dh = $GLOBALS['dh'];
            $common_dh = $GLOBALS['common_dh'];
            $node_ha['parent_id'] = $trainingData['t_idNode'];
            
            $node_ha['name'] = $trainingData['nameTraining'];
            $node_ha['title'] = $trainingData['trainingCode'];
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
                $node_ha['id_course'] = ADA_TRAINING_SERVICE_ID;
            } else {
                $node_ha['id_course'] = $serviceId;
            }

            if ($instanceId == null) {
                $node_ha['id_instance'] = ADA_TRAINING_INSTANCE_SERVICE_ID;
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
     * 
     */
    private function getComment($idNode, $id_instance = null) {
            $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
            $dh = $GLOBALS['dh'];
//            $nodeDataAr = $dh->get_node_info($idNode);
            $nodeDataAr = $dh->get_node_children($idNode, $id_instance);
            return $nodeDataAr;
    }
       /**
     * validate data sended by user
     * @param array $dataAr contains the data POSTED
     * @return array  $dataAr contains the data validated
     */
    private function dataTrainingValidator($dataAr) {

        $validDataAr['api_key'] = DataValidator::validate_string($dataAr['api_key']);
        $validDataAr['jurisdiction_id'] = DataValidator::validate_string($dataAr['jurisdiction_id']);
        $validDataAr['nameTraining'] = DataValidator::validate_string($dataAr['nameTraining']);
        $validDataAr['trainingCode'] = DataValidator::validate_string($dataAr['trainingCode']);
        $validDataAr['company'] = DataValidator::validate_string($dataAr['company']);
        $validDataAr['trainingAddress'] = DataValidator::validate_string($dataAr['trainingAddress']);
        $validDataAr['CAP'] = DataValidator::validate_string($dataAr['CAP']);
        $validDataAr['city'] = DataValidator::validate_string($dataAr['city']);
        $validDataAr['phone'] = DataValidator::validate_string($dataAr['phone']);
        $validDataAr['durationHours'] = DataValidator::is_uinteger($dataAr['durationHours']);
        $validDataAr['trainingType'] = DataValidator::validate_string($dataAr['trainingType']);
        $validDataAr['userType'] = DataValidator::validate_string($dataAr['userType']);
        $validDataAr['qualificationRequired'] = DataValidator::validate_string($dataAr['qualificationRequired']);
        $validDataAr['longitude'] = DataValidator::validate_string($dataAr['longitude']);
        $validDataAr['latitude'] = DataValidator::validate_string($dataAr['latitude']);
        $validDataAr['hash'] = DataValidator::validate_string($dataAr['hash']);
        $validDataAr['source'] = DataValidator::validate_string($dataAr['source']);
        $validDataAr['nation'] = DataValidator::validate_string($dataAr['nation']);
        $validDataAr['minAge'] = DataValidator::is_uinteger($dataAr['minAge']);
        $validDataAr['maxAge'] = DataValidator::is_uinteger($dataAr['maxAge']);
        $validDataAr['price'] = DataValidator::is_uinteger($dataAr['price']);
        $validDataAr['reservedForDisabled'] = DataValidator::is_uinteger($dataAr['reservedForDisabled']); //boolean type 
        $validDataAr['favoredCategoryRequests'] = DataValidator::validate_string($dataAr['favoredCategoryRequests']);
       
        $validDataAr['expiration'] = DataValidator::is_uinteger($dataAr['expiration']);
        $validDataAr['notes'] = DataValidator::validate_string($dataAr['notes']);
        $validDataAr['linkMoreInfo'] = DataValidator::validate_url($dataAr['linkMoreInfo']);
        $validDataAr['media_url'] = DataValidator::validate_url($dataAr['media_url']);
        $validDataAr['locale'] = DataValidator::validate_string($dataAr['locale']);

        $validDataAr['sourceTrainingName'] = DataValidator::validate_string($dataAr['sourceTrainingName']);
        $validDataAr['sourceTrainingSurname'] = DataValidator::validate_string($dataAr['sourceTrainingSurname']);
        $validDataAr['published'] = DataValidator::is_uinteger($dataAr['published']); //boolean type 
        return $validDataAr;

    } 
    
    /**
     * Validate the data need to comment a job or a training
     * @param array $dataAr
     * @return array
     */
    private function dataCommentValidator($dataAr) {
        $dataAr['api_key'] = DataValidator::validate_string($dataAr['api_key']);
        $dataAr['jurisdiction_id'] = DataValidator::validate_string($dataAr['jurisdiction_id']);
        $dataAr['locale'] = DataValidator::validate_string($dataAr['locale']);
        $dataAr['job_id'] = DataValidator::is_uinteger($dataAr['job_id']);
        $dataAr['training_id'] = DataValidator::is_uinteger($dataAr['training_id']);
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

