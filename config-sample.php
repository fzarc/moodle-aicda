<?php
// =============================================================
// config-sample.php — AI & Cybersecurity Defense Academy
// =============================================================
// Plantilla de configuración para reproducir la instalación.
//
// USO:
//   1. cp config-sample.php config.php
//   2. Ajustar wwwroot y dataroot a tu entorno
//   3. Importar el dump:  mysql -uroot -proot moodle < docs/moodle_dump.sql
//   4. chown www-data:www-data config.php && chmod 640 config.php
//
// IMPORTANTE: este archivo es solo una plantilla. El config.php real
// queda fuera del repositorio por contener credenciales.

unset($CFG);
global $CFG;
$CFG = new stdClass();

// -------- Base de datos --------
$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'root';
$CFG->dbpass    = 'root';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array(
    'dbpersist' => 0,
    'dbport'    => '',
    'dbsocket'  => '',
    'dbcollation' => 'utf8mb4_unicode_ci',
);

// -------- URL y rutas --------
$CFG->wwwroot   = 'http://localhost/moodle';
$CFG->dataroot  = '/var/www/moodledata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');
