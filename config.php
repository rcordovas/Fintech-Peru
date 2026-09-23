<?php
declare(strict_types=1);

/**
 * Configuración de base de datos (PDO) — ajuste según su entorno.
 * Recomendación: use variables de entorno en despliegues reales.
 */

const DB_HOST = '98.81.232.2';
const DB_NAME = 'ia_peru_fintech';
const DB_USER = 'root';
const DB_PASS = 'THGUGAUAooiuyas';

// Salt para hashing (demo). Cambiar en despliegues reales.
const LOG_SALT = 'ACADEMIC_DEMO_CHANGE_ME_2026';

function db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;

  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
  $pdo = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
  return $pdo;
}
