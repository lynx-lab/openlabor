<?php
/**
 * @package     Openlabor
 * @author	Maurizio Graffio Mazzoneschi <graffio@lynxlab.com>
 * @copyright	Copyright (c) 2013, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class AMAOpenLaborDataHandler extends AMA_DataHandler {

    /**
     * Returns an instance of AMA_DataHandler.
     *
     * @param  string $dsn - optional, a valid data source name
	 *
     * @return an instance of AMA_DataHandler
     */
    static function instance($dsn = null) {
        if(self::$instance === NULL) {
            self::$instance = new AMAOpenLaborDataHandler($dsn);
        }
        else {
            self::$instance->setDSN($dsn);
        }
        //return null;
        return self::$instance;
    }

    /**
     * add job offers
     * 
     * @access public
     * 
     * @param array $jobData contains all the jobs offer data
     * 
     * @return an error if something goes wrong or true
     */
    
    public function addJobOffers ($jobsData) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        $oldOffers = $this->listJobOffers();
        $i=0;
        $o=0;
        foreach ($jobsData as $oneJobData) {
            if (count($oldOffers) == 0) {
                 $idInserted[$i] = $this->addJobOffer($oneJobData);
            } else {
                $JobAlreadyExists = false;
//                foreach ($oldOffers as $oneOldOffer) {
//                    if (in_array($oneJobData['idJobOriginal'],$oneOldOffer)) {
                foreach ($oldOffers as $oldOneOffer) {
                    if ($oneJobData['IdJobOriginal'] == $oldOneOffer['idJobOriginal']) {
                        unset($oldOffers[$o]);
                        $JobAlreadyExists = true;
                        $o++;
                        break;
                    }
                    $o++;
                }
                if ($JobAlreadyExists) {
                    $idInserted[$i] = $this->updateJob($oneJobData);
                } else {
                    $idInserted[$i] = $this->addJobOffer($oneJobData);
                }
            }
            $i++;
        }
        return $idInserted;
    }
    
    public function updateJob($JobData) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        if (!is_array($JobData)) return translateFN('dati non corretti');
        $CPIData = $this->getCPIFromName($JobData['CPI']);
        if (!self::isError($CPIData) && is_array($CPIData) && $CPIData[0]['idCPI'] != null) {
            $idCPI = $CPIData[0]['idCPI'];
        }else {
            $idCPI = 0;
        }
        
        if (!is_int($JobData['jobExpiration'])) {
            $jobexpiration = Abstract_AMA_DataHandler::date_to_ts($JobData['jobExpiration']); 
        } else {
            $jobexpiration = $JobData['jobExpiration'];
        }
        $data = array();
        $data = array(
            $JobData['IdJobOriginal'],
            $jobexpiration,
            $JobData['workersRequired'],
            $JobData['professionalProfile'],
            $JobData['positionCode'],
            $JobData['position'],
            $JobData['contractType'],
            $JobData['qualificationRequired'],
            $JobData['descriptionQualificationRequired'],
            $JobData['professionalTrainingRequired'],
            $JobData['address'],
            $JobData['zipCode'],
            $JobData['cityCompany'],
            $JobData['nation'],
            $idCPI,
            $JobData['experienceRequired'],
            $JobData['durationExperience'],
            $JobData['minAge'],
            $JobData['maxAge'],
            $JobData['remuneration'],
            $JobData['rewards'],
            $JobData['reservedForDisabled'],
            $JobData['favoredCategoryRequests'],
            $JobData['ownVehicle'],  
            $JobData['notes'],
            $JobData['linkMoreInfo'],
            time(),
            $JobData['source'],
            $JobData['j_latitude'],
            $JobData['j_longitude'],
            $JobData['media_url'],
            $JobData['locale'],
            $JobData['sourceJobName'],
            $JobData['sourceJobSurname'],
            $JobData['published'],
            $JobData['j_idNode']
            
	);
        
        /*
         * needed to allow update from module proRoma.
         * priority to idJobOriginal
         */
        $clause = 'idJobOriginal=?';
        if (isset($JobData['idJobOriginal']) && $JobData['idJobOriginal'] != 0) {
            $data[] = $JobData['idJobOriginal'];
            $clause = 'idJobOriginal=?';
        }
        elseif (isset($JobData['idJobOffers']) && $JobData['idJobOffers'] != 0) {
            $data[] = $JobData['idJobOffers'];
            $clause = 'idJobOffers=?';
        }
