-- ============================================================
-- Library Management System : queries.sql
-- 8 queries (+ the 2 views). Run after schema.sql and data.sql.
-- Take an output screenshot of each for the report.
-- ============================================================
USE library_db;

-- Q1. JOIN (3 tables): books currently issued, with member names
SELECT i.issue_id, b.title, m.name AS member, i.issue_date, i.due_date
FROM issue i
JOIN book b   ON b.book_id   = i.book_id
JOIN member m ON m.member_id = i.member_id
WHERE i.return_date IS NULL
ORDER BY i.due_date;

-- Q2. GROUP BY: number of book titles and copies per category
SELECT c.category_name, COUNT(b.book_id) AS titles, SUM(b.total_copies) AS copies
FROM category c
LEFT JOIN book b ON b.category_id = c.category_id
GROUP BY c.category_id, c.category_name
ORDER BY titles DESC;

-- Q3. GROUP BY ... HAVING: members who have 2 or more issue records
SELECT m.member_id, m.name, COUNT(*) AS books_issued
FROM member m
JOIN issue i ON i.member_id = m.member_id
GROUP BY m.member_id, m.name
HAVING COUNT(*) >= 2
ORDER BY books_issued DESC;

-- Q4. Aggregates: fine totals (collected vs pending)
SELECT SUM(CASE WHEN paid_status = 'Paid'   THEN amount ELSE 0 END) AS total_collected,
       SUM(CASE WHEN paid_status = 'Unpaid' THEN amount ELSE 0 END) AS total_pending,
       COUNT(*)                                                     AS fines_raised,
       AVG(amount)                                                  AS average_fine
FROM fine;

-- Q5. Subquery: books that have never been issued
SELECT book_id, title, author
FROM book
WHERE book_id NOT IN (SELECT book_id FROM issue)
ORDER BY title;

-- Q6. Date function (DATEDIFF): overdue days and fine accrued so far (Rs 2/day)
SELECT i.issue_id, b.title, m.name AS member, i.due_date,
       DATEDIFF(CURDATE(), i.due_date)     AS days_overdue,
       DATEDIFF(CURDATE(), i.due_date) * 2 AS fine_so_far
FROM issue i
JOIN book b   ON b.book_id   = i.book_id
JOIN member m ON m.member_id = i.member_id
WHERE i.return_date IS NULL AND i.due_date < CURDATE()
ORDER BY days_overdue DESC;

-- Q7. JOIN (4 tables): unpaid fines per member
SELECT m.name AS member, b.title, f.amount, f.paid_status
FROM fine f
JOIN issue i  ON i.issue_id  = f.issue_id
JOIN member m ON m.member_id = i.member_id
JOIN book b   ON b.book_id   = i.book_id
WHERE f.paid_status = 'Unpaid';

-- Q8. Top 5 most borrowed books (GROUP BY + ORDER BY + LIMIT)
SELECT b.title, COUNT(i.issue_id) AS times_issued
FROM book b
JOIN issue i ON i.book_id = b.book_id
GROUP BY b.book_id, b.title
ORDER BY times_issued DESC, b.title
LIMIT 5;

-- ---------- The 2 views ----------
SELECT * FROM v_issued_books  ORDER BY due_date;
SELECT * FROM v_overdue_books ORDER BY days_overdue DESC;
