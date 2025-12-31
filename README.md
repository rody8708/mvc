Comprehensive Guide to the Custom MVC Framework
Introduction

This repository hosts a PHP web application that implements a custom Model‑View‑Controller (MVC) framework. Its primary purpose is to provide a functional base for creating and managing web modules with user control, authentication, role management, notifications and automatic CRUD generation. The system is organised into clearly separated folders and uses a modular architecture to make it easy to extend and maintain.

Project structure

The application lives in the mvc folder and is organised as follows:

config/ – contains global configuration files such as config.php. This script starts the session, defines the time zone, controls inactivity expiry and loads error logging and the autoload mechanism
github.com
github.com
.

routes/ – stores the routing files. The main file web.php defines all routes for the application and dynamically includes module routes
github.com
.

app/ – holds the application source code:

controllers/ – controllers that process HTTP requests and orchestrate business logic.

models/ – models that handle persistence using SQLite and perform other data operations.

views/ – presentation templates that show information to the user.

core/ – base classes and utilities for the framework (e.g. Controller, Model, Router, Functions, Logger, User, Mailer).

libs/ – third‑party libraries such as PHPMailer for sending e‑mail.

logs/ – log files (app.log) where activity and error messages are stored.

db/ – SQLite databases (db.sqlite for the main app and invoices_license.sqlite for licences) plus classes to connect to them.

public/ – web‑accessible directory containing index.php, static assets (assets/css, assets/js, assets/images) and download links for the mobile/desktop apps.

Configuration and autoload

The config/config.php file runs at the beginning of each request. It performs several tasks:

Displays errors in development and sets the time zone
github.com
.

Starts the session and controls maximum inactivity (15 minutes by default). If the user is inactive for too long, the session is destroyed and marked as expired
github.com
.

Defines constants such as BASE_PATH, BASE_URL, DB_PATH and NOTIFICATION_MODE
github.com
.

Initialises the logger and registers handlers for fatal errors to capture exceptions into the log
github.com
.

Loads utilities (Functions.php) and the class autoload mechanism
github.com
.

The autoloader (app/core/autoload.php) converts namespaces under App\ into file paths. It lower‑cases intermediate directories (except for the filename) and loads the corresponding class
github.com
. If a class is not found, it records a message in the log and stops execution
github.com
.

Core
Controller class

The base class App\Core\Controller initialises three properties: an instance of Functions, an instance of NotificationModel and an instance of User
github.com
. It provides several helper methods:

loadModel($model) – loads and returns an instance of a model; it checks that the file exists and aborts if it does not
github.com
.

loadView($view, $data = []) – loads the given view and uses a general layout (layouts/main.php); it extracts variables from the $data array for use inside the view
github.com
.

requireLogin() / requireAdmin() – verify that the user is authenticated or has admin role; otherwise they redirect to the login page and log the event
github.com
github.com
.

setFlashAlert($message, $type) – stores flash messages in the session to be shown on the next request
github.com
.

Model class

App\Core\Model encapsulates the SQLite database connection. It implements a singleton pattern to share the connection (self::$db) and sets the error mode to exceptions
github.com
. The getDb() method forces the creation of the connection if none exists
github.com
.

Functions class

This file groups many utility functions. Some of the most important include:

File operations – copy, move and delete files and directories while logging the results
github.com
. It also includes uploadAvatar() to upload avatar images with size validation (up to 3 MB) and image type validation (JPG/PNG)
github.com
.

Client information – obtain the client’s IP address, browser and operating system
github.com
, as well as return a complete array with these details and the current user
github.com
.

Session and security – sanitise inputs to prevent XSS
github.com
, generate random tokens
github.com
, check if the user is authenticated and handle redirection
github.com
, and generate/validate CSRF tokens
github.com
.

Module handling – add or delete modules in modules.json while avoiding duplicates
github.com
github.com
.

User data in JSON – save, update, retrieve and delete JSON files for user preferences or data, and update individual fields
github.com
github.com
.

Date formatting – convert UTC dates to the local time zone (America/New_York)
github.com
.

Logger class

