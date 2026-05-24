# CLAUDE.md — Assignment 4: AI & Cybersecurity Defense Academy

> Archivo de contexto para Claude Code. Colocar en la raíz del directorio
> Moodle: `/var/www/html/moodle/CLAUDE.md`

## Contexto del proyecto

Desarrollo de un plataforma de aprendizaje virtual en Moodle para una
academia ficticia llamada **AI & Cybersecurity Defense Academy**. Es la
entrega **Assignment 4** de la asignatura *Development of Collaborative
Environments* (Grado en Ingeniería Informática y Tecnologías Virtuales,
curso 2025/2026).

La entrega consiste en 6 ejercicios: configuración administrativa, 2 block
plugins, 2 report plugins y 1 theme clonado. Total: 10 puntos.

## Entorno

| Elemento | Valor |
|---|---|
| Versión Moodle | 4.4.12 |
| Directorio raíz | `/var/www/html/moodle` |
| Directorio de datos | `/var/www/moodledata` |
| Servidor web | Apache2 |
| PHP | 8.1.0 – 8.3.x |
| Base de datos | MariaDB (>10.6.7) |
| Gestión de BD | phpMyAdmin / Moodle Adminer (`local_adminer`) |
| Prefijo de tablas | `mdl_` |

## Reglas y convenciones (OBLIGATORIO)

- **Prohibido Lorem Ipsum.** Todo texto, imagen y descripción debe estar
  relacionado con el tema de IA y ciberseguridad.
- Todos los plugins (blocks y reports) deben soportar **inglés (en) e
  italiano (it)**. Toda cadena visible se carga con `get_string()`.
- No modificar el core de Moodle ni librerías externas. Extender siempre
  mediante plugins.
- El número de versión de cada plugin sigue el formato `YYYYMMDD00`
  (ej. `2026052200`). Hay que incrementarlo tras cada cambio para que
  Moodle detecte la actualización.
- El campo `requires` de `version.php` debe apuntar a la versión de
  Moodle instalada.
- El valor `component` debe coincidir exactamente con el nombre de la
  carpeta del plugin.
- Tras modificar archivos de idioma o `version.php`, recargar Moodle y
  ejecutar *Upgrade Moodle database*.
- Durante el desarrollo de themes, activar **Theme designer mode** para
  desactivar la caché.

## Estructura de datos del assignment

### Categorías de cursos (5) y sus cursos (17 total)

- **Artificial Intelligence Fundamentals**: Introduction to Artificial
  Intelligence · Prompt Engineering Basics · Machine Learning
  Fundamentals · AI Tools for Productivity
- **Cybersecurity Defense**: Ethical Hacking Fundamentals · Network
  Security Basics · Linux for Cybersecurity · Secure Web Applications
- **Digital Forensics**: Introduction to Digital Forensics · Incident
  Response Basics · Malware Analysis Fundamentals
- **Professional Skills**: Team Communication · Time Management for IT
  Projects · Remote Collaboration Tools
- **Intensive Certifications**: AI Security Certification · Ethical
  Hacking Certification · Security Management Certification

### Cohorts (6)

Academy Directors · Administrative Staff · Instructors · Artificial
Intelligence Students · Cybersecurity Students · Digital Forensics
Students

### Usuarios (108 total)

100 estudiantes + 1 academy director + 2 administrative staff + 5
instructores. Todos los usuarios deben pertenecer a un cohort.

### Reglas de matrícula

- Categorías **AI Fundamentals, Cybersecurity Defense y Digital
  Forensics**: matrícula automática por cohort (*Cohort sync*). Los
  estudiantes de cada cohort se matriculan en los cursos de su categoría
  correspondiente.
- Categorías **Professional Skills e Intensive Certifications**:
  *Self enrolment*.

## Tablas principales de la BD

| Tabla | Uso |
|---|---|
| `mdl_user` | Información de usuarios |
| `mdl_cohort` | Cohorts |
| `mdl_cohort_members` | Relación usuario ↔ cohort |
| `mdl_course` | Cursos |
| `mdl_course_categories` | Categorías de cursos |
| `mdl_user_enrolments` + `mdl_enrol` | Matrículas |
| `mdl_role_assignments` | Asignación de roles |

## API de acceso a base de datos

