<?php
/**
 * CourseModelForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
require_once ROOT_DIR.'/include/Forms/lib/classes/FForm.inc.php';
/**
 *
 */
class JobReportForm extends FForm {
    public function  __construct() {
        parent::__construct();
        $this->addHidden('api_key')->withData(OPENLABOR_API_KEY);
        $this->addHidden('service_code')->withData(JOB_REPORT);
        
        $this->addTextInput('position', translateFN('Title (ex. developer)'))
               ->setRequired()
               ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

        $this->addTextInput('professionalProfile', translateFN('Professional Profile'))
                ->setRequired()
                ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

        $this->addTextInput('workersRequired', translateFN('Number of workers required'))
                ->setRequired()
                ->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR);

        $this->addTextInput('jobExpiration', translateFN('Deadline to submit'). ' ' .translateFn('dd/mm/yyyyy'))
                ->setRequired()
                ->setValidator(FormValidator::DATE_VALIDATOR);

        $this->addTextInput('qualificationRequired', translateFN('Educational qualification'));
        
        $this->addTextarea('descriptionQualificationRequired', translateFN('Desired Skills and Experience'));

        $this->addTextInput('remuneration', translateFN('Remuneration offered by the company in €'));
        
        $this->addTextInput('linkMoreInfo', translateFN('More info URL'));

        $this->addTextarea('notes', translateFN('Notes'));

        $this->addTextInput('cityCompany', translateFN('location of the job'))
               ->setRequired()
               ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR);

        $this->addTextInput('j_latitude', translateFN('Latitudine'));
        
        $this->addTextInput('j_longitude', translateFN('Longitude'));
        
        
    }
}