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
 * English strings for report_usercohorts.
 *
 * @package    report_usercohorts
 * @copyright  2026 Fernando Capla
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Users by cohort';
$string['usercohorts:view'] = 'View users-by-cohort report';

$string['cohortheading'] = '{$a->name} — {$a->count} users';
$string['nocohorts'] = 'No cohorts have been created yet.';
$string['nomembers'] = 'This cohort has no members yet.';

$string['col_photo'] = 'Photo';
$string['col_name'] = 'Full name';
$string['col_email'] = 'Email';
$string['col_courses'] = 'Enrolled courses';

$string['privacy:metadata'] = 'The Users by cohort report only displays existing user, cohort and enrolment data; it does not store any personal data itself.';
