<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

class block_oc_mooc_nav extends block_base {

    public function init() {
		global $PAGE;
        $this->title = get_string('pluginname', 'block_oc_mooc_nav');
		//$PAGE->blocks->add_region('center');
		//$this->instance->region = 'center';
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return false;
    }

    public function instance_allow_config() {
        return true;
    }

    public function applicable_formats() {
        return array(
                'all' => true
        );
    }

    public function specialization() {
        if (!empty($this->config->title)) {
			$this->title = $this->config->title;
		} else {
			$this->config->title = 'MOOC Kapitelnavigation';
		}
    }

    public function get_content() {
        global $USER, $PAGE, $COURSE, $DB, $CFG;

		require_once($CFG->dirroot.'/mod/occapira/locallib.php');
		require_once(dirname(__FILE__).'/locallib.php');
		$latest = 1;
		$latest_chapter = 0;
		$next = false;
		$records = $DB->get_records_sql('SELECT * FROM {course_sections} WHERE course = ? AND section != 0 ORDER BY section DESC', array($COURSE->id));
		foreach ($records as $record) {
			$percentage = occapira_get_section_percentage($record->course, $record->id);
			//echo $record->course.' '.$record->section.'<br />';
			if ($percentage > 0) {
				//echo $record->course.' '.$record->section.': '.$percentage.'<br />';
				$latest = $record->section;
				if ($percentage == 100 and $next = true) {
					$latest++;
				}
				break;
			}
			$next = true;
		}
		if ($latest > 0) {
			$lc = get_chapter_for_lection($latest, $this->config->chapter_configtext);
			$latest_chapter = $lc['number'];
			//print_object($latest_chapter);
		}
		//echo $latest_chapter.' '.$latest.'<br />';
		
        if ($this->content !== null) {
            return $this->content;
        }
		
		$content = '';
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////
		// Zuerst müssen die 5 Tabs gerendert werden (Teilnehmer + Map, Newsforum, Badges, Social Media, Kurs)
		//print_object($COURSE);die();
		if ($COURSE) {
			$content .= get_tabs($COURSE->id, $this->config->socialmedia_link, $this->config->discussion_link, $latest_chapter, $latest).'<br />';
		}
		
		$translate .= '<div style="margin-left:10px;" id="google_translate_element"></div><script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: \'de\', layout: google.translate.TranslateElement.InlineLayout.SIMPLE, multilanguagePage: true}, \'google_translate_element\');
}
</script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>';
		if ($COURSE->id == 20 || $COURSE->id == 68) {
			$content .= $translate;
		}
		
		//////////////////////////////////////////////////////////////////////
		// Ab hier werden die Kapitelbildchen zum durchscrollen/wischen gebaut
		
		// $img_urls['0.png'] == https://mooin.oncampus.de/pluginfile.php/79/mod_folder/content/0/0.png?oid=1424271017
		$img_urls = get_img_urls($this->config->directory_link);
		$chapter_configtext = $this->config->chapter_configtext;
		$chapters = get_chapters($chapter_configtext);
		
		
		
		//print_object($chapters);
		
		$exploded = explode('?', $PAGE->url->out(false));

		$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
		$is_teacher = has_capability('moodle/course:update', $context);//has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
		// nur anzeigen, wenn wir in der Kursansicht sind
		if ($CFG->wwwroot.'/course/view.php' == $exploded[0]) {

			$active_chapter = optional_param('chapter', 0, PARAM_INT);
			if (!$is_teacher) {
				$hidden = 0;
				$cha = 0;
				foreach($chapters as $ch) {
					if ($cha == $active_chapter) {
						break;
					}
					if ($ch['enabled'] == 'hidden') {
						$hidden++;
					}
					$cha++;
				}
				$active_chapter = $active_chapter - $hidden;
			}
			
			$content .= '<script>var chapter = '.$active_chapter.';</script>';
			$content .= '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>'; // TODO jquery.js im block-verzeichnis verwenden
			$content .= '<script src="'.new moodle_url('/blocks/oc_mooc_nav/js/vendor/modernizr.js').'"></script>';
			$content .= '<script src="'.new moodle_url('/blocks/oc_mooc_nav/js/vendor/plugins.js').'"></script>';
			$content .= '<script src="'.new moodle_url('/blocks/oc_mooc_nav/js/sly.js').'"></script>';
			$content .= '<script src="'.new moodle_url('/blocks/oc_mooc_nav/js/centered.js').'"></script>';
			$content .= '<link rel="stylesheet" type="text/css" href="'.new moodle_url('/blocks/oc_mooc_nav/css/nav.css').'">';
			$content .= '<div align="center" class="wrap">';
			$content .= '
						<button class="btn prev btn_prev_left"><!-- prev --></button>
						<button class="btn next btn_next_right"><!-- next --></button>
					';
			$content .= '<div class="frame" id="forcecentered">
					<ul class="clearfix oc-kapitelzahl-'.count($chapters).'">';
			// li-tags mit den Bildern für die Navigation erzeugen
			$i = 0;
			foreach ($chapters as $chapter) { // $all_chapters
				//$img_url = $img_path.$chapter['number'].'.png';
				if ($chapter['enabled'] != 'hidden' or $is_teacher) {
					$img_url = $img_urls[$chapter['img']];
					$class = '';
					if ($chapter['enabled'] == 'false') {
						$class = 'chapter-disabled';
					}
					if ($i == 0) {
						$content .= '<li class="first-chapter'.$class.'">';
					}
					else if ($i == (count($chapters) - 1)) {
						$content .= '<li class="last-chapter'.$class.'">';
					}
					else {
						$content .= '<li class="'.$class.'">';
					}
					$low = $chapter['first_lection'];
					$content .= '<a href="../course/view.php?id='.$COURSE->id.'&chapter='.$chapter['number'].'&selected_week='.$low.'"><img src="'.$img_url.'" data-item="'.$chapter['number'].'"/></a></li>';
				}
				$i++;
			}
			
			$content .= '</ul>
				</div>';
			$content .= '</div>';
		}

        $this->content = new stdClass();
        $this->content->text = $content;

        return $this->content;
    }
}
