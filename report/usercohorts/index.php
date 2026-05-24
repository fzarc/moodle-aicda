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
 * Users-by-cohort report. For every cohort, lists each member with
 * their profile picture, full name, email and the number of courses
 * they are enrolled in.
 *
 * @package    report_usercohorts
 * @copyright  2026 Fernando Capla
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('reportusercohorts', '', null, '', ['pagelayout' => 'report']);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_usercohorts'));

$cohorts = $DB->get_records_select('cohort', '1=1', [], 'name ASC');

if (empty($cohorts)) {
    echo $OUTPUT->notification(get_string('nocohorts', 'report_usercohorts'), 'info');
    echo $OUTPUT->footer();
    exit;
}

foreach ($cohorts as $cohort) {
    $members = $DB->get_records_select(
        'cohort_members',
        'cohortid = ?',
        [$cohort->id],
        'id ASC'
    );
    $membercount = count($members);

    $heading = new stdClass();
    $heading->name = format_string($cohort->name);
    $heading->count = $membercount;
    echo $OUTPUT->heading(get_string('cohortheading', 'report_usercohorts', $heading), 3);

    if ($membercount === 0) {
        echo $OUTPUT->notification(get_string('nomembers', 'report_usercohorts'), 'info');
        continue;
    }

    $table = new html_table();
    $table->head = [
        get_string('col_photo', 'report_usercohorts'),
        get_string('col_name', 'report_usercohorts'),
        get_string('col_email', 'report_usercohorts'),
        get_string('col_courses', 'report_usercohorts'),
    ];
    $table->attributes = ['class' => 'generaltable report-usercohorts'];
    $table->data = [];

    foreach ($members as $member) {
        $user = $DB->get_record_select('user', 'id = ?', [$member->userid]);
        if (!$user || $user->deleted) {
            continue;
        }

        $coursecount = $DB->count_records_sql(
            "SELECT COUNT(DISTINCT e.courseid)
               FROM {user_enrolments} ue
               JOIN {enrol} e ON e.id = ue.enrolid
              WHERE ue.userid = ?",
            [$user->id]
        );

        $profileurl = new moodle_url('/user/profile.php', ['id' => $user->id]);

        $table->data[] = [
            $OUTPUT->user_picture($user, ['size' => 35]),
            html_writer::link($profileurl, fullname($user)),
            s($user->email),
            $coursecount,
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();
