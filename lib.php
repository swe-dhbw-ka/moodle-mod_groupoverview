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
 * Activity module interface functions are defined here
 *
 * @package     mod_groupoverview
 * @copyright   2016 Corinna Hertweck <corinnah.development@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the information if the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool true if the feature is supported, false if it is not supported, null if unknown
 */
function groupoverview_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Adds a new instance of the groupoverview into the database
 *
 * Given an object containing all the settings form data, this function will
 * save a new instance and return the id of the new instance.
 *
 * @param stdClass $groupoverview An object from the form in mod_form.php
 * @return int The id of the newly inserted groupoverview record
 */
function groupoverview_add_instance(stdClass $groupoverview) {
    global $DB;

    $groupoverview->timecreated = time();
    $groupoverview->timemodified = $groupoverview->timecreated;

    $groupoverview->id = $DB->insert_record('groupoverview', $groupoverview);
    foreach ($groupoverview->categoryname as $key => $value) {
        $value = trim($value);
        if (isset($value) && $value <> '') {
            $category = new stdClass();
            $category->name = $value;
            $category->groupoverviewid = $groupoverview->id;
            if (isset($groupoverview->categorydescription[$key])) {
                $category->description = $groupoverview->categorydescription[$key];
            }
            $category->timecreated = $groupoverview->timecreated;
            $category->timemodified = $groupoverview->timecreated;
            $DB->insert_record('groupoverview_categories', $category);
        }
    }

    return $groupoverview->id;
}

/**
 * Updates the existing instance of the groupoverview in the database
 *
 * Given an object containing all the settings form data, this function will
 * update the instance record with the new form data.
 *
 * @param stdClass $groupoverview An object from the form in mod_form.php
 * @return bool true
 */
function groupoverview_update_instance(stdClass $groupoverview) {
    global $DB;

    $groupoverview->timemodified = time();
    $groupoverview->id = $groupoverview->instance;

    // Update, delete or insert categories.
    foreach ($groupoverview->categoryname as $key => $value) {
        $value = trim($value);
        $category = new stdClass();
        $category->name = $value;
        $category->groupoverviewid = $groupoverview->id;
        if (isset($groupoverview->categorydescription[$key])) {
            $category->description = $groupoverview->categorydescription[$key];
        }
        $category->timemodified = time();
        if (isset($groupoverview->categoryid[$key]) && !empty($groupoverview->categoryid[$key])) {
            $category->id = $groupoverview->categoryid[$key];
            if (isset($value) && $value <> '') {
                $DB->update_record('groupoverview_categories', $category);
            } else {
                $categories = $DB->get_records('groupoverview_categories', array('id' => $category->id));
                groupoverview_delete_categories_and_their_mappings($categories);
            }
        } else {
            if (isset($value) && $value <> '') {
                $category->timecreated = $category->timemodified;
                $DB->insert_record('groupoverview_categories', $category);
            }
        }
    }

    $DB->update_record('groupoverview', $groupoverview);

    return true;
}

/**
 * Deletes the groupoverview instance, its categories and the mappings that are associated with these categories.
 *
 * @param int $id ID of the groupoverview instance
 * @return bool Success indicator
 */
