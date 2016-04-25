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
 * Provides the restore_groupoverview_activity_structure_step class.
 *
 * @package     mod_groupoverview
 * @category    backup
 * @copyright   2016 Corinna Hertweck <corinnah.development@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Structure step to restore groupoverview activity instance.
 *
 * @copyright 2016 Corinna Hertweck <corinnah.development@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_groupoverview_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the structure of the backup data to be processed.
     *
     * @return array of restore_path_element
     */
    protected function define_structure() {
        $paths = array();
        $groupinfo = $this->get_setting_value('groups');

        $paths[] = new restore_path_element('groupoverview', '/activity/groupoverview');
        $paths[] = new restore_path_element('groupoverview_category', '/activity/groupoverview/categories/category');
        if ($groupinfo) {
            $paths[] = new restore_path_element(
                    'groupoverview_mapping',
                    '/activity/groupoverview/categories/category/mappings/mapping');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the groupoverview element data.
     *
     * @param array $data
     */
    protected function process_groupoverview($data) {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $data->timemodified = time();

        $newid = $DB->insert_record('groupoverview', $data);

        $this->apply_activity_instance($newid);
    }

    /**
     * Process the category element data.
     *
     * @param array $data
     */
    protected function process_groupoverview_category($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->groupoverviewid = $this->get_new_parentid('groupoverview');
        $data->timemodified = time();

        $newitemid = $DB->insert_record('groupoverview_categories', $data);
        $this->set_mapping('groupoverview_category', $oldid, $newitemid);
    }

    /**
     * Process the mapping element data.
     *
     * @param array $data
     */
    protected function process_groupoverview_mapping($data) {
        global $DB;

        $data = (object)$data;

        $data->categoryid = $this->get_new_parentid('groupoverview_category');
        $data->groupid = $this->get_mappingid('group', $data->groupid);
        $data->timemodified = time();

        $newitemid = $DB->insert_record('groupoverview_mappings', $data);
    }

    /**
     * Define additional things to do after the steps are executed.
     */
    protected function after_execute() {
        $this->add_related_files('mod_groupoverview', 'intro', null);
    }
}
