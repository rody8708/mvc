# Copilot Instructions for Custom MVC Framework

## Overview
This repository contains a custom PHP-based Model-View-Controller (MVC) framework designed for modular web application development. The framework includes user authentication, role management, notifications, and automatic CRUD generation. The project is structured to ensure separation of concerns and ease of extensibility.

## Project Structure
- **`config/`**: Contains global configuration files (e.g., `config.php`, `mail.php`). Key constants and session management are defined here.
- **`routes/`**: Defines application routes in `web.php` and dynamically includes module-specific routes.
- **`app/`**:
  - **`controllers/`**: Handles HTTP requests and orchestrates business logic.
  - **`models/`**: Manages database interactions and data operations.
  - **`views/`**: Contains presentation templates.
  - **`core/`**: Framework utilities and base classes (e.g., `Controller`, `Model`, `Router`, `Logger`, `Mailer`).
  - **`libs/`**: Third-party libraries (e.g., PHPMailer).
  - **`logs/`**: Stores application logs.
  - **`db/`**: SQLite databases and connection classes.
- **`public/`**: Web-accessible directory with `index.php` and static assets (CSS, JS, images).

## Key Components
### Core Classes
- **`Controller`**: Base class for controllers. Provides methods like `loadModel`, `loadView`, `requireLogin`, and `setFlashAlert`.
- **`Model`**: Implements a singleton pattern for SQLite database connections.
- **`Router`**: Manages route registration and dispatching.
- **`Logger`**: Logs messages to both files and the database.
- **`Mailer`**: Simplifies email sending using PHPMailer.

### Utilities
- **`Functions.php`**: Provides utility functions for file operations, client information, session management, and more.
- **`User.php`**: Centralizes user role management.

## Developer Workflows
### Routing
Define routes in `routes/web.php` using the `Router` class. Example:
```php
$router->get('/home', 'HomeController@index');
```

### Adding a New Module
1. Create a new controller in `app/controllers/`.
2. Define the corresponding model in `app/models/`.
3. Add views in `app/views/`.
4. Register routes in `routes/web.php`.

### Logging
Use the `Logger` class for logging:
```php
Logger::info('User logged in', ['user_id' => $userId]);
```

### Email Notifications
Use the `Mailer` class for sending emails:
```php
Mailer::sendActivationEmail($userEmail, $activationLink);
```

## Project-Specific Conventions
- **Session Management**: Sessions expire after 15 minutes of inactivity.
- **Error Handling**: Fatal errors are logged using the `Logger` class.
- **File Uploads**: Use `Functions::uploadAvatar` for avatar uploads (max size: 3MB, formats: JPG/PNG).
- **CSRF Protection**: Generate and validate CSRF tokens using `Functions`.

## External Dependencies
- **PHPMailer**: Located in `app/libs/PHPMailer/`. Used for email functionality.
- **SQLite**: Database engine for persistence.

## Examples
### Controller Example
```php
class HomeController extends Controller {
    public function index() {
        $this->loadView('home', ['title' => 'Welcome']);
    }
}
```

### Model Example
```php
class UserModel extends Model {
    public function getUserById($id) {
        $stmt = self::getDb()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
```

### View Example
```php
<h1><?= $title ?></h1>
<p>Welcome to the home page!</p>
```

## Notes
- Ensure all new modules follow the MVC pattern.
- Use the `Logger` class for consistent logging.
- Follow the existing folder structure for maintainability.