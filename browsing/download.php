<?php
/**
 * Download Area
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright           Copyright (c) 2011, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.2
 */


/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';
/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'user');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR, AMA_TYPE_STUDENT);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_TUTOR => array('layout','node','course'),
  AMA_TYPE_STUDENT => array('layout','node','course')
);

require_once ROOT_DIR.'/include/module_init.inc.php';

$self =  whoami();

include_once 'include/browsing_functions.inc.php';
include_once ROOT_DIR.'/include/upload_funcs.inc.php';

/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR.'/include/HtmlLibrary/UserModuleHtmlLib.inc.php';

if (isset($err_msg)) {
    $status = $err_msg;
} else {
    $status = translateFN('Area di condivisione files');
}

$help = translateFN('Da qui lo studente pu&ograve; inviare un file da allegare al nodo corrente');

$id_node = $_SESSION['sess_id_node'];
$id_course = $_SESSION['sess_id_course'];
$id_course_instance = $_SESSION['sess_id_course_instance'];

// ******************************************************
// get user object
$userObj = read_user_from_DB($sess_id_user);
if ((is_object($userObj)) && (!AMA_dataHandler::isError($userObj))) {
               $id_profile = $userObj->tipo;
               $user_name =  $userObj->username;
               $user_name_name = $userObj->nome;
               $user_type = $userObj->convertUserTypeFN($id_profile);
               $user_family = $userObj->template_family;
		if ($id_profile==AMA_TYPE_STUDENT) {
	               $user_history = $userObj->history;
	               $user_level = $userObj->get_student_level($sess_id_user,$sess_id_course_instance);
		}
}  else {
               $errObj = new ADA_error(translateFN("Utente non trovato"),translateFN("Impossibile proseguire."));
}

$ymdhms = today_dateFN();

$help = translateFN("Da qui lo studente può scaricare i file allegati ai nodi");

$banner = include ("$root_dir/include/banner.inc.php");

    $course_ha = $dh->get_course($id_course);
    if (AMA_DataHandler::isError($course_ha)){
      $msg = $course_ha->getMessage();
      header("Location: " . $http_root_dir . "/browsing/student.php?status=$msg");
    }

    $author_id = $course_ha['id_autore'];
    //il percorso in cui caricare deve essere dato dal media path del corso, e se non presente da quello di default
    if($course_ha['media_path'] != "") {
      $media_path = $course_ha['media_path']  ;
    }
    else {
      $media_path = MEDIA_PATH_DEFAULT . $author_id ;
    }
    $download_path = $root_dir . $media_path;

