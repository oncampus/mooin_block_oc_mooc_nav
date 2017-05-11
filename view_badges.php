<?php
require_once('../../config.php');

$courseid = optional_param('courseid', 1, PARAM_INT);

$PAGE->set_url('/blocks/oc_mooc_nav/view_badges.php', array('courseid' => $courseid));

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

// Not needed anymore.
unset($contextid);

require_login($course);

$systemcontext = context_system::instance();

$PAGE->set_pagelayout('incourse');
$PAGE->set_title("$course->shortname: " . get_string('participants'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagetype('course-view-' . $course->format);
$PAGE->add_body_class('path-user');                     // So we can style it independently.
$PAGE->set_other_editing_capability('moodle/course:manageactivities');

require_once('./locallib.php');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('badges', 'block_oc_mooc_nav'));

// Zertifikate //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*
	$blockrecord = $DB->get_record('block_instances', array('blockname' => 'oc_mooc_nav', 'parentcontextid' => $context->id), '*', MUST_EXIST);
	$blockinstance = block_instance('oc_mooc_nav', $blockrecord);
	$total = $blockinstance->config->capira_questions;//0;
	$min_prozent = $blockinstance->config->capira_min;
	$cert_m = $DB->get_record('modules', array('name' => 'simplecertificate'));
	
	// Capira Sokrates
	if ($min_prozent > 0 and $total == 0 and $cert_cm = $DB->get_record('course_modules', array('module' => $cert_m->id, 'course' => $courseid, 'visible' => 1))) {
		if (has_capability('mod/simplecertificate:addinstance', $context)) {
			$simple_cert = $DB->get_record('simplecertificate', array('course' => $courseid, 'id' => $cert_cm->instance));
			$cert_issues = $DB->get_records('simplecertificate_issues', array('certificateid' => $simple_cert->id));
			echo 'Anzahl ausgestellter Zertifikate ('.get_string('only_for_trainers', 'block_oc_mooc_nav').'): '.count($cert_issues);
		}
		
		
		// Capira Sokrates
		$prozent = 0;
		$count = 0;
		$ltis = $DB->get_records('lti', array('course' => $courseid));
		//print_object($ltis);
		foreach ($ltis as $lti) {
			$subs = $DB->get_records('lti_submission', array('ltiid' => $lti->id, 'userid' => $USER->id));
			$grade = 0;
			foreach ($subs as $sub) {
				if ($sub->gradepercent > $grade) {
					$grade = $sub->gradepercent;
				}
			}
			$prozent += $grade;
			$count++;
		}
		if ($count > 0) {
			$prozent = $prozent / $count;
		}
		//echo '<br />'.$prozent.' '.$count.'<br />';
				
		
		if ($prozent >= $min_prozent) {
			// zertifikat anzeigen
			$module_context = context_module::instance ($cert_cm->id);
			require_capability('mod/simplecertificate:view', $module_context);
			
			$url = new moodle_url('/mod/simplecertificate/view.php', array (
					'id' => $cert_cm->id,
					'tab' => 0,
					'page' => 0,
					'perpage' => 30,
			));
			$canmanage = 0;//has_capability('mod/simplecertificate:manage', $module_context);
						
			$link = new moodle_url('/mod/simplecertificate/view.php', array('id' => $cert_cm->id, 'action' => 'get'));
			$button = new single_button($link, get_string('certificate', 'block_oc_mooc_nav'));
			$button->add_action(
								new popup_action('click', $link, 'view' . $cert_cm->id, 
												array('height' => 600, 'width' => 800)));
			echo html_writer::tag('h2', html_writer::tag('div', get_string('certificate', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text')));
			echo html_writer::tag('div', get_string('cert_descr', 'block_oc_mooc_nav', $min_prozent));
			echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:left'));
		}
	}
	
	// Capira alt
	if ($min_prozent > 0 and $total > 0 and $cert_cm = $DB->get_record('course_modules', array('module' => $cert_m->id, 'course' => $courseid, 'visible' => 1))) {
		if (has_capability('mod/simplecertificate:addinstance', $context)) {
			$simple_cert = $DB->get_record('simplecertificate', array('course' => $courseid, 'id' => $cert_cm->instance));
			$cert_issues = $DB->get_records('simplecertificate_issues', array('certificateid' => $simple_cert->id));
			echo 'Anzahl heruntergeladener Zertifikate ('.get_string('only_for_trainers', 'block_oc_mooc_nav').'): '.count($cert_issues);
		}
		
		$capiras = $DB->get_records('occapira', array('course' => $courseid));
		$user_total = 0;
		foreach ($capiras as $capira) {
			//$layer = $DB->get_record('occapira_grades', array('occapira' => $capira->id));
			//$total += $layer->total;
			$layers = $DB->get_records('occapira_grades', array('occapira' => $capira->id, 'userid' => $USER->id));
			foreach ($layers as $l) {
				if ($l->grade > 0) {
					$user_total++;
				}
			}
		}
		
		$prozent = 100 / $total * $user_total;
		//echo 'total: '.$total.', '.$USER->username.': '.$user_total.', %: '.$prozent;
		if ($prozent >= $min_prozent) {
			// zertifikat anzeigen
			$module_context = context_module::instance ($cert_cm->id);
			require_capability('mod/simplecertificate:view', $module_context);
			
			$url = new moodle_url('/mod/simplecertificate/view.php', array (
					'id' => $cert_cm->id,
					'tab' => 0,
					'page' => 0,
					'perpage' => 30,
			));
			$canmanage = 0;//has_capability('mod/simplecertificate:manage', $module_context);
						
			$link = new moodle_url('/mod/simplecertificate/view.php', array('id' => $cert_cm->id, 'action' => 'get'));
			$button = new single_button($link, get_string('certificate', 'block_oc_mooc_nav'));
			$button->add_action(
								new popup_action('click', $link, 'view' . $cert_cm->id, 
												array('height' => 600, 'width' => 800)));
			echo html_writer::tag('h2', html_writer::tag('div', get_string('certificate', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text')));
			echo html_writer::tag('div', get_string('cert_descr', 'block_oc_mooc_nav', $min_prozent));
			echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:left'));
		}
	}
*/

$blockrecord = $DB->get_record('block_instances', array('blockname' => 'oc_mooc_nav', 'parentcontextid' => $context->id), '*', MUST_EXIST);


$blockinstance = block_instance('oc_mooc_nav', $blockrecord);
$total = $blockinstance->config->capira_questions;//0;
$min_prozent = $blockinstance->config->capira_min;
$cert_m = $DB->get_record('modules', array('name' => 'simplecertificate'));


/* Cert start */
if ($min_prozent > 0 and $cert_cm = $DB->get_record('course_modules', array('module' => $cert_m->id, 'course' => $courseid, 'visible' => 1))) {
    if (has_capability('mod/simplecertificate:addinstance', $context)) {
        $simple_cert = $DB->get_record('simplecertificate', array('course' => $courseid, 'id' => $cert_cm->instance));
        $cert_issues = $DB->get_records('simplecertificate_issues', array('certificateid' => $simple_cert->id));
        echo 'Anzahl ausgestellter Zertifikate (' . get_string('only_for_trainers', 'block_oc_mooc_nav') . '): ' . count($cert_issues);
    }

    $percentage = 0;
    $mod_count = 0;

    /* hvp start */
    require_once($CFG->libdir . '/gradelib.php');
    $hvp_percentage = 0;
    $hvp_module = $DB->get_record('modules', array('name' => 'hvp'));
    $cm = $DB->get_records('course_modules', array('course' => $courseid, 'module' => $hvp_module->id, 'completion' => 2, 'visible' => 1));
    $hvp_count = count($cm);

    if ($hvp_count != 0) {
        foreach ($cm as $module) {
            $grading_info = grade_get_grades($module->course, 'mod', 'hvp', $module->instance, $USER->id);
            $user_grade = $grading_info->items[0]->grades[$USER->id]->grade;

            $hvp_percentage += $user_grade / $hvp_count;
        }

        $percentage = $hvp_percentage;
        $mod_count++;
    }
    /* hvp end */

    /* capira_lti start */
    $lti_percentage = 0;
    $lti_count = 0;
    $ltis = $DB->get_records('lti', array('course' => $courseid));

    foreach ($ltis as $lti) {
        $subs = $DB->get_records('lti_submission', array('ltiid' => $lti->id, 'userid' => $USER->id));
        $grade = 0;
        foreach ($subs as $sub) {
            if ($sub->gradepercent > $grade) {
                $grade = $sub->gradepercent;
            }
        }
        $lti_percentage += $grade;
        $lti_count++;
    }

    if ($lti_count > 0) {
        $percentage += $lti_percentage / $lti_count;
        $mod_count++;
    }
    /* capira_lti end */

    /* capira start */
    if ($total > 0) {
        $capiras = $DB->get_records('occapira', array('course' => $courseid));
        $user_total = 0;
        foreach ($capiras as $capira) {
            $layers = $DB->get_records('occapira_grades', array('occapira' => $capira->id, 'userid' => $USER->id));
            foreach ($layers as $l) {
                if ($l->grade > 0) {
                    $user_total++;
                }
            }
        }

        if ($user_total != 0) {
            $percentage += 100 / $total * $user_total;
            $mod_count++;
        }
    }
    /* capira end */

    $percentage = $percentage / $mod_count;

    if ($percentage >= $min_prozent) {
        // zertifikat anzeigen
        $module_context = context_module::instance($cert_cm->id);
        require_capability('mod/simplecertificate:view', $module_context);

        $url = new moodle_url('/mod/simplecertificate/view.php', array(
            'id' => $cert_cm->id,
            'tab' => 0,
            'page' => 0,
            'perpage' => 30,
        ));
        $canmanage = 0;//has_capability('mod/simplecertificate:manage', $module_context);

        $link = new moodle_url('/mod/simplecertificate/view.php', array('id' => $cert_cm->id, 'action' => 'get'));
        $button = new single_button($link, get_string('certificate', 'block_oc_mooc_nav'));
        $button->add_action(
            new popup_action('click', $link, 'view' . $cert_cm->id,
                array('height' => 600, 'width' => 800)));
        echo html_writer::tag('h2', html_writer::tag('div', get_string('certificate', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text')));
        echo html_writer::tag('div', get_string('cert_descr', 'block_oc_mooc_nav', $min_prozent));
        echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:left'));
    }
}
/* Cert end */

if ($courseid == 25 and has_capability('mod/simplecertificate:addinstance', $context)) {
    $link = new moodle_url('/mod/simplecertificate/view.php', array('id' => 3564, 'action' => 'get'));
    $button = new single_button($link, 'ATTESTATION DE FORMATEUR');
    $button->add_action(
        new popup_action('click', $link, 'view' . 3564,
            array('height' => 600, 'width' => 800)));
    echo html_writer::tag('h2', html_writer::tag('div', 'ATTESTATION DE FORMATEUR', array('class' => 'oc_badges_text')));
    echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:left'));
}
if ($courseid == 21 and has_capability('mod/simplecertificate:addinstance', $context)) {
    $link = new moodle_url('/mod/simplecertificate/view.php', array('id' => 3565, 'action' => 'get'));
    $button = new single_button($link, 'ATTESTATION DE FORMATEUR');
    $button->add_action(
        new popup_action('click', $link, 'view' . 3565,
            array('height' => 600, 'width' => 800)));
    echo html_writer::tag('h2', html_writer::tag('div', 'ATTESTATION DE FORMATEUR', array('class' => 'oc_badges_text')));
    echo html_writer::tag('div', $OUTPUT->render($button), array('style' => 'text-align:left'));
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo html_writer::tag('h2', html_writer::tag('div', get_string('course_badges', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text')));

echo html_writer::tag('div', get_string('badge_overview_description', 'block_oc_mooc_nav'));
echo '<br />';
echo '<div>' . html_writer::link(new moodle_url('/user/profile.php', array('id' => $USER->id)), get_string('profile_badges', 'block_oc_mooc_nav')) . '<br />';
echo html_writer::link(new moodle_url('/badges/mybackpack.php'), get_string('badge_options', 'block_oc_mooc_nav')) . '</div><br />';

// Eigene, in diesem Kurs erworbene Badges

$out = html_writer::tag('div', get_string('overview', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text'));
echo html_writer::tag('h2', $out);
//display_badges($USER->id, $courseid);
ob_start();
display_user_and_availbale_badges($USER->id, $courseid);
$out = ob_get_contents();
ob_end_clean();
if ($out != '<ul class="badges"></ul>') {
    echo $out;
} else {
    echo html_writer::tag('div', get_string('no_badges_available', 'block_oc_mooc_nav'), array('class' => 'oc-no-badges'));
}

// Badges, die man erreichen kann (in diesem Kurs und Plattformbadges)
// echo html_writer::tag('div', get_string('available_badges', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text'));
// echo html_writer::tag('div', get_string('in_course', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text'));
// display_badges(0, $courseid);
// echo html_writer::tag('div', get_string('in_mooin', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text'));
// display_badges(0, 0);

// in den letzten 24h/7d an Teilnehmer diesen Kurses verliehene Badges
$out = html_writer::tag('div', get_string('awarded_badges', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text'));
echo html_writer::tag('h2', $out);
// echo html_writer::tag('div', get_string('lastday', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text'));
// display_badges(0, $courseid, 24 * 60 * 60);
//echo html_writer::tag('div', get_string('lastweek', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text'));
ob_start();
display_badges(0, $courseid, 12 * 31 * 7 * 24 * 60 * 60);
$out = ob_get_contents();
ob_end_clean();
if ($out != '') {
    echo $out;
} else {
    echo html_writer::tag('div', get_string('no_badges_awarded', 'block_oc_mooc_nav'), array('class' => 'oc-no-badges'));
}

// TODO Zertifikate

// TODO Highscore
//echo html_writer::tag('div', get_string('highscore', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text'));
//echo html_writer::tag('div', get_string('in_course', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text'));
//display_highscore($courseid);
//echo html_writer::tag('div', get_string('in_mooin', 'block_oc_mooc_nav'), array('class' => 'oc_badges_text'));


echo $OUTPUT->footer();
