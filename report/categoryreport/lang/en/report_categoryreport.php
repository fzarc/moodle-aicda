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
 * English strings for report_categoryreport.
 *
 * @package    report_categoryreport
 * @copyright  2026 Fernando Capla
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Categories and courses';
$string['categoryreport:view'] = 'View categories and courses report';

$string['categoryheading'] = '{$a->name} — {$a->count} courses';
$string['nocategories'] = 'No course categories have been created yet.';
$string['nocourses'] = 'This category has no courses yet.';
$string['nodesc'] = 'No description available';

$string['col_name'] = 'Course';
$string['col_desc'] = 'Description';
$string['col_start'] = 'Start date';
$string['col_end'] = 'End date';
$string['col_duration'] = 'Duration';
$string['col_students'] = 'Students';
$string['col_samecount'] = 'Courses with same student count';

$string['privacy:metadata'] = 'The Categories and courses report only displays existing course and enrolment data; it does not store any personal data itself.';