if (isset($_GET['file'])){
    $complete_file_name = $_GET['file'];
    $filenameAr = explode('_',$complete_file_name);
    $stop = count($filenameAr)-1;
    $course_instance = $filenameAr[0];
    $id_sender  = $filenameAr[1];
    $id_node =  $filenameAr[2]."_".$filenameAr[3];
    $filename = "";
    for ($k = 5; $k<=$stop;$k++){
        $filename .=  $filenameAr[$k];
        if ($k<$stop)
           $filename .= "_";
    }
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
   // always modified
    header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");                          // HTTP/1.0
    //header("Content-Type: text/plain");
    //header("Content-Length: ".filesize($name));
    header("Content-Description: File Transfer");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=".basename($filename));
    @readfile("$download_path/$complete_file_name");
    exit;

} else {

	// indexing files
	$elencofile = leggidir($download_path);
	if ($elencofile == NULL) {
//           $lista = translateFN("Nessun file inviato dagli studenti di questa classe.");
           $html = translateFN("Nessun file inviato dagli studenti di questa classe.");
	} else {
  //          $fstop = count($elencofile);
  //          $lista ="<ol>";
        //  for  ($i=0; $i<$fstop; $i++){
//            $div = CDOMElement::create('div','id:file_sharing');
            $table = CDOMElement::create('table','id:file_sharing_table');
//            $div->addChild($table);
            $thead = CDOMElement::create('thead');
            $tbody = CDOMElement::create('tbody');
            $tfoot = CDOMElement::create('tfoot');
            $table->addChild($thead);
            $table->addChild($tbody);
            
            $trHead = CDOMElement::create('tr');

            $thHead = CDOMElement::create('th','class: file');
            $thHead->addChild(new CText(translateFN('file')));
            $trHead->addChild($thHead);

            $thHead = CDOMElement::create('th','class: student');
            $thHead->addChild(new CText(translateFN('inviato da')));
            $trHead->addChild($thHead);

            $thHead = CDOMElement::create('th','class: date');
            $thHead->addChild(new CText(translateFN('data')));
            $trHead->addChild($thHead);
            
            $thHead = CDOMElement::create('th','class: node');
            $thHead->addChild(new CText(translateFN('nodo')));
            $trHead->addChild($thHead);

            $thead->addChild($trHead);
            
            $i=0;
            foreach ($elencofile as $singleFile) {
                $i++;
        	 $data = $singleFile['data'];
         	 $complete_file_name = $singleFile['file'];
	         $filenameAr = explode('_',$complete_file_name);
	         $stop = count($filenameAr)-1;
	         $course_instance = $filenameAr[0];
	         $id_sender  = $filenameAr[1];
		 if (is_numeric($id_sender)) {
		         $id_node =  $filenameAr[2]."_".$filenameAr[3];
		         $filename = '';
		         for ($k = 5; $k<=$stop;$k++){
		              $filename .=  $filenameAr[$k];
		              if ($k<$stop)
       		          	$filename .= "_";
	          	 }
                        $sender_array = $common_dh->get_user_info($id_sender);
                        if(!AMA_Common_DataHandler::isError($sender_array)) {
                            $id_profile = $sender_array['tipo'];
                            switch ($id_profile){
                                case   AMA_TYPE_STUDENT:
                                case   AMA_TYPE_AUTHOR:
                                case   AMA_TYPE_TUTOR:
                                      $user_name_sender =  $sender_array['username'];
                                      $user_surname_sender =  $sender_array['cognome'];
                                      $user_name_sender = $sender_array['nome'];
                                      $user_name_complete_sender = $user_name_sender .' ' . $user_surname_sender;
                                        break;
                                default:
                                    // errore
                                   $sender_error = 1;
                            }
         		}

	        	if ((!$sender_error) AND ($course_instance == $sess_id_course_instance)){
                            if (!isset($fid_node) OR ($fid_node == $id_node)) {
                                $out_fields_ar = array('nome');
                                $clause = "ID_NODO = '$id_node'";
                                $nodes = $dh->_find_nodes_list($out_fields_ar, $clause);
                                if(!AMA_DB::isError($nodes)) {
                                    foreach ($nodes as $single_node) {
                                        $id_node = $single_node[0];
                                        $node_name = $single_node[1];
                                    }
                                }
				$tr = CDOMElement::create('tr','id:row'.$i);
				$tbody->addChild($tr);

                                $td = CDOMElement::create('td');
                                $td->addChild(new CText('<a href="download.php?file='.$complete_file_name.'" target=_blank>'.$filename.'</a> '));
                                $tr->addChild($td);
                                
                                $td = CDOMElement::create('td');
                                $td->addChild(new CText($user_name_complete_sender));
                                $tr->addChild($td);

                                $td = CDOMElement::create('td');
                                $td->addChild(new CText($data));
                                $tr->addChild($td);

                                $td = CDOMElement::create('td');
                                $td->addChild(new CText('<a href=../browsing/view.php?id_node='.$id_node.'>'.$node_name.'</a>'));
                                $tr->addChild($td);
                                
                            }   
                        }
		}
           } // end foreach
           $html = $table->getHtml();
        } 
}
  $navigation_history  = $_SESSION['sess_navigation_history'];
  $last_visited_module = $navigation_history->lastModule();

$node_data = array(
               'banner'=>$banner,
//               'data'=>$lista,
               'data'=>$html,
               'status'=>$status,
               'user_name'=>$user_name_name,
               'user_type'=>$user_type,
               'messages'=>$user_messages->getHtml(),
               'agenda'=>$user_agenda->getHtml(),
               'title'=>$node_title,
               'course_title'=>$course_title,
               'path'=>$nodeObj->findPathFN(),
               'help'=>$help,
               'back'=>$last_visited_module
);


/* 5.
  HTML page building
  */

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_DATATABLE,
		JQUERY_NO_CONFLICT
	);
$layout_dataAr['CSS_filename']= array(
		JQUERY_DATATABLE_CSS
	);
  $render = null;
  $options['onload_func'] = 'dataTablesExec()';
  ARE::render($layout_dataAr, $node_data, $render, $options);

?>
