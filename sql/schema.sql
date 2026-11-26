-- ============================================================
-- Library Management System : schema.sql
-- Works on MySQL 8.0.16+ / MariaDB 10.2.1+ (CHECK is enforced)
-- Import first, then data.sql.
-- WARNING: drops and recreates the library_db database.
-- ============================================================
DROP DATABASE IF EXISTS library_db;
CREATE DATABASE library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_db;

-- ---------- category ----------
CREATE TABLE category (
    category_id   INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------- book ----------
-- author is a plain column (see report: a separate author table with a
-- many-to-many link would be better in a larger system).
CREATE TABLE book (
    book_id          INT AUTO_INCREMENT PRIMARY KEY,
    isbn             VARCHAR(20)  NOT NULL UNIQUE,
    title            VARCHAR(150) NOT NULL,
    author           VARCHAR(100) NOT NULL,
    publisher        VARCHAR(100),
    pub_year         SMALLINT,
    category_id      INT NOT NULL,
    total_copies     INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    CONSTRAINT chk_book_copies  CHECK (total_copies >= 0 AND available_copies >= 0
                                       AND available_copies <= total_copies),
    CONSTRAINT chk_book_year    CHECK (pub_year IS NULL OR pub_year BETWEEN 1000 AND 2100),
    CONSTRAINT fk_book_category FOREIGN KEY (category_id) REFERENCES category(category_id),
    INDEX idx_book_title (title),
    INDEX idx_book_author (author)
) ENGINE=InnoDB;

-- ---------- member ----------
CREATE TABLE member (
    member_id   INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    phone       VARCHAR(15),
    member_type ENUM('Student','Faculty') NOT NULL,
    join_date   DATE NOT NULL,
    status      ENUM('Active','Suspended') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB;

-- ---------- staff ----------
CREATE TABLE staff (
    staff_id      INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ---------- issue ----------
CREATE TABLE issue (
    issue_id    INT AUTO_INCREMENT PRIMARY KEY,
    book_id     INT NOT NULL,
    member_id   INT NOT NULL,
    staff_id    INT NOT NULL,
    issue_date  DATE NOT NULL,
    due_date    DATE NOT NULL,
    return_date DATE NULL DEFAULT NULL,          -- NULL until returned
    CONSTRAINT chk_issue_due    CHECK (due_date >= issue_date),
    CONSTRAINT chk_issue_return CHECK (return_date IS NULL OR return_date >= issue_date),
    CONSTRAINT fk_issue_book    FOREIGN KEY (book_id)   REFERENCES book(book_id),
    CONSTRAINT fk_issue_member  FOREIGN KEY (member_id) REFERENCES member(member_id),
    CONSTRAINT fk_issue_staff   FOREIGN KEY (staff_id)  REFERENCES staff(staff_id),
    INDEX idx_issue_open (return_date, due_date)
) ENGINE=InnoDB;

-- ---------- fine ----------
-- One fine per issue (UNIQUE issue_id); only created when a book is returned late.
CREATE TABLE fine (
    fine_id     INT AUTO_INCREMENT PRIMARY KEY,
    issue_id    INT NOT NULL UNIQUE,
    amount      DECIMAL(8,2) NOT NULL,
    paid_status ENUM('Paid','Unpaid') NOT NULL DEFAULT 'Unpaid',
    paid_date   DATE NULL DEFAULT NULL,
    CONSTRAINT chk_fine_amount CHECK (amount > 0),
    CONSTRAINT chk_fine_paid   CHECK ((paid_status = 'Paid'   AND paid_date IS NOT NULL)
                                   OR (paid_status = 'Unpaid' AND paid_date IS NULL)),
    CONSTRAINT fk_fine_issue   FOREIGN KEY (issue_id) REFERENCES issue(issue_id)
) ENGINE=InnoDB;

-- ============================================================
-- Views
-- ============================================================

-- Books that are out right now (not yet returned)
CREATE VIEW v_issued_books AS
SELECT i.issue_id,
       b.book_id,
       b.title,
       m.member_id,
       m.name        AS member_name,
       m.member_type,
       i.issue_date,
       i.due_date,
       GREATEST(DATEDIFF(CURDATE(), i.due_date), 0) AS days_overdue
FROM issue  i
JOIN book   b ON b.book_id   = i.book_id
JOIN member m ON m.member_id = i.member_id
WHERE i.return_date IS NULL;

-- Books that are out AND past their due date
CREATE VIEW v_overdue_books AS
SELECT i.issue_id,
       b.title,
       m.member_id,
       m.name  AS member_name,
       m.email,
       m.phone,
       i.issue_date,
       i.due_date,
       DATEDIFF(CURDATE(), i.due_date) AS days_overdue
FROM issue  i
JOIN book   b ON b.book_id   = i.book_id
JOIN member m ON m.member_id = i.member_id
WHERE i.return_date IS NULL
  AND i.due_date < CURDATE();
