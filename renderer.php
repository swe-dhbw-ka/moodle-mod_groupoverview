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
 * Class mod_groupoverview_renderer is defined here.
 *
 * @package     mod_groupoverview
 * @category    output
 * @copyright   2016 Corinna Hertweck <corinnah.development@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('mod_edit.php');

/**
 * The renderer for groupoverview module
 *
 * @copyright 2015 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupoverview_renderer extends plugin_renderer_base {

    /**
     * Renders the top of the page with the title and the description.
     *
     * @param stdClass $groupoverview The groupoverview instance record
     * @return string The html for the top of the page
     */
    public function show_top_of_page($groupoverview) {

        if ($this->page->user_allowed_editing()) {
            $this->page->set_button($this->edit_button($this->page->url));
        }

        $out = $this->header();

        if ($groupoverview->shownameview) {
            $out .= $this->view_page_heading($groupoverview);
        }

        if ($groupoverview->showdescriptionview) {
            $out .= $this->view_page_description($groupoverview);
        }

        return $out;
    }

    /**
     * Renders warnings at the top of the page depending on the group mode settings
     *
     * @param bool $hasmanagingcapability Boolean that indicates whether the logged in user has the capability to manage groups
     * (course:managegroups)
     * @param int $groupmode The number of the group mode (0, 1 or 2)
     * @return string The html for the top of the page
     */
    public function show_warnings($hasmanagingcapability, $groupmode) {

        if ($groupmode == 0 && $hasmanagingcapability) {
            $nogroups = get_string('groupsnone', 'group');
            return $this->show_warning_label() .
                html_writer::span(get_string('warning:groupmode:nogroups:managegroups', 'mod_groupoverview', $nogroups));
        } else if ($groupmode == 1 && $hasmanagingcapability) {
            $groupmodes = new stdClass();
            $groupmodes->seperate = get_string('groupsseparate', 'group');
            $groupmodes->visible = get_string('groupsvisible', 'group');
            return $this->show_warning_label() .
                html_writer::span(get_string('warning:groupmode:seperate:managegroups', 'mod_groupoverview', $groupmodes));
        } else if ($groupmode == 1 && !$hasmanagingcapability) {
            return $this->show_warning_label() .
                html_writer::span(get_string('warning:groupmode:seperate:notallowedtomanagegroups', 'mod_groupoverview'));
        }

        return '';
    }

    /**
     * Renders a label that reads 'Warning' (or a translation of it) to show in front of warnings
     *
     * @return string The html of warning label
     */
    protected function show_warning_label() {
        return html_writer::span('<b>' . get_string('warning', 'mod_groupoverview') . ' </b>');
    }

    /**
     * Renders the bottom of the page
     *
     * @return string The html of the bottom of the page
     */
    public function show_bottom_of_page() {

        $out = $this->footer();

        return $out;
    }

    /**
     * Render the page title at the view.php page
     *
     * @param stdClass $groupoverview The groupoverview instance record
     * @return string The html for the page title
     */
    protected function view_page_heading($groupoverview) {
        return $this->heading(format_string($groupoverview->name), 2, null, 'mod_groupoverview-heading');
    }

    /**
     * Render the groupoverview description at the view.php page
     *
     * @param stdClass $groupoverview The groupoverview instance record
     * @return string The html for the groupoverview description
     */
    protected function view_page_description($groupoverview) {

        if (html_is_blank($groupoverview->intro)) {
            return '';
        }

        return $this->box(
                format_module_intro(
                    'groupoverview',
                    $groupoverview,
                    $this->page->cm->id),
                    'generalbox',
                    'mod_groupoverview-description');
    }

    /**
     * Returns the renderable that is filled with the data of the groupoverview
     *
     * @param renderable $page The renderable
     */
    public function render_page(renderable $page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_groupoverview/' . $page->get_templatename(), $data);
    }
}