# PlanetbiruCompareDB

During the development phase, changes in database structure are unavoidable. Developers may have several databases with different structures.

To compare database structures, developers will find it difficult without using tools specifically designed for that purpose. Developers must note down table names, field names, along with data types and default data one by one. This will take a very long time and is inefficient.

This tool is very useful for comparing two databases, whether on the same server or on different servers. Differences in database structure will be clearly visible.

The program is made in PHP language. Developers must ensure that the web server has access to the databases to be compared.

## Key Features

*   **Database Structure Comparison**: Easily compare tables and fields between two databases.
*   **Responsive Interface**: User interface designed to work on desktop and mobile devices.
*   **Highlight Differences**: Visual indicators for missing tables, missing fields, or different field definitions.
*   **Generate Synchronization SQL**: Automatically generate `ALTER TABLE` or `CREATE TABLE` SQL statements to synchronize databases.
    *   Handles `ADD COLUMN`, `DROP COLUMN`, and `MODIFY COLUMN`.
    *   Handles missing tables by generating full `CREATE TABLE` statements.
*   **Direct SQL Execution**: Execute generated synchronization queries directly from the interface after confirmation.
*   **Show DDL**: Right-click on any table name to view its `CREATE TABLE` statement.
*   **Easy Configuration**: Clean and responsive modal for entering database connection details, including a "Swap" button to quickly swap configurations.
*   **Modern Tech Stack**: Built with pure Vanilla JavaScript (ES6 Class) on the frontend and PHP backend. Requires no jQuery or other heavy frameworks.

## Requirements

*   Web server (such as Apache, Nginx).
*   PHP (7.x or later recommended).
*   PHP Data Objects (PDO) extension enabled.
*   Specific PDO driver for the database you want to connect to (e.g., `php_pdo_mysql`, `php_pdo_pgsql`).

## Installation

1.  Download or clone this repository.
2.  Place the files in your web server directory (e.g., `htdocs` folder for XAMPP).
3.  Access the `index.php` file via your browser (e.g., `http://localhost/PlanetbiruCompareDB/`).

## How to Use

1.  Open the application in your web browser.
2.  Click the **Configuration** button to open the settings modal.
3.  Enter connection details (Driver, Host, Port, Database Name, Username, Password) for **Database 1** and **Database 2**.
4.  Click **OK** or close the modal.
5.  Click **List Tables**. The application will connect to both databases and display all tables.
6.  Tables with structural differences will be highlighted.
7.  Click on a table name to load and compare field structures for that table side-by-side.
8.  If differences are found in the field view, a **Generate Sync SQL** button will appear.
9.  Click the button to open a modal displaying the SQL commands needed to synchronize the schema.
10. Review the generated SQL. You can choose to make DB1 like DB2, or DB2 like DB1.
11. Click the **Execute** button for the desired action. You will be asked for confirmation before any changes are made to your database.
12. **Tip**: Right-click on any table name in the list to quickly view its `CREATE TABLE` statement.
