<?php
declare(strict_types=1);

/**
 * Point de sortie centralisé des réponses HTTP de l'API.
 *
 * Toutes les réponses JSON passent par Response::json() : code de statut,
 * en-têtes et encodage sont ainsi garantis identiques partout, et le script
 * se termine systématiquement après l'envoi (aucune sortie parasite possible).
 */
final class Response
{
    /**
     * Options d'encodage JSON communes.
     * - UNESCAPED_UNICODE / SLASHES : charge utile lisible (accents, URLs) ;
     * - HEX_TAG / HEX_AMP : neutralise « < », « > » et « & » si la réponse était un jour injectée dans du HTML ;
     * - THROW_ON_ERROR : une donnée non encodable lève une exception au lieu de renvoyer « false » silencieusement.
     */
    private const JSON_FLAGS = JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_AMP
        | JSON_THROW_ON_ERROR;

    /**
     * Envoie une réponse JSON puis termine le script.
     *
     * @param array<string, mixed> $data   Données à sérialiser.
     * @param int                  $status Code HTTP (200, 405, 503…).
     * @param int                  $maxAge Durée de cache navigateur en secondes (0 = aucune mise en cache).
     * @return never
     */
    public static function json(array $data, int $status = 200, int $maxAge = 0): never
    {
        try {
            $body = json_encode($data, self::JSON_FLAGS);
        } catch (JsonException $e) {
            // Échec d'encodage : on journalise côté serveur sans exposer le détail au client
            error_log('Response::json — encodage impossible : ' . $e->getMessage());
            $status = 500;
            $body = '{"error":"Erreur interne du serveur."}';
        }

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header($maxAge > 0 ? "Cache-Control: public, max-age={$maxAge}" : 'Cache-Control: no-store');

        echo $body;
        exit;
    }
}
