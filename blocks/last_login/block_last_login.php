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
 * Last login block — lists the 10 users whose most recent access is the
 * oldest, with elapsed time and a link to their profile.
 *
 * @package    block_last_login
 * @copyright  2026 Fernando Capla
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_last_login extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_last_login');
    }

    public function get_content() {
        global $CFG, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $users = $DB->get_records_select(
            'user',
            'lastaccess > 0 AND deleted = 0 AND id != 1',
            [],
            'lastaccess ASC',
            '*',
            0,
            10
        );

        if (empty($users)) {
            $this->content->text = html_writer::tag('p', get_string('nousers', 'block_last_login'));
            return $this->content;
        }

        $now = time();
        $items = '';
        foreach ($users as $user) {
            $elapsed = $now - $user->lastaccess;
            $url = new moodle_url('/user/profile.php', ['id' => $user->id]);
            $namelink = html_writer::link($url, fullname($user));

            $a = new stdClass();
            $a->name = $namelink;
            $a->time = format_time($elapsed);

            $items .= html_writer::tag(
                'li',
                get_string('userline', 'block_last_login', $a),
                ['class' => 'last-login-item']
            );
        }

        $this->content->text = html_writer::tag('ul', $items, ['class' => 'last-login-list']);
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
