# Laravel Support Tickets System

A comprehensive support ticket management system built with Laravel, featuring real-time messaging, file attachments, ticket categorization, and role-based access control.

## Features

- **User Roles**: Admin, Agent, and Customer roles with different permissions
- **Ticket Management**: Create, view, update, close, reopen, and archive support tickets
- **Real-time Messaging**: WebSockets integration for instant communication
- **File Attachments**: Upload and manage attachments on tickets
- **Categorization**: Organize tickets by categories and labels
- **Activity Logging**: Track all changes and activities in the system
- **Dashboard**: Visual statistics and metrics for admins and agents
- **User Management**: Admin tools for managing users and their roles

## Screenshots

![Screenshot 1](https://laraveldaily.com/uploads/2022/11/laravel-support-tickets-01.png)

![Screenshot 2](https://laraveldaily.com/uploads/2022/11/laravel-support-tickets-02.png)

## Technologies Used

- **PHP 8.1+**
- **Laravel 9**
- **MySQL/PostgreSQL**
- **Laravel WebSockets** for real-time communication
- **Spatie Media Library** for file uploads and management
- **Spatie Activity Log** for tracking system activities
- **Spatie Permission** for role-based access control
- **Alpine.js** for frontend interactivity
- **Tailwind CSS** for styling

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/laravel-support-tickets.git
   ```

2. Navigate to the project directory:
   ```
   cd laravel-support-tickets
   ```

3. Copy `.env.example` file to `.env` and edit database credentials:
   ```
   cp .env.example .env
   ```

4. Install PHP dependencies:
   ```
   composer install
   ```

5. Generate application key:
   ```
   php artisan key:generate
   ```

6. Run database migrations and seed initial data:
   ```
   php artisan migrate --seed
   ```

7. Install frontend dependencies:
   ```
   npm install
   ```

8. Build frontend assets:
   ```
   npm run build
   ```
   or for development:
   ```
   npm run dev
   ```

9. Start the WebSockets server (for real-time messaging):
   ```
   php artisan websockets:serve
   ```

10. Start the Laravel development server:
    ```
    php artisan serve
    ```

11. Access the application at `http://localhost:8000`

## Default Login Credentials

- **Admin**: admin@admin.com / password
- You can also register new user accounts through the registration form

## Usage

- **Admins** can manage all aspects of the system including users, categories, and labels
- **Agents** can view and respond to tickets assigned to them
- **Customers** can create tickets and view their own ticket history

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
