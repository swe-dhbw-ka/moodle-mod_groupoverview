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
 * Groupoverview instance settings form is defined here.
 *
 * @package     mod_groupoverview
 * @copyright   2016 Corinna Hertweck <corinnah.development@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Defines the groupoverview instance settings form
 *
 * @copyright 2015 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupoverview_mod_form extends moodleform_mod {

    /**
     * Defines the fields of the form
     */
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        // Start the general form section.
        $mform->addElement('header', 'general', get_string('general', 'core_form'));

        // Add the groupoverview name field.
        $mform->addElement('text', 'name', get_string('groupoverviewname', 'mod_groupoverview'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', 'core', 255), 'maxlength', 255, 'client');

        // Add the show name at the view page field.
        $mform->addElement('advcheckbox', 'shownameview', get_string('shownameview', 'mod_groupoverview'));
        $mform->setDefault('shownameview', 1);
        $mform->addHelpButton('shownameview', 'shownameview', 'mod_groupoverview');

        // Add the instruction/description field.
        if ($CFG->version >= 2015051100) {
            // Moodle 2.9.0 and higher use the new API.
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Add the show description at the view page field.
        $mform->addElement('advcheckbox', 'showdescriptionview', get_string('showdescriptionview', 'mod_groupoverview'));
        $mform->setDefault('showdescriptionview', 1);
        $mform->addHelpButton('showdescriptionview', 'showdescriptionview', 'mod_groupoverview');

        // Start the category form section.
        $mform->addElement('header', 'categories', get_string('categories', 'mod_groupoverview'));

        $repeatarray = array();
        $repeatarray[] = $mform->createElement(
                'static',
                'categoryheader',
                '<h4>' . get_string('categoryno', 'mod_groupoverview') . '</h4>');
        $repeatarray[] = $mform->createElement('text', 'categoryname', get_string('categorynameno', 'mod_groupoverview'));
        $repeatarray[] = $mform->createElement(
                'textarea',
                'categorydescription',
                get_string('categorydescriptionno', 'mod_groupoverview'));
        $repeatarray[] = $mform->createElement('hidden', 'categoryid', 0);

        if ($this->_instance) {
            $repeatno = $DB->count_records('groupoverview_categories', array('groupoverviewid' => $this->_instance));
            $repeatno += 2;
        } else {
            $repeatno = 4;
        }

        $mform->setType('categoryname', PARAM_CLEANHTML);
        $mform->setType('categorydescription', PARAM_CLEANHTML);
        $mform->setType('optionid', PARAM_INT);

        $mform->setType('categoryid', PARAM_INT);

        $this->repeat_elements($repeatarray, $repeatno,
                array(), 'category_repeats', 'category_add_fields', 2, null, true);

        // Make the first option required.
        if ($mform->elementExists('categoryname[0]')) {
            $mform->addRule('categoryname[0]', get_string('atleastonecategory', 'mod_groupoverview'), 'required', null, 'client');
        }

        // Add standard elements.
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Sets the default values for the form by using the data that is already stored in the database.
     * {@inheritDoc}
     * @see moodleform_mod::data_preprocessing()
     */
    public function data_preprocessing(&$defaultvalues) {
        global $DB;
        if (!empty($this->_instance)
                && ($categorynames = $DB->get_records_menu(
                        'groupoverview_categories', array('groupoverviewid' => $this->_instance), 'id', 'id,name'))
                && ($categorydescriptions = $DB->get_records_menu(
                        'groupoverview_categories', array('groupoverviewid' => $this->_instance), 'id', 'id,description')) ) {
                    $categoryids = array_keys($categorynames);
                    $categorynames = array_values($categorynames);
                    $categorydescriptions = array_values($categorydescriptions);

            foreach (array_keys($categorynames) as $key) {
                        $defaultvalues['categoryname['.$key.']'] = $categorynames[$key];
                        $defaultvalues['categorydescription['.$key.']'] = $categorydescriptions[$key];
                        $defaultvalues['categoryid['.$key.']'] = $categoryids[$key];
            }

        }
    }
}
