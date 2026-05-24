<?php
// CLI script: configures enrolment methods for Assignment 4 AICDA.
// - Cats 5,6,7 -> add Cohort sync to the matching cohort (Student role).
// - Cats 8,9   -> enable Self enrolment (creates instance if missing).
// - Runs cohort sync to populate user_enrolments + role_assignments.

define('CLI_SCRIPT', true);
require(__DIR__ . '/../config.php');
require_once($CFG->dirroot . '/enrol/cohort/locallib.php');
require_once($CFG->libdir . '/enrollib.php');

global $DB;

$cohortmap = [
    5 => 'ai_students',
    6 => 'cyber_students',
    7 => 'forensics_students',
];
$selfcats = [8, 9];

$studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);

$cohortplugin = enrol_get_plugin('cohort');
foreach ($cohortmap as $catid => $idnumber) {
    $cohort = $DB->get_record('cohort', ['idnumber' => $idnumber], '*', MUST_EXIST);
    $courses = $DB->get_records('course', ['category' => $catid]);
    foreach ($courses as $course) {
        $exists = $DB->record_exists('enrol', [
            'courseid'    => $course->id,
            'enrol'       => 'cohort',
            'customint1'  => $cohort->id,
        ]);
        if ($exists) {
            mtrace("[SKIP] cohort sync {$course->shortname} <- {$idnumber}");
            continue;
        }
        $cohortplugin->add_instance($course, [
            'name'        => '',
            'status'      => ENROL_INSTANCE_ENABLED,
            'customint1'  => $cohort->id,
            'customint2'  => 0,
            'roleid'      => $studentrole->id,
        ]);
        mtrace("[ADD]  cohort sync {$course->shortname} <- {$idnumber}");
    }
}

$selfplugin = enrol_get_plugin('self');
foreach ($selfcats as $catid) {
    $courses = $DB->get_records('course', ['category' => $catid]);
    foreach ($courses as $course) {
        $instances = $DB->get_records('enrol', ['courseid' => $course->id, 'enrol' => 'self']);
        if (empty($instances)) {
            $selfplugin->add_instance($course, [
                'name'        => '',
                'status'      => ENROL_INSTANCE_ENABLED,
                'roleid'      => $studentrole->id,
                'customint6'  => 1,
            ]);
            mtrace("[ADD]  self enrol {$course->shortname}");
        } else {
            foreach ($instances as $inst) {
                if ((int)$inst->status !== ENROL_INSTANCE_ENABLED) {
                    $selfplugin->update_status($inst, ENROL_INSTANCE_ENABLED);
                    mtrace("[ON]   self enrol {$course->shortname}");
                } else {
                    mtrace("[SKIP] self enrol {$course->shortname} already on");
                }
            }
        }
    }
}

mtrace("--- Running cohort sync to populate enrolments ---");
$trace = new text_progress_trace();
enrol_cohort_sync($trace);
$trace->finished();

mtrace("Done.");
