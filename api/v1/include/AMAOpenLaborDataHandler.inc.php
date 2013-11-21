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
        $oldOffers = $this->listOffers();
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
        if (!self::isError($CPIData) && is_array($CPIData)) {
            $idCPI = $CPIData[0]['idCPI'];
        }
        
        if (!is_int($JobData['jobExpiration'])) {
            $jobexpiration = Abstract_AMA_DataHandler::date_to_ts($JobData['jobExpiration']); 
        } else {
            $jobexpiration = $JobData['jobExpiration'];
        }
        
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
            $JobData['IdJobOriginal'],
            $JobData['j_latitude'],
            $JobData['j_longitude'],
            $JobData['media_url'],
            $JobData['locale'],
            $JobData['sourceJobName'],
            $JobData['sourceJobSurname'],
            $JobData['published']
            
	);
        unset($JobData);

        //fine validazione campi
        $update_sql = 'UPDATE OL_jobOffers SET idJobOriginal=?, jobExpiration=?, workersRequired=?, professionalProfile=?, positionCode=?, position=?,
                contractType=?, qualificationRequired=?, descriptionQualificationRequired=?, professionalTrainingRequired=?, address=?, zipCode=?, cityCompany=?,nation=?, 
                idCPI=?, experienceRequired=?, durationExperience=?, minAge=?, maxAge=?, remuneration=?, rewards=?, reservedForDisabled=?, favoredCategoryRequests=?,
                ownVehicle=?, notes=?, linkMoreInfo=?, dateInsert=?, sourceJob=?, j_latitude=?,j_longitude=?,media_url=?,locale=?,sourceJobName=?,sourceJobSurname=?,published=? where idJobOriginal=?';
        
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
     * @abstract add job offer to table
     * @todo add a node each job
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
            'address'=>$this->sql_prepared($JobData['address']),
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
            'published'=>$this->sql_prepared($JobData['published'])
                
	);
        unset($JobData);

        //fine validazione campi
        $keys = array_keys($data);

        $sql = "INSERT INTO `OL_jobOffers` (".implode(',',$keys).") VALUES (".implode(",",$data).")";
        ADALogger::log_db("trying inserting the job offer: ".$sql);

        $res = $db->query($sql);
        // if an error is detected, an error is created and reported
        if (self::isError($res)) {
            return new AMA_Error($this->errorMessage(AMA_ERR_ADD)." while in addJobOffer.".AMA_SEP.": ".$res->getMessage());
        }
        return $db->lastInsertID();
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
    public function listOffers($condition='where 1') {
        $db =& $this->getConnection();
        if (self::isError($db)) return $db;
        $sql = 'SELECT O.*,C.* FROM `OL_jobOffers` as O, `OL_CPI` as C '. ' ' .$condition.
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
}
