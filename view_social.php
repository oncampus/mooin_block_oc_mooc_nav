<?php
require_once('../../config.php');

$courseid = optional_param('courseid', 1, PARAM_INT);
$socialmediaid = optional_param('socialmediaid', 0, PARAM_INT);

$PAGE->set_url('/blocks/oc_mooc_nav/view_social.php', array('courseid' => $courseid));

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);
$smcontext = context_module::instance($socialmediaid);

// Not needed anymore.
unset($contextid);

require_login($course);

$systemcontext = context_system::instance();

$PAGE->set_pagelayout('incourse');
//$PAGE->set_title("$course->shortname: ".get_string('participants'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagetype('course-view-' . $course->format);
$PAGE->add_body_class('path-user');                     // So we can style it independently.
$PAGE->set_other_editing_capability('moodle/course:manageactivities');

// Ab hier beginnt der code aus mod/page/view.php /////////////////////////////////
if (!$cm = get_coursemodule_from_id('page', $socialmediaid)) {
    print_error('invalidcoursemodule');
}
$page = $DB->get_record('page', array('id' => $cm->instance), '*', MUST_EXIST);

$PAGE->set_title($course->shortname . ': ' . $page->name);
//$PAGE->set_activity_record($page);

echo $OUTPUT->header();

if ($socialmediaid == 0) {
    echo $OUTPUT->heading('No social media id!');
    echo $OUTPUT->footer();
    exit;
}

//$context = context_module::instance($cm->id);
$content = file_rewrite_pluginfile_urls($page->content, 'pluginfile.php', $smcontext->id, 'mod_page', 'content', $page->revision);
$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $context;
$content = format_text($content, $page->contentformat, $formatoptions);

echo $OUTPUT->box($content, "generalbox center clearfix");
// Hier endet der code aus mod/page/view.php /////////////////////////////////

echo $OUTPUT->footer();
