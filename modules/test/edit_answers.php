<?php
/**
 * Add exercise
 *
 * @package
 * @author		Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array();

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_AUTHOR);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
	AMA_TYPE_AUTHOR => array('layout', 'node', 'course', 'course_instance'),
);

require_once ROOT_DIR.'/include/module_init.inc.php';

//$self =  whoami();
$self = 'answers';

require_once(ROOT_DIR.'/services/include/author_functions.inc.php');
$layout_dataAr['node_type'] = $self;

$online_users_listing_mode = 2;
$online_users = ADAGenericUser::get_online_usersFN($id_course_instance,$online_users_listing_mode);


require_once(MODULES_TEST_PATH.'/config/config.inc.php');
require_once(MODULES_TEST_PATH.'/include/init.inc.php');
//needed to promote AMADataHandler to AMATestDataHandler. $sess_selected_tester is already present in session
$GLOBALS['dh'] = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

require_once(MODULES_TEST_PATH.'/include/management/answersManagementTest.inc.php');
$question = $dh->test_getNode($_GET['id_question']);
$management = new AnswersManagementTest($_GET['id_question']);
$form_return = $management->run();

if (!AMATestDataHandler::isError($question) && !empty($question)) {
	$get_topic = (isset($_GET['topic'])?'&topic='.$_GET['topic']:'');

	$edit_question_link = CDOMElement::create('a');
	$edit_question_link->setAttribute('href', MODULES_TEST_HTTP.'/edit_question.php?action=mod&id_question='.$question['id_nodo'].$get_topic);
	$edit_question_link->addChild(new CText(sprintf(translateFN('Modifica %s'),translateFN('domanda'))));
	$edit_question_link = $edit_question_link->getHtml();

	$delete_question_link = CDOMElement::create('a');
	$delete_question_link->setAttribute('href', MODULES_TEST_HTTP.'/edit_question.php?action=del&id_question='.$question['id_nodo'].$get_topic);
	$delete_question_link->addChild(new CText(sprintf(translateFN('Cancella %s'),translateFN('domanda'))));
	$delete_question_link = $delete_question_link->getHtml();

	$back_link = CDOMElement::create('a');
	$back_link->setAttribute('href', MODULES_TEST_HTTP.'/index.php?id_test='.$question['id_nodo_radice'].$get_topic.'#liQuestion'.$question['id_nodo']);
	$back_link->addChild(new CText(translateFN('Indietro')));
	$back_link = $back_link->getHtml();
}

// per la visualizzazione del contenuto della pagina
$banner = include ($root_dir.'/include/banner.inc.php');

$content_dataAr = array(
        'head'=>$head_form,
        'banner'=>$banner,
		'path'=>$form_return['path'],
        'form'=>$form_return['html'],
        'status'=>$form_return['status'],
        'user_name'=>$user_name,
        'user_type'=>$user_type,
        'messages'=>$user_messages->getHtml(),
        'agenda'=>$user_agenda->getHtml(),
        'title'=>$node_title,
        'course_title'=>$course_title,
        'back'=>$back,
		'edit_question'=> $edit_question_link,
		'delete_question'=> $delete_question_link,
		'back_link'=> $back_link,
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

$layout_dataAr['JS_filename'] = array(
	JQUERY,
	JQUERY_UI,
	JQUERY_NO_CONFLICT
);
$layout_dataAr['CSS_filename'] = array(
	JQUERY_UI_CSS
);

ARE::render($layout_dataAr, $content_dataAr);