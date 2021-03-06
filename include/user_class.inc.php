<?php
/**
 * User classes
 *
 *
 * @package		model
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		user_classes
 * @version		0.1
 */

/**
 *
 *
 */
abstract class ADAGenericUser {
    /*
   * Data stored in table Utente
    */
    public $id_user;
    public $nome;
    public $cognome;
    public $tipo;
    public $email;
    public $telefono;
    public $username;
    public $template_family;   // layout
    // ADA specific
    protected $indirizzo;
    protected $citta;
    protected $provincia;
    protected $nazione;
    protected $codice_fiscale;
    protected $birthdate;
    protected $sesso;
    protected $stato;
    protected $lingua;
    protected $timezone;

    // we do not store user's password ???
    protected $password;
    // END of ADA specific

// ATTENZIONE A QUESTI QUI SOTTO
    public $livello = 1;
    public $history='';
    public $exercise='';
    public $address;
    public $status;
    public $full=0; //  user exists
    public $error_msg;

    /*
   * Data stored in table Utente_Tester
    */
    protected $testers;
    /*
   * Path to user's home page
    */
    protected $homepage;

    /*
   * getters
    */

    public function getId() {
        return $this->id_user;
    }

    public function getFirstName() {
        return $this->nome;
    }

    public function getLastName() {
        return $this->cognome;
    }

