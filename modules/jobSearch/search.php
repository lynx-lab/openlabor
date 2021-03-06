<?php
/**
 * received the post data to search
 * prepare the request for openLabor WS
 * interact with the openLabor WS
 * 
 * return json or HTML (?)
 *
 * @package		OpenLabor
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright           Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU Public License v.3
 * @version		0.1
 */

// error_reporting(E_ALL ^ E_NOTICE);
// ini_set('display_errors','On');
//ini_set('display_errors',1);

/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('layout');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_TUTOR => array('layout'),
    AMA_TYPE_AUTHOR => array('layout'),
    AMA_TYPE_VISITOR => array('layout'),
);

/**
 * Performs basic controls before entering this module
 */
require_once(ROOT_DIR.'/include/module_init.inc.php');

require_once('include/config.inc.php');
require_once ROOT_DIR . '/modules/jobSearch/include/Forms/SearchForm.inc.php';
require_once MODULES_DIR. '/jobSearch/include/searchHtmlLib.inc.php';
require_once ROOT_DIR.'/include/data_validation.inc.php';
require_once('include/restRequest.inc.php');
require_once MODULES_DIR. '/jobSearch/include/jobClasses.inc.php';


/**
 * include of AMA specific for OpenLabor.
 * it is used by API
 */
require_once(ROOT_DIR.'/api/'.API_VERSION.'/include/AMAOpenLaborDataHandler.inc.php');



/*
 * 
require_once(MODULES_TEST_PATH.'/config/config.inc.php');
require_once(MODULES_TEST_PATH.'/include/init.inc.php');
 */

//$self = whoami();
$self = whoami();
$render = null;
$options = array();

/**
 * Negotiate login page language
 */
$lang_get = $_GET['lang'];

Translator::loadSupportedLanguagesInSession();
$supported_languages = Translator::getSupportedLanguages();
$login_page_language_code = Translator::negotiateLoginPageLanguage($lang_get);
$_SESSION['sess_user_language'] = $login_page_language_code;

/**
 * @FIXME: al momento non utilizzabile
 */
//print_r($_SESSION);
$navigation_history = $_SESSION['sess_navigation_history'];
$last_visited_node  = $navigation_history->lastModule();
$go_back_link = CDOMElement::create('a', 'href:'.$last_visited_node);
$go_back_link->addChild(new CText(translateFN('Indietro')));
//print_r($go_back_link);



