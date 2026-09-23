# IA Perú Fintech — Portal (PHP + MySQL) [Demo académica]

Proyecto de **portada** + **buscador** de servicios (catálogo) para fintech ficticia. Implementación con **buenas prácticas**:
- Consultas parametrizadas (PDO) para evitar SQL Injection
- Escaping de salida para evitar XSS reflejado
- Logging minimizado (hash de IP con salt)
- Headers de hardening (CSP, XFO, XCTO, Referrer-Policy)

## Requisitos
- PHP 8.0+
- MySQL 8.x (o MariaDB compatible)

## Instalación
1) Importe el esquema en MySQL:
- Ejecute `init.sql`.

2) Configure `config.php`:
- Ajuste DB_HOST/DB_USER/DB_PASS si corresponde.

3) Ejecute el sitio:
- PHP built-in:
  ```bash
  cd fintech_ia_peru_portal
  php -S 127.0.0.1:8080
  ```
  Abra: http://127.0.0.1:8080

## Uso
- Sin búsqueda muestra el catálogo (12 servicios).
- Con búsqueda `?q=...` muestra resultados y el término buscado (escapado).

## Imagen
La portada usa `assets/ia-peru-fintech.jpg`. Esta carpeta ya incluye la imagen que compartiste (si estuvo disponible al generar el ZIP).
Para cualquier consulta escribe a racscord@gmail.com
