<?php

/** ***********************************
 * INSTALLER HAVE TO CHANGE THIS AREA
 */

/**
 * ADA PROVIDER IN WHICH SEARCH DATA
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

/*
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


/**
 * version of OpenLabor api rest used by this module
 */
define('API_VERSION','v1');

/*
 * URL of Semantic API
 */
define('URL_LAVORI5','http://openlabor.lynxlab.com/services/search/lavori5/');
define('URL_LAVORI4','http://openlabor.lynxlab.com/services/search/lavori4/');

/*
 * URL for Open data files
 * URL_OL_PROV_ROMA = job opportunities of province of Rome
 * URL_OF_NON_FINANZIATA_PROV_ROMA = training courses 
 */ 
define('URL_OL_PROV_ROMA','http://co.provincia.roma.it/ido/XmlOpenData/Preselezioni.xml'); 
define('URL_OF_NON_FINANZIATA_PROV_ROMA','http://85.18.173.117/opendata/CorsiFormazioneNonFinanziata.xml'); 

/*
 * JOB OPPORTUNITIES 
 * parameters 
 * $label: the name of each field to insert in the DB 
 */
//$labels = array('id','scadenza','posti','profilo','codice', 'qualifica','titolo_studio', 'descrizione','corsi','comune','CPI','esperienza','durata esperienza','eta min','eta max','paga','incentivi','disabili','categorie protette','mezzo proprio','note','dettaglio');
$labels = array ('IdJobOriginal','jobExpiration','workersRequired','professionalProfile','positionCode','position','qualificationRequired','descriptionQualificationRequired','professionalTrainingRequired','cityCompany','CPI','experienceRequired','durationExperience','minAge','maxAge','remuneration','rewards','reservedForDisabled','favoredCategoryRequests','ownVehicle','notes','linkMoreInfo');
$elements = array ('Id','DataScadenzaOfferta','NumeroLavoratoriRichiesti','DescrizioneProfiloProfessionale','CodiceQualifica','Qualifica','TipologiaTitoloStudioRichiesto','DescrizioneTitoloStudioRichiesto','CorsiProfessionaliRichiesti','ComuneAzienda','CentroPerImpiego','EsperienzaRichiesta','DurataEsperienzaRichiesta','EtaMin','EtaMax','RetribuzioneIndicataDalDatoreDiLavoro','PremiIncentivi','PreselezioneDisabili','EventualiCategorieAgevolateRichieste','DisponibilitaMezzoProprio','Annotazioni','LinkUlterioriInformazioni');

/*
 * TRAINING OPPORTUNITIES 
 * parameters 
 * $label: the name of each field to insert in the DB 
 */
//$trainingLabels = array('id','scadenza','posti','profilo','codice', 'qualifica','titolo_studio', 'descrizione','corsi','comune','CPI','esperienza','durata esperienza','eta min','eta max','paga','incentivi','disabili','categorie protette','mezzo proprio','note','dettaglio');
$trainingLabels = array('nameTraining','company','trainingAddress','CAP','city','phone','durationHours','trainingType','userType','qualificationRequired','longitude','latitude');
$trainingElements = array('DenominazioneCorso','Istituto','IndirizzoSedeCorso','CAP','Comune','TELEFONO','DurataCorsoOre','TipologiaCorso','TipologiaUtenti','TitoloDiStudioRichiesto','longitudine','latitudine');


/*
 * Centri per l'Impiego della Provincia di Roma
 * URL e Elements
 */
define('URL_CPI','include/CentriPerImpiego.xml');
$cpiElements = array ('idCentro','Denominazione','Indirizzo','Cap','Comune','BacinoDiCompetenza','Telefono','Fax','eMail','Latitudine','Longitudine');

/*
 * utility for educational qualification
 */
define('laurea','corso di laurea|laurea|diploma|');
define('diploma', 'ISTITUTO PROFESSIONALE|SCUOLA MAGISTRALE|ISTITUTO TECNICO|ISTITUTO MAGISTRALE|SCIENTIFICO|CLASSICO|LINGUISTICO|ISTITUTO D\'ARTE|LICEO ARTISTICO|ISTITUTO SUPERIORE');
define('media','media,');

/*
 * 
 */
define('SEARCH_AND',true);
define('LOGFILEREQUEST','log/openlaborRequest.txt');