if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' && (isset($_GET['submit']))) {

    if (!array_key_exists('op', $_GET)) {
    //    $op = 'training';
        $op = 'jobs';
        $service_code = SEARCH_JOBS;
//        $op = '001';
    } else {
        $op = $_GET['op'];
    }
    switch ($op) {
        case 'jobs':
            $service_code = SEARCH_JOBS;
            break;
        case 'training':
            $service_code = SEARCH_TRAINING;
            break;
    }
    if (array_key_exists('out', $_GET)) {
        $out = $_GET['out'];
    }
    if (!($_GET)) {
        $_GET['qualificationRequired'] = null;
        $_GET['keywords'] = null;
        $_GET['city'] = null;
    } 

    $toSearch['qualificationRequired'] = $_GET['qualification'];
    $toSearch['keywords'] = $_GET['keywords'];
    $toSearch['city'] = $_GET['city'];

    /*
     * Service code defines operation 
     * 
     * es.: 
     * http://localhost/openlabor/api/v1/requests.json?service_code&keywords=...
     * looks for jobs
     */
    $format = 'json';
    $urlApi = URL_API_REQUESTS.'.'.$format.'?service_code='.$service_code;

    /*
     * data preparation in case of post 
     * json = $jsonToSearch
     * urlEncoded plain post = $encodedToSearch
     * 
     *  $jsonToSearch = json_encode($toSearch);
     */
    $encodedToSearch = '';
    foreach ($toSearch as $key=>$value) {
        if ($encodedToSearch != '') {
            $encodedToSearch .= '&';
        }
        $encodedToSearch .= $key . '=' . urlencode($value);
    }

    /*
     * prepare the header of http request in case method != GET
     */

    //$curlHeader = array('Content-Type: application/json');
    //$curlHeader = array("Content-Type: application/x-www-form-urlencoded");

    /*
     * In case method is GET
     * we have to make the URL with the GET data
     */
    $curlPost = false;
    if (!$curlPost) {
        if ($toSearch['keywords'] != '') {
            $urlApi .= '&keywords='.urlencode($toSearch['keywords']);
        }
        if ($toSearch['city'] != '') {
            $urlApi .= '&city='.urlencode($toSearch['city']);
            /*
            if ($toSearch['keywords'] != '') {
                $more = '&';
            }else {
                $more = '?';
            }
            $urlApi .= $more.'city='.urlencode($toSearch['city']);
             * 
             */
        }
        if ($toSearch['qualificationRequired'] != '') {
            $urlApi .= '&qualification='.urlencode($toSearch['qualificationRequired']);
            /*
            if ($toSearch['keywords'] != '' && $toSearch['city'] != '') {
                $more = '&';
            }else {
                $more = '?';
            }
            $urlApi .= $more.'qualificationRequired='.urlencode($toSearch['qualificationRequired']);
             * 
             */
        }
    }
    /*
     * Send request to the WS
     * using the REST_request class
     */
    $jobsResults = REST_request::sendRequest($jsonToSearch,$curlHeader,$urlApi,$curlPost);
    $jobsData = json_decode($jobsResults);
    
    //$apiTextLink = translateFN('Esegui la chiamata alle API utilizzata per la ricerca');
    $apiTextLink = translateFN('URL dell\'API utilizzata: ').$urlApi;
    $linkApi = BaseHtmlLib::link($urlApi, $apiTextLink);
    
    //require_once("./include/tag_cloud.inc.php");
    
    $summary =  translateFN('Risultati della ricerca'); // per: '.$labelsDesc;
    $min = 0;
    $max = 100;
    switch ($op) {
        case 'jobs':
            $withLink = true;
            $jobsTable = searchHtmlLib::OffersTable($jobsData, $withLink);
            $data = $jobsTable->getHtml();

            $searchParameter = '';
            if ($toSearch['keywords'] == '' && $toSearch['city'] == '' && $toSearch['qualificationRequired'] == '') {
                $searchParameter = translateFN('Tutto');

            } else {
                if ($toSearch['keywords'] != '') {
                    $searchParameter .= $toSearch['keywords'];
                }
                if ($toSearch['city'] != '') {
                    if ($searchParameter != '') {
                        $searchParameter .= ', ';
                    }
                    $searchParameter .= $toSearch['city'];
                }
                if ($toSearch['qualificationRequired'] != '') {
                    if ($searchParameter != '') {
                        $searchParameter .=', ';
                    }
                    $searchParameter .= $toSearch['qualificationRequired'];
                }
            }    

            $help = translateFN('Hai cercato').': '. $searchParameter; 
            $href = HTTP_ROOT_DIR.'/modules/jobSearch/search.php';
            $textLink = translateFN('Cambia i parametri di ricerca');
            $linkNewSearch = BaseHtmlLib::link($href, $textLink);
            $help .= ' - ' . $linkNewSearch->getHtml();
            $help .= ' - ' . $linkApi->getHtml();
            
        
            break;
            
        case 'training':
            $withLink = true;
            $jobsTable = searchHtmlLib::TrainingTable($jobsData, $withLink);
            $data = $jobsTable->getHtml();

            $searchParameter = '';
            if ($toSearch['keywords'] == '' && $toSearch['city'] == '' && $toSearch['qualificationRequired'] == '') {
                $searchParameter = translateFN('Tutto');

            } else {
                if ($toSearch['keywords'] != '') {
                    $searchParameter .= $toSearch['keywords'];
                }
                if ($toSearch['city'] != '') {
                    if ($searchParameter != '') {
                        $searchParameter .= ', ';
                    }
                    $searchParameter .= $toSearch['city'];
                }
                if ($toSearch['qualificationRequired'] != '') {
                    if ($searchParameter != '') {
                        $searchParameter .=', ';
                    }
                    $searchParameter .= $toSearch['qualificationRequired'];
                }
            }    

            $help = translateFN('Hai cercato').': '. $searchParameter; 
            $href = HTTP_ROOT_DIR.'/modules/jobSearch/search.php';
            $textLink = translateFN('Cambia i parametri di ricerca');
            $linkNewSearch = BaseHtmlLib::link($href, $textLink);
            $help .= ' - ' . $linkNewSearch->getHtml();
            $help .= ' - ' . $linkApi->getHtml();
        
            break;
    }
    /*
     * 
    $layout_dataAr['JS_filename'] = array(
                    JQUERY,
                    JQUERY_DATATABLE,
                    JQUERY_NO_CONFLICT
            );
    $layout_dataAr['CSS_filename']= array(
                    JQUERY_DATATABLE_CSS
            );
     */
    $dataTablesLanguage = 'language_'.$_SESSION['sess_user_language'].'.txt';
    $userLanguage = $_SESSION['sess_user_language'];

    $options['onload_func'] = 'dataTablesExec()';