App\Core\Logger implements a logging system that writes to a file (app/logs/app.log) and to the database. Its init() method creates the log file and directory if they do not exist
github.com
. Each time a message is recorded, the logger gathers client data (IP, browser, operating system, user) using Functions::getClientDetails() and formats the entry before writing it to the file and to the logs table
github.com
github.com
. It provides convenient static methods info(), warning() and error() for different severity levels
github.com
.

Router class

App\Core\Router manages all application routes. It allows registering routes with an HTTP method, a path and an action (controller@method). The dispatch() method normalises the URL, searches for a match in the registered routes and, if found, creates the controller instance and executes the specified method
github.com
. It also includes special logic for API routes (/api_invoice) and handles 404 errors when no route is found
github.com
.

User class

App\Core\User centralises user role management. It defines a constant ROLES with possible roles (user, admin, editor, supervisor, moderator) and offers static methods to query the current role’s ID and name, as well as functions to check whether the user is an administrator, editor, supervisor, moderator or normal user
github.com
.

Mailer class

App\Core\Mailer wraps the sending of e‑mail using PHPMailer. It retrieves configuration from config/mail.php and provides static methods to send:

Activation emails (sendActivationEmail)
github.com
.

Password reset links (sendPasswordResetEmail)
github.com
.

Confirmation emails after changing a password (sendPasswordChangedEmail)
github.com
.

Models
UserModel

This model manages the users table and related tables. Among its methods are:

Create users with activation tokens and validate duplicates
github.com
.

Activate accounts using a token
github.com
.

Check whether an email or username already exists
github.com
.

Retrieve users by e‑mail or ID
github.com
.

Obtain lists of all users with their roles and account statuses
github.com
 and fetch all roles
github.com
.

Update user roles, role names and delete users
github.com
.

Create users from the admin panel and update their data, including optional password changes
github.com
github.com
.

Manage password‑reset tokens: save, obtain and delete tokens
github.com
 and update the password
github.com
.

Manage user preferences and additional data: get preferences (getByUserId), enable/disable dark mode (setDarkMode), change language (setLanguage), create default preferences (createUserPreferences)
github.com
, save avatar files (saveAvatarFile, updateAvatarFileName)
github.com
 and delete avatar records
github.com
.

Retrieve user log records, manage account deletion requests and reactivate inactive accounts
github.com
.

Get all account statuses (account_statuses)
github.com
.

LogModel

This model provides methods to retrieve and count logs. It can obtain a limited number of logs with pagination and filter them by user, level, IP address, action and date ranges
github.com
. It also calculates the total number of filtered results for pagination
github.com
.

NotificationModel

This model manages notifications for users:

Retrieve a user’s unread notifications sorted by date
github.com
.

Mark a notification as read or mark all as read
github.com
.

Create new notifications with different severity levels
github.com
.

ToolModel

This model allows automatic creation and deletion of CRUD modules:

generateModule() – accepts the module name and a list of fields. It creates the controller, model, view, CSS file, JS file, an image directory and the corresponding route
github.com
. It also writes basic templates for each component (controller
github.com
, model
github.com
, view
github.com
 and route file
github.com
).

deleteModule() – removes all files created for a specific module and deletes its image directory
github.com
.

Controllers
HomeController

Displays the main page (index()), reading the application version from configuration files (app_version.php) and setting the download URLs for the app
github.com
. It also has methods to display contact, privacy and help pages
github.com
.

PruebaController

Simple example module: loads the modules/prueba view
github.com
.

AuthController

Handles user registration and authentication:

registerForm() and register() – show the registration form and process account creation. They validate required fields, e‑mail format, password length and duplicates. If registration succeeds, they send an activation e‑mail using Mailer
github.com
.

activate() – activates the account using a token received in the URL and then redirects to the login page
github.com
.

loginForm() and login() – show the login form and process authentication. They implement protection against multiple failed attempts by temporarily blocking the IP address and check whether the account is active
github.com
.

logout() – destroys the session and redirects to the homepage
github.com
.

forgotPasswordForm() and sendResetLink() – generate a recovery token and send it by e‑mail
github.com
.

resetPasswordForm() and resetPassword() – allow the user to enter a new password via the token. They verify its validity and update the password in the database
github.com
.

LogsController

Manages the application’s log viewer:

index() – requires admin role, shows the logs view with pagination. It fetches logs from LogModel according to the requested page and calculates the total number of pages
github.com
.

