<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Logbook Generator API

Laravel-based API system for managing logbook templates and data with role-based access control.

## Features

### 🚀 Core Features
- **Logbook Management**: Create, edit, and manage logbook templates and entries
- **User Management**: Comprehensive user CRUD with role-based permissions
- **Role-Based Access Control**: Super Admin, Admin, Manager, Institution Admin, User roles
- **Audit Logging**: Track all system activities and changes
- **RESTful API**: Clean and documented API endpoints

### ⚡ Performance Features
- **Frontend Caching**: Smart localStorage caching system
  - Reduces API calls by up to 90%
  - 5-minute cache duration (configurable)
  - Automatic cache invalidation on data changes
  - Manual refresh capability
  - See [CACHING.md](CACHING.md) for details

### 🎨 Admin Dashboard
- **Dashboard**: Real-time statistics and charts
- **User Management**: Create, edit, delete users with role assignments
- **Logbook Management**: View all templates with creator info and entry counts
- **Responsive Design**: Mobile-friendly Tailwind CSS interface

## Tech Stack

- **Backend**: Laravel 11, PostgreSQL
- **Authentication**: Laravel Sanctum (Bearer Token)
- **Authorization**: Spatie Laravel Permission
- **Frontend**: Blade Templates, Vanilla JavaScript, Tailwind CSS
- **Charts**: Chart.js

## Installation

1. Clone repository
```bash
git clone https://github.com/febryanalza/loggenerator_api.git
cd loggenerator_api
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Setup database
```bash
# Edit .env with your PostgreSQL credentials
php artisan migrate
php artisan db:seed
```

5. Start server
```bash
php artisan serve
```

## API Documentation

### Authentication
All API endpoints require Bearer token authentication:
```
Authorization: Bearer {your-token}
```

### Key Endpoints

#### Logbook Templates
- `GET /api/templates/admin/all` - Get all templates (admin)
- `GET /api/templates/{id}` - Get single template
- `POST /api/templates` - Create template
- `PUT /api/templates/{id}` - Update template
- `DELETE /api/templates/{id}` - Delete template

#### Users
- `GET /api/admin/users` - Get all users (admin)
- `POST /api/admin/users` - Create user
- `PUT /api/admin/users/{id}/role` - Update user role

## Cache System

The application uses localStorage-based caching for improved performance:

- **Cache Duration**: 5 minutes (configurable)
- **Cache Keys**: 
  - `logbook_templates_cache` - Template data
  - `users_management_cache` - User data
  - `institutions_cache` - Institution data

### Cache Benefits
- 50x faster page loads (10ms vs 500ms)
- Reduced server load
- Better user experience
- Network efficiency

### Testing Cache
Visit `/cache-demo.html` to see interactive cache demonstration with metrics.

For detailed documentation, see [CACHING.md](CACHING.md)

## Admin Pages

- `/admin/dashboard` - Main dashboard with statistics
- `/admin/user-management` - User CRUD interface
- `/admin/logbook-management` - Template management
- `/admin/content-management` - Content management (dev)
- `/admin/transactions` - Activity logs (dev)

## Security

- Bearer token authentication via Sanctum
- Role-based access control via Spatie Permission
- CSRF protection
- SQL injection prevention via Eloquent ORM
- XSS protection via Blade templating

## Development

### Code Style
- Follow PSR-12 standards
- Use PHPDoc for documentation
- Write descriptive commit messages

### Testing
```bash
php artisan test
```

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
