<?php
declare(strict_types=1);

/**
 * Peru IA Fintech (ficticio) — Portal (Landing + Buscador)
 * Implementación segura: evita SQLi/XSS, minimiza logging, agrega headers.
 */

require_once __DIR__ . '/config.php';

// Headers de hardening (ajustar CSP en proyectos reales)
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; base-uri 'self'; frame-ancestors 'none'");
// HSTS solo si HTTPS
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
  header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

// Entrada de búsqueda
$term = trim((string)($_GET['q'] ?? ''));
$term = mb_substr($term, 0, 80); // límite defensivo
$term_norm = mb_strtolower($term);

$pdo = db();

// Logging minimizado (no almacenar IP en claro)
if ($term !== '') {
  $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
  $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
  $ua = mb_substr($ua, 0, 120);
  $ip_hash = hash('sha256', $ip . LOG_SALT);

  $stmt = $pdo->prepare("
    INSERT INTO search_logs(term, term_normalized, ip_hash, user_agent)
    VALUES(:t, :tn, :iph, :ua)
  ");
  $stmt->execute([
    ':t'  => $term,
    ':tn' => $term_norm,
    ':iph'=> $ip_hash,
    ':ua' => $ua
  ]);
}

// Consulta parametrizada (evita SQL Injection)
$results = [];
if ($term !== '') {
  $like = '%' . $term . '%';

  // FIX HY093: placeholders distintos (PDO MySQL con emulación OFF no tolera :q repetido)
  $stmt = $pdo->prepare("
    SELECT id, name, category, short_desc
    FROM services
    WHERE enabled = 1
      AND (name LIKE :q1 OR category LIKE :q2 OR short_desc LIKE :q3)
    ORDER BY name ASC
    LIMIT 20
  ");
  $stmt->execute([
    ':q1' => $like,
    ':q2' => $like,
    ':q3' => $like
  ]);
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  // Catálogo en landing
  $stmt = $pdo->query("
    SELECT id, name, category, short_desc
    FROM services
    WHERE enabled = 1
    ORDER BY name ASC
    LIMIT 12
  ");
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Escape seguro para evitar XSS reflejado
function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>IA Perú Fintech — Portal</title>
  <link rel="stylesheet" href="assets/style.css"/>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <span class="brand-mark">IA</span>
      <span class="brand-name">Perú Fintech</span>
    </div>
    <nav class="nav">
      <a href="#servicios">Servicios</a>
      <a href="#seguridad">Seguridad</a>
      <a href="#contacto">Contacto</a>
    </nav>
  </header>

  <main class="container">
    <section class="hero">
      <div class="hero-left">
        <h1>Servicios financieros potenciados por IA, con enfoque en seguridad y confianza.</h1>
        <p class="lead">
          Portal ficticio para fines académicos: portada sobria, buscador funcional y diseño orientado a negocio.
          Implementación segura: consultas parametrizadas, salida escapada y evidencia minimizada.
        </p>

        <form class="search" method="get" action="index.php" aria-label="Buscador de servicios">
          <label for="q" class="sr-only">Buscar servicios</label>
          <input id="q" name="q" type="text"
                 placeholder="Buscar: crédito, fraude, scoring, onboarding..."
                 value="<?= e($term) ?>" autocomplete="off"/>
          <button type="submit">Buscar</button>
        </form>

        <?php if ($term !== ''): ?>
          <div class="search-meta">
            Resultados para: <strong><?= e($term) ?></strong> (<?= count($results) ?>)
          </div>
        <?php endif; ?>

        <div class="cards" id="servicios">
          <?php foreach ($results as $r): ?>
            <article class="card">
              <div class="card-top">
                <div class="pill"><?= e((string)$r['category']) ?></div>
              </div>
              <h3><?= e((string)$r['name']) ?></h3>
              <p><?= e((string)$r['short_desc']) ?></p>
            </article>
          <?php endforeach; ?>
        </div>

        <?php if ($term !== '' && count($results) === 0): ?>
          <div class="empty">
            No se encontraron servicios para <strong><?= e($term) ?></strong>. Pruebe con otro término.
          </div>
        <?php endif; ?>
      </div>

      <div class="hero-right">
        <figure class="poster">
          <?php if (file_exists(__DIR__ . '/assets/ia-peru-fintech.jpg')): ?>
            <img src="assets/ia-peru-fintech.jpg" alt="IA Perú Fintech (imagen referencial)"/>
          <?php else: ?>
            <div class="missing-img">Coloque la imagen en <span class="mono">assets/ia-peru-fintech.jpg</span></div>
          <?php endif; ?>
          <figcaption>Imagen referencial incluida en la portada.</figcaption>
        </figure>
      </div>
    </section>

    <section class="section" id="seguridad">
      <h2>Controles de seguridad implementados (resumen)</h2>
      <ul class="list">
        <li>Protección contra SQL Injection mediante consultas preparadas (PDO).</li>
        <li>Protección contra XSS reflejado mediante escaping de salida (htmlspecialchars).</li>
        <li>Headers de hardening: CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy.</li>
        <li>Logging minimizado: no se almacena IP en claro (hash + salt).</li>
      </ul>
      <p class="note">
        Nota académica: use un entorno controlado y nunca exponga sistemas intencionalmente vulnerables en Internet.
      </p>
    </section>

    <section class="section" id="contacto">
      <h2>Contacto</h2>
      <div class="contact">
        <div>
          <div class="k">Correo</div>
          <div class="v">seguridad@ia-peru-fintech.local</div>
        </div>
        <div>
          <div class="k">Teléfono</div>
          <div class="v">(+51) 01 700-0000</div>
        </div>
        <div>
          <div class="k">Horario</div>
          <div class="v">L–V 09:00–18:00</div>
        </div>
      </div>
    </section>

    <footer class="footer">
      <div>© <?= date('Y') ?> IA Perú Fintech (ficticio). Uso académico.</div>
      <div class="small">Versión demo segura • PHP + MySQL</div>
    </footer>
  </main>
</body>
</html>
