<?php
/**
 * Connexion à la base de données PostgreSQL
 * Utilise PDO pour une connexion sécurisée
 */

require_once __DIR__ . '/config.php';

class Database
{
    private static ?PDO $instance = null;

    /**
     * Obtenir l'instance de connexion (Singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "pgsql:host=%s;port=%s;dbname=%s",
                    DB_HOST,
                    DB_PORT,
                    DB_NAME
                );

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false
                ]);

                // Configuration de l'encodage UTF-8
                self::$instance->exec("SET NAMES 'UTF8'");

            } catch (PDOException $e) {
                if (DEBUG_MODE) {
                    die("Erreur de connexion à la base de données : " . $e->getMessage());
                } else {
                    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
                }
            }
        }

        return self::$instance;
    }

    /**
     * Empêcher le clonage de l'instance
     */
    private function __clone()
    {
    }

    /**
     * Empêcher la désérialisation
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Fonction helper pour obtenir la connexion
 */
function db(): PDO
{
    return Database::getInstance();
}

/**
 * Exécuter une requête SELECT et retourner tous les résultats
 */
function dbFetchAll(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Exécuter une requête SELECT et retourner un seul résultat
 */
function dbFetchOne(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Exécuter une requête INSERT, UPDATE ou DELETE
 */
function dbExecute(string $sql, array $params = []): int
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Obtenir le dernier ID inséré
 */
function dbLastInsertId(string $sequenceName = null): string
{
    return db()->lastInsertId($sequenceName);
}

/**
 * Compter le nombre de résultats
 */
function dbCount(string $table, string $where = '', array $params = []): int
{
    $sql = "SELECT COUNT(*) as count FROM {$table}";
    if ($where) {
        $sql .= " WHERE {$where}";
    }
    $result = dbFetchOne($sql, $params);
    return (int) ($result['count'] ?? 0);
}
