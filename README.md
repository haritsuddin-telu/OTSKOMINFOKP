# OTS Kominfo - One Time Secret & WhatsApp Service

A secure one-time secret sharing application with WhatsApp integration, built with Laravel and Node.js.

## Prerequisites

Before interacting with this project, ensure you have the following installed:

- **PHP**: ^8.1
- **Composer**: [Download Composer](https://getcomposer.org/)
- **Node.js**: [Download Node.js](https://nodejs.org/) (LTS recommended)
- **MySQL**: (via XAMPP or standalone)

## Project Setup

Follow these steps to set up the project on your local machine.

### 1. Clone the Repository

```bash
git clone https://github.com/haritsuddin-telu/OTSKOMINFOKP.git
cd OTSKOMINFOKP
```

### 2. Backend Setup (Laravel)

1.  **Install PHP Dependencies**:
    ```bash
    composer install
    ```

2.  **Environment Configuration**:
    Copy the example environment file and configure your database settings.
    ```bash
    cp .env.example .env
    ```
    Open `.env` and set your database credentials:
    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=ots_kominfo
    DB_USERNAME=root
    DB_PASSWORD=
    ```

3.  **Generate Application Key**:
    ```bash
    php artisan key:generate
    ```

4.  **Run Migrations**:
    Ensure your MySQL database (`ots_kominfo`) exists, then run:
    ```bash
    php artisan migrate
    ```

5.  **Link Storage**:
    ```bash
    php artisan storage:link
    ```

6.  **Install & Build Frontend Assets**:
    ```bash
    npm install
    npm run build
    ```

### 3. WhatsApp Service Setup

The WhatsApp integration runs as a separate Node.js service.

1.  **Navigate to the Service Directory**:
    ```bash
    cd whatsapp-service
    ```

2.  **Install Dependencies**:
    ```bash
    npm install
    ```

## Running the Application

You need to run both the Laravel backend and the WhatsApp service.

### 1. Start the Laravel Server
Open a terminal in the project root:
```bash
php artisan serve
```
The app will be accessible at `http://localhost:8000`.

### 2. Start the WhatsApp Service
Open a **new** terminal, navigate to `whatsapp-service`, and start the service:
```bash
cd whatsapp-service
node index.js
```

### 3. Connect WhatsApp (Web Interface)
Instead of scanning the terminal, you can now manage the connection via the browser:
1.  Go to `http://localhost:8000/whatsapp/connect`.
2.  Wait for the **QR Code** to appear (5-15 seconds for engine startup).
3.  Scan with your WhatsApp (Linked Devices).
4.  Once connected, you can see the status "WhatsApp Terhubung" on the main form.
5.  To disconnect, click the red **Putuskan Sambungan** button on the connect page.

## Troubleshooting

-   **Database Errors**: Ensure your MySQL server is running and the database name in `.env` matches your actual database.
-   **WhatsApp Issues**: 
    -   If the service hangs, stop the node process and restart it.
    -   If you cannot logout via web, delete the `.wwebjs_auth` folder inside `whatsapp-service` manually.