//  $optionsAr = array('onload_func' => "close_page('$close_page_message');");
//  $optionsAr = array('onload_func' => "dataTablesExec('$userLanguage');");
//  print_r($userLanguage);
} else {
    switch ($op) {
        case 'jcard':
            $service_code = SEARCH_JOBS;
            /*
             * Send request to the api
             * using the REST_request class
             */
            if(DataValidator::is_uinteger($_GET['id']) !== false) {
                $withLink = TRUE;
                $id = $_GET['id'];
                $jsonToSearch = '';
                $curlHeader = '';
                $curlPost = false;
                $format = 'json';
                $urlApi = URL_API_REQUESTS.'.'.$format.'?service_code='.$service_code.'&jobID='.$id;
                $jobResult = REST_request::sendRequest($jsonToSearch,$curlHeader,$urlApi,$curlPost);
                $jobData = json_decode($jobResult);
                $jobDataAr = (array)$jobData[0];
                $positionCode4Char = substr($jobDataAr['positionCode'],0,7);
                
                $jobTable = searchHtmlLib::jobCardTable($jobData, $withLink);
                $jobDataHtml = $jobTable->getHtml();
//                print_r($jobsData);
                $relatedTrainingData = job::getRelatedTraining($positionCode4Char); //listTrainingFromISTATCode
//                $relatedTrainingData = job::listTrainingFromISTATCode($positionCode4Char); //listTrainingFromISTATCode
//                print_r($relatedTraining);
                $jobCard = searchHtmlLib::jobCardShow($jobDataHtml, $relatedTrainingData, true);
                $data = $jobCard->getHtml();
                    $options['onload_func'] = 'dataTablesTRelatedExec()';

            } else {
                $data = 'scheda';
            }
            $help = ucfirst(translateFN('offer details')); 
            $back_link = CDOMElement::create('a', 'href:'.$last_visited_node);
            $back_link->addChild(new CText(translateFN('Torna')));            
            $back='<a href="javascript: history.go(-1)">'.translateFN('Back to the list').'</a>';
            $help .= ' '. $back;            
            break;
        case 'tcard':
            $service_code = SEARCH_TRAINING;
            /*
             * Send request to the api
             * using the REST_request class
             */
            if(DataValidator::is_uinteger($_GET['id']) !== false) {
                $layout_dataAr['JS_filename'] = array(
                                MODULES_DIR.'/jobSearch/js/leaflet/leaflet.js',
                                MODULES_DIR.'/jobSearch/js/leaflet_map.js'

                        );
                $layout_dataAr['CSS_filename']= array(
                                MODULES_DIR.'/jobSearch/js/leaflet/leaflet.css'
                        );
                $withLink = TRUE;
                $id = $_GET['id'];
                $jsonToSearch = '';
                $curlHeader = '';
                $curlPost = false;
                $format = 'json';
                $urlApi = URL_API_REQUESTS.'.'.$format.'?service_code='.$service_code.'&ID='.$id;
                $Result = REST_request::sendRequest($jsonToSearch,$curlHeader,$urlApi,$curlPost);
                $DataObj = json_decode($Result);
                $DataAll = (array)$DataObj;
                $Data = (array)$DataAll[0];
                $lat = $Data['t_latitude'];
                $lon = $Data['t_longitude'];
    //            $lon = ($lon*-1);
                $zoom = '16';
                $PoppetContent = htmlspecialchars($Data['nameTraining'],ENT_QUOTES) .'<BR /> ' 
			. htmlspecialchars($Data['trainingAddress'],ENT_QUOTES)
			.'<BR /> ' . $Data['CAP'].'<BR /> ' 
			. htmlspecialchars($Data['city'],ENT_QUOTES);
                $PoppetContent = htmlspecialchars($PoppetContent,ENT_QUOTES);
                
                $options['onload_func'] = "makeMap('$lat','$lon','$zoom','$PoppetContent')";
                
                //print_r($cpiData);
                unset($Data['t_latitude']);
                unset($Data['t_longitude']);
                unset($Data['idTraining']);
                unset($Data['IdTrainingOriginal']);
                unset($Data['hash']);
                unset($Data['t_published']);
                unset($Data['t_idNode']);
                unset($Data['t_locale']);
                unset($Data['t_dateInsert']);
                unset($Data['t_expiration']);
                unset($Data['t_expiration']);
                unset($Data['trainingCode']);
                
                $DataHtml = searchHtmlLib::trainingShow($Data);
                $data = $DataHtml->getHtml(); 
                
                
//                $Table = searchHtmlLib::trainingCardTable($DataObj, $withLink);
                //$data = $Table->getHtml();
            } else {
                $data = 'scheda';
            }
            $help = ucfirst(translateFN('training details')); 
            $back_link = CDOMElement::create('a', 'href:'.$last_visited_node);
            $back_link->addChild(new CText(translateFN('Torna')));            
            $back='<a href="javascript: history.go(-1)">'.translateFN('Back to the list').'</a>';
            $help .= ' '. $back;            
            break;            
        case 'tMap':
            $service_code = SEARCH_TRAINING;
            /*
             * Send request to the api
             * using the REST_request class
             */
            if(DataValidator::is_uinteger($_GET['id']) !== false) {
                $layout_dataAr['JS_filename'] = array(
                                MODULES_DIR.'/jobSearch/js/leaflet/leaflet.js',
                                MODULES_DIR.'/jobSearch/js/leaflet_map.js'

                        );
                $layout_dataAr['CSS_filename']= array(
                                MODULES_DIR.'/jobSearch/js/leaflet/leaflet.css'
                        );
                $withLink = TRUE;
                $id = $_GET['id'];
                $jsonToSearch = '';
                $curlHeader = '';
                $curlPost = false;
                $format = 'json';
                $urlApi = URL_API_REQUESTS.'.'.$format.'?service_code='.$service_code;
                $Result = REST_request::sendRequest($jsonToSearch,$curlHeader,$urlApi,$curlPost);
                $DataObj = json_decode($Result);
                foreach ($DataObj as $singleObj) {
                    $singleData = (array)$singleObj;
                }
                $DataAll = (array)$DataObj;
                $Data = (array)$DataAll[0];
                $lat = $Data['t_latitude'];
                $lon = $Data['t_longitude'];
    //            $lon = ($lon*-1);
                $zoom = '16';
                $PoppetContent = htmlspecialchars($Data['nameTraining'],ENT_QUOTES) .'<BR /> ' 
			. htmlspecialchars($Data['trainingAddress'],ENT_QUOTES)
			.'<BR /> ' . $Data['CAP'].'<BR /> ' 
			. htmlspecialchars($Data['city'],ENT_QUOTES);
                $PoppetContent = htmlspecialchars($PoppetContent,ENT_QUOTES);
                
                $options['onload_func'] = "makeMap('$lat','$lon','$zoom','$PoppetContent')";
                
                //print_r($cpiData);
                unset($Data['t_latitude']);
                unset($Data['t_longitude']);
                unset($Data['idTraining']);
                unset($Data['IdTrainingOriginal']);
                unset($Data['hash']);
                unset($Data['t_published']);
                unset($Data['t_idNode']);
                unset($Data['t_locale']);
                unset($Data['t_dateInsert']);
                unset($Data['t_expiration']);
                
                $DataHtml = searchHtmlLib::trainingShow($Data);
                $data = $DataHtml->getHtml(); 
                
                
//                $Table = searchHtmlLib::trainingCardTable($DataObj, $withLink);
                //$data = $Table->getHtml();
            } else {
                $data = 'scheda';
            }
            $help = ucfirst(translateFN('training details')); 
            $back_link = CDOMElement::create('a', 'href:'.$last_visited_node);
            $back_link->addChild(new CText(translateFN('Torna')));            
            $back='<a href="javascript: history.go(-1)">'.translateFN('Back to the list').'</a>';
            $help .= ' '. $back;            
            break;            
            
        case 'cpi':
            $layout_dataAr['JS_filename'] = array(
                            MODULES_DIR.'/jobSearch/js/leaflet/leaflet.js',
                            MODULES_DIR.'/jobSearch/js/leaflet_map.js'
                            
                    );
            $layout_dataAr['CSS_filename']= array(
                            MODULES_DIR.'/jobSearch/js/leaflet/leaflet.css'
                    );

            if(DataValidator::is_uinteger($_GET['idcpi']) !== false) {
                $idcpi = $_GET['idcpi'];
                $cpiData = $_SESSION['sess_cpi_'.$idcpi];
                
                /*
                 * options for marker (using leaflet.js)
                 */
                $lat = $_SESSION['sess_cpi_'.$idcpi]['latitude'];
                $lon = $_SESSION['sess_cpi_'.$idcpi]['longitude'];
    //            $lon = ($lon*-1);
                $zoom = '16';
                $PoppetContent = htmlspecialchars($cpiData['cpicod'],ENT_QUOTES) .'<BR /> ' 
			. htmlspecialchars($cpiData['address'],ENT_QUOTES)
			.'<BR /> ' . $cpiData['cap'].'<BR /> ' 
			. htmlspecialchars($cpiData['city'],ENT_QUOTES);
                $PoppetContent = htmlspecialchars($PoppetContent,ENT_QUOTES);
                
                $options['onload_func'] = "makeMap('$lat','$lon','$zoom','$PoppetContent')";
                
                //print_r($cpiData);
                $cpiDataHtml = searchHtmlLib::CPIShow($cpiData);
                $data = $cpiDataHtml->getHtml(); 
            } else {
                $data = translateFN('no data');
            }
//            print_r($_SESSION['sess_cpi_'.$idcpi]);

            $back_link = CDOMElement::create('a', 'href:'.$last_visited_node);
            $back_link->addChild(new CText(translateFN('Torna')));            
            $help = translateFN('Centro Per l\'Impiego').': '.$cpiData['cpicod'];
            $back='<a href="javascript: history.go(-1)">'.translateFN('Torna alla scheda').'</a>';
            $help .= ' '. $back;
            break;
        case '': 
            default:
            $help = translateFN('Imposta i parametri per la ricerca'); 
            $searchForm = new SearchForm();
            $data = $searchForm->getHtml();
            break;
    }
}
/*
 * Go back link
 */