fetchPage() – responds to AJAX requests to load filtered logs and paginate. It allows filtering by user, level, IP address, action and dates, and returns the HTML of the table and the paginator in JSON format
github.com
.

AdminController

Allows user management from the admin panel:

manageUsers() – shows the list of users along with their roles and account statuses
github.com
.

changeRole() – updates a user’s role. It verifies the CSRF token and logs the action
github.com
.

deleteUser() – deletes a specific user and removes their preferences JSON
github.com
.

createUser() – creates a user from the admin panel. If the user is created as inactive, an activation token is generated and sent
github.com
.

updateUser() – updates an existing user’s data, including password and role
github.com
.

ProfileController

Manages actions related to the user’s profile:

index() – shows user information and their latest logs
github.com
.

updateProfile() – updates the name and e‑mail of the authenticated user and refreshes the session
github.com
.

changePassword() – allows the user to change the current password by checking the old password and validating the new one
github.com
.

toggleDarkMode() – activates or deactivates dark mode for the user and saves it in the user_preferences table and the JSON
github.com
.

uploadAvatar() – uploads an avatar safely, saves its metadata to the database, renames the file using the database ID and updates the user’s JSON
github.com
.

changeLanguage() – updates the user’s preferred language between Spanish and English
github.com
.

deleteAccount() – marks the account as deleted (status 3), records the deletion request and logs the user out
github.com
.

NotificationController

Provides endpoints for handling notifications:

fetch() – returns the current user’s unread notifications in JSON format
github.com
.

markAsRead() – marks a specific notification as read
github.com
.

markAllAsRead() – marks all of the user’s notifications as read
github.com
.

ToolController

Orchestrates the generation and deletion of CRUD modules:

crudGeneratorView() – shows the CRUD generation view (accessible only to administrators)
github.com
.

generateCrud() – processes the generation form. It checks whether the module already exists, generates the files through ToolModel, updates modules.json and reports success or error
github.com
.

deleteModule() – deletes the files of a module and removes it from modules.json
github.com
.

getModules() – returns the list of registered modules from modules.json
github.com
.

Routes

The routes are defined in routes/web.php. The main ones include:

Method & path	Action	Description
GET /	HomeController@index	Shows the home page
github.com
.
GET /logs, POST /logs/fetch	LogsController@index / LogsController@fetchPage	Displays and filters logs
github.com
.
GET /auth/register, POST /auth/register	AuthController@registerForm / AuthController@register	Registration form and processing
github.com
.
GET /activar	AuthController@activate	Activates the account via token
github.com
.
GET /auth/login, POST /auth/login, GET /auth/logout	AuthController@loginForm / AuthController@login / AuthController@logout	Login and logout
github.com
.
GET /admin/users, POST /admin/change-role, POST /admin/delete-user, POST /admin/create-user, POST /admin/update-user	AdminController@manageUsers / changeRole / deleteUser / createUser / updateUser	User administration
github.com
.
Password reset (GET /password/request, POST /password/request, GET /password/reset, POST /password/reset)	Handled by AuthController	Sends reset link and processes reset
github.com
.
GET /profile, POST /profile/update, POST /profile/change-password, POST /profile/toggle-dark-mode, POST /profile/upload-avatar, POST /profile/change-language, POST /profile/delete-account	ProfileController@index / updateProfile / changePassword / toggleDarkMode / uploadAvatar / changeLanguage / deleteAccount	Profile management
github.com
.
GET /notifications/fetch, POST /notifications/mark-as-read, POST /notifications/mark-all-read	NotificationController@fetch / markAsRead / markAllAsRead	Notification handling
github.com
.
GET /layouts/contact, GET /layouts/privacy, GET /layouts/help	HomeController@contact / privacy / help	Contact, privacy and help pages
github.com
.

At the end of the route definitions, the router automatically includes any .php file in routes/modules to incorporate routes for dynamically generated modules and then dispatches the request with the current URL
github.com
github.com
.

Conclusion

The custom MVC framework in this repository is robust and modular. Its clear separation of configuration, routing, core classes, models, controllers and views makes it easy to navigate and extend. By leveraging reusable utility functions, a flexible routing system and tools for module generation and user management, the framework provides a solid foundation for building web applications. Further improvements—such as stricter input validation, improved error handling and additional documentation—can enhance its robustness and security, but the current architecture already demonstrates good separation of concerns and thoughtful design.