function groupoverview_delete_instance($id) {
    global $DB;

    if (! $groupoverview = $DB->get_record('groupoverview', array('id' => $id))) {
        return false;
    }

    $result = true;

    if (! $categories = $DB->get_records('groupoverview_categories', array('groupoverviewid' => $groupoverview->id))) {
        groupoverview_delete_categories_and_their_mappings($categories);
    }

    if (! $DB->delete_records('groupoverview', array('id' => $groupoverview->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Deletes an array of categories and the mappings that are associated with it
 *
 * @param array $categories The categories whichs instances and mappings should be deleted from the database
 * @return bool Success indicator
 */
function groupoverview_delete_categories_and_their_mappings($categories) {
    global $DB;

    $result = true;
    foreach ($categories as $category) {
        if (! $DB->delete_records('groupoverview_mappings', array('categoryid' => $category->id))) {
            $result = false;
        }
        if (! $DB->delete_records('groupoverview_categories', array('id' => $category->id))) {
            $result = false;
        }
    }
    return $result;
}

/**
 * Returns the categories of a specific groupoverview module instance
 *
 * @param int $groupoverviewid The ID of the groupoverview module instance
 * @return array The categories of the specified groupoverview module instance
 */
function groupoverview_get_categories($groupoverviewid) {
    global $DB;

    return $DB->get_records('groupoverview_categories', array('groupoverviewid' => $groupoverviewid));
}

/**
 * Similar to groupoverview_get_categories($groupoverviewid), this function returns a list of all the categories
 * of the groupoverview module instance. Additionally, it adds an array of groups to the category in its 'linked_groups'
 * attribute.
 *
 * @param int $groupoverviewid The ID of the groupoverview module instance
 * @return array The categories with the groups that are mapped to them
 */
function groupoverview_get_categories_with_groups($groupoverviewid) {
    $categories = groupoverview_get_categories($groupoverviewid);
    foreach ($categories as $category) {
        $category->linked_groups = groupoverview_get_groups_in_a_category($category->id);
    }
    return $categories;
}

/**
 * Returns the groups that are mapped to a specific category.
 *
 * @param int $categoryid The ID of the category
 * @return array The groups that are associated with the specified category
 */
function groupoverview_get_groups_in_a_category($categoryid) {
    global $DB;

    $groupids = $DB->get_records('groupoverview_mappings', array('categoryid' => $categoryid), 'groupid', 'groupid');
    $groups = array();
    foreach ($groupids as $groupid) {
        try {
            $group = $DB->get_record('groups', array('id' => $groupid->groupid), '*', MUST_EXIST);
            $groups[] = $group;
        } catch (Exception $e) {
            $DB->delete_records('groupoverview_mappings', array('groupid' => $groupid->groupid));
        }
    }
    return $groups;
}

/**
 * Saves an array of mappings to the database. The current mapping data gets compared to the existing one to check which mappings
 * have to be updated or added.
 *
 * @param array $mappeddata The mappings that the user wants to be saved
 * @param array $existingdata The existing mappings of the groupoverview module instance
 */
function groupoverview_save_mappings($mappeddata, $existingdata = null) {
    if (!property_exists($mappeddata, 'mappings')) {
        return;
    }
    $existingdataarray = (array)$existingdata;
    if (empty($existingdataarray)) {
        groupoverview_add_mappings($mappeddata);
    } else {
        foreach (array_keys($mappeddata->mappings) as $key) {
            if (!isset($existingdata->mappings[$key]) || empty($existingdata->mappings[$key])) {
                groupoverview_add_mapping($key, $mappeddata->mappings[$key]);
            } else if ($existingdata->mappings[$key] != $mappeddata->mappings[$key]) {
                $oldcategoryid = $existingdata->mappings[$key];
                $newcategoryid = $mappeddata->mappings[$key];
                groupoverview_update_mapping($key, $oldcategoryid, $newcategoryid);
            }
        }
    }
}

/**
 * Adds an array of mappings to the database
 *
 * @param array $mappeddata The mappings to add to the database
 */
function groupoverview_add_mappings($mappeddata) {
    foreach ($mappeddata->mappings as $key => $value) {
        groupoverview_add_mapping($key, $value);
    }
}

/**
 * Updates an array of mappings stored in the database
 *
 * @param array $mappings The mappings to update
 */
function groupoverview_update_mappings($mappings) {
    foreach ($mappings as $key => $value) {
        groupoverview_update_mapping($key, $value->oldcategoryid, $value->newcategoryid);
    }
}

/**
 * Adds the mapping of a group to a category to the database
 *
 * @param int $groupid The ID of the group
 * @param int $categoryid The ID of the category
 * @return bool|number Returns false if mapping cannot be inserted into the DB. If it gets inserted, the id of the entry is
 * returned.
 */
function groupoverview_add_mapping($groupid, $categoryid) {
    global $DB;

    $mapping = new stdClass();
    $mapping->timecreated = time();
    $mapping->timemodified = $mapping->timecreated;
    $mapping->categoryid = $categoryid;
    $mapping->groupid = $groupid;
    return $DB->insert_record('groupoverview_mappings', $mapping, true);
}

/**
 * Updates a group->category mapping.
 *
 * @param int $groupid The id of the group
 * @param int $oldcategoryid The ID of the old category
 * @param int $newcategoryid The ID of the new category
 * @return bool Success indicator
 */
function groupoverview_update_mapping($groupid, $oldcategoryid, $newcategoryid) {
    global $DB;

    if ($mapping = $DB->get_record('groupoverview_mappings', array('groupid' => $groupid, 'categoryid' => $oldcategoryid))) {
        $oldgroupoverviewid = $DB->get_record('groupoverview_categories', array('id' => $oldcategoryid), 'groupoverviewid');
        $newgroupoverviewid = $DB->get_record('groupoverview_categories', array('id' => $newcategoryid), 'groupoverviewid');
        if ($oldgroupoverviewid == $newgroupoverviewid) {
            $mapping->timemodified = time();
            $mapping->categoryid = $newcategoryid;
            return $DB->update_record('groupoverview_mappings', $mapping);
        }
    }
    return false;
}

/**
 * Checks whether the given group ID is valid in this course. In other words, this function checks whether the group is part of this
 * course. This is supposed to help prevent the storing of invalid mappings.
 *
 * @param int $courseid The ID of the course
 * @param int $groupid The ID of the group
 * @return bool Returns true, if the group is part of this course and false, if it is not.
 */
function groupoverview_is_groupid_valid($courseid, $groupid) {
    $groups = groups_get_all_groups($courseid);
    $groupids = array();
    foreach ($groups as $group) {
        $groupids[] = $group->id;
    }
    return in_array($groupid, $groupids);
}

/**
 * Checks whether the given category ID is valid in this course. In other words, this function checks whether the category is part
 * of this groupoverview module instance. This is supposed to help prevent the storing of invalid mappings.
 *
 * @param int $groupoverviewid The ID of the groupoverview module instance
 * @param int $categoryid The ID of the category
 * @return bool Returns true, if the category is part of this groupoverview module instance and false, if it is not.
 */
function groupoverview_is_category_valid($groupoverviewid, $categoryid) {
    $categories = groupoverview_get_categories($groupoverviewid);
    $categoryids = array();
    foreach ($categories as $category) {
        $categoryids[] = $category->id;
    }
    return in_array($categoryid, $categoryids);
}

/**
 * Returns the mappings of specific groupoverview module instance
 *
 * @param int $groupoverviewid The groupoverview module instance
 * @return stdClass The mappings
 */
function groupoverview_get_mappings($groupoverviewid) {
    $data = new stdClass();
    $categories = groupoverview_get_categories($groupoverviewid);
    foreach ($categories as $category) {
        $groups = groupoverview_get_groups_in_a_category($category->id);
        foreach ($groups as $group) {
            $data->mappings[$group->id] = $category->id;
        }
    }

    return $data;
}

/**
 * Adds items into the groupoverview administration block
 *
 * @param settings_navigation $settingsnav The settings navigation object
 * @param navigation_node $node The node to add module settings to
 */
function groupoverview_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $node) {
    global $PAGE;

    if ($PAGE->user_allowed_editing()) {
        $url = $PAGE->url;
        $url->param('sesskey', sesskey());

        if ($PAGE->user_is_editing()) {
            $url->param('edit', 'off');
            $editstring = get_string('turneditingoff', 'core');
        } else {
            $url->param('edit', 'on');
            $editstring = get_string('turneditingon', 'core');
        }

        $node->add($editstring, $url, navigation_node::TYPE_SETTING);
    }
}

/**
 * Return the page type patterns that can be used by blocks
 *
 * @param string $pagetype Current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function groupoverview_page_type_list($pagetype, $parentcontext, $currentcontext) {
    return array(
        'mod-groupoverview-view' => get_string('page-mod-groupoverview-view', 'mod_groupoverview'),
    );
}