//        unset($JobData);

        //fine validazione campi
        $update_sql = 'UPDATE OL_jobOffers SET idJobOriginal=?, jobExpiration=?, workersRequired=?, professionalProfile=?, positionCode=?, position=?,
                contractType=?, qualificationRequired=?, descriptionQualificationRequired=?, professionalTrainingRequired=?, j_address=?, zipCode=?, cityCompany=?,nation=?, 
                idCPI=?, experienceRequired=?, durationExperience=?, minAge=?, maxAge=?, remuneration=?, rewards=?, reservedForDisabled=?, favoredCategoryRequests=?,
                ownVehicle=?, notes=?, linkMoreInfo=?, dateInsert=?, sourceJob=?, j_latitude=?,j_longitude=?,media_url=?,locale=?,sourceJobName=?,sourceJobSurname=?,
                published=?,j_idNode=? where '.$clause; // idJobOriginal=?';
        
        ADALogger::log_db("trying updating the job offer: ".$update_sql);
        $res = $this->queryPrepared($update_sql, $data);
        
        // if an error is detected, an error is created and reported
        if (self::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in updateJobOffer.".AMA_SEP.": ".$res->getMessage());
        }
//        return $db->lastInsertID();
        return TRUE;
    }


    /**
     * add job offer to table
     */
    public function addJobOffer($JobData) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        if (!is_array($JobData)) return translateFN('dati non corretti');
        if ($JobData['CPI'] != NULL) {
            $CPIData = $this->getCPIFromName($JobData['CPI']);
            if (!self::isError($CPIData) && is_array($CPIData)) {
                $idCPI = $CPIData[0]['idCPI'];
            } else {
                $idCPI = 0;
            }
        }
        else {
                $idCPI = 0;
        }
        
        if (!is_int($JobData['jobExpiration'])) {
            $jobexpiration = Abstract_AMA_DataHandler::date_to_ts($JobData['jobExpiration']); 
        } else {
            $jobexpiration = $JobData['jobExpiration'];
        }
        $JobData['j_idNode'] = '0';
        $data = array(
            'IdJobOriginal'=>$this->or_zero($JobData['IdJobOriginal']),
            'jobExpiration'=>$this->or_zero($jobexpiration),
            'workersRequired'=>$this->or_zero($JobData['workersRequired']),
            'professionalProfile'=>$this->sql_prepared($JobData['professionalProfile']),
            'positionCode'=>$this->sql_prepared($JobData['positionCode']),
            'position'=>$this->sql_prepared($JobData['position']),
            'contractType'=>$this->sql_prepared($JobData['contractType']),
            'qualificationRequired'=>$this->sql_prepared($JobData['qualificationRequired']),
            'descriptionQualificationRequired'=>$this->sql_prepared($JobData['descriptionQualificationRequired']),
            'professionalTrainingRequired'=>$this->sql_prepared($JobData['professionalTrainingRequired']),
            'j_address'=>$this->sql_prepared($JobData['address']),
            'zipCode'=>$this->sql_prepared($JobData['zipCode']),
            'cityCompany'=>$this->sql_prepared($JobData['cityCompany']),
            'nation'=>$this->sql_prepared($JobData['nation']),
            'idCPI'=>$this->or_zero($idCPI),
            'experienceRequired'=>$this->sql_prepared($JobData['experienceRequired']),
            'durationExperience'=>$this->sql_prepared($JobData['durationExperience']),
            'minAge'=>$this->sql_prepared($JobData['minAge']),
            'maxAge'=>$this->sql_prepared($JobData['maxAge']),
            'remuneration'=>$this->sql_prepared($JobData['remuneration']),
            'rewards'=>$this->sql_prepared($JobData['rewards']),
            'reservedForDisabled'=>$this->sql_prepared($JobData['reservedForDisabled']),
            'favoredCategoryRequests'=>$this->sql_prepared($JobData['favoredCategoryRequests']),
            'ownVehicle'=>$this->sql_prepared($JobData['ownVehicle']),
            'notes'=>$this->sql_prepared($JobData['notes']),
            'linkMoreInfo'=>$this->sql_prepared($JobData['linkMoreInfo']),
            'dateInsert'=>time(),
            'j_latitude'=>$this->sql_prepared($JobData['j_latitude']),
            'j_longitude'=>$this->sql_prepared($JobData['j_longitude']),
            'sourceJob'=>$this->sql_prepared($JobData['source']),
            'media_url'=>$this->sql_prepared($JobData['media_url']),
            'locale'=>$this->sql_prepared($JobData['locale']),
            'sourceJobName'=>$this->sql_prepared($JobData['sourceJobName']),
            'sourceJobSurname'=>$this->sql_prepared($JobData['sourceJobSurname']),
            'published'=>$this->sql_prepared($JobData['published']),
            'j_idNode'=>$this->sql_prepared($JobData['j_idNode'])
                
	);

        //fine validazione campi
        $keys = array_keys($data);

        $sql = "INSERT INTO `OL_jobOffers` (".implode(',',$keys).") VALUES (".implode(",",$data).")";
        ADALogger::log_db("trying inserting the job offer: ".$sql);

        $res = $db->query($sql);
        // if an error is detected, an error is created and reported
        if (self::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in addJobOffer.".AMA_SEP.": ".$res->getMessage());
        }
        $jobId = $db->lastInsertID();
        $idNodeJob = $this->addNodeJob($JobData);
        if (!self::isError($idNodeJob) && $idNodeJob != null) {
            $JobData['j_idNode'] = $idNodeJob;
            $JobData['idJobOffers'] = $jobId;            
            $this->updateJob($JobData);
        }
        unset($JobData);
        
        return $jobId;
    }
        

    /**
     * @author graffio  <graffio@lynxlab.com
     * @param Array $dataAr Job Offer data
     * @param Int $jobId the Id of job offer inserted
     * @param INT $serviceId the Id of service to wh
     * @return string $insertedNodeId the Id of node related o job offer to whom the node is child
     */
    public function addNodeJob($dataAr, $serviceId = null, $instanceId = null) {
        $common_dh = $GLOBALS['common_dh'];
        if ($serviceId == null) {
            $node_ha['id_course'] = ADA_JOB_SERVICE_ID;
            $node_ha['parent_id'] = ADA_JOB_SERVICE_ID.'_0';            
        } else {
            $node_ha['id_course'] = $serviceId;
            $node_ha['parent_id'] = $serviceId.'_0';
        }
        
        if ($instanceId == null) {
            $node_ha['id_instance'] = ADA_JOB_INSTANCE_SERVICE_ID;
        } else {
            $node_ha['id_instance'] = $instanceId;
        }
        
        $node_ha['id_node_author'] = 1; // assuming ADMIN of platform
        if (is_int($dataAr['source']) && $dataAr['source'] > 0) {
            $node_ha['id_node_author'] = $dataAr['source'];
        } elseif (DataValidator::validate_email ($dataAr['source'])) {
            $id_node_author = $common_dh->find_user_from_email($dataAr['source']);
            if (!AMA_DB::isError($id_node_author)) $node_ha['id_node_author'] = $id_node_author;
        }
        

        $node_ha['title'] = $dataAr['positionCode'];
        $node_ha['name'] = $dataAr['position'];
        $node_ha['text'] = $dataAr['professionalProfile'].PHP_EOL.$dataAr['notes'].PHP_EOL.$dataAr['professionalProfile'];
        $node_ha['type'] = ADA_LEAF_TYPE;
        $node_ha['creation_date'] = $this->ts_to_date(time());

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
        $node_ha['pubblicato'] = $dataAr['published'];

        $idNode = $this->add_node($node_ha);
        return $idNode;
        
    }
   
        /**
     * add job offers
     * 
     * @access public
     * 
     * @param array $jobData contains all the jobs offer data
     * 
     * @return an error if something goes wrong or true
     */
    
    public function addTrainingOffers ($trainingsData) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        /*
         * condition to find all the training offers
         */
        $condition = 'where 1';
        $codeToSearch = array();
        $orderby='nameTraining';
        $limit=0; //no limit
        /*
         * end condition to find all the training offers
         */
        $oldTrainings = $this->listTrainingOffers($condition, $codeToSearch,$orderby, $limit);
        $i=0;
        $o=0;
        foreach ($trainingsData as $oneTrainingData) {
            if (count($oldTrainings) == 0) {
                 $idInserted[$i] = $this->addTrainingOffer($oneTrainingData);
            } else {
                $TrainingAlreadyExists = false;
//                foreach ($oldOffers as $oneOldOffer) {
//                    if (in_array($oneJobData['idJobOriginal'],$oneOldOffer)) {
                foreach ($oldTrainings as $oldOneTraining) {
                    if ($oneTrainingData['IdTrainingOriginal'] == $oldOneTraining['idTrainingOriginal'] && $oldOneTraining['idTrainingOriginal'] > 0) {
                        unset($oldTrainings[$o]);
                        $TrainingAlreadyExists = true;
                        $o++;
                        break;
                    }
                    $o++;
                }
                if ($TrainingAlreadyExists) {
                    $idInserted[$i] = $this->updateTraining($oneTrainingData);
                } else {
                    $idInserted[$i] = $this->addTrainingOffer($oneTrainingData);
                }
            }
            $i++;
        }
        return $idInserted;
    }

     /**
     * add training offer to table
     */
    public function addTrainingOffer($TrainingData) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        if (!is_array($TrainingData)) return translateFN('dati non corretti');
        
        if (!is_int($TrainingData['$TrainingData'])) {
            $trainingexpiration = Abstract_AMA_DataHandler::date_to_ts($TrainingData['trainingExpiration']); 
        } else {
            $trainingexpiration = $TrainingData['trainingExpiration'];
        }
        $TrainingData['t_idNode'] = '0';
        $data = array(
            'IdTrainingOriginal'=>$this->or_zero($TrainingData['IdTrainingOriginal']),
            'nameTraining'=>$this->sql_prepared($TrainingData['nameTraining']),
            'trainingCode'=>$this->sql_prepared($trainingCode[0]),
            'company'=>$this->sql_prepared($TrainingData['company']),
            'trainingAddress'=>$this->sql_prepared($TrainingData['trainingAddress']),
            'CAP'=>$this->sql_prepared($TrainingData['CAP']),
            'city'=>$this->sql_prepared($TrainingData['city']),
            'phone'=>$this->sql_prepared($TrainingData['phone']),
            'durationHours'=>$this->or_zero($TrainingData['durationHours']),
            'trainingType'=>$this->sql_prepared($TrainingData['trainingType']),
            't_userType'=>$this->sql_prepared($TrainingData['userType']),
            't_qualificationRequired'=>$this->sql_prepared($TrainingData['qualificationRequired']),
            't_latitude'=>$this->sql_prepared($TrainingData['latitude']),
            't_longitude'=>$this->sql_prepared($TrainingData['longitude']),
            't_dateInsert'=>time(),
            'hash'=>$this->sql_prepared($TrainingData['hash']),
            't_source'=>$this->sql_prepared($TrainingData['source']),
            
            't_nation'=>$this->sql_prepared($TrainingData['nation']),
            't_minAge'=>$this->sql_prepared($TrainingData['minAge']),
            't_maxAge'=>$this->sql_prepared($TrainingData['maxAge']),
            't_price'=>$this->sql_prepared($TrainingData['price']),
            't_reservedForDisabled'=>$this->sql_prepared($TrainingData['reservedForDisabled']),
            't_favoredCategoryRequests'=>$this->sql_prepared($TrainingData['favoredCategoryRequests']),
            't_notes'=>$this->sql_prepared($TrainingData['notes']),
            't_expiration'=>$this->or_zero($TrainingData['expiration']),
            't_linkMoreInfo'=>$this->sql_prepared($TrainingData['linkMoreInfo']),
            't_media_url'=>$this->sql_prepared($TrainingData['media_url']),
            't_locale'=>$this->sql_prepared($TrainingData['locale']),
            'sourceTrainingName'=>$this->sql_prepared($TrainingData['sourceTrainingName']),
            'sourceTrainingSurname'=>$this->sql_prepared($TrainingData['sourceTrainingSurname']),
            't_published'=>$this->sql_prepared($TrainingData['published']),
            't_idNode'=>$this->sql_prepared($TrainingData['t_idNode'])
                
	);

        //fine validazione campi
        $keys = array_keys($data);

        $sql = "INSERT INTO `OL_training` (".implode(',',$keys).") VALUES (".implode(",",$data).")";
        ADALogger::log_db("trying inserting the training offer: ".$sql);

        $res = $db->query($sql);
        // if an error is detected, an error is created and reported
        if (self::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in addTrainingOffer.".AMA_SEP.": ".$res->getMessage());
        }
        $trainingId = $db->lastInsertID();
        $trainingCodeAdded = $this->addTrainingCode($trainingId,$TrainingData['trainingCode'] );
        $idNodeTraining = $this->addNodeTraining($TrainingData);
        if (!self::isError($idNodeTraining) && $idNodeTraining != null) {
            $TrainingData['t_idNode'] = $idNodeTraining;
            $TrainingData['idTraining'] = $trainingId;            
            $this->updateTraining($TrainingData);
        }
        unset($TrainingData);
        
        return $trainingId;
    }
    
    /* *
     * add training Code to table
     */
    public function addTrainingCode($idTraining, $codes = array()){
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        foreach ($codes as $singleCode) {
            $istatCode = $singleCode[0];
            $weight = floatval($singleCode[1]);
            $sql = "INSERT INTO `OL_TrainingCode` (id_TC_training,ISTATCode,code_weight) VALUES ($idTraining,'$istatCode',$weight)";
            ADALogger::log_db("trying inserting the training Code: ".$sql);

            $res = $db->query($sql);
            // if an error is detected, an error is created and reported
            if (self::isError($res)) {
                return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in addTrainingCode.".AMA_SEP.": ".$res->getMessage());
            }
            
        }
        return true;
    }
    
    /* *
     * add training Code to table
     */
    public function getTrainingCode($idTraining) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        foreach ($codes as $singleCode) {
            $sql = "SELECT ISTATCode,code_weight  FROM `OL_TrainingCode` where id_TC_training = $idTraining";
            ADALogger::log_db("trying select the training Code: ".$sql);

            $res =  $this->getAllPrepared($sql, null, AMA_FETCH_ORDERED);            // if an error is detected, an error is created and reported
            if (self::isError($res)) {
                return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in addTrainingCode.".AMA_SEP.": ".$res->getMessage());
            }
            
        }
        return $res;
    }
    
    
    /**
     * @author graffio  <graffio@lynxlab.com
     * @param Array $dataAr Trainig Offer data
     * @param Int $trainingId the Id of Training offer inserted
     * @param INT $serviceId the Id of service to wh
     * @return string $insertedNodeId the Id of node related to training offer to whom the node is child
     */
    public function addNodeTraining($dataAr, $serviceId = null, $instanceId = null) {
        $common_dh = $GLOBALS['common_dh'];
        if ($serviceId == null) {
            $node_ha['id_course'] = ADA_TRAINING_SERVICE_ID;
            $node_ha['parent_id'] = ADA_TRAINING_SERVICE_ID.'_0';            
        } else {
            $node_ha['id_course'] = $serviceId;
            $node_ha['parent_id'] = $serviceId.'_0';
        }
        
        if ($instanceId == null) {
            $node_ha['id_instance'] = ADA_TRAINING_INSTANCE_SERVICE_ID;
        } else {
            $node_ha['id_instance'] = $instanceId;
        }
        
        $node_ha['id_node_author'] = 1; // assuming ADMIN of platform
        if (is_int($dataAr['t_sourceId']) && $dataAr['t_sourceId'] > 0) {
            $node_ha['id_node_author'] = $dataAr['t_sourceId'];
        } elseif (DataValidator::validate_email ($dataAr['t_sourceId'])) {
            $id_node_author = $common_dh->find_user_from_email($dataAr['t_sourceId']);
            if (!AMA_DB::isError($id_node_author)) $node_ha['id_node_author'] = $id_node_author;
        }
        

        $node_ha['title'] = $dataAr['trainingCode'];
        $node_ha['name'] = $dataAr['nameTraining'];
        $node_ha['text'] = $dataAr['trainingType'].PHP_EOL. $dataAr['userType'].PHP_EOL. $dataAr['qualificationRequired'];
        $node_ha['type'] = ADA_LEAF_TYPE;
        $node_ha['creation_date'] = $this->ts_to_date(time());

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
        $node_ha['pubblicato'] = $dataAr['published'];

        $idNode = $this->add_node($node_ha);
        return $idNode;
        
    }

    
    
        public function updateTraining($TData) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        if (!is_array($TData)) return translateFN('dati non corretti');
        
        if (!is_int($TData['expiration'])) {
            $t_expiration = Abstract_AMA_DataHandler::date_to_ts($TData['expiration']); 
        } else {
            $t_expiration = $TData['expiration'];
        }
        $data = array();
        $data = array(
            $TData['IdTrainingOriginal'],
            $t_expiration,
            $TData['nameTraining'],
            $TData['trainingCode'],
            $TData['company'],
            $TData['trainingAddress'],
            $TData['CAP'],
            
            $TData['city'],
            $TData['phone'],
            $TData['durationHours'],
            $TData['trainingType'],
            $TData['userType'],
            $TData['qualificationRequired'],
            $TData['longitude'],
            $TData['latitude'],
            time(),
            //$TData['t_dateInsert'],
            $TData['hash'],
            $TData['source'],
            $TData['nation'],
            $TData['minAge'],
            $TData['maxAge'],
            $TData['price'],
            $TData['reservedForDisabled'],
            $TData['favoredCategoryRequests'],
            $TData['t_notes'],
            $TData['t_linkMoreInfo'],
            $TData['t_media_url'],
            $TData['t_locale'],
            $TData['sourceTrainingName'],
            $TData['sourceTrainingSurname'],
            $TData['t_published'],
            $TData['t_idNode']
	);
        
        /*
         * needed to allow update from module proRoma.
         * priority to idJobOriginal
         */
        $clause = 'IdTrainingOriginal=?';
        if (isset($TData['IdTrainingOriginal']) && $TData['IdTrainingOriginal'] != 0) {
            $data[] = $TData['IdTrainingOriginal'];
            $clause = 'idTrainingOriginal=?';
        }
        elseif (isset($TData['idTraining']) && $TData['idTraining'] != 0) {
            $data[] = $TData['idTraining'];
            $clause = 'idTraining=?';
        }