############################### SPANISH  ######################################################

Guía completa de uso del framework MVC personalizado
Introducción

Este repositorio contiene una aplicación web escrita en PHP que implementa un framework MVC (Modelo‑Vista‑Controlador) propio. Su objetivo es proporcionar una base funcional para crear y administrar módulos web con control de usuarios, autenticación, gestión de roles, notificaciones y generación automática de módulos. El sistema está organizado en carpetas claramente separadas y utiliza una arquitectura modular que facilita su expansión y mantenimiento.

Estructura del proyecto

La aplicación se aloja en la carpeta mvc y se organiza de la siguiente manera:

config/: contiene archivos de configuración global como config.php. Este script inicia la sesión, define la zona horaria, controla la caducidad por inactividad y carga el registro de errores y el autoload
github.com
github.com
.

routes/: alberga los archivos de rutas. web.php es el principal y define las rutas de la aplicación, además de incluir rutas de módulos generados dinámicamente
github.com
.

app/: código fuente de la aplicación.

controllers/: controladores que procesan las peticiones y orquestan la lógica de negocio.

models/: modelos que gestionan la persistencia en la base de datos SQLite y otras operaciones de datos.

views/: plantillas de presentación (Vistas) que muestran la información al usuario.

core/: clases base y utilidades del framework (por ejemplo, Controller, Model, Router, Functions, Logger, User, Mailer).

libs/: librerías de terceros; incluye PHPMailer para el envío de correos.

logs/: archivos de registro (app.log) donde se almacenan mensajes de actividad y errores.

db/: bases de datos SQLite (db.sqlite para la aplicación principal y invoices_license.sqlite para licencias) y clases de conexión.

public/: directorio accesible públicamente. Contiene index.php, archivos estáticos (assets/css, assets/js, assets/images) y puntos de descarga para las aplicaciones móviles/desktop.

Configuración y autoload

El archivo config/config.php se ejecuta al inicio y realiza varias tareas:

Muestra los errores en entorno de desarrollo y establece la zona horaria
github.com
.

Inicia la sesión y controla la inactividad máxima (15 minutos por defecto). Si el usuario está inactivo se destruye la sesión y se marca como expirada
github.com
.

Define constantes como BASE_PATH, BASE_URL, DB_PATH y NOTIFICATION_MODE
github.com
.

Inicializa el logger y registra manejadores de errores fatales para capturar excepciones en el log
github.com
.

Carga utilidades (Functions.php) y el autoload de clases
github.com
.

El autoload (app/core/autoload.php) convierte el namespace App\ en rutas de archivos. Convierte las carpetas intermedias a minúsculas (excepto el nombre del archivo) y carga la clase correspondiente
github.com
. Si no encuentra la clase, registra un mensaje en el log y detiene la ejecución
github.com
.

Núcleo
Clase Controller

La clase base App\Core\Controller inicializa tres propiedades: una instancia de Functions, una instancia de NotificationModel y una instancia de User
github.com
. Proporciona métodos útiles:

loadModel($model): carga y devuelve una instancia de un modelo (verifica que el archivo exista y termina la ejecución si no existe)
github.com
.

loadView($view, $data = []): carga la vista indicada y utiliza un diseño general (layouts/main.php). Extrae variables del arreglo $data para usarlas en la vista
github.com
.

requireLogin() / requireAdmin(): comprueban si el usuario está autenticado o tiene rol de administrador; en caso contrario redirigen al login y registran un aviso en el log
github.com
github.com
.

setFlashAlert($message, $type): almacena mensajes flash en la sesión para mostrarlos en la siguiente solicitud
github.com
.

Clase Model

App\Core\Model encapsula la conexión a la base de datos SQLite. Implementa un patrón Singleton para compartir la conexión (self::$db) y establece el modo de error a excepciones
github.com
. El método getDb() fuerza la creación de la conexión si aún no existe
github.com
.

Clase Functions

Este archivo agrupa numerosas funciones utilitarias. Algunas de las más importantes son:

