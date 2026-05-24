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
 * Cohort welcome block — greets the user by name with a cohort-specific
 * message and the size of their cohort.
 *
 * @package    block_cohort_welcome
 * @copyright  2026 Fernando Capla
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_cohort_welcome extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_cohort_welcome');
    }

    public function get_content() {
        global $USER, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (!isloggedin() || isguestuser()) {
            $this->content->text = html_writer::tag(
                'p',
                get_string('welcomeguest', 'block_cohort_welcome')
            );
            return $this->content;
        }

        $fullname = fullname($USER);
        $text  = html_writer::tag('h4', get_string('hello', 'block_cohort_welcome', $fullname));

        $memberships = $DB->get_records_select(
            'cohort_members',
            'userid = ?',
            [$USER->id],
            'id'
        );

        if (empty($memberships)) {
            $text .= html_writer::tag('p', get_string('nocohort', 'block_cohort_welcome'));
            $this->content->text = $text;
            return $this->content;
        }

        foreach ($memberships as $membership) {
            $cohort = $DB->get_record_select(
                'cohort',
                'id = ?',
                [$membership->cohortid]
            );
            if (!$cohort) {
                continue;
            }
            $members = $DB->get_records_select(
                'cohort_members',
                'cohortid = ?',
                [$cohort->id]
            );

            // Cohort-specific message keyed by idnumber, with safe fallback.
            $stringkey = 'msg_' . $cohort->idnumber;
            if (!empty($cohort->idnumber) &&
                get_string_manager()->string_exists($stringkey, 'block_cohort_welcome')) {
                $cohortmsg = get_string($stringkey, 'block_cohort_welcome');
            } else {
                $cohortmsg = get_string('msg_default', 'block_cohort_welcome', format_string($cohort->name));
            }

            $a = new stdClass();
            $a->cohort = format_string($cohort->name);
            $a->count  = count($members);

            $text .= html_writer::tag('p', $cohortmsg);
            $text .= html_writer::tag(
                'p',
                get_string('cohortmembers', 'block_cohort_welcome', $a),
                ['class' => 'cohort-welcome-members']
            );
        }

        $this->content->text = $text;
        return $this->content;
    }

    public function applicable_formats() {
        return [
            'all'         => true,
            'site'        => true,
            'my'          => true,
            'course-view' => true,
        ];
    }

    public function has_config() {
        return false;
    }

    public function instance_allow_multiple() {
        return false;
    }
}
