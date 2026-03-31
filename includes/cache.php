<?php
/**
 * Système de cache fichier
 * Guerre Iran - Optimisation des performances
 */

class Cache
{
    private static ?Cache $instance = null;
    private string $cachePath;
    private bool $enabled;
    private int $defaultTtl;

    private function __construct()
    {
        $this->cachePath = defined('CACHE_PATH') ? CACHE_PATH : BASE_PATH . '/cache';
        $this->enabled = defined('CACHE_ENABLED') ? CACHE_ENABLED : true;
        $this->defaultTtl = defined('CACHE_TTL') ? CACHE_TTL : 3600;

        if ($this->enabled && !is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Obtenir l'instance singleton
     */
    public static function getInstance(): Cache
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Générer une clé de cache unique
     */
    private function generateKey(string $key): string
    {
        return md5($key);
    }

    /**
     * Obtenir le chemin du fichier cache
     */
    private function getFilePath(string $key): string
    {
        $hashedKey = $this->generateKey($key);
        $subDir = substr($hashedKey, 0, 2);
        $dirPath = $this->cachePath . '/' . $subDir;

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        return $dirPath . '/' . $hashedKey . '.cache';
    }

    /**
     * Récupérer une valeur du cache
     */
    public function get(string $key, $default = null)
    {
        if (!$this->enabled) {
            return $default;
        }

        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return $default;
        }

        $content = file_get_contents($filePath);
        $data = unserialize($content);

        if ($data === false) {
            $this->delete($key);
            return $default;
        }

        if ($data['expires'] !== 0 && $data['expires'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Stocker une valeur dans le cache
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $ttl = $ttl ?? $this->defaultTtl;
        $expires = $ttl > 0 ? time() + $ttl : 0;

        $data = [
            'value' => $value,
            'expires' => $expires,
            'created' => time()
        ];

        $filePath = $this->getFilePath($key);
        return file_put_contents($filePath, serialize($data), LOCK_EX) !== false;
    }

    /**
     * Vérifier si une clé existe dans le cache
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Supprimer une clé du cache
     */
    public function delete(string $key): bool
    {
        $filePath = $this->getFilePath($key);
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return true;
    }

    /**
     * Supprimer les clés correspondant à un pattern
     */
    public function deletePattern(string $pattern): int
    {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cachePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $content = file_get_contents($file->getPathname());
                $data = @unserialize($content);
                if ($data && isset($data['key']) && fnmatch($pattern, $data['key'])) {
                    unlink($file->getPathname());
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Vider tout le cache
     */
    public function flush(): bool
    {
        if (!is_dir($this->cachePath)) {
            return true;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cachePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                unlink($file->getPathname());
            } else {
                rmdir($file->getPathname());
            }
        }

        return true;
    }

    /**
     * Nettoyer les entrées expirées
     */
    public function cleanup(): int
    {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cachePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $content = file_get_contents($file->getPathname());
                $data = @unserialize($content);
                if ($data && $data['expires'] !== 0 && $data['expires'] < time()) {
                    unlink($file->getPathname());
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Récupérer ou calculer une valeur (cache-aside pattern)
     */
    public function remember(string $key, callable $callback, ?int $ttl = null)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Invalider le cache pour une table spécifique
     */
    public function invalidateTable(string $table): void
    {
        $prefix = "db:{$table}:";
        $this->deleteByPrefix($prefix);
    }

    /**
     * Supprimer les clés commençant par un préfixe
     */
    private function deleteByPrefix(string $prefix): int
    {
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cachePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                unlink($file->getPathname());
                $count++;
            }
        }

        return $count;
    }

    /**
     * Obtenir des statistiques du cache
     */
    public function getStats(): array
    {
        $stats = [
            'enabled' => $this->enabled,
            'path' => $this->cachePath,
            'files' => 0,
            'size' => 0,
            'expired' => 0
        ];

        if (!is_dir($this->cachePath)) {
            return $stats;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->cachePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $stats['files']++;
                $stats['size'] += $file->getSize();

                $content = file_get_contents($file->getPathname());
                $data = @unserialize($content);
                if ($data && $data['expires'] !== 0 && $data['expires'] < time()) {
                    $stats['expired']++;
                }
            }
        }

        return $stats;
    }
}

/**
 * Fonction helper pour accéder au cache
 */
function cache(): Cache
{
    return Cache::getInstance();
}

/**
 * Raccourci pour récupérer une valeur du cache
 */
function cacheGet(string $key, $default = null)
{
    return cache()->get($key, $default);
}

/**
 * Raccourci pour stocker une valeur dans le cache
 */
function cacheSet(string $key, $value, ?int $ttl = null): bool
{
    return cache()->set($key, $value, $ttl);
}

/**
 * Raccourci pour le pattern remember
 */
function cacheRemember(string $key, callable $callback, ?int $ttl = null)
{
    return cache()->remember($key, $callback, $ttl);
}

/**
 * Raccourci pour invalider le cache d'une table
 */
function cacheInvalidate(string $table): void
{
    cache()->invalidateTable($table);
}

/**
 * Invalider tout le cache (après modifications)
 */
function cacheFlush(): void
{
    cache()->flush();
}

/**
 * Invalider le cache de la configuration
 */
function cacheInvalidateConfig(): void
{
    cache()->delete('config:all');
}
