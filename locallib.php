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
/**
 * @package   block_oc_mooc_nav
 * @copyright 2015 oncampus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function get_tabs($cid, $socialmediaid, $forumid = 0, $latest_chapter, $latest) {
	global $DB, $PAGE, $COURSE, $USER;
	
	// ID des Newsforums ermitteln
	$news_forum_id = '';
	if ($news_forum = $DB->get_record('forum', array('course' => $cid, 'type' => 'news'))) {
		$news_forum_id = $news_forum->id;
	}	
	
	// Link zum Kurs
	$url = new moodle_url('/course/view.php', array('id' => $cid, 'chapter' => $latest_chapter, 'selected_week' => $latest));
    $course_link = html_writer::link($url, ' ', array('id' => 'oc-coursestart', 'title' => 'Kurs'));
	// Link zur Teilnehmerliste mit GoogleMaps
	$url = new moodle_url('/blocks/oc_mooc_nav/users.php', array('id' => $cid, 'tab' => 1));
    $participants_link = html_writer::link($url, ' ', array('id' => 'oc-teilnehmer', 'title' => 'Kursteilnehmer'));
	// Link zur Badgeübersicht
	$url = new moodle_url('/blocks/oc_mooc_nav/view_badges.php', array('courseid' => $cid, 'tab' => 1));
    $badges_link = html_writer::link($url, ' ', array('id' => 'oc-badges', 'title' => 'Auszeichnungen'));
	// Link zum Newsarchiv (Nachrichtenforum)
	$url = new moodle_url('/mod/forum/view.php', array('f' => $news_forum_id, 'tab' => 1));
    $forum_link = html_writer::link($url, ' ', array('id' => 'oc-news', 'title' => 'News'));
	// Link zur SocialMedia-Seite
	$url = new moodle_url('/blocks/oc_mooc_nav/view_social.php', array('courseid' => $cid, 'socialmediaid' => $socialmediaid, 'tab' => 1));
    $social_link = html_writer::link($url, ' ', array('id' => 'oc-coursesocial', 'title' => 'Social Media'));
	// Link zum allgemeinen Forum
	$url = new moodle_url('/blocks/oc_mooc_nav/forum_view.php', array('id' => $forumid, 'tab' => 1));
    $discussion_forum_link = html_writer::link($url, ' ', array('id' => 'oc-diskussion', 'title' => 'Diskussionsforen'));
	
	$links = array( 'course' => $course_link, 
					'news' => $forum_link, 
					'users' => $participants_link, 
					'forum' => $discussion_forum_link, 
					'social' => $social_link, 
					'badges' => $badges_link);
	$tabs = array();
	$count = 0;
	$out = '';
	$exploded = explode('?', $PAGE->url->out(false));
	
	$is_news = false;
	
	$oc_debug = '';
	
	foreach ($links as $key => $link) {
		$count++;
		$style = 'oc_mooc_nav';
		// wenn wir im aktuell angeklickten Tab sind...
		if (strpos($link, $exploded[0]) !== false and $key != 'news') {
			$style = 'oc_mooc_nav_clicked';
			$oc_debug .= 'link: 1<br />';
		}
		if ($key == 'news' and strpos($exploded[0], '/mod/forum/') !== false 
				and strpos($exploded[0], 'search.php') === false
				and strpos($exploded[0], 'user.php') === false) {
			$id = optional_param('id', 0, PARAM_INT);
			$f = optional_param('f', 0, PARAM_INT);
			$d = optional_param('d', 0, PARAM_INT);
			$reply = optional_param('reply', 0, PARAM_INT);
			$is_news = is_news($news_forum_id, $id, $f, $d, $reply);
			if ($is_news) {
				$style = 'oc_mooc_nav_clicked';
				$oc_debug .= 'news: 1<br />';
			}
		}
		if ($key == 'users' and (strpos($exploded[0], '/user/') !== false or strpos($exploded[0], '/groupselect/') !== false)) {
			$style = 'oc_mooc_nav_clicked';
		}
		if ($key == 'forum' and strpos($exploded[0], '/mod/forum/') !== false) {
			if (strpos($exploded[0], '/mod/forum/search.php') !== false) {
				$style = 'oc_mooc_nav_clicked';
			}
			else if ($is_news == false) {
				$style = 'oc_mooc_nav_clicked';
				$oc_debug .= 'forum: 1<br />';
			}
		}
		$tabs[] = html_writer::tag('li', $link, array('class' => $style));
	}
	$nav = implode($tabs);
	
	$ul = html_writer::tag('ul', $nav, array('id' => 'oc-coursenavlist'));
	$section = html_writer::tag('section', $ul, array('id' => 'oc-coursenav'));
	
	return $section;
}

function is_news($news_forum_id, $id, $f, $d, $reply) {
	global $DB, $USER;
	if ($reply > 0) {
		$record = $DB->get_record('forum_posts', array('id' => $reply));
		$record = $DB->get_record('forum_discussions', array('id' => $record->discussion));
		if ($news_forum_id == $record->forum) {
			return true;
		}
	}
	else if ($d > 0) {
		$record = $DB->get_record('forum_discussions', array('id' => $d));
		if ($news_forum_id == $record->forum) {
			return true;
		}
	}
	else if ($f > 0) {
		$record = $DB->get_record('forum', array('id' => $f));
		if ($news_forum_id == $record->id) {
			return true;
		}
	}
	else if ($id > 0) {
		//$cm = get_coursemodule_from_id('forum', $id);
		$cm = $DB->get_record('course_modules', array('id' => $id));
		$record = $DB->get_record("forum", array("id" => $cm->instance));
		if ($news_forum_id == $record->id) {
			return true;
		}
	}
	return false;
}

function display_badges($userid = 0, $courseid = 0, $since = 0, $print = true) {
    global $CFG, $PAGE, $USER, $SITE;
    require_once($CFG->dirroot . '/badges/renderer.php');

    // Determine context.
    if (isloggedin()) {
        $context = context_user::instance($USER->id);
    } else {
        $context = context_system::instance();
    }

	if ($userid == 0) {
		if ($since == 0) {
			$records = get_badges($courseid, null, null, null);
		}
		else {
			$records = get_badges_since($courseid, $since, false);
			// globale Badges
			// if ($courseid != 0) {
				// $records = array_merge(get_badges_since($courseid, $since, true), $records);
			// }
		}
        $renderer = new core_badges_renderer($PAGE, '');

        // Print local badges.
        if ($records) {
            //$right = $renderer->print_badges_list($records, $userid, true);
			if ($since == 0) {
				print_badges($records);
			}
			else {
				print_badges($records, true);
			}
        }
	}
    elseif ($USER->id == $userid || has_capability('moodle/badges:viewotherbadges', $context)) {
        $records = badges_get_user_badges($userid, $courseid, null, null, null, true);
        $renderer = new core_badges_renderer($PAGE, '');

        // Print local badges.
        if ($records) {
            $right = $renderer->print_badges_list($records, $userid, true);
			if ($print) {
				echo html_writer::tag('dd', $right);
				//print_badges($records);
			}
			else {
				return html_writer::tag('dd', $right);
			}
        }
    }
}

function display_user_and_availbale_badges($userid, $courseid) {
    global $CFG, $USER;
    require_once($CFG->dirroot . '/badges/renderer.php');
	
	$coursebadges = get_badges($courseid, null, null, null);
    $userbadges = badges_get_user_badges($userid, $courseid, null, null, null, true);
	
	foreach ($userbadges as $ub) {
		$coursebadges[$ub->id]->highlight = true;
		$coursebadges[$ub->id]->uniquehash = $ub->uniquehash;
	}
	
	print_badges($coursebadges, false, true, true);
}

function print_badges($records, $details = false, $highlight = false, $badgename = false) {
	global $DB;
	$lis = '';
	foreach ($records as $record) {
		if ($record->type == 2) {
			$context = context_course::instance($record->courseid);
		}
		else {
			$context = context_system::instance();
		}
		$opacity = '';
		if ($highlight) {
			$opacity = ' opacity: 0.15;';
			if ($record->highlight) {
				$opacity = ' opacity: 1.0;';
			}
		}
		$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $record->id, '/', 'f1', false);
		$image = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image', 'style' => 'width: 100px; height: 100px;'.$opacity));
		if ($record->uniquehash) {
			$url = new moodle_url('/badges/badge.php', array('hash' => $record->uniquehash));
		}
		else {
			$url = new moodle_url('/badges/overview.php', array('id' => $record->id));
		}
		$detail = '';
		if ($details) {
			$user = $DB->get_record('user', array('id' => $record->userid));
			$detail = '<br />'.$user->firstname.' '.$user->lastname.'<br />('.date('d.m.y H:i', $record->dateissued).')';
		}
		else if ($badgename) {
			$detail = '<br />'.$record->name;
		}
		$link = html_writer::link($url, $image.$detail, array('title' => $record->name));
		$lis .= html_writer::tag('li', $link);
	}
	echo html_writer::tag('ul', $lis, array('class' => 'badges'));
}

function get_badges_list($userid, $courseid = 0) {
	global $CFG, $USER;
    require_once($CFG->dirroot . '/badges/renderer.php');

    if ($courseid == 0) {
		$context = context_system::instance();
	}
	else {
		$context = context_course::instance($courseid);
	}

	if ($USER->id == $userid || has_capability('moodle/badges:viewotherbadges', $context)) {
		if ($courseid == 0) {
			$records = get_global_user_badges($userid);
		}
		else {
			$records = badges_get_user_badges($userid, $courseid, null, null, null, true);
		}
        // Print local badges.
        if ($records) {
			$out = '';
			foreach ($records as $record) {
				$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $record->id, '/', 'f1', false);
				$image = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'badge-image', 'style' => 'width: 30px; height: 30px;'));
				$url = new moodle_url('/badges/badge.php', array('hash' => $record->uniquehash));
				$link = html_writer::link($url, $image, array('title' => $record->name));
				$out .= $link;
			}
			return $out;
        }
    }
}

function get_global_user_badges($userid) {
	 global $DB;

    $params = array(
        'userid' => $userid,
		'type' => 1
    );
    $sql = 'SELECT 
                b.*, 
				bi.uniquehash
             FROM 
                {badge} b, 
                {badge_issued} bi 
            WHERE bi.userid = :userid 
              AND b.id = bi.badgeid 
			  AND b.type = :type 
		 ORDER BY bi.dateissued DESC';
	
    $badges = $DB->get_records_sql($sql, $params);
	
    return $badges;
}

function get_badges($courseid = 0, $page = 0, $perpage = 0, $search = '') {
    global $DB;
    $params = array();
    $sql = 'SELECT
                b.*
            FROM
                {badge} b
            WHERE b.type > 0 ';

	if ($courseid == 0) {
		$sql .= ' AND b.type = :type';
		$params['type'] = 1;
	}
	
	if ($courseid != 0) {
		$sql .= ' AND b.courseid = :courseid';
        $params['courseid'] = $courseid;
    }
	
    if (!empty($search)) {
        $sql .= ' AND (' . $DB->sql_like('b.name', ':search', false) . ') ';
        $params['search'] = '%'.$DB->sql_like_escape($search).'%';
    }
	
    $badges = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

    return $badges;
}

function get_badges_since($courseid, $since, $global = false) {
	global $DB;
	if (!$global) {
		$params = array();
		$sql = 'SELECT
					b.*,
					bi.id,
					bi.badgeid,
					bi.userid,
					bi.dateissued,
					bi.uniquehash
				FROM
					{badge} b,
					{badge_issued} bi
				WHERE b.id = bi.badgeid ';
	
	
		$sql .= ' AND b.courseid = :courseid';
        $params['courseid'] = $courseid;
    
		if ($since > 0) {
			$sql .= ' AND bi.dateissued > :since ';
			$since = time() - $since;
			$params['since'] = $since;
		}
		$sql .= ' ORDER BY bi.dateissued DESC ';
		$sql .= ' LIMIT 0, 20 ';
		$badges = $DB->get_records_sql($sql, $params);
	}
	else {
		$params = array('courseid' => $courseid);
		$sql = 'SELECT
					b.*,
					bi.id,
					bi.badgeid,
					bi.userid,
					bi.dateissued,
					bi.uniquehash
				FROM
					{badge} b,
					{badge_issued} bi,
					{user_enrolments} ue,
					{enrol} e
				WHERE b.id = bi.badgeid 
				AND	bi.userid = ue.userid 
				AND ue.enrolid = e.id 
				AND e.courseid = :courseid ';
	
	
		$sql .= ' AND b.type = :type';
        $params['type'] = 1;
    
		if ($since > 0) {
			$sql .= ' AND bi.dateissued > :since ';
			$since = time() - $since;
			$params['since'] = $since;
		}
		$sql .= ' ORDER BY bi.dateissued DESC ';
		$sql .= ' LIMIT 0, 20 ';
		$badges = $DB->get_records_sql($sql, $params);
	}
	
	$correct_badges = array();
	foreach ($badges as $badge) {
		$badge->id = $badge->badgeid;
		$correct_badges[] = $badge;
	}
	return $correct_badges;
}

function get_img_urls($cmid) {
	$img_urls = array();
	$fs = get_file_storage();
	try {
		$foldercontext = context_module::instance($cmid);
	}
	catch (Exception $e) {
		return $img_urls;
	}
	$dir = $fs->get_area_tree($foldercontext->id, 'mod_folder', 'content', 0);
	foreach ($dir['files'] as $file) {
		$filename = $file->get_filename();
		$url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
				$file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $filename, false);
		$image = $url->out(false, array('oid' => $file->get_timemodified()));
		//$image = html_writer::empty_tag('img', array('src' => $image));
		$img_urls[$filename] = $image;
	}
	return $img_urls;
}

function get_chapters($chapter_configtext) {
	$lines = preg_split( "/[\r\n]+/", trim($chapter_configtext));
	$chapters = array();
	$number = 0;
	$first = 1;
	foreach ($lines as $line) {
		// name=Kapitel 1;lections=8;enabled=true
		$elements = explode(';', $line);
		$chapter = array();
		$chapter['number'] = $number;
		$number++;
		$chapter['first_lection'] = $first;
		foreach ($elements as $element) {
			// name=Kapitel 1
			$ex = explode('=', $element);
			$chapter[$ex[0]] = $ex[1];
		}
		$first += $chapter['lections'];
		$chapters[] = $chapter;
	}
	return $chapters;
}

function get_chapter_for_lection($lection, $chapter_configtext) {
	$sections = 0;
	$chapters = get_chapters($chapter_configtext);
	foreach ($chapters as $chapter) {
		$sections = $sections + $chapter['lections'];
		if ($sections >= $lection) {
			return $chapter;
		}
	}
	return false;
}

function display_highscore($courseid) {
	
}
?>