$breadcrumbs = translateFN('Ricerca di lavoro e formazione');
    /*
     * needed to sort the tables
     */
    $layout_dataAr['JS_filename'] = array(
                    JQUERY,
                    JQUERY_DATATABLE,
                    JQUERY_DATATABLE_DATE,
                    JQUERY_NO_CONFLICT,
                    MODULES_DIR.'/jobSearch/js/leaflet/leaflet.js',
                    MODULES_DIR.'/jobSearch/js/leaflet_map.js'
            );
    $layout_dataAr['CSS_filename']= array(
                    JQUERY_DATATABLE_CSS,
                    MODULES_DIR.'/jobSearch/js/leaflet/leaflet.css'
        );

/*
 * Output
 */
$content_dataAr = array(
    'status' => translateFN('Ricerca'),
    'breadcrumbs'=>$breadcrumbs,
    'user_name' => $user_name,
    'user_type' => $user_type,
    'user_level' => $user_level,
    'visited' => '-',
    'icon' => $icon,
    //'navigation_bar' => $navBar->getHtml(),
    'help' => $help,
    'data' =>  $data,
    'go_back' => $go_back_link->getHtml(),
//    'back_link' => $back_link->getHtml(),
    'title' => $title
);

$content_dataAr['notes'] = $other_node_data['notes'];
$content_dataAr['personal'] = $other_node_data['private_notes'];


