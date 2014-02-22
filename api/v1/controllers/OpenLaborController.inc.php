<?php

/*
 *
 * 
 */
class openLaborController {
   public $languages;
   
   public function __construct() {
        $GLOBALS['common_dh'] = AMA_Common_DataHandler::instance();
        $common_dh = $GLOBALS['common_dh'];
        
       $this->languages = $common_dh->find_languages();
       $this->LogRequest($_REQUEST,$_SERVER);
   } 
   
   /**
    * 
    * @param type $data
    * @param type $ObjType
    * @return type xml data
    */
   public function array2xml($data,$ObjType) {
      
        $xmlData = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><'.$ObjType.'_info></'.$ObjType.'_info>');
        // function call to convert array to xml
        $returnXML = $this->array_to_xml($data,$xmlData,$ObjType);
        return $returnXML->asXML();
   }
   
    // function defination to convert array to xml
    public function array_to_xml($data, &$xmlData,$ObjType='info') {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                if (!is_numeric($k)) {
                    self::array_to_xml($v, $xmlData->addChild($k));
                } else {
                    self::array_to_xml($v, $xmlData->addChild($ObjType));
                }
            } else {
                $xmlData->addChild($k, $v);
            }
            /*
            is_array($v)
                ? array_to_xml($v, $xmlData->addChild($k))
                : $xmlData->addChild($k, $v);
             * 
             */
        }
        return $xmlData;        
    }
    
    public function isAllowed($verb, $apikey = null, $ipAddress = null) {
//        print_r($GLOBALS);
        return (in_array($apikey, $GLOBALS['allowedKeys']) || ($verb == 'GET'));
    }


    /**
     * 
     * @param type $XML (simpleXML object)
     * ex: $XML = simplexml_load_file($XMLFile); 
     */
    public static function XML2Array($XML) {
        $json = json_encode($XML);
        $array = json_decode($json,TRUE);
        return $array;
    }
   /*
    * Log the request
    */
   public function LogRequest($request,$server,$NumResults, $fileLog=NULL) {
       $logRow = '**************';
       if ($fileLog == NULL) {
           $logFilename = LOGFILEREQUEST;
       }
       $logRow .= $server['REQUEST_TIME'];
       $logRow .= '|'.$server['REMOTE_ADDR'];
       $logRow .= '|'.$server['REDIRECT_URL'];
       $logRow .= '|'.$server['QUERY_STRING'];
       $logRow .= '|'.$server['HTTP_USER_AGENT'];
       $logRow .= '|'.$server['REDIRECT_STATUS'];
       $logRow .= '|'.$server['REQUEST_METHOD'];
       $logRow .= '|'.$server['HTTP_ACCEPT_LANGUAGE'];
       $logRow .= '|'.$NumResults.PHP_EOL;
       $fp = fopen($logFilename, "a+");
       if ($fp){
          fwrite($fp, $logRow);
          fclose($fp);
       } 
   }    
       
   /*
    * Log the request
    */
   public function LogReport($request,$server,$text) {
       $logRow = PHP_EOL.'**************';
       $logFilename = 'log/openlaborReport.txt'; //LOGFILEREPORT;
       $logRow .= $server['REQUEST_TIME'];
       $logRow .= '|'.$text.PHP_EOL;

       $fp = fopen($logFilename, "a+");
       if ($fp){
          fwrite($fp, $logRow);
          fclose($fp);
       } 
   }
}
?>
