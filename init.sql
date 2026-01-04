-- IA Perú Fintech (ficticio) — Esquema MySQL (demo académica)
CREATE DATABASE IF NOT EXISTS ia_peru_fintech CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE ia_peru_fintech;

CREATE TABLE IF NOT EXISTS services (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  category VARCHAR(60) NOT NULL,
  short_desc VARCHAR(280) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_services_name (name),
  KEY idx_services_category (category)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS search_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  term VARCHAR(80) NOT NULL,
  term_normalized VARCHAR(80) NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  user_agent VARCHAR(120) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_search_created (created_at),
  KEY idx_search_term (term_normalized)
) ENGINE=InnoDB;

INSERT INTO services (name, category, short_desc, enabled) VALUES
('Crédito Inteligente', 'Crédito', 'Originación y preaprobación basada en modelos de riesgo y capacidad de pago.', 1),
('Scoring Alternativo con IA', 'Riesgo', 'Evaluación de riesgo con variables alternativas y explicabilidad para auditoría.', 1),
('Detección de Fraude en Tiempo Real', 'Fraude', 'Monitoreo transaccional con reglas + modelos para alertas de alta precisión.', 1),
('Onboarding Digital Seguro', 'Identidad', 'Verificación de identidad con señales de fraude y validación biométrica (ficticio).', 1),
('Motor de Recomendaciones Financieras', 'Personalización', 'Recomendaciones para ahorro, inversión y productos basadas en comportamiento.', 1),
('Prevención de Lavado (AML) Asistida', 'Cumplimiento', 'Priorización de alertas AML con reducción de falsos positivos (ficticio).', 1),
('Asistente Virtual de Atención', 'Atención', 'Chat asistido por IA con flujos controlados y auditoría de respuestas.', 1),
('Monitoreo de Riesgo de Proveedores', 'Terceros', 'Señales de riesgo y continuidad sobre proveedores críticos (ficticio).', 1),
('Optimización de Cobranza', 'Cobranza', 'Segmentación y estrategias de contacto basadas en propensión de pago.', 1),
('Analítica de Seguridad de Canales', 'Seguridad', 'Detección de anomalías en accesos y sesiones en web y app.', 1);