if ($log_enabled)
    $content_dataAr['go_history'] = $go_history;
else
    $content_dataAr['go_history'] = translateFN("cronologia");

if ($reg_enabled) {
    $content_dataAr['add_bookmark'] = $add_bookmark;
} else {
    $content_dataAr['add_bookmark'] = "";
}

$content_dataAr['bookmark'] = $bookmark;
$content_dataAr['go_bookmarks_1'] = $go_bookmarks;
$content_dataAr['go_bookmarks_2'] = $go_bookmarks;

if ($mod_enabled) {
    $content_dataAr['add_node'] = $add_node;
    $content_dataAr['edit_node'] = $edit_node;
    $content_dataAr['delete_node'] = $delete_node;
    $content_dataAr['send_media'] = $send_media;
    $content_dataAr['add_exercise'] = $add_exercise;
    $content_dataAr['add_note'] = $add_note;
    $content_dataAr['add_private_note'] = $add_private_note;
    $content_dataAr['edit_note'] = $edit_note;
    $content_dataAr['delete_note'] = $delete_note;
    $content_dataAr['import_exercise'] = $import_exercise;
} else {
    $content_dataAr['add_node'] = '';
    $content_dataAr['edit_node'] = '';
    $content_dataAr['delete_node'] = '';
    $content_dataAr['send_media'] = '';
    $content_dataAr['add_note'] = '';
    $content_dataAr['add_private_note'] = '';
    $content_dataAr['edit_note'] = '';
    $content_dataAr['delete_note'] = '';
}

if ($com_enabled) {
    $content_dataAr['ajax_chat_link'] = $ajax_chat_link;
    $content_dataAr['messages'] = $user_messages->getHtml();
    $content_dataAr['agenda'] = $user_agenda->getHtml();
    $content_dataAr['events'] = $user_events->getHtml();
    $content_dataAr['chat_users'] = $online_users;
} else {
    $content_dataAr['chat_link'] = translateFN("chat non abilitata");
    $content_dataAr['messages'] = translateFN("messaggeria non abilitata");
    $content_dataAr['agenda'] = translateFN("agenda non abilitata");
    $content_dataAr['chat_users'] = "";
}

ARE::render($layout_dataAr, $content_dataAr, $render, $options);
//ARE::render($layout_dataAr, $content_dataAr);