//        unset($JobData);
        //fine preparazione campi
        
        $update_sql = 'UPDATE OL_training SET idTrainingOriginal=?, t_expiration=?, nameTraining=?, trainingCode=?, company=?, trainingAddress=?,
                CAP=?, city=?, phone=?, durationHours=?, trainingType=?, t_userType=?, t_qualificationRequired=?,t_longitude=?, 
                t_latitude=?, t_dateInsert=?, hash=?, t_source=?, t_nation=?, t_minAge=?, t_maxAge=?, t_price=?, t_reservedForDisabled=?,
                t_favoredCategoryRequests=?, t_notes=?, t_linkMoreInfo=?, t_media_url=?, t_locale=?, sourceTrainingName=?,sourceTrainingSurname=?,t_published=?,t_idNode=? 
                where '.$clause; // idJobOriginal=?';
        
        ADALogger::log_db("trying updating the Training offer: ".$update_sql);
        $res = $this->queryPrepared($update_sql, $data);
        
        // if an error is detected, an error is created and reported
        if (self::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in updateJobOffer.".AMA_SEP.": ".$res->getMessage());
        }
        return TRUE;
    }


    
    /**
     * Add a node
     * only add a node. Leaves out position, author and course.
     * This function is called from the public add_node function.
     *
     * @access private
     *
     * @param $node_ha an associative array containing all the node's data (see public function)
     *
     * @return an AMA_Error object if something goes wrong,
     *         true on success
     *
     * @see add_node()
     */
    public function add_node($node_ha) {
        
        ADALogger::log_db("entered _add_node");

        $db =& $this->getConnection();
        if ( AMA_DB::isError( $db ) ) return $db;

        // FIXME: l'id del nodo dovrebbe venire ottenuto qui e non passato nell'array $node_ha
        // Fixed by Graffio 08/11/2011
        //$id_node = $this->sql_prepared($node_ha['id']);
        $id_author = $node_ha['id_node_author'];
        $name = $this->sql_prepared($this->or_null($node_ha['name']));
        $title = $this->sql_prepared($this->or_null($node_ha['title']));

        $text = $this->sql_prepared($node_ha['text']);
        $type = $this->sql_prepared($this->or_zero($node_ha['type']));
        $creation_date = time(); //$this->date_to_ts($this->or_null($node_ha['creation_date']));
        $parent_id = $this->sql_prepared($node_ha['parent_id']);
        $order = $this->sql_prepared($this->or_null($node_ha['order']));
        $level = $this->sql_prepared($this->or_zero($node_ha['level']));
        $version = $this->sql_prepared($this->or_zero($node_ha['version']));
        $n_contacts = $this->sql_prepared($this->or_zero($node_ha['n_contacts']));
        $icon = $this->sql_prepared($this->or_null($node_ha['icon']));

        $bgcolor = $this->sql_prepared($this->or_null($node_ha['bgcolor']));
        $color = $this->sql_prepared($this->or_null($node_ha['color']));
        $correctness = $this->sql_prepared($this->or_zero($node_ha['correctness']));
        $copyright = $this->sql_prepared($this->or_zero($node_ha['copyright']));
        $id_position = $this->sql_prepared($node_ha['id_position']);
        $lingua = $this->sql_prepared($node_ha['lingua']);
        $pubblicato = $this->sql_prepared($node_ha['pubblicato']);

        if (array_key_exists('id_instance',$node_ha)) {
            $id_instance = $this->sql_prepared($this->or_null($node_ha['id_instance']));
        }
        else {
            $id_instance = "''";
        }
        if (isset($node_ha['id_course']) and ($node_ha['parent_id'] == null || $node_ha['parent_id'] == '')) {
            $new_node_id = $node_ha['id_course']. '_' . '0';
        } else {
            $parentId = $node_ha['parent_id'];
            $regExp = '#^([1-9][0-9]*)_#';                        
            preg_match($regExp, $parentId, $stringFound);
            if (count($stringFound) > 0) {
                $idCourse = $stringFound[1];
                
                $last_node = $this->get_max_idFN($idCourse);
                $tempAr = explode ("_", $last_node);
                $newId =intval($tempAr[1]) + 1;
                $new_node_id = $idCourse . "_" . $newId;
            }
        }
            $id_node = $this->sql_prepared($new_node_id);

        // insert a row into table nodo
        $sql  = "insert into nodo (id_nodo, id_utente,id_posizione, nome, titolo, testo, tipo, data_creazione, id_nodo_parent, ordine, livello, versione, n_contatti, icona, colore_didascalia, colore_sfondo, correttezza, copyright, lingua, pubblicato, id_istanza)";
        $sql .= " values ($id_node,  $id_author, $id_position, $name, $title, $text, $type, $creation_date, $parent_id, $order, $level, $version, $n_contacts, $icon, $color, $bgcolor, $correctness, $copyright, $lingua, $pubblicato, $id_instance)";
        ADALogger::log_db("trying inserting the node: $sql");

        $res = $db->query($sql);
        // if an error is detected, an error is created and reported
        if (AMA_DB::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD) . " while in _add_node." .
                            AMA_SEP . ": " . $res->getMessage());
        }

        
        // the sql_prepared form will be of unvaluable help in the future
        $sqlnode_id = $this->sql_prepared($id_node);

        /*
     * if exists a parent node for this node, check if it has type ADA_LEAF_TYPE
     * and change it in ADA_GROUP_TYPE
        */
        if( isset($node_ha['parent_id']) && ($node_ha['parent_id'] != "") ) {
            $parent_node_ha = $this->get_node_info($node_ha['parent_id']);
            if(!AMA_DB::isError($parent_node_ha)) {
                if ( $parent_node_ha['type'] == ADA_LEAF_TYPE ) {
                    $result = $this->change_node_type($node_ha['parent_id'], ADA_GROUP_TYPE);
                    if ( AMA_DB::isError($parent_node_ha) ) {
                        return $result;
                    }
                }
            }
        }
        return $new_node_id;
    }

    /**
     * 
     */
    public function get_node_children($node_id, $id_course_instance = null) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        $condition = null;
        $values = array($node_id);
        if ($id_course_instance != null) {
            $condition = ' AND id_istanza = ?';
            array_push($values, $id_course_instance);
        }    
        $sql = 'SELECT N.*, U.nome AS u_name, U.cognome AS u_surname, U.e_mail as u_email, U.id_utente as u_id_user FROM nodo AS N LEFT JOIN utente AS U ON (N.id_utente=U.id_utente) WHERE N.id_nodo_parent=?' . $condition; 
        $res =  $this->getAllPrepared($sql, $values, AMA_FETCH_ASSOC);
        return $res;