Operaciones de archivos: copiar, mover y eliminar archivos y directorios registrando los resultados
github.com
. También incluye uploadAvatar() para subir avatares con validación de tamaño (hasta 3 MB) y tipo de imagen (JPG/PNG)
github.com
.

Información del cliente: obtener la IP, navegador y sistema operativo del cliente
github.com
, así como devolver un arreglo completo con estos datos y el usuario actual
github.com
.

Gestión de sesión y seguridad: sanitizar entradas para prevenir XSS
github.com
, generar tokens aleatorios
github.com
, verificar si el usuario está autenticado y manejar redirecciones
github.com
, y generar/validar tokens CSRF
github.com
.

Manejo de módulos: agregar o eliminar módulos en modules.json evitando duplicados
github.com
github.com
.

Gestión de usuarios en JSON: guardar, actualizar, obtener y eliminar archivos JSON para preferencias o datos de usuario, así como actualizar campos individuales
github.com
github.com
.

Formato de fechas: convertir fechas UTC a la zona horaria local (America/New_York)
github.com
.

Clase Logger

App\Core\Logger implementa un sistema de logs que escribe en un archivo (app/logs/app.log) y en la base de datos. Su método init() crea el archivo y directorio de log si no existen
github.com
. Cada vez que se registra un mensaje, el logger recopila datos del cliente (IP, navegador, sistema operativo, usuario) usando Functions::getClientDetails() y formatea la entrada antes de escribirla en el archivo y en la tabla logs
github.com
github.com
. Proporciona métodos estáticos convenientes info(), warning() y error() para distintos niveles de severidad
github.com
.

Clase Router

App\Core\Router gestiona las rutas de la aplicación. Permite registrar rutas con un método HTTP, un path y una acción (controlador@método). El método dispatch() normaliza la URL, busca una coincidencia en las rutas registradas y, en caso de encontrarla, crea la instancia del controlador y ejecuta el método especificado
github.com
. También incluye lógica especial para rutas de la API (/api_invoice) y maneja 404 cuando no se encuentra ruta
github.com
.

Clase User

App\Core\User centraliza la gestión de roles de usuario. Define una constante ROLES con los posibles roles (usuario, admin, editor, supervisor, moderador) y ofrece métodos estáticos para consultar el ID y nombre del rol actual, así como funciones para comprobar si el usuario es administrador, editor, supervisor, moderador o usuario normal
github.com
.

Clase Mailer

App\Core\Mailer encapsula el envío de correos utilizando PHPMailer. Obtiene la configuración de config/mail.php y ofrece métodos estáticos para enviar:

Correos de activación (sendActivationEmail)
github.com
.

Correos con enlaces para restablecer la contraseña (sendPasswordResetEmail)
github.com
.

Correos de confirmación tras cambiar la contraseña (sendPasswordChangedEmail)
github.com
.

Modelos
UserModel

Administra la tabla users y tablas relacionadas. Entre sus métodos se encuentran:

Crear usuarios con token de activación y validar duplicados
github.com
.

Activar cuentas usando un token
github.com
.

Comprobar si un correo o nombre existen
github.com
.

Obtener usuarios por email o ID
github.com
.

Obtener listas de todos los usuarios con sus roles y estados de cuenta
github.com
 y obtener todos los roles
github.com
.

Actualizar roles de usuario, nombres de rol y eliminar usuarios
github.com
.

Crear usuarios desde el panel de administración y actualizar sus datos, incluyendo cambios opcionales de contraseña
github.com
github.com
.

Gestionar tokens de recuperación de contraseña: guardar, obtener y eliminar tokens
github.com
 y actualizar la contraseña
github.com
.

Gestionar preferencias y datos adicionales del usuario: obtener preferencias (getByUserId), activar/desactivar modo oscuro (setDarkMode), cambiar idioma (setLanguage), crear preferencias por defecto (createUserPreferences)
github.com
, guardar avatares (saveAvatarFile, updateAvatarFileName)
github.com
 y eliminar registros de avatar
github.com
.

Obtener registros de logs de un usuario, gestionar solicitudes de eliminación de cuenta y reactivar cuentas inactivas
github.com
.

Obtener todos los estados de cuenta (account_statuses)
github.com
.

LogModel

