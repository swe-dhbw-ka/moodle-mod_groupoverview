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
 * Strings of the groupoverview activity module are defined here.
 *
 * @package     mod_groupoverview
 * @category    string
 * @copyright   2016 Corinna Hertweck <corinnah.development@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['atleastonecategory'] = 'You need to provide at least one category.';
$string['categories'] = 'Categories';
$string['categorydescriptionno'] = 'Description {no}';
$string['categoryimg'] = 'Image for the category';
$string['categorynameno'] = 'Name {no}';
$string['categoryno'] = 'Category {no}';
$string['mappingerror:category'] = 'Selected category is not valid.';
$string['mappingerror:group'] = 'Group ID {$a} is not part of this course. ';
$string['mappingwarning'] = 'There are no groups in this course yet.';
$string['modulename'] = 'Groupoverview';
$string['modulename_help'] = 'The groupoverview module allows students and teachers to see a structured overview of all the groups.
        Teachers define this structure by creating categories to which the groups of the course can be mapped.';
$string['modulenameplural'] = 'Groupoverviews';
$string['page-mod-groupoverview-view'] = 'Groupoverview Module Main Page';
$string['pluginadministration'] = 'Groupoverview Administration';
$string['pluginname'] = 'Groupoverview';
$string['groupoverview:addinstance'] = 'Add a new groupoverview';
$string['groupoverview:view'] = 'View groupoverview';
$string['groupoverviewname'] = 'Name';
$string['showdescriptionview'] = 'Display description on view page';
$string['showdescriptionview_help'] = 'If enabled, the description above will be displayed on the groupoverview page.';
$string['shownameview'] = 'Display name on view page';
$string['shownameview_help'] = 'If enabled, the groupoverview name will be displayed as heading on the groupoverview page.';
$string['warning'] = 'Warning';
$string['warning:groupmode:nogroups:managegroups'] = 'The group mode of this course is set to \'{$a}\'. Therefore, users are not
        able to view the groups\' descriptions.';
$string['warning:groupmode:seperate:managegroups'] = 'The group mode of this course is set to \'{$a->seperate}\'. Therefore, users
        are only able to view the names and descriptions of the groups they are in. If you want them to see all the groups, you have
        to set the group mode to \'{$a->visible}\'.';
$string['warning:groupmode:seperate:notallowedtomanagegroups'] = 'You are only allowed to see the names of the groups you are in.';