//        parent::get_node_children($node_id, $id_course_instance);
    }
    /**
     * list job offers
     * 
     * @access public
     * 
     * @param 
     * 
     * @return an error if something goes wrong or an array contains all the offers
     */
    public function listJobOffers($condition='where 1') {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        $sql = 'SELECT  O.*, C.*  FROM `OL_jobOffers` as O, `OL_CPI` as C '. ' ' .$condition.
                ' AND O.idCPI = C.idCPI' ; 
//        print_r($sql);
        $res =  $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);
        return $res;
    }

    public function getJobFromId($jobId) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        $sql = 'SELECT O.*,C.* FROM `OL_jobOffers` as O, `OL_CPI` as C where idjobOffers = '.$jobId.
                ' AND O.idCPI = C.idCPI' ; 
//        print_r($sql);
        $res =  $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);
        return $res;
    }
    
     /**
     * list Training offers
     * 
     * @access public
     * 
     * @param 
     * 
     * @return an error if something goes wrong or an array contains all the offers
     */
    public function listTrainingOffers($condition='where 1', $codeToSearch = array(),$orderby='code_weight', $limit=0, $howToOrder='ASC') {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        /*
        if (sizeof($codeToSearch) > 0) {
            $condition .= ' AND (';
            foreach ($codeToSearch as $singleCode) {
                $condition .= ' I.ISTATCode LIKE'; 
            }
        }
         * 
         */
        $condition .= ' AND I.id_TC_training = T.idTraining';
        $sql = 'SELECT distinct(T.idTraining), T.*, I.ISTATCode, I.code_weight FROM  `OL_training` AS T, OL_TrainingCode AS I' . ' ' .$condition . ' ORDER BY '. 
                $orderby . ' '. $howToOrder;
        if ($limit > 0) $sql.= ' LIMIT '. $limit; 
        $res =  $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);
        if (self::isError($res)) {
            return new AMA_Error("error in reading training",$res);
        }
        $resultCompacted = array();
        $idTraningAlreadyChecked = array();
        $i = 0;
        foreach ($res as $singleTraining) {
            $idToCheck = $singleTraining['idTraining'];
            $isAlreadyChecked = array_search($idToCheck,$idTraningAlreadyChecked);
            if ($isAlreadyChecked === FALSE) {
                if (count($idTraningAlreadyChecked)>0) $i++;
                $resultCompacted[] = $singleTraining;
                array_push($idTraningAlreadyChecked, $singleTraining['idTraining']);
                unset ($resultCompacted[$i]['ISTATCode']);
                unset ($resultCompacted[$i]['code_weight']);
                unset ($resultCompacted[$i]['trainingCode']);
                $resultCompacted[$i]['trainingCode'] = array();
            }
            array_push($resultCompacted[$i]['trainingCode'],array($singleTraining['ISTATCode'],$singleTraining['code_weight']));
        }
