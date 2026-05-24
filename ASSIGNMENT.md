# AI & Cybersecurity Defense Academy

**Assignment 4** — *Development of Collaborative Environments*
Grado en Ingeniería Informática y Tecnologías Virtuales · Curso 2025/2026

Plataforma de aprendizaje virtual desarrollada sobre Moodle 4.4.12 para
una academia ficticia centrada en **Inteligencia Artificial** y
**Ciberseguridad defensiva**. Incluye 5 categorías, 17 cursos, 6 cohorts
y 108 usuarios con sus matrículas configuradas.

> **Nota:** este `ASSIGNMENT.md` es el documento de entrega.
> `README.md` se mantiene intacto por ser parte del core de Moodle.

---

## Contenido del repositorio

| Ruta | Descripción |
|---|---|
| `blocks/cohort_welcome/` | **Ejercicio 2** — Block: saludo personalizado por cohort (EN+IT) |
| `blocks/last_login/` | **Ejercicio 3** — Block: últimos 10 usuarios en iniciar sesión (EN+IT) |
| `report/usercohorts/` | **Ejercicio 4** — Report: usuarios agrupados por cohort (EN+IT) |
| `report/categoryreport/` | **Ejercicio 5** — Report: categorías, cursos y estudiantes (EN+IT) |
| `theme/aicda/` | **Ejercicio 6** — Theme custom clonado de Moove |
| `docs/moodle_dump.sql` | Dump completo de la BD (usuarios, cursos, matrículas, etc.) |
| `docs/users_aicda.csv` | CSV usado para `Users → Upload users` (108 usuarios) |
| `docs/setup_enrolments.sql` | Script SQL para configurar Cohort sync / Self enrolment |
| `docs/setup_enrolments.php` | Versión PHP CLI del script de matrículas |
| `config-sample.php` | Plantilla de configuración (el `config.php` real no se commitea) |
| `CLAUDE.md` | Contexto y reglas del proyecto |

---

## Entorno de desarrollo

| Componente | Versión |
|---|---|
| Moodle | 4.4.12 |
| PHP | 8.1 – 8.3 |
| Base de datos | MariaDB ≥ 10.6.7 |
| Servidor web | Apache 2 |
| Prefijo de tablas | `mdl_` |

---

## Cómo reproducir la instalación

### 1. Clonar el repositorio en el DocumentRoot

```bash
cd /var/www/html
git clone <URL_DEL_REPO> moodle
cd moodle
sudo chown -R www-data:www-data .
sudo chmod -R 775 .
```

### 2. Crear el directorio de datos

```bash
sudo mkdir -p /var/www/moodledata
sudo chown -R www-data:www-data /var/www/moodledata
sudo chmod -R 777 /var/www/moodledata
```

### 3. Crear la base de datos e importar el dump

```bash
mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS moodle DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -uroot -proot moodle < docs/moodle_dump.sql
```

### 4. Configurar Moodle

```bash
cp config-sample.php config.php
# Editar wwwroot si tu URL no es http://localhost/moodle
sudo chown www-data:www-data config.php
sudo chmod 640 config.php
```

### 5. Acceder

Abre `http://localhost/moodle` en el navegador.

**Credenciales por defecto:**
- Administrador: `admin` / *(contraseña configurada durante la
  instalación original)*
- Estudiantes / staff: ver `docs/users_aicda.csv`

---

## Estructura del assignment (6 ejercicios)

### Ejercicio 1 — Administración y generación de contenido (2 pts)
Configuración del sitio, creación de las 5 categorías, 17 cursos,
6 cohorts, 108 usuarios y reglas de matrícula (Cohort sync para
AI/Cyber/Forensics, Self enrolment para Skills/Certifications).

### Ejercicio 2 — Block `cohort_welcome` (1.5 pts)
Saludo al usuario logado con su nombre, mensaje personalizado según su
cohort y total de miembros del cohort. Idiomas: EN + IT.

### Ejercicio 3 — Block `last_login` (1.5 pts)
Listado de los 10 usuarios cuyo último login es más antiguo, con tiempo
transcurrido y enlace al perfil. Idiomas: EN + IT.

### Ejercicio 4 — Report `usercohorts` (1.5 pts)
Tabla por cohort con foto, nombre, email y número de cursos
matriculados de cada usuario. Idiomas: EN + IT.

### Ejercicio 5 — Report `categoryreport` (1.5 pts)
Tabla por categoría con sus cursos: nombre, descripción, fechas,
duración, número de estudiantes y cuántos cursos de la categoría
comparten ese mismo número de estudiantes. Idiomas: EN + IT.

### Ejercicio 6 — Theme `aicda` (2 pts)
Theme clonado de Moove con identidad visual IA/ciberseguridad: logo,
sliders, colores, CSS custom, HTML blocks en la portada.

---

## Categorías y cursos

- **Artificial Intelligence Fundamentals** — Introduction to AI ·
  Prompt Engineering Basics · Machine Learning Fundamentals · AI Tools
  for Productivity
- **Cybersecurity Defense** — Ethical Hacking Fundamentals · Network
  Security Basics · Linux for Cybersecurity · Secure Web Applications
- **Digital Forensics** — Introduction to Digital Forensics · Incident
  Response Basics · Malware Analysis Fundamentals
- **Professional Skills** — Team Communication · Time Management for IT
  Projects · Remote Collaboration Tools
- **Intensive Certifications** — AI Security Certification · Ethical
  Hacking Certification · Security Management Certification

## Cohorts (6)

Academy Directors · Administrative Staff · Instructors · Artificial
Intelligence Students · Cybersecurity Students · Digital Forensics
Students

---

## Notas para el evaluador

- Todos los plugins custom siguen la convención `component =
  carpeta` y exponen sus cadenas vía `get_string()` en `lang/en` y
  `lang/it`.
- El core de Moodle no ha sido modificado: toda la lógica del
  assignment vive en los plugins listados arriba.
- El `config.php` real no está en el repositorio por contener
  credenciales locales; usa `config-sample.php` como punto de partida.

---

## Licencia

Moodle se distribuye bajo GPLv3. El código custom de los plugins de
este repositorio se publica bajo la misma licencia.
