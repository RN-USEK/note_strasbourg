# Simple PHP Note-Taking Application

A basic web application for creating and displaying notes, built with PHP and SQLite. This project is designed to demonstrate client-server communication and database interaction in a PHP environment, suitable for running in GitHub Codespaces.

## Description

This application allows users to:
1.  Add new notes via an HTML form.
2.  View a list of all previously added notes.

It uses a single PHP file (`notes_app.php`) to handle both the front-end display (HTML) and the back-end logic (PHP processing and database operations). Data is stored in an SQLite database file (`notes.db`) created in the same directory as the script.

## Prerequisites

To run this application, you need:
* PHP (version 7.4 or higher recommended).
* The PHP SQLite3 extension (`php-sqlite3`) enabled for PDO.

These prerequisites are typically handled by the Codespaces environment configuration if a `.devcontainer/devcontainer.json` file is set up correctly.

## Setup and Running in GitHub Codespaces

1.  **Open in Codespaces:**
    * If this project is in a GitHub repository, you can open it directly in a new Codespace.
    * Ensure your Codespace environment has PHP and the SQLite3 extension. A recommended `.devcontainer/devcontainer.json` configuration is provided below.

2.  **Development Container Configuration (`.devcontainer/devcontainer.json`):**
    Create a `.devcontainer` folder in your project root and add a `devcontainer.json` file with the following content to ensure PHP and SQLite are correctly set up:

    ```json
    {
        "name": "PHP SQLite Notes App",
        "image": "[mcr.microsoft.com/devcontainers/php:8.2](https://mcr.microsoft.com/devcontainers/php:8.2)", // Or your preferred PHP version
        "features": {
            "ghcr.io/devcontainers/features/sqlite:1": {} // Installs SQLite CLI
        },
        "postCreateCommand": "sudo apt-get update && sudo apt-get install -y php-sqlite3 && sudo docker-php-ext-enable sqlite3",
        "forwardPorts": [8000],
        "portsAttributes": {
            "8000": {
                "label": "Notes App",
                "onAutoForward": "openPreview"
            }
        },
        "customizations": {
            "vscode": {
                "extensions": [
                    "bmewburn.vscode-intelephense-client",
                    "xdebug.php-debug"
                ]
            }
        }
    }
    ```
    If you add or modify this file, rebuild the Codespace container when prompted.

3.  **Place `notes_app.php`:**
    Ensure the `notes_app.php` file (containing the application code) is in the root of your project directory within the Codespace.

4.  **Start the PHP Development Server:**
    Open the terminal in your Codespace (usually `Ctrl+` \` or `Cmd+` \`) and run:
    ```bash
    php -S 0.0.0.0:8000 notes_app.php
    ```
    This will start the PHP built-in web server, listening on port 8000.

5.  **Access the Application:**
    * GitHub Codespaces should automatically forward port 8000. You'll likely see a notification in VS Code to open the application in a browser or a preview tab.
    * Alternatively, go to the "Ports" tab in VS Code, find port 8000, and click the "Open in Browser" (globe) icon.
    * The application will be accessible at a URL like `https://[your-codespace-name]-8000.app.github.dev/notes_app.php`.

## Application Structure

* **`notes_app.php`**: The single file containing all HTML, CSS (via Tailwind CDN), and PHP logic.
    * **PHP Logic (Top of the file):**
        * Database connection (PDO SQLite).
        * Table creation (`notes`) if it doesn't exist.
        * Handling `POST` requests for adding new notes (with PRG pattern).
        * Fetching existing notes for display.
    * **HTML Structure (Bottom of the file):**
        * Form for adding notes.
        * Section for displaying notes.
* **`notes.db`**: The SQLite database file that will be automatically created in the same directory as `notes_app.php` when the script is first run and a note is added or the database is accessed. This file stores the `notes` table.

## Key Features Implemented

* **Add Note:** Users can submit text to be stored as a note.
* **Display Notes:** All stored notes are displayed in reverse chronological order (newest first).
* **Database Persistence:** Notes are stored in an SQLite database.
* **Security Considerations:**
    * Uses prepared statements (`bindParam`) to prevent SQL injection.
    * Uses `htmlspecialchars()` to prevent XSS when displaying notes.
* **User Experience:**
    * Implements the Post/Redirect/Get (PRG) pattern to prevent form resubmission on page refresh.

## Potential Improvements / Future Work

* **Delete Notes:** Add functionality to remove notes.
* **Edit Notes:** Allow users to modify existing notes.
* **User Authentication:** Implement a login system so users can have private notes.
* **AJAX Integration:** Use JavaScript (AJAX/Fetch API) for adding and displaying notes without full page reloads, for a smoother user experience.
* **Enhanced Styling:** Improve the visual design beyond basic Tailwind CSS.
* **Input Validation:** More robust server-side and client-side validation for note content.

## Troubleshooting

* **"Could not find driver" (PDOException):** Ensure the `php-sqlite3` extension is installed and enabled in your PHP environment (see `devcontainer.json`).
* **Permission Denied for `notes.db`:** The directory containing `notes_app.php` must be writable by the web server process. In Codespaces with the PHP built-in server, this is usually not an issue as the server runs as your user.
* **Port Not Forwarding:** Check the "Ports" tab in VS Code and ensure port 8000 is listed and forwarded.

---

This README should provide a good overview for anyone looking to understand or run your `notes_app.php` project in GitHub Codespaces.
