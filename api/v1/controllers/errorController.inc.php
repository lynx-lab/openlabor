<?php

class errorController extends openLaborController {

    
	public function __construct() {
		parent::__construct();
        }

        public function printError($verb = 'no verb',$format = 'json', $message = 'generic', $httpStatus = '400',$parameters,$url_elements) {
             /*
             * view result in correct format
             */
            header("Status: " . $httpStatus, false, $httpStatus);
            if ($format == 'xml') {
                $errorResult= str_replace('&', '&amp;',$message);
                header ("Content-type: text/xml");
            } else {
                header('Content-Type: application/json; charset=utf8');
                $errorResult = json_encode($message);
            }
            echo $errorResult;

            
        }


}


    
