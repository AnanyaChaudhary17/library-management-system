-- ============================================================
-- Library Management System : data.sql  (import AFTER schema.sql)
-- 5 categories, 20 books, 10 members, 2 staff, 15 issues, 2 fines.
-- Dates are relative to today (CURDATE) so there are always
-- overdue books, whenever you import this file.
-- Staff logins:  admin / admin123    librarian / lib123
-- ============================================================
USE library_db;

INSERT INTO staff (staff_id, name, username, password_hash) VALUES
(1, 'Admin Librarian',  'admin',     '$2y$10$Y5jjSP1qicYBE4ARCoavZuap7jHDArOgLgQrJqhnwv186ZgYvtXnu'),
(2, 'Library Assistant','librarian', '$2y$10$BW04MOcKk4Pgv.mS6z2Fm.9CWz..3Tu8aKkseekXFR6uFZ7PA4gaG');

INSERT INTO category (category_id, category_name) VALUES
(1, 'Computer Science'), (2, 'Mathematics'), (3, 'Physics'), (4, 'Literature'), (5, 'History');

-- available_copies is set correctly at the bottom of this file
INSERT INTO book (book_id, isbn, title, author, publisher, pub_year, category_id, total_copies, available_copies) VALUES
( 1, '9788100000001', 'Introduction to Algorithms',              'Thomas H. Cormen',        'MIT Press',       2009, 1, 3, 3),
( 2, '9788100000002', 'Database System Concepts',                'Abraham Silberschatz',    'McGraw-Hill',     2019, 1, 3, 3),
( 3, '9788100000003', 'Operating System Concepts',               'Abraham Silberschatz',    'Wiley',           2018, 1, 2, 2),
( 4, '9788100000004', 'Computer Networks',                       'Andrew S. Tanenbaum',     'Pearson',         2010, 1, 2, 2),
( 5, '9788100000005', 'Let Us C',                                'Yashavant Kanetkar',      'BPB',             2016, 1, 4, 4),
( 6, '9788100000006', 'Clean Code',                              'Robert C. Martin',        'Prentice Hall',   2008, 1, 2, 2),
( 7, '9788100000007', 'Higher Engineering Mathematics',          'B. S. Grewal',            'Khanna',          2017, 2, 5, 5),
( 8, '9788100000008', 'Linear Algebra and Its Applications',     'Gilbert Strang',          'Cengage',         2005, 2, 2, 2),
( 9, '9788100000009', 'Discrete Mathematics',                    'Kenneth H. Rosen',        'McGraw-Hill',     2012, 2, 3, 3),
(10, '9788100000010', 'Concepts of Physics',                     'H. C. Verma',             'Bharati Bhawan',  2015, 3, 4, 4),
(11, '9788100000011', 'Fundamentals of Physics',                 'Halliday and Resnick',    'Wiley',           2013, 3, 3, 3),
(12, '9788100000012', 'A Brief History of Time',                 'Stephen Hawking',         'Bantam',          1998, 3, 2, 2),
(13, '9788100000013', 'Godan',                                   'Premchand',               'Rajkamal',        1936, 4, 2, 2),
(14, '9788100000014', 'The Guide',                               'R. K. Narayan',           'Indian Thought',  1958, 4, 2, 2),
(15, '9788100000015', 'Gitanjali',                               'Rabindranath Tagore',     'Macmillan',       1912, 4, 1, 1),
(16, '9788100000016', 'India After Gandhi',                      'Ramachandra Guha',        'Picador',         2007, 5, 2, 2),
(17, '9788100000017', 'The Discovery of India',                  'Jawaharlal Nehru',        'Oxford',          1946, 5, 2, 2),
(18, '9788100000018', 'Wings of Fire',                           'A. P. J. Abdul Kalam',    'Universities Press', 1999, 4, 3, 3),
(19, '9788100000019', 'Artificial Intelligence: A Modern Approach','Stuart Russell',        'Pearson',         2020, 1, 2, 2),
(20, '9788100000020', 'Calculus',                                'James Stewart',           'Cengage',         2015, 2, 3, 3);

