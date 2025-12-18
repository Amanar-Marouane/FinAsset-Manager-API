# Installation steps

1. Clone the repository:

   ```bash
   git clone https://github.com/Amanar-Marouane/FinAsset-Manager-API.git
    cd FinAsset-Manager-API
    ```
2. Install dependencies using Composer:
    ```bash
    composer install
    ```
3. Copy the example environment file and configure your environment variables:
    ```bash
    cp .env.example .env
    ```
   Update the `.env` file with your database credentials and other necessary configurations.

4. Generate an application key:
    ```bash
    php artisan key:generate
    ```

5. Generate a JWT secret key:
    ```bash
    php artisan jwt:secret
    ```
    Output key to be added to `.env` file.

6. Run database migrations and seed the default user:
    ```bash
    php artisan migrate
    ```

7. Start the development server:
    ```bash
    php artisan serve
    ```