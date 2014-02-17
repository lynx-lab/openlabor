<?php
/**
 * @package     Openlabor
 * @author	Maurizio Graffio Mazzoneschi <grafifo@lynxlab.com>
 * @copyright	Copyright (c) 2013, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */


/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');
require_once(ROOT_DIR.'/config/ada_config.inc.php');
require_once (CORE_LIBRARY_PATH .'/includes.inc.php');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_SWITCHER, AMA_TYPE_ADMIN);

/*
 * specific API inclusion version 1
 * 
 */
//require_once (ROOT_DIR . '/api/v1/include/config.inc.php');

/**
 * Get needed objects
 */
$neededObjAr = array(
AMA_TYPE_VISITOR => array('layout','default_tester'),
AMA_TYPE_SWITCHER => array('layout','default_tester'),
AMA_TYPE_ADMIN => array('layout','default_tester')
);

/**
 * Performs basic controls before entering this module
 */
require_once(ROOT_DIR.'/include/module_init.inc.php');

/**
 * Module Specific inclusions
 */
require_once(MODULES_DIR.'/proRoma/config/config.inc.php');
require_once(MODULES_DIR.'/proRoma/include/includes.inc.php');

//needed to promote AMADataHandler to AMATestDataHandler. $sess_selected_tester is already present in session
//$GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN($sess_selected_tester));
$GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
$dh = $GLOBALS['dh'];
$self = whoami();
if (!isset($typeData)) $typeData = 'jobs';
if (!isset($getCode)) $getCode = 0; // false. Default it do not get the ISTAT code for each corse (only for training)


switch ($typeData) {
    case 'jobs':
       $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
       $jobsData =  $remoteJobs->contents;
       $Jobs = $dh->addJobOffers($jobsData);
       print_r($Jobs);

    case 'training':
       echo 'start time:' . AMA_DataHandler::ts_to_date(time(), "%d/%m/%Y %H:%M:%S") . '<BR />'; 
        
       $remoteTraining = new remoteXMLResource(URL_OF_NON_FINANZIATA_PROV_ROMA,$trainingLabels,$trainingElements);
       $TrainingData =  $remoteTraining->contents;
       $logMsg = 'inizio import training';
       utility::LogUpdate($logMsg);
       $logMsg = ' start time:' . AMA_DataHandler::ts_to_date(time(), "%d/%m/%Y %H:%M:%S");
       utility::LogUpdate($logMsg);
       if ($get_code) {
           $n = 0;
           $nameTrainingAlreadyCoded = array();
           for ($index = 0; $index < count($TrainingData) -1; $index++) {
               $singleTraining = $TrainingData[$index];
               $singleTraining['nameTraining'] = DataValidator::validate_string($singleTraining['nameTraining']);
               $codeExisting = array_search($singleTraining['nameTraining'], $nameTrainingAlreadyCoded);
               if ($singleTraining['nameTraining'] != false && $singleTraining['nameTraining'] != '' && !$codeExisting)  {
                   $logMsg = $index . ') getIstatCode for: ' . $singleTraining['nameTraining'];
                   utility::LogUpdate($logMsg);   
                   $timeStartIstatCode = time();
                   /** 
                    * Get ISTAT Code via CURL
                    */
                   $nameToSearch = urlencode($singleTraining['nameTraining']);
//                   $singleTraining['trainingCode'] = utility::getIstatCode($singleTraining['nameTraining']);
                   $singleTraining['trainingCode'] = utility::getIstatCode($nameToSearch);
                   /*
                    * 
                    */
                   sleep (2);
                   $timeElapsed = time() - $timeStartIstatCode;
                   $logMsg ='getIstatCode for: ' . $singleTraining['nameTraining']. ' return '. $singleTraining['trainingCode'] . ' Time elapsed: ' . $timeElapsed; 
                   utility::LogUpdate($logMsg);   
                   $TrainingData[$index]['trainingCode'] = $singleTraining['trainingCode'];
                   $nameTrainingAlreadyCoded[$singleTraining['trainingCode']] = $singleTraining['nameTraining'];

               }else {
                  $logMsg = 'getIstatCode: ' . $singleTraining['nameTraining'] . ' Already existing. Code: '. $codeExisting;
                  utility::LogUpdate($logMsg);   
                  $singleTraining['trainingCode'] = $codeExisting;
               }
           }
       }
       $logMsg = 'number of imported training: ' . count($TrainingData);
       utility::LogUpdate($logMsg);
       $logMsg = 'number of Code: ' . count($nameTrainingAlreadyCoded);
       utility::LogUpdate($logMsg);
       $logMsg = 'end time:' . AMA_DataHandler::ts_to_date(time(), "%d/%m/%Y %H:%M:%S");
       utility::LogUpdate($logMsg);
       
       $Trainings = $dh->addTrainingOffers($TrainingData);
       var_dump($Trainings);
       echo 'end time:' . AMA_DataHandler::ts_to_date(time(), "%d/%m/%Y %H:%M:%S"). '<BR />'; 
        break;
    
    case 'CPI':
       $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
       $cpiAr = $cpiObj->contents;
       $lastCPI = $dh->addCPI($cpiAr);
       print_r($lastCPI);
       break;
    
}