Proporciona métodos para recuperar y contar logs completos o filtrados. Permite obtener un número limitado de logs con paginación y filtrar por usuario, nivel, IP, acción y rangos de fechas
github.com
. También calcula el número total de resultados filtrados para paginar
github.com
.

NotificationModel

Gestiona las notificaciones para los usuarios:

Obtener notificaciones no leídas de un usuario ordenadas por fecha
github.com
.

Marcar una notificación como leída o todas como leídas
github.com
.

Crear nuevas notificaciones con distintos niveles de severidad
github.com
.

ToolModel

Permite generar y eliminar módulos CRUD de forma automática:

generateModule() recibe el nombre del módulo y una lista de campos. Crea el controlador, modelo, vista, archivo CSS, archivo JS, un directorio de imágenes y la ruta correspondiente
github.com
. También escribe plantillas básicas para cada componente (controlador
github.com
, modelo
github.com
, vista
github.com
 y archivo de ruta
github.com
).

deleteModule() elimina todos los archivos creados para un módulo concreto y borra su directorio de imágenes
github.com
.

Controladores
HomeController

Muestra la página principal (index()), leyendo la versión de la aplicación desde archivos de configuración (app_version.php) y estableciendo las URLs de descarga de la aplicación
github.com
. También tiene métodos para mostrar las páginas de contacto, privacidad y ayuda
github.com
.

PruebaController

Ejemplo sencillo de módulo: carga la vista modules/prueba
github.com
.

AuthController

Maneja el registro y autenticación de usuarios:

registerForm() y register(): muestran el formulario de registro y procesan la creación de la cuenta. Validan campos obligatorios, formato de correo, longitud de contraseña y duplicados. Si el registro es exitoso, envían un correo de activación utilizando Mailer
github.com
.

activate(): activa la cuenta mediante un token recibido por URL y redirige al login
github.com
.

loginForm() y login(): muestran el formulario de login y procesan la autenticación. Implementan protección contra múltiples intentos fallidos bloqueando temporalmente la IP y comprueban si la cuenta está activa
github.com
.

logout(): destruye la sesión y redirige al inicio
github.com
.

forgotPasswordForm() y sendResetLink(): generan un token de recuperación y lo envían por correo
github.com
.

resetPasswordForm() y resetPassword(): permiten al usuario ingresar una nueva contraseña mediante el token. Verifican su validez y actualizan la contraseña en la base de datos
github.com
.

LogsController

Administra el visualizador de logs de la aplicación:

index(): requiere rol de administrador, muestra la vista con paginación de logs. Obtiene los logs desde LogModel según la página solicitada y calcula el total de páginas
github.com
.

fetchPage(): responde a solicitudes AJAX para cargar logs filtrados y paginar. Permite filtrar por usuario, nivel, IP, acción y fechas, y devuelve el HTML de la tabla y el paginador en formato JSON
github.com
.

AdminController

Permite gestionar usuarios desde el panel de administración:

manageUsers(): muestra la lista de usuarios junto con sus roles y estados de cuenta
github.com
.

changeRole(): actualiza el rol de un usuario. Verifica token CSRF y registra la acción en el log
github.com
.

deleteUser(): elimina a un usuario determinado y borra su JSON de preferencias
github.com
.

createUser(): crea un usuario desde el panel. Si el usuario se crea como inactivo, se genera y envía un token de activación
github.com
.

updateUser(): actualiza datos de un usuario existente incluyendo su contraseña y rol
github.com
.

ProfileController

Administra las acciones relacionadas con el perfil del usuario:

index(): muestra la información de usuario y sus últimos logs
github.com
.

updateProfile(): actualiza nombre y correo del usuario autenticado y refresca la sesión
github.com
.

changePassword(): permite cambiar la contraseña actual comprobando la contraseña antigua y validando la nueva
github.com
.

toggleDarkMode(): activa o desactiva el modo oscuro para el usuario y lo guarda en la tabla user_preferences y en el JSON
github.com
.

uploadAvatar(): sube un archivo de avatar de forma segura, guarda los metadatos en la base de datos, renombra el archivo con el ID de la base de datos y actualiza el JSON del usuario
github.com
.

changeLanguage(): actualiza el idioma preferido del usuario entre español e inglés
github.com
.

