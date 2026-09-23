<?php
/**
 * check_documento.php
 *
 * Validacion basica de documentos peruanos:
 * - DNI: valida formato de 8 digitos.
 * - RUC: valida formato, prefijo 10/20 y digito verificador SUNAT.
 *
 * IMPORTANTE:
 * - La validacion de DNI realizada aqui es solo estructural/formato.
 *   Confirmar que un DNI existe o pertenece a una persona requiere
 *   consultar una fuente oficial autorizada, como RENIEC.
 * - La validacion de RUC incluye el calculo del digito verificador,
 *   pero no confirma que el RUC exista, este activo o tenga una
 *   condicion tributaria especifica.
 *
 * DATOS SINTETICOS DE PRUEBA:
 * - DNI: 12345678
 * - RUC: 20123456786
 *
 * Los valores anteriores deben usarse solo para pruebas de software.
 */

declare(strict_types=1);

/**
 * Elimina espacios, guiones y cualquier caracter no numerico.
 */
function normalizarDocumento(string $documento): string
{
    return preg_replace('/\D+/', '', $documento) ?? '';
}

/**
 * Valida el formato basico de un DNI peruano.
 *
 * Regla:
 * - Exactamente 8 digitos.
 */
function validarDni(string $dni): bool
{
    $dni = normalizarDocumento($dni);

    return preg_match('/^\d{8}$/', $dni) === 1;
}

/**
 * Calcula el digito verificador de un RUC peruano.
 *
 * Se utilizan los primeros 10 digitos con los pesos:
 * 5, 4, 3, 2, 7, 6, 5, 4, 3, 2
 */
function calcularDigitoRuc(string $primerosDiezDigitos): ?int
{
    if (preg_match('/^\d{10}$/', $primerosDiezDigitos) !== 1) {
        return null;
    }

    $pesos = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    $suma = 0;

    for ($i = 0; $i < 10; $i++) {
        $suma += ((int) $primerosDiezDigitos[$i]) * $pesos[$i];
    }

    $resto = $suma % 11;
    $digito = 11 - $resto;

    if ($digito === 10) {
        $digito = 0;
    } elseif ($digito === 11) {
        $digito = 1;
    }

    return $digito;
}

/**
 * Valida un RUC peruano.
 *
 * Reglas aplicadas:
 * - Exactamente 11 digitos.
 * - Debe iniciar por 10 o 20.
 * - El ultimo digito debe coincidir con el digito verificador.
 */
function validarRuc(string $ruc): bool
{
    $ruc = normalizarDocumento($ruc);

    if (preg_match('/^(?:10|20)\d{9}$/', $ruc) !== 1) {
        return false;
    }

    $base = substr($ruc, 0, 10);
    $digitoIngresado = (int) $ruc[10];
    $digitoCalculado = calcularDigitoRuc($base);

    return $digitoCalculado !== null && $digitoIngresado === $digitoCalculado;
}

/**
 * Identifica y valida el tipo de documento.
 */
function validarDocumento(string $documento): array
{
    $numero = normalizarDocumento($documento);

    if (strlen($numero) === 8) {
        return [
            'tipo' => 'DNI',
            'numero' => $numero,
            'valido' => validarDni($numero),
            'nivel_validacion' => 'FORMATO',
            'mensaje' => validarDni($numero)
                ? 'DNI con formato valido de 8 digitos.'
                : 'DNI invalido.'
        ];
    }

    if (strlen($numero) === 11) {
        $valido = validarRuc($numero);

        return [
            'tipo' => 'RUC',
            'numero' => $numero,
            'valido' => $valido,
            'nivel_validacion' => 'FORMATO_Y_DIGITO_VERIFICADOR',
            'mensaje' => $valido
                ? 'RUC valido por formato y digito verificador.'
                : 'RUC invalido.'
        ];
    }

    return [
        'tipo' => 'DESCONOCIDO',
        'numero' => $numero,
        'valido' => false,
        'nivel_validacion' => 'NINGUNO',
        'mensaje' => 'El documento no corresponde al formato esperado de DNI o RUC.'
    ];
}

/*
 * Ejemplos de ejecucion:
 *
 * php check_documento.php 12345678
 * php check_documento.php 20123456786
 */
if (PHP_SAPI === 'cli' && isset($argv[1])) {
    $resultado = validarDocumento($argv[1]);

    echo json_encode(
        $resultado,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    ) . PHP_EOL;
}
