<?php
/**
 * check_emisor.php
 *
 * Valida de forma basica una tarjeta por su IIN/BIN y determina
 * si corresponde a Visa o Mastercard.
 *
 * IMPORTANTE:
 * - No consulta redes de pago ni bancos.
 * - Solo valida el patron del IIN/BIN y el digito de control Luhn.
 * - No almacene ni registre el PAN completo en logs.
 *
 * Tarjetas sinteticas/de prueba:
 * - Visa:       4111111111111111
 * - Mastercard: 5555555555554444
 */

declare(strict_types=1);

/**
 * Elimina espacios, guiones y otros caracteres no numericos.
 */
function normalizarTarjeta(string $tarjeta): string
{
    return preg_replace('/\D+/', '', $tarjeta) ?? '';
}

/**
 * Valida el numero con el algoritmo de Luhn.
 */
function validarLuhn(string $numero): bool
{
    if ($numero === '' || !ctype_digit($numero)) {
        return false;
    }

    $suma = 0;
    $paridad = strlen($numero) % 2;

    for ($i = 0, $len = strlen($numero); $i < $len; $i++) {
        $digito = (int) $numero[$i];

        if (($i % 2) === $paridad) {
            $digito *= 2;
            if ($digito > 9) {
                $digito -= 9;
            }
        }

        $suma += $digito;
    }

    return ($suma % 10) === 0;
}

/**
 * Determina el emisor/marca usando rangos IIN/BIN conocidos.
 *
 * Visa:
 *   - Prefijo 4
 *
 * Mastercard:
 *   - 51 a 55
 *   - 2221 a 2720
 */
function detectarEmisor(string $numero): string
{
    if ($numero === '' || !ctype_digit($numero)) {
        return 'DESCONOCIDO';
    }

    // Visa
    if (str_starts_with($numero, '4')) {
        return 'VISA';
    }

    // Mastercard: rango clasico 51-55
    if (strlen($numero) >= 2) {
        $prefijo2 = (int) substr($numero, 0, 2);

        if ($prefijo2 >= 51 && $prefijo2 <= 55) {
            return 'MASTERCARD';
        }
    }

    // Mastercard: rango 2221-2720
    if (strlen($numero) >= 4) {
        $prefijo4 = (int) substr($numero, 0, 4);

        if ($prefijo4 >= 2221 && $prefijo4 <= 2720) {
            return 'MASTERCARD';
        }
    }

    return 'DESCONOCIDO';
}

/**
 * Devuelve solo una representacion enmascarada.
 */
function enmascararTarjeta(string $numero): string
{
    $len = strlen($numero);

    if ($len <= 4) {
        return str_repeat('*', $len);
    }

    return str_repeat('*', $len - 4) . substr($numero, -4);
}

/**
 * Valida la tarjeta y devuelve el resultado.
 */
function validarTarjeta(string $tarjeta): array
{
    $numero = normalizarTarjeta($tarjeta);

    if (strlen($numero) < 12 || strlen($numero) > 19) {
        return [
            'valida' => false,
            'emisor' => 'DESCONOCIDO',
            'tarjeta' => enmascararTarjeta($numero),
            'mensaje' => 'Longitud de tarjeta invalida.'
        ];
    }

    $emisor = detectarEmisor($numero);
    $luhn = validarLuhn($numero);

    return [
        'valida' => $luhn && $emisor !== 'DESCONOCIDO',
        'emisor' => $emisor,
        'tarjeta' => enmascararTarjeta($numero),
        'mensaje' => $luhn
            ? ($emisor !== 'DESCONOCIDO'
                ? 'Tarjeta valida por formato, BIN/IIN y Luhn.'
                : 'Luhn valido, pero el emisor no es Visa ni Mastercard.')
            : 'Numero de tarjeta invalido segun Luhn.'
    ];
}

/*
 * Ejecucion por consola:
 *   php check_emisor.php 4111111111111111
 */
if (PHP_SAPI === 'cli' && isset($argv[1])) {
    $resultado = validarTarjeta($argv[1]);
    echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
