<?php

class utility {

    function __construct() {
        
    }

    /**
     * @abstract return the code of words
     * @param string $words
     * @return string $istatCode
     * 
     */
    public static function getIstatCode($words,$singleCat = true) {
        
        $urlSemanticApi = URL_LAVORI4.$words;
        $keywords = $words;
        //$curlHeader = array("Content-Type: application/x-www-form-urlencoded");
        $curlHeader = '';
        $codeResult = REST_request::sendRequest($keywords,$curlHeader,$urlSemanticApi,$curlPost);
        $codeResult = json_decode($codeResult,TRUE);
        if ($singleCat) {
            $istatCode = $codeResult['job_types']['categories'][0]; 
        }
        else {
            $istatCode = $codeResult['job_types']['categories'];        
        }
        return $istatCode;
    }


   /*
    * Log the request
    */
   public static function LogUpdate($text) {
       $logRow = PHP_EOL;
       $logFilename = 'updateReport.log'; //LOGFILEREPORT;
       $logRow .= '|'.$text.PHP_EOL;

       $fp = fopen($logFilename, "a+");
       if ($fp){
          fwrite($fp, $logRow);
          fclose($fp);
       } 
   }
   
}
