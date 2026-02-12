# PlanetbiruCompareDB

During the development phase, changes in database structure are unavoidable. Developers may have several databases with different structures.

To compare the structure of the database, developers will find it difficult if not use tools specifically designed for that. Developers must note one by one table names, field names, along with the default data type and data. This will take a very long time and be inefficient.

This tool is very useful for comparing two databases both on the same server and on different servers. The difference in database structure will be clearly seen.

The program is made in PHP language. Developers must ensure that the web server has access to the database to be compared.

To enter a server name, port number, username and password is very easy. Users simply enter it in the space provided. For convenience reasons, users can also swap entries.

## Features

*   **Compare Database Structures**: Easily compare tables and fields between two databases.
*   **Highlight Differences**: Visual indicators for missing tables, missing fields, or different field definitions.
*   **Generate Synchronization SQL**: Automatically generate `ALTER TABLE` or `CREATE TABLE` SQL statements to synchronize the databases.
    *   Handles `ADD COLUMN`, `DROP COLUMN`, and `MODIFY COLUMN`.
    *   Handles missing tables by generating full `CREATE TABLE` statements.
*   **Execute SQL Directly**: Execute the generated synchronization queries directly from the interface.
*   **Vanilla JavaScript**: Lightweight frontend without jQuery dependency.
*   **User-Friendly Interface**: Uses modal dialogs for SQL preview, confirmation, and error messages.

## How to Use

1.  Enter the connection details (Host, Port, Database Name, Username, Password) for both Database 1 and Database 2.
2.  Click **List Tables**.
3.  Tables with differences will be highlighted in red.
4.  Click on a table name to view field details.
5.  If differences are found, click **Generate Sync SQL**.
6.  Review the generated SQL in the modal window.
7.  Click **Execute** on the respective side to apply changes to the target database.
