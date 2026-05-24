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
 * Categories / courses / students report. For every course category,
 * lists each course with its description, dates, duration, student
 * count and how many courses in the same category share the same
 * student count.
 *
 * @package    report_categoryreport
 * @copyright  2026 Fernando Capla
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('reportcategoryreport', '', null, '', ['pagelayout' => 'report']);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'report_categoryreport'));

$categories = $DB->get_records_select('course_categories', '1=1', [], 'sortorder ASC');

if (empty($categories)) {
    echo $OUTPUT->notification(get_string('nocategories', 'report_categoryreport'), 'info');
    echo $OUTPUT->footer();
    exit;
}

$datefmt = get_string('strftimedatefullshort', 'core_langconfig');
$studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);

foreach ($categories as $category) {
    $courses = $DB->get_records_select(
        'course',
        'category = ?',
        [$category->id],
        'fullname ASC'
    );
    $coursecount = count($courses);

    $heading = new stdClass();
    $heading->name = format_string($category->name);
    $heading->count = $coursecount;
    echo $OUTPUT->heading(get_string('categoryheading', 'report_categoryreport', $heading), 3);

    if ($coursecount === 0) {
        echo $OUTPUT->notification(get_string('nocourses', 'report_categoryreport'), 'info');
        continue;
    }

    // Student count per course in this category, via role_assignments at course
    // context level (Student archetype). Uses a single grouped SQL query.
    $studentcounts = $DB->get_records_sql(
        "SELECT c.id, COUNT(DISTINCT ra.userid) AS students
           FROM {course} c
           JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :ctxlevel
           LEFT JOIN {role_assignments} ra ON ra.contextid = ctx.id AND ra.roleid = :roleid
          WHERE c.category = :catid
          GROUP BY c.id",
        [
            'ctxlevel' => CONTEXT_COURSE,
            'roleid'   => $studentrole->id,
            'catid'    => $category->id,
        ]
    );

    // Tally how many courses share each student-count value, so we can
    // answer "courses in this category with the same number of students".
    $tally = [];
    foreach ($studentcounts as $row) {
        $tally[(int)$row->students] = ($tally[(int)$row->students] ?? 0) + 1;
    }

    $table = new html_table();
    $table->head = [
        get_string('col_name',     'report_categoryreport'),
        get_string('col_desc',     'report_categoryreport'),
        get_string('col_start',    'report_categoryreport'),
        get_string('col_end',      'report_categoryreport'),
        get_string('col_duration', 'report_categoryreport'),
        get_string('col_students', 'report_categoryreport'),
        get_string('col_samecount','report_categoryreport'),
    ];
    $table->attributes = ['class' => 'generaltable report-categoryreport'];
    $table->data = [];

    foreach ($courses as $course) {
        $ctx = context_course::instance($course->id);

        $description = !empty($course->summary)
            ? format_text($course->summary, $course->summaryformat, ['context' => $ctx])
            : html_writer::tag('em', get_string('nodesc', 'report_categoryreport'));

        $start = !empty($course->startdate) ? userdate($course->startdate, $datefmt) : '—';
        $end   = !empty($course->enddate)   ? userdate($course->enddate,   $datefmt) : '—';

        if (!empty($course->startdate) && !empty($course->enddate) && $course->enddate > $course->startdate) {
            $duration = format_time($course->enddate - $course->startdate);
        } else {
            $duration = '—';
        }

        $students = isset($studentcounts[$course->id]) ? (int)$studentcounts[$course->id]->students : 0;
        $same = $tally[$students] ?? 0;

        $courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);
        $namelink = html_writer::link(
            $courseurl,
            format_string($course->fullname, true, ['context' => $ctx])
        );

        $table->data[] = [
            $namelink,
            $description,
            $start,
            $end,
            $duration,
            $students,
            $same,
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();
