<?php

namespace App\Db;

class Mysql 
{
    private $db_name;
    private $db_user;
    private $db_password;
    private $db_port;
    private $db_host;
    private $pdo = null;
    private static $_instance = null;

    private function __construct()
    {
        // Je charge les variables d'environnement depuis le fichier .env
        $envFile = _ROOTPATH_.'/.env';
        
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            $lines = explode("\n", $envContent);
            
            $envVars = [];
            foreach ($lines as $line) {
                $line = trim($line);
                // J'ignore les lignes vides et les commentaires
                if (!empty($line) && strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                    list($key, $value) = explode('=', $line, 2);
                    $envVars[trim($key)] = trim($value);
                }
            }
            
            // J'assigne les valeurs avec des valeurs par défaut si nécessaire
            $this->db_name = $envVars['db_name'] ?? '';
            $this->db_user = $envVars['db_user'] ?? '';
            $this->db_password = $envVars['db_password'] ?? '';
            $this->db_port = $envVars['db_port'] ?? '3306';
            $this->db_host = $envVars['db_host'] ?? 'localhost';
            
        } else {
            throw new \Exception("Fichier .env non trouvé à l'emplacement: " . $envFile);
        }
    }

    public static function getInstance(): self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Mysql();
        }
        return self::$_instance;
    }

    public function getPDO(): \PDO
    {
        if (is_null($this->pdo)) {
            try {
                $dsn = 'mysql:dbname=' . $this->db_name . ';charset=utf8;host=' . $this->db_host . ';port=' . $this->db_port;
                $this->pdo = new \PDO($dsn, $this->db_user, $this->db_password);
                $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                throw new \PDOException("Erreur de connexion à la base de données: " . $e->getMessage());
            }
        }
        return $this->pdo;
    }
}