deleteAccount(): marca la cuenta como eliminada (estado 3), registra la solicitud de cierre y cierra la sesión
github.com
.

NotificationController

Proporciona endpoints para manejar notificaciones:

fetch(): devuelve las notificaciones no leídas del usuario autenticado en formato JSON
github.com
.

markAsRead(): marca una notificación específica como leída
github.com
.

markAllAsRead(): marca todas las notificaciones del usuario como leídas
github.com
.

ToolController

Orquesta la generación y eliminación de módulos CRUD:

crudGeneratorView(): muestra la vista de generación de módulos (accesible sólo para administradores)
github.com
.

generateCrud(): procesa el formulario de generación. Comprueba si el módulo ya existe, genera los archivos a través de ToolModel, actualiza modules.json e informa del éxito o error
github.com
.

deleteModule(): elimina los archivos de un módulo y los quita de modules.json
github.com
.

getModules(): devuelve la lista de módulos registrados en modules.json
github.com
.

Rutas

Las rutas se definen en routes/web.php. Las principales incluyen:

GET / → HomeController@index: muestra la página principal
github.com
.

GET /logs y GET|POST /logs/fetch → métodos de LogsController para ver y filtrar logs
github.com
.

GET /auth/register, POST /auth/register → formulario y procesamiento de registro en AuthController
github.com
.

GET /activar → activa la cuenta mediante token
github.com
.

GET|POST /auth/login, GET /auth/logout → autenticación y cierre de sesión
github.com
.

Rutas de administración (/admin/users, /admin/change-role, /admin/delete-user, /admin/create-user, /admin/update-user) mapeadas a métodos de AdminController
github.com
.

Rutas para restablecer contraseña (/password/request, /password/reset) manejadas por PasswordResetController (en realidad las funciones se encuentran en AuthController)
github.com
.

Rutas de perfil (/profile, /profile/update, /profile/change-password, /profile/toggle-dark-mode, /profile/upload-avatar, /profile/change-language, /profile/delete-account) controladas por ProfileController
github.com
.

Rutas de notificaciones (/notifications/fetch, /notifications/mark-as-read, /notifications/mark-all-read) atendidas por NotificationController
github.com
.

Rutas de HomeController para páginas de contacto, privacidad y ayuda (/layouts/contact, /layouts/privacy, /layouts/help)
github.com
.

Finalmente, el enrutador carga automáticamente cualquier archivo .php en routes/modules para incluir rutas de módulos generados dinámicamente
github.com
. Después de registrar todas las rutas, el enrutador despacha la petición con la URL actual
github.com
.

Vistas y plantillas

Las vistas se almacenan en app/views. Hay una plantilla principal layouts/main.php (no mostrada aquí) que define el esqueleto HTML (cabecera, menú, pie) y en la que se inserta el contenido de cada vista. Ejemplos de vistas:

views/home.php: página inicial con enlaces de descarga y detalles de la versión.

views/logs.php: tabla de logs con filtros y paginación.

views/admin/admin_users.php: lista de usuarios con formularios para crear, editar o eliminar usuarios.

views/profile/profile.php: muestra información del usuario y sus logs.

views/modules/prueba.php: módulo de ejemplo creado por PruebaController.

views/admin/crud-generator.php: formulario para generar módulos CRUD.

Los módulos generados automáticamente tendrán sus vistas en views/modules/<nombre>.php, sus hojas de estilo en public/assets/css/<nombre>.css y sus scripts JavaScript en public/assets/js/<nombre>.js, tal como define ToolModel
github.com
.

Módulos dinámicos y modules.json

ToolController junto con ToolModel permiten crear módulos de manera dinámica. Al generar un módulo, se añade una entrada al archivo config/modules.json mediante Functions::addModuleToJson()
github.com
. Cada entrada contiene el nombre del módulo, su slug, la etiqueta de menú (menu_label), si es sólo para administradores y si requiere autenticación. Cuando se elimina un módulo, Functions::removeModuleFromJson() lo elimina del JSON
github.com
.

routes/web.php incluye todas las rutas de los módulos leyendo los archivos ubicados en routes/modules/*.php
github.com
. De esta manera, los módulos generados aparecen automáticamente en el enrutador sin necesidad de editarlos manualmente.
