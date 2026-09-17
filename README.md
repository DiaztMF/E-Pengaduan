# E-Pengaduan

A native PHP public grievance and community reporting web application with administrative workflow management and MySQL storage.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.x-purple?logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-blue?logo=mysql)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-4%20%7C%205-purple?logo=bootstrap)](https://getbootstrap.com/)

## Installation

Clone the repository into your local web server root (e.g., `htdocs` or `/var/www/html`):

```bash
git clone https://github.com/DiaztMF/E-Pengaduan.git
cd E-Pengaduan
```

## Quick Start

1. Import the MySQL database schema and configure database credentials in `config.php`:

```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "epengaduan";
```

2. Start the PHP built-in web server:

```bash
php -S localhost:8000
```

Navigate to [http://localhost:8000](http://localhost:8000) in your browser. Access the administrative portal at `/admin`.

## What is E-Pengaduan?

`E-Pengaduan` is a public service reporting portal designed for local institutions and village administrations. Residents can register complaints, upload evidence documents, and track investigation progress via automated tracking ticket numbers.

## Why E-Pengaduan?

Manual paper-based grievance systems suffer from loss of public trust, unrecorded complaints, and poor transparency. `E-Pengaduan` digitalizes citizen complaints with an audit trail, status updates (Pending -> Verified -> Resolved), and response logging.

## API / Routes

### Web Controllers & Pages
- `index.php`: Citizen report submission portal and public ticket tracking form.
- `admin/`: Administrative dashboard for verifying submissions, assigning field teams, and publishing response notes.
- `config.php`: Centralized database connection and session management handler.

## Examples

Verifying report status in `config.php`:

```php
function getReportStatus($conn, $ticketId) {
    $stmt = $conn->prepare("SELECT status, tanggapan FROM pengaduan WHERE id_pengaduan = ?");
    $stmt->bind_param("s", $ticketId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
```

## Architecture & Development Guides

- Architecture: Pure native PHP MVC pattern with MySQLi prepared statements.
- UI Framework: Responsive Bootstrap styling for both mobile and desktop views.
- Security: Role-based session guards separating public filers from administrative staff.

## License

MIT License. See [LICENSE](LICENSE) for full details.