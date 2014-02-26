<?php

class job {
    
    public static function getRelatedTraining($code) {
        
    //                $urlApiSearchTraining = URL_API_REQUESTS.'.'.$format.'?service_code='.SEARCH_TRAINING.&;
        if (sizeof($_SESSION['sess_userObj']->getTesters()) > 0) {
            $providers = $_SESSION['sess_userObj']->getTesters();
            $provider = $providers[0];
        }else {
            $provider = DATA_PROVIDER;
        }
        $GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
        $dh = $GLOBALS['dh'];
        $condition = 'where trainingCode like \''.$code . '%\'';
//        $relatedTrainig = $dh->listTrainingOffers($condition);
        $relatedTrainig = $dh->listTrainingFromISTATCode($code);
        
        if (AMA_DataHandler::isError($relatedTrainig)) {
            $relatedTrainig = null;
        }
        return $relatedTrainig;
    }
}