<?php
/**
 * received the post data to report
 * prepare the request for openLabor API
 * interact with the openLabor API
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
ini_set('display_errors',0);

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
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('layout'),
    AMA_TYPE_TUTOR => array('layout'),
    AMA_TYPE_AUTHOR => array('layout'),
);

/**
 * Performs basic controls before entering this module
 */
require_once(ROOT_DIR.'/include/module_init.inc.php');

require_once('include/config.inc.php');
require_once ROOT_DIR . '/modules/jobSearch/include/Forms/jobReportForm.inc.php';
//require_once MODULES_DIR. '/jobSearch/include/jobReportHtmlLib.inc.php';
require_once ROOT_DIR.'/include/data_validation.inc.php';
require_once('include/restRequest.inc.php');


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



if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

    $jobReportPost = $_POST;
    
    /* **********
     * send request to API in order to write the job offer
     * prepare the header of http request 
     */

    $jobReportPost['sourceJob'] = $_SESSION['sess_userObj']->username;
    $jobReportPost['jobExpiration'] = Abstract_AMA_DataHandler::date_to_ts($jobReportPost['jobExpiration']);
//    $jobReportPost = $_SESSION['sess_userObj']->id_user();

   
    
    //$curlHeader = array('Content-Type: application/json');
    //$curlHeader = array("Content-Type: multiport/form-data");
    //$curlHeader = array('Accept: application/json','Content-Type: application/json');
    $format = 'json';
    $urlApi = URL_API_REQUESTS.'.'.$format;
    $curlHeader = array("Content-Type: application/x-www-form-urlencoded");
    $jobReportData = http_build_query($jobReportPost);
    $curlPost = true;
    $jobsCode = REST_request::sendRequest($jobReportData,$curlHeader,$urlApi,$curlPost);
    $jobReportedId = json_decode($jobCode,TRUE);

    
    /*
     * needed to sort the tables
     */
    $layout_dataAr['JS_filename'] = array(
                    JQUERY,
                    JQUERY_DATATABLE,
                    JQUERY_DATATABLE_DATE,
                    JQUERY_NO_CONFLICT
            );
    $layout_dataAr['CSS_filename']= array(
                    JQUERY_DATATABLE_CSS
            );
    

    $dataTablesLanguage = 'language_'.$_SESSION['sess_user_language'].'.txt';
    $userLanguage = $_SESSION['sess_user_language'];
//    $options['onload_func'] = "dataTablesExec('$userLanguage');";
//    $options['onload_func'] = 'dataTablesExec("it")';
    $options['onload_func'] = 'dataTablesExec()';
//  $optionsAr = array('onload_func' => "close_page('$close_page_message');");
//  $optionsAr = array('onload_func' => "dataTablesExec('$userLanguage');");
//  print_r($userLanguage);
    $jobIdAr = json_decode($jobsCode, true);
    $data=translateFN('Successful reported job with id').' ' .$jobIdAr['AddedJobId'];
} else {
    $help = translateFN('Send a job offer'); 
    $jobReportForm = new JobReportForm();
    $data = $jobReportForm->getHtml();
}
/*
 * Go back link
 */

$breadcrumbs = translateFN('Report a job');
//print_r($_SESSION['sess_userObj']);

/*
 * Output
 */
$content_dataAr = array(
    'status' => translateFN('Report'),
    'breadcrumbs'=>$breadcrumbs,
    'user_name' => $_SESSION['sess_userObj']->nome,
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


