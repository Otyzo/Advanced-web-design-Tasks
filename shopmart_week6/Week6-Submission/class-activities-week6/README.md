# Week 6 — Class Activities 1–3

These are the three in-class warm-up exercises from the BIT3208 Week 6 material (Database Integration and CRUD Operations), built as standalone mini files separate from the main Employee Records System (Practical Task 3).

They use the simpler `studentdb` / `students` example (Full Name, Email, Course) shown in the lecture demonstrations.

## Files

| File | Activity | Covers |
|---|---|---|
| `db_connect.php` | — | Shared MySQL connection used by all three activities |
| `activity1_register.php` | Class Activity 1 | Student registration form → insert into MySQL |
| `activity2_display.php` | Class Activity 2 | Retrieve and display all student records in a table |
| `activity3_edit_delete.php` | Class Activity 3 | Update a student's course, delete a student record |
| `students.sql` | — | Creates `studentdb` and the `students` table with sample data |

## Setup (XAMPP)

1. Copy this folder into `C:\xampp\htdocs\` (e.g. as `week6-class-activities`).
2. Start Apache and MySQL.
3. Import `students.sql` via phpMyAdmin.
4. Visit `http://localhost/week6-class-activities/activity1_register.php` to begin.

The three pages link to each other: register → view all → edit/delete.

## Skills Mapped

- **Activity 1:** Form creation, database connection, data insertion
- **Activity 2:** SQL queries, data retrieval, table formatting
- **Activity 3:** Record management, CRUD operations
