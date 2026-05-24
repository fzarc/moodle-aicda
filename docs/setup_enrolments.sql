-- Configures Assignment 4 AICDA enrolment methods directly via SQL.
-- - Cats 5,6,7: add Cohort sync enrol instance (Student role) linking to the right cohort.
-- - Cats 8,9:   enable existing Self enrol instances.
-- - Replicates cohort_sync: populates user_enrolments + role_assignments.
-- Idempotent: re-running produces no duplicates.

START TRANSACTION;

SET @now := UNIX_TIMESTAMP();

-- 1) Cohort enrol instances (one per course, linking to matching cohort).
INSERT INTO mdl_enrol
    (enrol, status, courseid, sortorder, name, enrolperiod, enrolstartdate, enrolenddate,
     expirynotify, expirythreshold, notifyall, roleid, customint1, customint2,
     timecreated, timemodified)
SELECT 'cohort', 0, c.id, 3, NULL, 0, 0, 0,
       0, 86400, 0, 5, co.id, 0,
       @now, @now
FROM mdl_course c
JOIN mdl_cohort co ON (
    (c.category = 5 AND co.idnumber = 'ai_students') OR
    (c.category = 6 AND co.idnumber = 'cyber_students') OR
    (c.category = 7 AND co.idnumber = 'forensics_students')
)
WHERE NOT EXISTS (
    SELECT 1 FROM mdl_enrol e
    WHERE e.courseid = c.id AND e.enrol = 'cohort' AND e.customint1 = co.id
);

-- 2) Enable self enrol for cats 8,9.
UPDATE mdl_enrol e
JOIN mdl_course c ON c.id = e.courseid
SET e.status = 0, e.timemodified = @now
WHERE e.enrol = 'self' AND c.category IN (8, 9) AND e.status = 1;

-- 3) Populate user_enrolments (cohort sync replication).
INSERT INTO mdl_user_enrolments
    (status, enrolid, userid, timestart, timeend, modifierid, timecreated, timemodified)
SELECT 0, e.id, cm.userid, 0, 2147483647, 2, @now, @now
FROM mdl_enrol e
JOIN mdl_cohort_members cm ON cm.cohortid = e.customint1
JOIN mdl_course c ON c.id = e.courseid
WHERE e.enrol = 'cohort' AND c.category IN (5, 6, 7)
  AND NOT EXISTS (
      SELECT 1 FROM mdl_user_enrolments ue
      WHERE ue.enrolid = e.id AND ue.userid = cm.userid
  );

-- 4) Insert role_assignments (Student role, component=enrol_cohort).
INSERT INTO mdl_role_assignments
    (roleid, contextid, userid, timemodified, modifierid, component, itemid, sortorder)
SELECT 5, ctx.id, cm.userid, @now, 2, 'enrol_cohort', e.id, 0
FROM mdl_enrol e
JOIN mdl_cohort_members cm ON cm.cohortid = e.customint1
JOIN mdl_course c ON c.id = e.courseid
JOIN mdl_context ctx ON ctx.instanceid = c.id AND ctx.contextlevel = 50
WHERE e.enrol = 'cohort' AND c.category IN (5, 6, 7)
  AND NOT EXISTS (
      SELECT 1 FROM mdl_role_assignments ra
      WHERE ra.roleid = 5 AND ra.contextid = ctx.id AND ra.userid = cm.userid
        AND ra.component = 'enrol_cohort' AND ra.itemid = e.id
  );

COMMIT;

-- Verification.
SELECT '=== cohort enrol instances added ===' AS '';
SELECT c.category, c.shortname, e.id AS enrolid, co.idnumber AS cohort
FROM mdl_enrol e
JOIN mdl_course c ON c.id = e.courseid
JOIN mdl_cohort co ON co.id = e.customint1
WHERE e.enrol = 'cohort' AND c.category IN (5,6,7)
ORDER BY c.category, c.shortname;

SELECT '=== self enrol status for cats 8,9 ===' AS '';
SELECT c.category, c.shortname, e.status
FROM mdl_enrol e
JOIN mdl_course c ON c.id = e.courseid
WHERE e.enrol = 'self' AND c.category IN (8,9)
ORDER BY c.category, c.shortname;

SELECT '=== enrolments per course (cats 5,6,7) ===' AS '';
SELECT c.shortname, COUNT(ue.id) AS enrolled
FROM mdl_course c
JOIN mdl_enrol e ON e.courseid = c.id AND e.enrol = 'cohort'
JOIN mdl_user_enrolments ue ON ue.enrolid = e.id
WHERE c.category IN (5,6,7)
GROUP BY c.id
ORDER BY c.category, c.shortname;

SELECT '=== role assignments per course (cats 5,6,7) ===' AS '';
SELECT c.shortname, COUNT(ra.id) AS students
FROM mdl_course c
JOIN mdl_context ctx ON ctx.instanceid = c.id AND ctx.contextlevel = 50
JOIN mdl_role_assignments ra ON ra.contextid = ctx.id AND ra.roleid = 5 AND ra.component = 'enrol_cohort'
WHERE c.category IN (5,6,7)
GROUP BY c.id
ORDER BY c.category, c.shortname;
