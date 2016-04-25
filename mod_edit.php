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
 * Groupoverview instance mappings form is defined here.
 *
 * @package     mod_groupoverview
 * @copyright   2016 Corinna Hertweck <corinnah.development@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Defines the groupoverview instance mappings form
 *
 * @copyright 2015 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupoverview_mod_edit extends moodleform {

    /**
     * Defines the fields of the form
     */
    public function definition() {

        $mform = $this->_form;

        $groupoverviewid = $this->_customdata['groupoverviewid'];
        $courseid = $this->_customdata['courseid'];

        $groups = array_values(groups_get_all_groups($courseid));

        if (empty($groups)) {
            $mform->addElement('static', 'warning', get_string('warning', 'mod_groupoverview'),
                    get_string('mappingwarning', 'mod_groupoverview'));
        } else {
            $categories = groupoverview_get_categories($groupoverviewid);
            $categorynames = array();
            foreach ($categories as $category) {
                $categorynames[$category->id] = $category->name;
            }

            foreach ($groups as $group) {
                $mform->addElement('select', 'mappings[' . $group->id . ']', $group->name, $categorynames);
            }
        }

        // Add standard elements.
        $this->add_action_buttons();

        // Seems to be necessary in order for the redirects in view.php to work...otherwise the action attribute of the
        // html form is set to the current url and the user is immediately redirected there after clicking one of the buttons.
        $this->_form->updateAttributes(array('action' => ''));

    }

    /**
     * Validates the data entered by the user by checking whether the groups' IDs and the categories are valid in the context of
     * this groupoverview module instance.
     *
     * {@inheritDoc}
     * @see moodleform::validation()
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!array_key_exists('mappings', $data)) {
            return $errors;
        }
        $courseid = $this->_customdata['courseid'];
        $groupoverviewid = $this->_customdata['groupoverviewid'];
        foreach ($data['mappings'] as $key => $value) {
            if (!(groupoverview_is_groupid_valid($courseid, $key) and groupoverview_is_category_valid($groupoverviewid, $value))) {
                $errors['mappings[' . $key . ']'] = '';
                if (!groupoverview_is_groupid_valid($courseid, $key)) {
                    $errors['mappings[' . $key . ']'] .= get_string('mappingerror:group', 'mod_groupoverview', $key);
                }
                if (!groupoverview_is_category_valid($groupoverviewid, $value)) {
                    $errors['mappings[' . $key . ']'] .= get_string('mappingerror:category', 'mod_groupoverview');
                }
            }
        }
        return $errors;
    }
}