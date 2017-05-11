<?php
require_once('../../config.php');
$id = optional_param('id', 0, PARAM_INT); // This are required.
redirect(new moodle_url('/course/view.php', array('id' => $id, 'tab' => 1)));
?>