INSERT INTO member (member_id, name, email, phone, member_type, join_date, status) VALUES
( 1, 'Aarav Sharma',     'aarav@college.edu',   '9810000001', 'Student', '2024-07-15', 'Active'),
( 2, 'Priya Singh',      'priya@college.edu',   '9810000002', 'Student', '2024-07-15', 'Active'),
( 3, 'Rohan Das',        'rohan@college.edu',   '9810000003', 'Student', '2024-08-01', 'Active'),   -- holds 3 books (limit test)
( 4, 'Sneha Kumari',     'sneha@college.edu',   '9810000004', 'Student', '2024-08-01', 'Suspended'),-- suspended (rule test)
( 5, 'Dr. Anil Verma',   'anil@college.edu',    '9810000005', 'Faculty', '2020-01-10', 'Active'),
( 6, 'Prof. Meera Iyer', 'meera@college.edu',   '9810000006', 'Faculty', '2019-06-20', 'Active'),
( 7, 'Kabir Mehta',      'kabir@college.edu',   '9810000007', 'Student', '2025-01-12', 'Active'),
( 8, 'Isha Gupta',       'isha@college.edu',    '9810000008', 'Student', '2025-01-12', 'Active'),
( 9, 'Vikram Rao',       'vikram@college.edu',  '9810000009', 'Student', '2025-02-03', 'Active'),
(10, 'Dr. Neha Joshi',   'neha@college.edu',    '9810000010', 'Faculty', '2021-09-01', 'Active');

-- 15 issue records: 6 returned (2 late), 9 still out (3 overdue)
INSERT INTO issue (issue_id, book_id, member_id, staff_id, issue_date, due_date, return_date) VALUES
-- returned
( 1,  1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 26 DAY), DATE_SUB(CURDATE(), INTERVAL 30 DAY)),  -- on time
( 2,  2, 2, 1, DATE_SUB(CURDATE(), INTERVAL 35 DAY), DATE_SUB(CURDATE(), INTERVAL 21 DAY), DATE_SUB(CURDATE(), INTERVAL 16 DAY)),  -- 5 days late
( 3,  3, 5, 2, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 16 DAY), DATE_SUB(CURDATE(), INTERVAL 20 DAY)),  -- on time
( 4,  4, 7, 2, DATE_SUB(CURDATE(), INTERVAL 50 DAY), DATE_SUB(CURDATE(), INTERVAL 36 DAY), DATE_SUB(CURDATE(), INTERVAL 30 DAY)),  -- 6 days late
( 5,  5, 8, 1, DATE_SUB(CURDATE(), INTERVAL 28 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY), DATE_SUB(CURDATE(), INTERVAL 15 DAY)),  -- on time
(14, 14, 4, 2, DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_SUB(CURDATE(), INTERVAL 31 DAY), DATE_SUB(CURDATE(), INTERVAL 33 DAY)),  -- on time
-- still out, not yet due
( 6,  6, 3, 1, DATE_SUB(CURDATE(), INTERVAL  5 DAY), DATE_ADD(CURDATE(), INTERVAL  9 DAY), NULL),
( 7,  7, 3, 1, DATE_SUB(CURDATE(), INTERVAL  8 DAY), DATE_ADD(CURDATE(), INTERVAL  6 DAY), NULL),
( 8,  8, 3, 2, DATE_SUB(CURDATE(), INTERVAL  3 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), NULL),
(11, 11, 5, 2, DATE_SUB(CURDATE(), INTERVAL  2 DAY), DATE_ADD(CURDATE(), INTERVAL 12 DAY), NULL),
(12, 12, 5, 1, DATE_SUB(CURDATE(), INTERVAL  4 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), NULL),
(15, 15, 6, 1, DATE_SUB(CURDATE(), INTERVAL  6 DAY), DATE_ADD(CURDATE(), INTERVAL  8 DAY), NULL),  -- last copy of Gitanjali
-- still out, overdue
( 9,  9, 1, 1, DATE_SUB(CURDATE(), INTERVAL 25 DAY), DATE_SUB(CURDATE(), INTERVAL 11 DAY), NULL),  -- 11 days overdue
(10, 10, 2, 2, DATE_SUB(CURDATE(), INTERVAL 19 DAY), DATE_SUB(CURDATE(), INTERVAL  5 DAY), NULL),  -- 5 days overdue (return it => Rs 10)
(13, 13, 9, 2, DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 16 DAY), NULL); -- 16 days overdue

-- Fines for the two late returns (Rs 2/day): issue 2 = 5 days = 10, issue 4 = 6 days = 12
INSERT INTO fine (fine_id, issue_id, amount, paid_status, paid_date) VALUES
(1, 2, 10.00, 'Paid',   DATE_SUB(CURDATE(), INTERVAL 15 DAY)),
(2, 4, 12.00, 'Unpaid', NULL);

-- Make available_copies consistent with the books that are currently out
UPDATE book b
SET b.available_copies = b.total_copies -
    (SELECT COUNT(*) FROM issue i WHERE i.book_id = b.book_id AND i.return_date IS NULL);