Usar siempre la variable global `$DB`. Métodos de la Data Manipulation
API (https://docs.moodle.org/dev/Data_manipulation_API):

```php
// Múltiples registros con condición
$DB->get_records_select($table, $where, $params, $sort, $fields, $limitfrom, $limitnum);
// Un único registro
$DB->get_record_select($table, $where, $params);
// SQL directo (para JOINs complejos)
$DB->get_records_sql($sql, $params);
```

---

## Ejercicio 1 — Administración y generación de contenido (2 pts)

1. **Configuración del sitio**: `Site administration → General → Site
   home settings`. Full site name: `AI & Cybersecurity Defense
   Academy`; short name apropiado (ej. `AICDA`); descripción breve
   temática.
2. **Categorías**: `Courses → Manage courses and categories → Create new
   category`. Crear las 5 categorías.
3. **Cursos**: crear los 17 cursos dentro de sus categorías. Configurar
   General, Description, Course format. Descripciones reales.
4. **Cohorts**: `Users → Cohorts`. Crear los 6 cohorts.
5. **Usuarios**: preparar CSV (`username, firstname, lastname, email,
   password, cohort1`) y subir con `Users → Upload users`. La columna
   `cohort1` asigna el cohort automáticamente.
6. **Matrículas**: en los cursos de AI/Cybersecurity/Digital Forensics
   añadir método *Cohort sync* (`Participants → Enrolment methods`). En
   Professional Skills e Intensive Certifications activar *Self
   enrolment*.

## Ejercicio 2 — Block: saludo personalizado por cohort (1.5 pts)

Block plugin que muestra un saludo de bienvenida con el nombre completo
del usuario logado, un mensaje personalizado según su cohort, y el número
de usuarios que comparten ese cohort. Soporte EN + IT.

1. En `blocks/`, copiar `news_items` y renombrar a `cohort_welcome`.
2. Renombrar y editar: `block_cohort_welcome.php`, `version.php`,
   `db/access.php`, `lang/en/block_cohort_welcome.php`,
   `lang/it/block_cohort_welcome.php`, `classes/privacy/provider.php`.
3. En `get_content()`: declarar `global $USER, $DB;`.
4. Comprobar login con `$USER->firstname`.
5. Obtener cohort del usuario:

```php
$membership = $DB->get_records_select('cohort_members', 'userid = ?', array($USER->id), 'id');
foreach ($membership as $m) {
    $cohort = $DB->get_record_select('cohort', 'id = ?', array($m->cohortid));
    $members = $DB->get_records_select('cohort_members', 'cohortid = ?', array($cohort->id));
    // Construir mensaje personalizado segun $cohort->name y count($members)
}
```

6. Construir el mensaje con `get_string()`.
7. Crear archivos de idioma `lang/en` y `lang/it`.
8. Actualizar `version.php`, recargar Moodle, *Upgrade database*, añadir
   el bloque a la portada.

## Ejercicio 3 — Block: últimos usuarios en iniciar sesión (1.5 pts)

Block plugin que muestra los 10 usuarios que iniciaron sesión hace más
tiempo, con el tiempo transcurrido desde su último login. Clic en un
usuario redirige a su perfil. Soporte EN + IT.

1. Copiar `news_items` y renombrar (ej. `last_login`). Misma secuencia de
   archivos que el Ejercicio 2.
2. En `get_content()`:

```php
$users = $DB->get_records_select(
    'user',
    'lastaccess > 0 AND deleted = 0 AND id != 1',
    array(),
    'lastaccess ASC',
    '*',
    0, 10
);
```

3. Tiempo transcurrido: `time() - $user->lastaccess`, formatear en
   días/horas/minutos.
4. Enlace al perfil: `$CFG->wwwroot . '/user/profile.php?id=' .
   $user->id` envuelto en `<a href="...">`.
5. Cadenas en `lang/en` y `lang/it`.

## Ejercicio 4 — Report: usuarios y sus cohorts (1.5 pts)

Report plugin que muestra, agrupado por cohort: foto, nombre completo,
email y número de cursos en los que el usuario está matriculado. Al
inicio de cada cohort, mostrar nombre del cohort y total de usuarios.
Usar tablas HTML. Soporte EN + IT.

1. Copiar `report/backups` y renombrar a `report/usercohorts`. Reemplazar
   `backups` por `usercohorts` en todos los archivos.
2. Archivos a modificar: `version.php`, `settings.php`, `index.php`,
   `lang/en/report_usercohorts.php`, `classes/privacy/provider.php`.
3. Lógica en `index.php`:

```php
$cohorts = $DB->get_records_select('cohort', '1=1', array(), 'name');
foreach ($cohorts as $cohort) {
    $members = $DB->get_records_select('cohort_members', 'cohortid = ?', array($cohort->id));
    // Cabecera: $cohort->name + count($members)
    foreach ($members as $member) {
        $user = $DB->get_record_select('user', 'id = ?', array($member->userid));
        // Foto: $OUTPUT->user_picture($user)
        // Cursos: query a user_enrolments + enrol filtrando por userid
    }
}
```

4. Foto del usuario: `$OUTPUT->user_picture($user)`.
5. Número de cursos: consultar `user_enrolments` + `enrol` por `userid`.
6. Generar la tabla con el Output API (`html_table`).
7. Crear `lang/en` y `lang/it`; usar `get_string('key',
   'report_usercohorts')`.

## Ejercicio 5 — Report: categorías, cursos y estudiantes (1.5 pts)

Report plugin que muestra, por categoría: número de cursos (como
cabecera) y, por cada curso, nombre, descripción, fecha de inicio, fecha
de fin, duración, número de estudiantes y número de cursos de esa
categoría con el mismo número de estudiantes. Usar tablas HTML. Soporte
EN + IT.

1. Copiar el report plugin (ej. `report/categoryreport`). Misma
   estructura que el Ejercicio 4.
2. Lógica en `index.php`:
   - Iterar `course_categories`; cabecera con nombre + número de cursos.
   - Por cada categoría, iterar sus cursos (tabla `course` filtrada por
     `category`): nombre, descripción, `startdate`, `enddate`, duración
     (`enddate - startdate`), número de estudiantes.
   - Número de estudiantes: `user_enrolments` + `enrol` +
     `role_assignments` filtrando por rol Student. Usar
     `$DB->get_records_sql()` con JOINs.
   - Calcular cuántos cursos de la misma categoría tienen exactamente el
     mismo número de estudiantes que el curso actual.
3. Tabla con Output API (`html_table`).
4. Crear `lang/en` y `lang/it`.

## Ejercicio 6 — Custom theme clonado (2 pts)

Theme clonado del theme preferido (ej. Moove). Configuración backend y
frontend (logo, sliders, colores), CSS personalizado, uso de HTML
blocks, resultado visual adaptado al tema IA/ciberseguridad.

1. Instalar el theme base (ej. Moove) desde `Plugins → Install plugins`.
2. En `theme/`, copiar la carpeta `moove`, renombrar (ej. `aicda`),
   find-and-replace `moove` → `aicda` en todos los archivos.
3. Actualizar comentarios: package, copyright, author, license.
4. Activar el theme en `Appearance → Themes → Theme selector`.
5. Configurar logo, colores, sliders (imágenes IA/ciberseguridad),
   footer desde `Appearance → Themes → [theme]`.
6. Activar Theme designer mode durante el desarrollo.
7. Editar `style/theme.css` y `style/custom.css`. Verificar que
   `config.php` incluye los CSS en el array `sheets`.
8. Añadir HTML blocks (Text block) en la portada con imágenes temáticas.
9. Usar la sección Topic de la portada para contenido HTML embebido.
10. Cambiar el screenshot del theme en la carpeta `pix/`.

---

## Notas (elementos fuera del temario base)

Los siguientes puntos NO aparecen explícitamente en el material del curso
y se han añadido por necesidad del assignment:

- **Idioma italiano**: el temario usa español como segundo idioma de
  ejemplo, pero el assignment exige inglés + italiano. La mecánica es
  idéntica: duplicar `lang/en` a `lang/it` y traducir las cadenas.
- **Paginación de resultados** (Ejercicio 3): el límite de 10 registros
  usa los parámetros `$limitfrom`/`$limitnum` de `get_records_select()`,
  no mostrados explícitamente en las diapositivas pero documentados en la
  Data Manipulation API.
- **`get_records_sql()` con JOINs** (Ejercicio 5): el temario solo
  muestra JOINs manuales anidados; para este ejercicio es más eficiente
  SQL directo, también parte de la Data Manipulation API.

## Documentación de referencia

- Plugin types: https://docs.moodle.org/dev/Plugin_types
- Plugin files: https://docs.moodle.org/dev/Plugin_files
- Data Manipulation API: https://docs.moodle.org/dev/Data_manipulation_API
- Access API: https://docs.moodle.org/dev/Access_API
- Privacy API: https://docs.moodle.org/dev/Privacy_API
