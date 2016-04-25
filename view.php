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
 * View the groupoverview instance
 *
 * @package     mod_groupoverview
 * @copyright   2016 Corinna Hertweck <corinnah.development@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/completionlib.php');
require_once('lib.php');
require_once('renderable.php');

$cmid = required_param('id', PARAM_INT);
$edit = optional_param('edit', null, PARAM_BOOL);

$cm = get_coursemodule_from_id('groupoverview', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$groupoverview = $DB->get_record('groupoverview', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
require_capability('mod/groupoverview:view', $PAGE->context);

$PAGE->set_url('/mod/groupoverview/view.php', array('id' => $cm->id));
$PAGE->set_title($course->shortname.': '.$groupoverview->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_activity_record($groupoverview);

if ($edit !== null and confirm_sesskey() and $PAGE->user_allowed_editing()) {
    $USER->editing = $edit;
    redirect($PAGE->url);
}

// Trigger module viewed event.
$event = \mod_groupoverview\event\course_module_viewed::create(array(
   'objectid' => $groupoverview->id,
   'context' => $PAGE->context,
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('groupoverview', $groupoverview);
$event->trigger();

// Mark the module instance as viewed by the current user.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$output = $PAGE->get_renderer('mod_groupoverview');

echo $output->show_top_of_page($groupoverview);

if ($USER->editing !== null && $USER->editing === 1) {
    $mform = new mod_groupoverview_mod_edit(null, array(
            'groupoverviewid' => $groupoverview->id,
            'courseid' => $course->id));
    $toform = groupoverview_get_mappings($groupoverview->id);
    $mform->set_data($toform);
    if ($mform->is_cancelled()) {
        $USER->editing = 0;
        redirect($PAGE->url);
    } else if ($fromform = $mform->get_data()) {
        groupoverview_save_mappings($fromform, $toform);
        $USER->editing = 0;
        redirect($PAGE->url);
    }
    $mform->display();
} else {
    $categories = groupoverview_get_categories_with_groups($groupoverview->id);
    $page = new mod_groupoverview_renderable($categories, $course->id);
    echo $output->render_page($page);
}

echo $output->show_bottom_of_page($groupoverview);