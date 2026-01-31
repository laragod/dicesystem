<?php
declare(strict_types=1);

namespace Laragod\DiceSystem\Seed;

use RuntimeException;
use Laragod\DiceSystem\Contract\SeedStoreInterface;

class FileSeedStore implements SeedStoreInterface
{
    public function __construct(protected string $directory)
    {
        if (!is_dir($this->directory) && !@mkdir($this->directory, 0777, true) && !is_dir($this->directory)) {
            throw new RuntimeException("Cannot create seed directory: {$this->directory}");
        }
    }

    public function get(string $scope): ?int
    {
        $path = $this->pathFor($scope);
        if (!is_file($path)) {
            return null;
        }

        $raw = trim((string) @file_get_contents($path));
        if ($raw === '' || !is_numeric($raw)) {
            return null;
        }

        return (int) $raw;
    }

    public function put(string $scope, int $seed): void
    {
        $path = $this->pathFor($scope);
        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            throw new RuntimeException("Cannot open seed file: {$path}");
        }

        try {
            if (!flock($fp, LOCK_EX)) {
                throw new RuntimeException("Cannot lock seed file: {$path}");
            }
            ftruncate($fp, 0);
            fwrite($fp, (string)$seed);
            fflush($fp);
            flock($fp, LOCK_UN);
        } finally {
            fclose($fp);
        }
    }

    public function forget(string $scope): void
    {
        $path = $this->pathFor($scope);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    protected function pathFor(string $scope): string
    {
        // hash to avoid invalid filenames and leaking identifiers
        $name = hash('sha256', $scope) . '.seed';
        return rtrim($this->directory, '/\\') . DIRECTORY_SEPARATOR . $name;
    }
}
