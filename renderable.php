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
 * Groupoverview renderable.
 *
 * @package    mod_groupoverview
 * @copyright  2015 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Renderable to view the groupoverview.
 *
 * @copyright  2015 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupoverview_renderable implements renderable, templatable {

    /**
     * @var array $categories All the categories with the groups that are mapped to them
     */
    private $categories = null;

    /**
     * @var int $courseid The id of the course that the groupoverview instance belongs to
     */
    private $courseid = null;

    /**
     * Creates a renderable for groupoverview
     *
     * @param array $categories The categories of the groupoverview module instance
     * @param int $courseid The course ID. This is used to generate the links to the view of the individual groups.
     */
    public function __construct($categories, $courseid) {
        $this->categories = $categories;
        $this->courseid = $courseid;
    }

    /**
     * Returns the name of the template for this renderable.
     *
     * @return string The template name
     */
    public function get_templatename() {
        return 'view_groupoverview';
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return stdClass The data to render
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->categories = array_values($this->categories);
        array_values($data->categories)[0]->active = true;
        return $data;
    }
}