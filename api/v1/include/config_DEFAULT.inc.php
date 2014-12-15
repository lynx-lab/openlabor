<?php

/*
 * version of API
 */
define('VERSION','v1');

/*
 * PROVIDER IN WHICH SEARCH DATA
 */
define('DATA_PROVIDER','client0');

/* 
 * ADA SERVICE ID RELATED TO JOB OFFER DATA
 * Va creato il servizio nel provider definito in DATA_PROVIDER
 * (login come switcher, add service)
 */
define('ADA_JOB_SERVICE_ID','1');

/*
 * ADA INSTANCE SERVICE ID RELATED TO JOB OFFER DATA
 * Va creata l'instanza del servizio indicato in ADA_JOB_SERVICE_ID provider definito in DATA_PROVIDER
 * (login come switcher, add service)
 */
define('ADA_JOB_INSTANCE_SERVICE_ID','6');

/**
 * ADA SERVICE PROVIDER RELATED TO TRAINING OFFER DATA
 */
define('ADA_TRAINING_SERVICE_PROVIDER','client0');

/* 
 * ADA SERVICE ID RELATED TO TRAINING OFFER DATA
 * Va creato il servizio nel provider definito in ADA_TRAINING_SERVICE_PROVIDER
 * (login come switcher, add service)
 */
define('ADA_TRAINING_SERVICE_ID','2');

/*
 * ADA INSTANCE SERVICE ID RELATED TO TRAINING OFFER DATA
 * Va creata l'instanza del servizio indicato in ADA_TRAINING_SERVICE_ID provider definito in ADA_TRAINING_SERVICE_PROVIDER
 * (login come switcher, add service)
 */
define('ADA_TRAINING_INSTANCE_SERVICE_ID','7');

/*
 * PWD of Semantic API
 */
define('PWD_SEMANTIC','api key');

/*
 * URL of Semantic API
 */
/*

define('URL_BASE_SEMANTIC','http://openlabor.lynxlab.com/services/');
define('URL_LAVORI5',URL_BASE_SEMANTIC.'search/lavori5/');
define('URL_LAVORI4',URL_BASE_SEMANTIC.'search/lavori4/');
 * 
 */

define('URL_BASE_SEMANTIC','http://serendipity.lynxlab.com:81/services/');
define('URL_LAVORI5',URL_BASE_SEMANTIC.PWD_SEMANTIC.'/search/lavori5/');
define('URL_LAVORI4',URL_BASE_SEMANTIC.PWD_SEMANTIC.'/search/lavori4/');

define('MINPROBRATIO','0.01');

/*
 * Number of professional code to treat
 */
define('NUMBER_CODE',6);

define('DIR_INFO_SERVICES','doc_news');
define('DISCOVERY_INFO',DIR_INFO_SERVICES.'/discovery.xml');
define('SERVICES_INFO',DIR_INFO_SERVICES.'/services.xml');

/*
 * utility for query in educational qualification
define('laurea','corso di laurea|laurea|diploma|');
define('diploma', 'ISTITUTO PROFESSIONALE|SCUOLA MAGISTRALE|ISTITUTO TECNICO|ISTITUTO MAGISTRALE|SCIENTIFICO|CLASSICO|LINGUISTICO|ISTITUTO D\'ARTE|LICEO ARTISTICO|ISTITUTO SUPERIORE');
define('media','media,');
 */
define('laurea','qualificationRequired like \'%laurea%\'');
define('diploma', '(qualificationRequired like \'%ISTITUTO PROFESSIONALE%\' OR 
    qualificationRequired like \'%SCUOLA MAGISTRALE%\' OR 
    qualificationRequired like \'%ISTITUTO TECNICO%\' OR
    qualificationRequired like \'%ISTITUTO MAGISTRALE%\' OR
    qualificationRequired like \'%SCIENTIFICO%\' OR
    qualificationRequired like \'%CLASSICO%\' OR 
    qualificationRequired like \'%LINGUISTICO%\' OR 
    qualificationRequired like \'%ISTITUTO D\'\'ARTE%\' OR 
    qualificationRequired like \'%LICEO ARTISTICO%\' OR 
    qualificationRequired like \'%ISTITUTO SUPERIORE%\')');
define('media','qualificationRequired like \'%media%\'');

/*
 * 
 */
define('SEARCH_AND',true);
define('LOGFILEREQUEST','log/openlaborRequest.txt');

$allowedKeys = array ('num','num','num','num','num','num'); 