    public function getFullName() {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function getType() {
        return $this->tipo;
    }

    public function getTypeAsString() {
        switch($this->tipo) {
            case AMA_TYPE_ADMIN:
                return translateFN('Super amministratore');
            case AMA_TYPE_SWITCHER:
                return translateFN('Amministratore del provider');
            case AMA_TYPE_AUTHOR:
                return translateFN('Autore');
            case AMA_TYPE_TUTOR:
                return translateFN('Tutor');
            case AMA_TYPE_STUDENT:
                return translateFN('Studente');
            default:
                return translateFN('Ospite');
        }
    }
    public function getEmail() {
        return $this->email;
    }

    public function getAddress() {
        if($this->indirizzo != 'NULL') {
            return $this->indirizzo;
        }
        return '';
    }

    public function getCity() {
        if($this->citta != 'NULL') {
            return $this->citta;
        }
        return '';
    }

    public function getProvincia() {
        if($this->provincia != 'NULL') {
            return $this->provincia;
        }
        return '';
    }

    public function getProvince() {
        if($this->provincia != 'NULL') {
            return $this->provincia;
        }
        return '';
    }

    public function getCountry() {
        if($this->nazione != 'NULL') {
            return $this->nazione;
        }
        return '';
    }

    public function getFiscalCode() {
        return $this->codice_fiscale;
    }

    public function getBirthDate() {
        return $this->birthdate;
    }

    public function getGender() {
        return $this->sesso;
    }

    public function getPhoneNumber() {
        if($this->telefono != 'NULL') {
            return $this->telefono;
        }
        return '';
    }

    public function getStatus() {
        return $this->stato;
    }

    public function getLanguage() {
        return $this->lingua;
    }

    public function getTimezone() {
        return $this->timezone;
    }

    public function getUserName() {
        return $this->username;
    }

    public function getTesters() {
        if(is_array($this->testers)) {
            return $this->testers;
        }
        return array();
    }

    public function getDefaultTester() {
        if(is_array($this->testers) && sizeof($this->testers) > 0) {
            return $this->testers[0];
        }
        return NULL;
    }

    public function getHomePage($msg = null) {
        if ($msg!=null) {
            return $this->homepage."?message=$msg";
        }
        return $this->homepage;
    }

    /*
   * setters
    */
    public function setFirstName($firstname) {
        $this->nome = $firstname;
    }
    public function setLastName($lastname) {
        $this->cognome = $lastname;
    }

    public function setType($type) {
        $this->tipo = $type;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPhoneNumber($phone_number) {
        $this->telefono = $phone_number;
    }

//  public function setUserName($username) {
//    // NON SI PUO' MODIFICARE LO USERNAME
//  }

    public function setLayout($layout) {
        $this->template_family = $layout;
    }
    public function setAddress($address) {
        $this->indirizzo = $address;
    }

    public function setCity($city) {
        $this->citta = $city;
    }

    public function setProvince($province) {
        $this->provincia = $province;
    }

    public function setCountry($country) {
        $this->nazione = $country;
    }

    public function setFiscalCode($fiscal_code) {
        $this->codice_fiscale = $fiscal_code;
    }

    public function setBirthDate($birthdate) {
        $this->birthdate = $birthdate;
    }

    public function setGender($gender) {
        $this->sesso = $gender;
    }



    /**
     *
     * @param $user_id
     * @return unknown_type
     */
    // FIXME: controllare se servono questi controlli
    public function setUserId($id_user) {
        if(!is_numeric($id_user)) {
            return;
        }
        $this->id_user = (int)$id_user;
    }



    protected function setHomePage($home_page) {
        $this->homepage = $home_page;
    }

    public function setTesters($testersAr = array()) {
        // testersAr is an array containing tester ids.
        $this->testers = $testersAr;
    }

    public function setStatus($status) {
        $this->stato = $status;
    }

    public function setLanguage($language) {
        $this->lingua = $language;
    }

    public function setTimezone($timezone) {
        $this->timezone = $timezone;
    }

    public function setPassword($password) {
        if (DataValidator::validate_password($password, $password) != FALSE) {
            $this->password = sha1($password);
        }
    }


    public function addTester($tester) {
        $tester = DataValidator::validate_testername($tester);
        if($tester !== FALSE) {
            array_push($this->testers, $tester);
            return TRUE;
        }
        return FALSE;
    }

    /**
     *
     * @return array
     */
    public function toArray() {
        $user_dataAr = array(
                'id_utente'              => $this->id_user,
                'nome'                   => $this->nome,
                'cognome'                => $this->cognome,
                'tipo'                   => $this->tipo,
                'e_mail'                 => $this->email,
                'username'               => $this->username,
                'password'               => $this->password, // <--- fare attenzione qui
                'layout'                 => $this->template_family,
                'indirizzo'              => ($this->indirizzo != 'NULL') ? $this->indirizzo : '',
                'citta'                  => ($this->citta != 'NULL') ? $this->citta : '',
                'provincia'              => ($this->provincia != 'NULL') ? $this->provincia : '',
                'nazione'                => $this->nazione,
                'codice_fiscale'         => $this->codice_fiscale,
                'birthdate'              => $this->birthdate,
                'sesso'                  => $this->sesso,
                'telefono'               => ($this->telefono != 'NULL') ? $this->telefono : '',
                'stato'                  => $this->stato,
                'lingua'                 => $this->lingua,
                'timezone'               => $this->timezone

        );



        return $user_dataAr;
    }

    // MARK: existing methods

    public function get_messagesFN($id_user) {
    }


    // FIXME: sarebbe statico, ma viene usato come metodo non statico.
    public function convertUserTypeFN($id_profile) {
        switch  ($id_profile) {
            case 0: // reserved
                $user_type = translateFN('utente ada');
                break;

            case AMA_TYPE_AUTHOR:
                $user_type = translateFN('autore');
                break;

            case AMA_TYPE_ADMIN:
                $user_type = translateFN('amministratore');
                break;

            case AMA_TYPE_TUTOR:
            case AMA_TYPE_TUTOR:
                $user_type = translateFN('tutor');
                break;
            case AMA_TYPE_SWITCHER:
                $user_type = translateFN('switcher');
                break;

            case AMA_TYPE_STUDENT:
            case AMA_TYPE_STUDENT:
            default:
            // FIXME: trovare dove controlliamo $user_type == 'studente' e sostituire con $user_type == 'utente'
                $user_type = translateFN('utente');
        }
        return $user_type;
    }

    public function get_agendaFN($id_user) {
    }

    public static function get_online_usersFN($id_course_instance, $mode) {
    }

    private static function _online_usersFN($id_course_instance, $mode=0) {
    }

    public static function is_someone_thereFN($id_course_instance, $id_node) {
    }

    public function get_last_accessFN($id_course_instance="") {
    }

    public static function is_visited_by_userFN($node_id, $course_instance_id, $user_id) {
    }

    public static function is_visited_by_classFN($node_id, $course_instance_id, $course_id) {
    }

    public static function is_visitedFN($node_id) {
    }
}

/**
 *
 *
 */
class ADAGuest extends ADAGenericUser {
    public function __construct($user_dataHa=array()) {
        $this->id_user         = 0;
        $this->nome            = 'guest';
        $this->cognome         = 'guest';
        $this->tipo            = AMA_TYPE_VISITOR;
        $this->email           = 'vito@lynxlab.com';
        $this->telefono        = 0;
        $this->username        = 'guest';
        $this->template_family =  ADA_TEMPLATE_FAMILY;
        $this->indirizzo       = NULL;
        $this->citta           = NULL;
        $this->provincia       = NULL;
        $this->nazione         = NULL;
        $this->codice_fiscale  = NULL;
        $this->birthdate       = NULL;
        $this->sesso           = NULL;
        $this->telefono               = NULL;
        $this->stato                  = NULL;
        $this->lingua = 0;
        $this->timezone = 0;
        $this->testers = array(ADA_PUBLIC_TESTER);

        $this->setHomePage(HTTP_ROOT_DIR);
    }
}

/**
 *
 *
 */
abstract class ADALoggableUser extends ADAGenericUser {
    public function __construct($user_dataHa=array()) {
        /*
   * $user_dataHa is an associative array with the following keys:
   * nome, cognome, tipo, e_mail, telefono, username, layout, indirizzo, citta,
   * provincia, nazione, codice_fiscale, sesso,
   * telefono, stato
        */
        if(DataValidator::is_uinteger($user_dataHa['id'])) {
            $this->id_user = $user_dataHa['id'];
        }
        else {
            $this->id_user = 0;
        }
        $this->nome                   = $user_dataHa['nome'];
        $this->cognome                = $user_dataHa['cognome'];
        $this->tipo                   = $user_dataHa['tipo'];
        $this->email                  = $user_dataHa['email'];
        $this->telefono               = $user_dataHa['telefono'];
        $this->username               = $user_dataHa['username'];
        $this->template_family        = $user_dataHa['layout'];
        $this->indirizzo              = $user_dataHa['indirizzo'];
        $this->citta                  = $user_dataHa['citta'];
        $this->provincia              = $user_dataHa['provincia'];
        $this->nazione                = $user_dataHa['nazione'];
        $this->codice_fiscale         = $user_dataHa['codice_fiscale'];
        $this->birthdate              = $user_dataHa['birthdate'];
        $this->sesso                  = $user_dataHa['sesso'];

        $this->telefono               = $user_dataHa['telefono'];

        $this->stato                  = $user_dataHa['stato'];
        $this->lingua                  = $user_dataHa['lingua'];
        $this->timezone                  = $user_dataHa['timezone'];



    }

// MARK: USARE MultiPort::getUserMessages
    public function get_messagesFN($id_user) {
        return '';
    }

// MARK: usare MultiPort::getUserAgenda
    public function get_agendaFN($id_user) {
        return '';
    }

    public static function get_online_usersFN($id_course_instance,$mode) {
    $data =  self::_online_usersFN($id_course_instance,$mode);
        if (gettype($data)== 'string' || $data == 'null'){
            return $data;
        } else {
            $user_list = BaseHtmlLib::plainListElement('class:user_online', $data, FALSE);
            $user_list_html = $user_list->getHtml();
            /*
             *
            $t = new Table();
            $t->initTable('0','center','0','0','100%','','','','','','1');
            $t->setTable($data,$caption="",$summary="Utenti online");
            $tabled_data = $t->getTable();
             */
//            return $tabled_data;
            return $user_list_html;
        }
    }

    private static function _online_usersFN($id_course_instance,$mode=0) {
        $dh = $GLOBALS['dh'];
        $error = $GLOBALS['error'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];
        $sess_id_course = $_SESSION['sess_id_course'];
        $sess_id_user = $_SESSION['sess_id_user'];

        /*
     Viene passato $id_course_instance per filtrare l'istanza di corso
     su cui si sta lavorando.
        */
        
        /**
         * @author giorgio 28/giu/2013
         * fixed bug: if neither course instance nor session course instance is set, then return null
         */
        if (!isset($id_course_instance) && (
        		!isset($sess_id_course_instance) || is_null($sess_id_course_instance))) return null;
        
        if (!isset($id_course_instance))
            $id_course_instance = $sess_id_course_instance;
        $now = time();
        // $mode=0;  forcing mode to increase speed
        $tolerance = 240; // 4 minuti
        $limit = $now-$tolerance;
        $out_fields_ar = array('data_visita','id_utente_studente','session_id');
        $clause = "data_visita > $limit and id_istanza_corso ='$id_course_instance'";
        $dataHa = $dh->_find_nodes_history_list($out_fields_ar, $clause);
        if (AMA_DataHandler::isError($dataHa) || empty($dataHa)) {
            if (gettype($dataHa)=="object") {
                $msg = $dataHa->getMessage();
                return $msg;
            }
            // header("Location: $error?err_msg=$msg");
        } else {
            switch ($mode) {
                case 3:   // username, link to chat
                // should read from chat table...
                    break;
                case 2:  // username, link to msg & tutor
                    if (count($dataHa)) {
                        //$online_usersAr = array();
                        $online_users_idAr = array();
                        foreach ($dataHa as $user) {
                            $user_id = $user[2];
                            if (!in_array($user_id,$online_users_idAr)) {
                                if ($sess_id_user==$user_id) {
                                    // ora bisogna controllare che la sessione sia la stessa
                                    $user_session_id = $user[3];
                                    if ($user_session_id == session_id()) {
                                        $online_users_idAr[] = $user_id;
                                        //$online_usersAr[$user_id]['user']= "<img src=\"img/_student.png\" border=\"0\"> ".translateFN("io");
                                        $online_usersAr[]= translateFN("io");
                                        // if we don't want to show this user:
                                        //$online_usersAr[$user_id]['user']= "";
                                    } else {
                                        $online_users_idAr[] = $user_id;
                                        //$online_usersAr[$user_id]['user']= "<img src=\"img/_student.png\" border=\"0\"> ".translateFN("Un utente con i tuoi dati &egrave; gi&agrave; connesso!");
                                        $online_usersAr[]= translateFN("Un utente con i tuoi dati &egrave; gi&agrave; connesso!");
                                    }
                                    $currentUserObj = $_SESSION['sess_userObj'];
                                    $current_profile = $currentUserObj->getType();
                                    if ($current_profile == AMA_TYPE_STUDENT) {


                                    }
                                } else {
                                    $userObj = MultiPort::findUser($user_id);
                                    if(gettype($userObj) == 'object') { //instanceof ADAUser) { // && $userObj->getStatus() == ADA_STATUS_REGISTERED) {
//                                    $userObj = new User($user_id);
                                        $online_users_idAr[] = $user_id;
                                        $id_profile = $userObj->getType(); //$userObj->tipo;
                                        if ($id_profile == AMA_TYPE_TUTOR) {
                                            $online_usersAr[]= $userObj->username. " |<a href=\"$http_root_dir/comunica/send_message.php?destinatari=". $userObj->username."\"  target=\"_blank\">".translateFN("scrivi un messaggio")."</a> |"
                                                . " <a href=\"view.php?id_node=$sess_id_node&guide_user_id=".$userObj->id."\"> ".translateFN("segui")."</a> |";
                                            //$online_usersAr[$user_id]['user']= "<img src=\"img/_tutor.png\" border=\"0\"> ".$userObj->username. " |<a href=\"$http_root_dir/comunica/send_message.php?destinatari=". $userObj->username."\"  target=\"_blank\">".translateFN("scrivi un messaggio")."</a> |";
                                            //$online_usersAr[$user_id]['user'].= " <a href=\"view.php?id_node=$sess_id_node&guide_user_id=".$userObj->id."\"> ".translateFN("segui")."</a> |";
                                        } else {    // STUDENT
                                            // $online_usersAr[$user_id]['user']= "<a href=\"student.php?op=list_students&id_course_instance=$sess_id_course_instance&id_course=$sess_id_course\"><img src=\"img/_student.png\" border=0></a> ";
                                            $online_usersAr[]= $userObj->username. " |<a href=\"$http_root_dir/comunica/send_message.php?destinatari=". $userObj->username."\"  target=\"_blank\">".translateFN("scrivi un messaggio")."</a> |";
//                                            $online_usersAr[$user_id]['user']= "<img src=\"img/_student.png\" border=\"0\"> ";
//                                            $online_usersAr[$user_id]['user'].= $userObj->username. " |<a href=\"$http_root_dir/comunica/send_message.php?destinatari=". $userObj->username."\"  target=\"_blank\">".translateFN("scrivi un messaggio")."</a> |";
                                        }
                                    }
                                }
                            }
                        }

                        return  $online_usersAr;
                    } else {
                        return  translate("Nessuno");
                    }
                    break;
                case 1: // username, mail and timestemp // @FIXME
                    if (count($dataHa)) {
                        //$online_usersAr = array();
                        $online_users_idAr = array();
                        foreach ($dataHa as $user) {
                            $user_id = $user[2];
                            if (!in_array($user_id,$online_users_idAr)) {
                                $userObj = new User($user_id);
                                $time = date("H:i:s",$user[1]);
                                $online_users_idAr[] = $user_id;
                                $online_usersAr[$user_id]['user'] = $userObj->username;
                                $online_usersAr[$user_id]['email'] = $userObj->email;
                                $online_usersAr[$user_id]['time'] = $time;
                            }
                        }
                        return  $online_usersAr;
                    } else {
                        return  translateFN("Nessuno");
                    }
                    break;
                case 0:
                default;
                    if (count($dataHa)) {
                        $online_users_idAr = array();
                        foreach ($dataHa as $user) {
                            $user_id = $user[2];
                            if (!in_array($user_id,$online_users_idAr)) {
                                $online_users_idAr[] = $user_id;
                            }
                        }
                        return count($online_users_idAr)." ".translateFN("studente/i"); // only number of users online
                    } else {
                        return translateFN("Nessuno");
                    }
            }
        }
    }

    public static function is_someone_there_courseFN($id_course_instance) {
        $dh = $GLOBALS['dh'];
        $error = $GLOBALS['error'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];

        if (!isset($id_course_instance))
            $id_course_instance = $sess_id_course_instance;
        if (!isset($id_node))
            $id_node = $sess_id_node;

        $now = time();
        // $mode=0;  forcing mode to increase speed
        $tolerance = 600; // dieci minuti
        $limit = $now-$tolerance;
        $out_fields_ar = array('id_nodo','data_uscita','id_utente_studente');
        $clause = "data_uscita > $limit and id_istanza_corso ='$id_course_instance'";
        $dataHa = $dh->_find_nodes_history_list($out_fields_ar, $clause, true);
        if (AMA_DataHandler::isError($dataHa) || empty($dataHa)) {
            if (gettype($dataHa)=="object") {
                $msg = $dataHa->getMessage();
                return $msg;
            }
            // header("Location: $error?err_msg=$msg");
        } else {
            return $dataHa;
        }
    }

    public static function is_someone_thereFN($id_course_instance,$id_node) {
        $dh = $GLOBALS['dh'];
        $error = $GLOBALS['error'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];

        if (!isset($id_course_instance))
            $id_course_instance = $sess_id_course_instance;
        if (!isset($id_node))
            $id_node = $sess_id_node;

        $now = time();
        // $mode=0;  forcing mode to increase speed
        $tolerance = 600; // dieci minuti
        $limit = $now-$tolerance;
        $out_fields_ar = array('data_uscita','id_utente_studente');
        $clause = "data_uscita > $limit and id_istanza_corso ='$id_course_instance' and id_nodo ='$id_node'";
        $dataHa = $dh->_find_nodes_history_list($out_fields_ar, $clause);
        if (AMA_DataHandler::isError($dataHa) || empty($dataHa)) {
            if (gettype($dataHa)=="object") {
                $msg = $dataHa->getMessage();
                return $msg;
            }
            // header("Location: $error?err_msg=$msg");
        } else {
            return (count($dataHa)>=1);
        }
    }


    public function get_last_accessFN($id_course_instance="",$type="T",$dh = null) {
		if (is_null($dh)) {
			$dh = $GLOBALS['dh'];
		}
        // called by browsing/student.php
        $last_accessAr = $this->_get_last_accessFN($id_course_instance,$dh);
   
        if (is_array($last_accessAr))
            switch ($type) {
                case  "N":
                    return  $last_accessAr[0]; //es. 100_34
                    break;
                case "T":
                default:
                // vito, 11 mar 2009
                //return  substr(ts2dFN($last_accessAr[1]),0,5); // es. 10/06
                    return  substr($last_accessAr[1],0,5); // es. 10/06
                    break;
                case "UT":
                    return  $last_accessAr[1]; // unixtime
            }
        else
            return "-";
    }

    /**
     *
     * @param  $id_course_instance
     * @return array
     */
    private function _get_last_accessFN($id_course_instance="",$provider_dh) {
        // if used by student before entering a course, we must pass the DataHandler
        if ($provider_dh==NULL)   { 
            $provider_dh = $GLOBALS['dh'];
        }    
        //$error = $GLOBALS['error'];
        // $debug = $GLOBALS['debug'];
        $sess_id_user = $_SESSION['sess_id_user'];

        if (!isset($this->id_user)) {
            $id_user = $sess_id_user;
        }
        else {
            $id_user = $this->id_user;
        }

        if ($id_course_instance) {
            $last_visited_node = $provider_dh->get_last_visited_nodes($id_user, $id_course_instance, 10);
            /*
            * vito, 10 ottobre 2008: $last_visited_node è Array([0]=>Array([id_nodo], ...))
            */
            return array($last_visited_node[0]['id_nodo'], ts2dFN($last_visited_node[0]['data_uscita']));
        } else {
            
        }
    }

    public static function is_visited_by_userFN($node_id,$course_instance_id,$user_id) {
        //  returns  the number of visits for this node


        $dh = $GLOBALS['dh'];
        $error = $GLOBALS['error'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        $debug = $GLOBALS['debug'];

        $visit_count = 0;
        $out_fields_ar = array('id_utente_studente','data_visita','data_uscita');
        $history = $dh->find_nodes_history_list($out_fields_ar, $user_id, $course_instance_id, $node_id);
        foreach ($history as $visit) {
            // $debug=1; mydebug(__LINE__,__FILE__,$visit);$debug=0;
            if ($visit[1]== $user_id) {
                $visit_count++;
            }
        }
        return $visit_count;
    }

    public static function is_visited_by_classFN($node_id,$course_instance_id,$course_id) {
        //  returns  the number of visits for this node for instance $course_instance_id

        $dh = $GLOBALS['dh'];
        $error = $GLOBALS['error'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];
        $sess_id_course = $_SESSION['sess_id_course'];
        $sess_id_user = $_SESSION['sess_id_user'];
        $debug = $GLOBALS['debug'];

        $out_fields_ar = array('id_nodo','data_visita');
        $history = $dh->find_nodes_history_list($out_fields_ar, "", $course_instance_id, $node_id);
        $visit_count = count($history);

        return $visit_count;
    }

    public static function is_visitedFN($node_id) {
        //  returns  the number of global visits for this node

        $dh = $GLOBALS['dh'];
        $debug = $GLOBALS['debug'];
        $visit_count = 0;
        $out_fields_ar = array('n_contatti');
        //$search_fields_ar = array('id_nodo');
        //$history = $dh->find_nodes_list_by_key($out_fields_ar, $node_id, $search_fields_ar); ???
        $clause = "id_nodo = '$node_id'";
        $history = $dh->_find_nodes_list($out_fields_ar, $clause);
        $visit_count = sizeof($history)-1;
        return $visit_count;
    }
}

/**
 * AdaAbstractUser class:
 * 
 * This is just a rename of the 'old' ADAUser class which is now declared
 * and implemented in its own 'ADAUser.inc.php' file required below.
 * 
 * This was made abstract in order to be 100% sure that nobody will ever
 * instate it. Must instantiate the proper ADAUser class instead.
 * 
 * The whole ADA system will than be able to use the usual ADAUser class,
 * but with extended methods and properties for each customization.
 *
 *
 * @author giorgio 04/giu/2013
 *
 */

require_once 'ADAUser.inc.php';

abstract class ADAAbstractUser extends ADALoggableUser {
    //protected $history;
    
	protected  $whatsnew;

    public function __construct($user_dataAr=array()) {
        parent::__construct($user_dataAr);

        $this->setHomePage(HTTP_ROOT_DIR.'/browsing/user.php');
        $this->history = NULL;
    }
    
    /**
     * Must override setUserId method to get $whatsnew whenever we set $id_user
     *
     *    
     * @param $user_id
     * @author giorgio 03/mag/2013
     */
    public function setUserId($id_user) {    	
    	parent::setUserId($id_user);
    	$this->setwhatsnew (MultiPort::get_new_nodes($this));
    }
    
    /**
     * whatsnew getter
     * @return array returns whatsnew array, populated in the constructor
     * @author giorgio

     */
    public function getwhatsnew ()
    {
        return $this->whatsnew;
    }
    
    /**
     * whatsnew setter.
     *
     * @param array $newwhatsnew	new array to be set as the whatsnew array
     *
     * @return 
     */
    public function setwhatsnew($newwhatsnew)
    {
        $this->whatsnew = $newwhatsnew;        
    }
    
    /**
     * updates $whatsnew array based on the values from the db.
     *
     * @author giorgio
     *
     */
    public function updateWhatsNew()
    {
        $this->whatsnew = MultiPort::update_new_nodes_in_session($this);
    }

    /**
     *
     * @param $id_course_instance
     * @return void
     */
    public function set_course_instance_for_history($id_course_instance) {
        $historyObj = new History($id_course_instance, $this->id_user);
        // se non e' un errore, allora
        $this->history = $historyObj;
    }

    public function getHistoryInCourseInstance($id_course_instance)
    {
        if(($this->history == null) || ($this->history->id_course_instance != $id_course_instance)) {
            $this->history = new History($id_course_instance, $this->id_user);
        }
        return $this->history;
    }


// MARK: existing methods

    /**
     *
     * @param $id_user
     * @param $id_course_instance
     * @return integer
     */
    public function get_student_level($id_user, $id_course_instance) {
        $dh = $GLOBALS['dh'];
        // FIXME: _get_student_level was a private method, now it is public.
        $user_level = $dh->_get_student_level($id_user,$id_course_instance);
        if (AMA_DataHandler::isError($user_level)) {
            $this->livello = 0;
        }
        else {
            $this->livello = $user_level;
        }
        return $this->livello;
    }

    /**
     *
     * @param $id_user
     * @param $id_course_instance
     * @return void
     */
    public function get_student_score($id_user, $id_course_instance) {
        // NON CI SONO ESERCIZI, NON DOVREBBE ESSERCI PUNTEGGIO
    }

    /**
     *
     * @param $id_student
     * @param $id_course_instance
     * @return integer
     */
    public function get_student_status($id_student,$id_course_instance) {
        $dh = $GLOBALS['dh'];

        $this->status = 0;
        if ($this->tipo == AMA_TYPE_STUDENT) {
            $student_courses_subscribe_statusHa = $dh->course_instance_student_presubscribe_get_status($id_student);
            if (is_object($student_courses_subscribe_statusHa))
                return $student_courses_subscribe_statusHa->error;
            if (empty($student_courses_subscribe_statusHa))
                return "";
            foreach ($student_courses_subscribe_statusHa as $course_subscribe_status) {
                if ($course_subscribe_status['istanza_corso'] == $id_course_instance) {
                    $this->status = $course_subscribe_status['status'];
                    break;
                }
            }
        }
        return $this->status;
    }

    /**
     *
     * @param $id_student
     * @return string
     */
    public function get_student_family($id_student) {
        if (isset($this->template_family)) {
            return $this->template_family;
        }
        else {
            return ADA_TEMPLATE_FAMILY;
        }
    }

    /**
     *
     * @param $id_student
     * @param $node_type
     * @return integer
     */
    public function total_visited_nodesFN($id_student,$node_type="") {
        //  returns 0 or the number of nodes visited by this student
        if(is_object($this->history)) {
            return $this->history->get_total_visited_nodes($node_type);
        }
        return 0;
    }

    /**
     *
     * @param $id_student
     * @return integer
     */
    public function total_visited_notesFN($id_student) {
        $visited_nodes_count = $this->total_visited_nodesFN($id_student,ADA_NOTE_TYPE);
        return $visited_nodes_count;
    }

    public function getDefaultTester() {
        return NULL;
    }

    function get_exercise_dataFN($id_course_instance,$id_student="") {
        $dh = $GLOBALS['dh'];
        $out_fields_ar = array('ID_NODO','ID_ISTANZA_CORSO','DATA_VISITA','PUNTEGGIO','COMMENTO','CORREZIONE_RISPOSTA_LIBERA');
        $dataHa = $dh->find_ex_history_list($out_fields_ar, $this->id_user, $id_course_instance);

        if (AMA_DataHandler::isError($dataHa) || empty($dataHa)) {
            $this->user_ex_historyAr = '';
        } else {
            aasort($dataHa,array("-1")) ;
            $this->user_ex_historyAr = $dataHa;
        }
    }

	function history_ex_done_FN($id_student,$id_profile="",$id_course_instance=""){
		/**
			Esercizi svolti
			Crea array con nodo e punteggio, ordinato in ordine
			decrescente di punteggio.
		*/

			$dh = $GLOBALS['dh'];
			$error = $GLOBALS['error'];
			$http_root_dir = $GLOBALS['http_root_dir'];
			$debug = $GLOBALS['debug'];

		if (empty($id_profile))
			$id_profile = AMA_TYPE_TUTOR;

		$ids_nodi_padri = array();
		if(!empty($this->user_ex_historyAr)){
			foreach($this->user_ex_historyAr as $k=>$e){
				$exer_stats_ha[$k]['nome'] = $e[0];
				$exer_stats_ha[$k]['titolo'] = $e[1];
				$exer_stats_ha[$k]['id_nodo_parent'] = $e[2];
				$exer_stats_ha[$k]['id_exe'] = $e[3];
				$exer_stats_ha[$k]['id_nodo'] = $e[4];
				$exer_stats_ha[$k]['id_istanza'] = $e[5];
				$exer_stats_ha[$k]['data'] = $e[6];
				$exer_stats_ha[$k]['punteggio'] = $e[7];
				$exer_stats_ha[$k]['commento'] = $e[8];
				$exer_stats_ha[$k]['correzione'] = $e[9];

				$ids_nodi_padri[] = $exer_stats_ha[$k]['id_nodo_parent'];
			}

			if (!empty($ids_nodi_padri)) {
				$nodi_padri = $dh->get_nodes($ids_nodi_padri,array('nome','titolo'));
			}

			$label1 = translateFN('Esercizio');
			$label2 = translateFN('Data');
			$label3 = translateFN('Punteggio');
			$label4 = translateFN('Corretto');
			$data = array();

			foreach($exer_stats_ha as $k=>$e){
				$id_exe = $e['id_exe'];
				$id_nodo = $e['id_nodo'];
				$nome = $e['nome'];
				$titolo = $e['titolo'];
				$nome_padre = $nodi_padri[$e['id_nodo_parent']]['nome'];

				$punteggio = $e['punteggio'];
				if (($e['commento']!='-') OR ($e['correzione']!='-')) $corretto =  translateFN('Si');
				else $corretto =  translateFN('-');

				$date = ts2dFN($e['data'])." ".ts2tmFN($e['data']);

				if ($id_profile == AMA_TYPE_TUTOR) {
					$zoom_module = "$http_root_dir/tutor/tutor_exercise.php";
				}
				else {
					$zoom_module = "$http_root_dir/browsing/exercise_history.php";
				}

				// vito, 18 mar 2009
				$link = CDOMElement::create('a');
				if(!empty($id_course_instance) && is_numeric($id_course_instance)) {
					$link->setAttribute('href',$zoom_module.'?op=exe&id_exe='.$id_exe.'&id_student='.$id_student.'&id_nodo='.$id_nodo.'&id_course_instance='.$id_course_instance);
				}
				else {
					$link->setAttribute('href',$zoom_module.'?op=exe&id_exe='.$id_exe.'&id_student='.$id_student.'&id_nodo='.$id_nodo);
				}
				$link->addChild(new CText($nome_padre.' > '));
				$link->addChild(new CText($nome));
				$html = $link->getHtml();

				$data[] = array (
					$label1=>$html,
					$label2=>$date,
					$label3=>$punteggio,
					$label4=>$corretto
				);
			}
			$t = new Table();			
			$t->initTable('0','center','1','1','90%','','','','','1','0','','default','exercise_table');
			$t->setTable($data,translateFN("Esercizi e punteggio"),translateFN("Esercizi e punteggio"));
			$res = $t->getTable();
		}else{
			$res = translateFN("Nessun esercizio.");
		}
		return $res;
	} //end history_ex_done_FN
}

/**
 *
 *
 */
class ADAPractitioner extends ADALoggableUser {
    protected $tariffa;
    protected $profilo;

    public function __construct($user_dataAr=array()) {
        parent::__construct($user_dataAr);

        $this->tariffa = $user_dataAr['tariffa'];
        $this->profilo = $user_dataAr['profilo'];

        $this->setHomePage(HTTP_ROOT_DIR.'/tutor/tutor.php');
    }
    /*
   * getters
    */
    public function getFee() {
        return $this->tariffa;
    }

    public function getProfile() {
        return $this->profilo;
    }

    /*
   * setters
    */
    public function setFee($fee) {
        $this->tariffa = $fee;
    }

    public function setProfile($profile) {
        $this->profilo = $profile;
    }

    public function toArray() {
        $user_dataAr = parent::toArray();

        $user_dataAr['tariffa'] = $this->tariffa;
        $user_dataAr['profilo'] = $this->profilo;

        return $user_dataAr;
    }
}

/**
 *
 *
 */
class ADASwitcher extends ADALoggableUser {
    public function __construct($user_dataAr=array()) {
        parent::__construct($user_dataAr);

        $this->setHomePage(HTTP_ROOT_DIR.'/switcher/switcher.php');
    }
}

/**
 *
 *
 */
class ADAAuthor extends ADALoggableUser {
    public function __construct($user_dataAr=array()) {
        parent::__construct($user_dataAr);

        $this->setHomePage(HTTP_ROOT_DIR.'/services/author.php');
    }
}

/**
 *
 *
 */
class ADAAdmin extends ADALoggableUser {
    public function __construct($user_dataAr=array()) {
        parent::__construct($user_dataAr);

        $this->setHomePage(HTTP_ROOT_DIR.'/admin/admin.php');
    }
}
