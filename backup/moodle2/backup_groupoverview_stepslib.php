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
 * Provides the backup_groupoverview_activity_structure_step class.
 *
 * @package     mod_groupoverview
 * @category    backup
 * @copyright   2016 Corinna Hertweck <corinnah.development@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the definition of the backup structure
 *
 * @copyright 2016 Corinna Hertweck <corinnah.development@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_groupoverview_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the backup
     *
     * The groupoverview activity does not contain user data. Additional nodes apart from the instances itself are the categories
     * and mappings.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        $groupinfo = $this->get_setting_value('groups');

        // Define the groupoverview root element.
        $groupoverview = new backup_nested_element('groupoverview', array('id'), array(
            'name', 'intro', 'introformat', 'timecreated', 'timemodified', 'shownameview', 'showdescriptionview'));

        // Define each element.
        $categories = new backup_nested_element('categories');

        $category = new backup_nested_element(
                'category',
                array('id'),
                array('name', 'description', 'timecreated', 'timemodified'));
        $mappings = new backup_nested_element('mappings');

        $mapping = new backup_nested_element('mapping', array('id'), array('groupid', 'timecreated', 'timemodified'));

        // Build the tree.
        $groupoverview->add_child($categories);
        $categories->add_child($category);
        $category->add_child($mappings);
        $mappings->add_child($mapping);

        // Define the data source.
        $groupoverview->set_source_table('groupoverview', array('id' => backup::VAR_ACTIVITYID));
        $category->set_source_table('groupoverview_categories', array('groupoverviewid' => backup::VAR_PARENTID));
        if ($groupinfo) {
            $mapping->set_source_table('groupoverview_mappings', array('categoryid' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $mapping->annotate_ids('group', 'groupid');

        // Define file annotations.
        $groupoverview->annotate_files('mod_groupoverview', 'intro', null);

        return $this->prepare_activity_structure($groupoverview);
    }
}
