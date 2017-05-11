<?php 
// oncampus kapitelnavigation
$chapters = array();
// Kapitel 1
$first = 1; 
$sections = 8; 
$chapters[] = array('number' => 0, 'name' => 'Woche 1', 'enabled' => true, 'sections' => $sections, 'first_section' => $first);
// Kapitel 2
$first = $sections + $first;
$sections = 7;
$chapters[] = array('number' => 1, 'name' => 'Woche 2', 'enabled' => true, 'sections' => $sections, 'first_section' => $first);
// Kapitel 3
$first = $sections + $first;
$sections = 6;
$chapters[] = array('number' => 2, 'name' => 'Woche 3', 'enabled' => true, 'sections' => $sections, 'first_section' => $first);
// Kapitel 4
$first = $sections + $first;
$sections = 6;
$chapters[] = array('number' => 3, 'name' => 'Woche 4', 'enabled' => true, 'sections' => $sections, 'first_section' => $first);
// Kapitel 5
$first = $sections + $first;
$sections = 7;
$chapters[] = array('number' => 4, 'name' => 'Woche 5', 'enabled' => true, 'sections' => $sections, 'first_section' => $first);
// Kapitel 6
$first = $sections + $first;
$sections = 7;
$chapters[] = array('number' => 5, 'name' => 'Woche 6', 'enabled' => true, 'sections' => $sections, 'first_section' => $first);
// Kapitel 7
$first = $sections + $first;
$sections = 7;
$chapters[] = array('number' => 6, 'name' => 'Woche 7', 'enabled' => true, 'sections' => $sections, 'first_section' => $first);
// Kapitel 8
$first = $sections + $first;
$sections = 9;
$chapters[] = array('number' => 7, 'name' => 'Woche 8', 'enabled' => true, 'sections' => $sections, 'first_section' => $first);
// Kapitel 9
$chapter_enabled = false;
if (time() >= strtotime('30.05.2014 16:00:00')) {
	$chapter_enabled = true;
}
$first = $sections + $first;
$sections = 7;
$chapters[] = array('number' => 8, 'name' => 'Woche 9', 'enabled' => true, 'sections' => $sections, 'first_section' => $first);
// Kapitel 10
$first = $sections + $first;
$sections = 6;
$chapters[] = array('number' => 9, 'name' => 'Woche 10', 'enabled' => true, 'sections' => $sections, 'first_section' => $first);

$last_section = $first + $sections - 1;
$all_chapters = $chapters;
$img_path = new moodle_url('/blocks/oc_mooc_nav/pix/');
// oncampus ende
