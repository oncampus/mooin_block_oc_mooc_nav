<?php
 
class block_oc_mooc_nav_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

		$mform->addElement('text', 'config_title', get_string('blocktitle', 'block_oc_mooc_nav'));
		$mform->setDefault('config_title', 'default value');
		$mform->setType('config_title', PARAM_MULTILANG);
		
		$mform->addElement('text', 'config_socialmedia_link', get_string('socialmedia_link', 'block_oc_mooc_nav'), array('size'=>4));
		$mform->setDefault('config_socialmedia_link', '');
		$mform->setType('config_socialmedia_link', PARAM_INT);
		
		$mform->addElement('text', 'config_discussion_link', get_string('discussion_link', 'block_oc_mooc_nav'), array('size'=>4));
		$mform->setDefault('config_discussion_link', '');
		$mform->setType('config_discussion_link', PARAM_INT);
		
		$mform->addElement('header', 'configheader', get_string('certificate', 'block_oc_mooc_nav'));
		
		$mform->addElement('text', 'config_capira_questions', get_string('number_of_questions', 'block_oc_mooc_nav'), array('size'=>4));
		$mform->setDefault('config_capira_questions', '');
		$mform->setType('config_capira_questions', PARAM_INT);
		
		$mform->addElement('text', 'config_capira_min', get_string('required_questions', 'block_oc_mooc_nav'), array('size'=>4));
		$mform->setDefault('config_capira_min', '');
		$mform->setType('config_capira_min', PARAM_INT);
		
		$mform->addElement('header', 'configheader', get_string('chapter_config', 'block_oc_mooc_nav'));
		
		$mform->addElement('text', 'config_directory_link', get_string('directory_link', 'block_oc_mooc_nav'), array('size'=>4));
		$mform->setDefault('config_directory_link', '');
		$mform->setType('config_directory_link', PARAM_INT);
		
		$mform->addElement('textarea', 'config_chapter_configtext', get_string("configtext", "block_oc_mooc_nav"), 'wrap="virtual" rows="20" cols="50"');
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// Repeated elements
		/* 
		
		
		$repeatarray = array();
        $repeatarray[] = $mform->createElement('text', 'name', get_string('name', 'block_oc_mooc_nav'));
        $repeatarray[] = $mform->createElement('text', 'lections', get_string('lections', 'block_oc_mooc_nav'), array('size'=>4));
		$repeatarray[] = $mform->createElement('advcheckbox', 'enabled', get_string('enabled', 'block_oc_mooc_nav'));
        $repeatarray[] = $mform->createElement('hidden', 'optionid', 0);

        // if ($this->_instance){
            // $repeatno = $DB->count_records('choice_options', array('choiceid'=>$this->_instance));
            // $repeatno += 1;
        // } else {
            // $repeatno = 5;
        // }
		
		$repeatno = 5;
		
        $repeateloptions = array();
        $repeateloptions['enabled']['default'] = 1;
        $repeateloptions['lections']['type'] = PARAM_INT;
        // $repeateloptions['limit']['rule'] = 'numeric';
        // $repeateloptions['limit']['type'] = PARAM_INT;

        // $repeateloptions['option']['helpbutton'] = array('choiceoptions', 'choice');
        // $mform->setType('option', PARAM_CLEANHTML);

        //$mform->setType('optionid', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno, $repeateloptions, 'option_repeats', 'option_add_fields', 1, null, true);
		
		// // Make the first option required
        // if ($mform->elementExists('option[0]')) {
            // $mform->addRule('option[0]', get_string('atleastoneoption', 'choice'), 'required', null, 'client');
        // }
		 */
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		/* $mform->addElement('filemanager', 
							'config_attachments', 
							get_string('chapterimages', 'block_oc_mooc_nav'),
							null,
							array('subdirs' => 0, 
								'maxbytes' => 0, 
								'maxfiles' => 10,
								'accepted_types' => array('.png')
							)
						); */
    }
	
	// protected function get_data() {
		// return mform->get_data();
	// }
}