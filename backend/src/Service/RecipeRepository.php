<?php

namespace App\Service;

use PDO;
use RuntimeException;

class RecipeRepository
{
    private ?PDO $connection = null;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        $statement = $this->getConnection()->query(
            'SELECT id, title, description, created_at FROM recipes ORDER BY created_at DESC, id DESC'
        );

        return $statement->fetchAll();
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $title = trim((string) ($payload['title'] ?? ''));
        $description = trim((string) ($payload['description'] ?? ''));

        if ($title === '') {
            throw new RuntimeException('Le titre est obligatoire.');
        }

        $statement = $this->getConnection()->prepare(
            'INSERT INTO recipes (title, description) VALUES (:title, :description)'
        );
        $statement->execute([
            'title' => $title,
            'description' => $description,
        ]);

        $recipeId = (int) $this->getConnection()->lastInsertId();

        $select = $this->getConnection()->prepare(
            'SELECT id, title, description, created_at FROM recipes WHERE id = :id'
        );
        $select->execute(['id' => $recipeId]);

        $recipe = $select->fetch();

        if ($recipe === false) {
            throw new RuntimeException('La recette créée est introuvable.');
        }

        return $recipe;
    }

    private function getConnection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $host = $this->getEnv('DB_HOST', 'mysql-service');
        $port = $this->getEnv('DB_PORT', '3306');
        $database = $this->getEnv('DB_NAME', 'recipesdb');
        $user = $this->getEnv('DB_USER', 'maelic');
        $password = $this->getEnv('DB_PASSWORD', 'recipespassword');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);

        $this->connection = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->initializeSchema();

        return $this->connection;
    }

    private function initializeSchema(): void
    {
        $this->connection?->exec(
            'CREATE TABLE IF NOT EXISTS recipes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $count = (int) $this->connection?->query('SELECT COUNT(*) FROM recipes')->fetchColumn();

        if ($count > 0) {
            return;
        }

        $seed = $this->connection?->prepare(
            'INSERT INTO recipes (title, description) VALUES (:title, :description)'
        );

        $recipes = [
            [
                'title' => 'Tarte aux pommes',
                'description' => 'Dessert simple avec pommes, pâte brisée et cannelle.',
            ],
            [
                'title' => 'Salade grecque',
                'description' => 'Tomates, concombre, feta, olives et huile d\'olive.',
            ],
        ];

        foreach ($recipes as $recipe) {
            $seed->execute($recipe);
        }
    }

    private function getEnv(string $name, string $default): string
    {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }
}