//        print_r($resultCompacted);die();
        return $resultCompacted;
    }

     /**
     * list Training offers
     * 
     * @access public
     * 
     * @param 
     * 
     * @return an error if something goes wrong or an array contains all the offers
     */
    public function listTrainingFromISTATCode($code,$orderby='code_weight', $limit=0) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        $sql = 'SELECT T . * , C.ISTATCode, C.code_weight FROM `OL_training` AS T, OL_TrainingCode AS C WHERE T.idTraining = C.id_TC_training AND C.`ISTATCode` like \''. $code.'%\''
                    . ' ORDER BY '. $orderby . ' DESC';
        if ($limit > 0) $sql.= ' LIMIT '. $limit;
        $res =  $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);
        return $res;
    }
    
    /**
     * retrive single Training Offers by ID
     * @param INT $trainingId
     * @return array $res
     */
    public function getTrainingFromId($trainingId) {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        $sql = 'SELECT * FROM `OL_training` where idTraining = '.$trainingId; 
        $res =  $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);
        return $res;
    }    
    
    /** **********
     * CPI AREA
     * 
     */
    
    /**
     * 
     * @param type $name
     * @return type array
     */
    
    public function getCPIFromName($name) {
        $values = array($name);
        $sql = 'SELECT * FROM `OL_CPI` where CPICod = ?'; 
        $res =  $this->getAllPrepared($sql, $values, AMA_FETCH_ASSOC);    
        return $res;
    }

    public function getCPIFromId($id) {
        $values = array($id);
        $sql = 'SELECT * FROM `OL_CPI` where idCPI = ?'; 
        $res =  $this->getAllPrepared($sql, $values, AMA_FETCH_ASSOC);    
        return $res;
    }

    public function getAllCPI() {
        $sql = 'SELECT * FROM `OL_CPI` where 1'; 
        $res =  $this->getAllPrepared($sql, null, AMA_FETCH_ASSOC);    
        return $res;
    }    
    
    public function addCPI($CPIsData) {
        
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        if (!is_array($CPIsData)) return translateFN('dati non corretti');
        foreach ($CPIsData as $CPIData) {
            $data = array(
                'CPICod'=>$this->sql_prepared($CPIData['idCentro']),
                'nameCPI'=>$this->sql_prepared($CPIData['Denominazione']),
                'address'=>$this->sql_prepared($CPIData['Indirizzo']),
                'CAP'=>$this->sql_prepared($CPIData['Cap']),
                'city'=>$this->sql_prepared($CPIData['Comune']),
                'CPIzone'=>$this->sql_prepared($CPIData['BacinoDiCompetenza']),
                'phone'=>$this->sql_prepared($CPIData['Telefono']),
                'fax'=>$this->sql_prepared($CPIData['Fax']),
                'email'=>$this->sql_prepared($CPIData['eMail']),
                'latitude'=>$this->sql_prepared($CPIData['Latitudine']),
                'longitude'=>$this->sql_prepared($CPIData['Longitudine'])
            );
            //fine validazione campi
            $keys = array_keys($data);
            $sql = 'INSERT INTO `OL_CPI` ('.implode(',',$keys).') VALUES ('.implode(',',$data).')';
            ADALogger::log_db("trying inserting the OL_CPI: ".$sql);
            $res = $db->query($sql);

            /*
             * Versione con queryPrepared 
             * In questo caso non vanno preparati i dati precedentemente
             * 
            $values = explode(',',implode(',',$data));
            $sql = 'INSERT INTO OL_CPI ('.implode(',',$keys).') VALUES (?,?,?,?,?,?,?,?,?,?,?)';


            $res = $this->queryPrepared($sql,$values);
             */
            // if an error is detected, an error is created and reported
            if (self::isError($res)) {
                return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in addJobOffer.".AMA_SEP.": ".$res->getMessage());
            }

        }
        unset($CPIsData);

        return $db->lastInsertID();
    }    
    /**
     * return the max id node for a course
     * 
     */
    function get_max_idFN($id_course=1,$id_toc='',$depth=1){
      // return the max id_node of the course
      $dh = $GLOBALS['dh'];
      $id_node_max = $dh->_get_max_idFN($id_course,$id_toc,$depth);
      // vito, 15/07/2009
      if (AMA_DataHandler::isError($id_node_max)) {
        /*
         * Return a ADA_Error object with delayedErrorHandling set to TRUE.
         */
          return new ADA_Error(
            $id_node_max,translateFN('Errore in lettura max id'),
            'get_max_idFN',
            NULL,NULL,NULL,TRUE
          );
      }
      return $id_node_max;
    }

    
}
