<?php

declare(strict_types=1);

use App\Core\Autoloader;
use App\Core\Database;

$root = dirname(__DIR__);
$credentialsDirectory = $root . '/storage/private';
$credentialsPath = $credentialsDirectory . '/admin_account.json';
$credentialsCreated = false;

require_once $root . '/src/Core/Autoloader.php';
Autoloader::register(['App\\' => $root . '/src']);

try {
    if (is_file($credentialsPath)) {
        throw new RuntimeException(
            'The private admin credential file already exists. No account was changed.'
        );
    }

    if (!is_dir($credentialsDirectory) && !mkdir($credentialsDirectory, 0700, true)) {
        throw new RuntimeException('The private credentials directory could not be created.');
    }

    $username = 'admin_' . bin2hex(random_bytes(4));
    $email = $username . '@sheepy.local';
    $password = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
    $database = new Database(require $root . '/config/database.php');

    $userId = $database->transaction(static function (PDO $connection) use (
        $username,
        $email,
        $password,
        $credentialsPath,
        &$credentialsCreated
    ): int {
        $statement = $connection->prepare(
            "INSERT INTO users (username, email, password_hash, phone, role)
             VALUES (:username, :email, :password_hash, NULL, 'admin')"
        );
        $statement->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $payload = json_encode([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'created_at' => date(DATE_ATOM),
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $handle = fopen($credentialsPath, 'x');

        if ($handle === false) {
            throw new RuntimeException('The private credential file could not be created.');
        }

        $credentialsCreated = true;

        try {
            if (fwrite($handle, $payload . PHP_EOL) !== strlen($payload . PHP_EOL)) {
                throw new RuntimeException('The complete private credential file could not be written.');
            }
        } finally {
            fclose($handle);
        }

        @chmod($credentialsPath, 0600);

        return (int) $connection->lastInsertId();
    });

    fwrite(STDOUT, sprintf(
        "Admin account created successfully (user ID %d). Credentials are in the ignored private file.%s",
        $userId,
        PHP_EOL
    ));
} catch (Throwable $exception) {
    if ($credentialsCreated && is_file($credentialsPath)) {
        unlink($credentialsPath);
    }

    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    exit(1);
}
