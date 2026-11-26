# Library Management System

A role-based library management web application built with PHP and MySQL. It provides a simple interface for librarians to manage books and members, issue and return books, track overdue items, and collect fines.

## Features

- Staff authentication with password hashing and CSRF protection
- Dashboard with inventory, issue, overdue, and fine summaries
- Add, edit, search, view, and delete books
- Add, edit, view, and manage library members
- Issue books with 14-day loan periods
- Prevent issues for suspended members, exhausted borrowing limits, or unavailable copies
- Process returns and calculate overdue fines
- Track currently issued, overdue, and unpaid fine records
- InnoDB transactions with row locks for safe issue and return operations
- Seed database with sample categories, books, members, issues, and fines

## Technology Stack

| Component | Technology |
| --- | --- |
| Frontend | HTML5, CSS |
| Backend | PHP 8+ |
| Database | MySQL 8 or MariaDB |
| Database access | PDO with prepared statements |
| Local server | Apache / XAMPP or PHP built-in server |

## Database Design

The system uses six related tables:

- `category`
- `book`
- `member`
- `staff`
- `issue`
- `fine`

It also includes the `v_issued_books` and `v_overdue_books` views for reporting currently issued and overdue books.

See the [ER diagram](docs/er_diagram.png) and [database report](docs/Report.docx) for more detail.

## Requirements

- PHP 8.0 or newer
- PHP PDO MySQL extension
- MySQL 8.0.16+ or MariaDB 10.2.1+
- Apache web server or another PHP-capable server

## Installation

### 1. Clone or download the project

```bash
git clone <repository-url>
cd library-management-system
```

Place the project inside your web server document root if you are using XAMPP.

```text
C:\xampp\htdocs\library-management-system
```

### 2. Start MySQL and Apache

Start **Apache** and **MySQL** from the XAMPP Control Panel.

### 3. Configure the database connection

Update [includes/db.php](includes/db.php) if your MySQL credentials differ:

```php
const DB_HOST = 'localhost';
const DB_NAME = 'library_db';
const DB_USER = 'root';
const DB_PASS = '';
```

The empty password is the default for a standard local XAMPP installation.

### 4. Import the database

Open phpMyAdmin and import both SQL files in this order:

1. [sql/schema.sql](sql/schema.sql)
2. [sql/data.sql](sql/data.sql)

You can also run them from a MySQL client:

```sql
SOURCE /path/to/library-management-system/sql/schema.sql;
SOURCE /path/to/library-management-system/sql/data.sql;
```

The schema creates a new `library_db` database. It will drop an existing database with the same name, so do not run it against data you need to preserve.

### 5. Open the application

With XAMPP:

```text
http://localhost/library-management-system/
```

## Default Accounts

| Role | Username | Password |
| --- | --- | --- |
| Administrator | `admin` | `admin123` |
| Librarian | `librarian` | `lib123` |

Change these credentials before using the system outside a local demonstration environment.

## Usage

1. Log in with a staff account.
2. Use **Books** to add or edit titles and inventory.
3. Use **Members** to add or update student and faculty records.
4. Open **Issue** to issue a book to an eligible member.
5. Open **Return** to return an issued book.
6. Review **Issued**, **Overdue**, and **Fines** for outstanding activity.

## Business Rules

The main rules are centralized in [includes/functions.php](includes/functions.php):

| Rule | Value |
| --- | --- |
| Standard loan period | 14 days |
| Student borrowing limit | 3 books |
| Faculty borrowing limit | 5 books |
| Late fine | ₹2 per day |

Additional validation prevents:

- Issuing a book when no copies are available
- Issuing to suspended members
- Exceeding a member's borrowing limit
- Creating a fine for an on-time return

Late returns create exactly one fine per issue. Issue and return operations use database transactions to update issue records and available copy counts consistently.

## Useful Database Files

- [sql/schema.sql](sql/schema.sql) creates the database, tables, constraints, and views.
- [sql/data.sql](sql/data.sql) inserts sample categories, books, members, staff, issues, and fines.
- [sql/queries.sql](sql/queries.sql) contains reporting examples for joins, aggregates, subqueries, date calculations, views, and ranking.

To reset the demo data, run `sql/schema.sql` followed by `sql/data.sql`.

## Project Structure

```text
.
├── assets/
│   └── style.css
├── docs/
│   ├── Report.docx
│   ├── er.mmd
│   └── er_diagram.png
├── includes/
│   ├── db.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── sql/
│   ├── data.sql
│   ├── queries.sql
│   └── schema.sql
├── book_form.php
├── books.php
├── fines.php
├── index.php
├── issue.php
├── issued.php
├── login.php
├── logout.php
├── member_form.php
├── members.php
├── overdue.php
└── return.php
```

## Troubleshooting

### Cannot connect to the database

- Confirm MySQL is running.
- Confirm `library_db` was imported.
- Check the host, username, password, and database name in [includes/db.php](includes/db.php).

### Access denied or login failure

- Re-import [sql/data.sql](sql/data.sql) after [sql/schema.sql](sql/schema.sql).
- Confirm you are using one of the default credentials listed above.
- If PHP password verification fails, regenerate staff password hashes with `password_hash()`.

### Page is not found

- Confirm the folder name matches the URL.
- Confirm Apache is running.
- Use `http://localhost/library-management-system/` with XAMPP.

## Security Notes

This project includes session authentication, password hashing, prepared SQL statements, and CSRF checks for POST forms. For public deployment, also configure HTTPS, restrict database privileges, keep PHP and MySQL updated, set a strong database password, and replace the sample staff credentials.
