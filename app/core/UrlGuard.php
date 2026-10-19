<?php
declare(strict_types=1);

/**
 * Filtre des URLs saisies dans le back-office avant de les écrire dans un attribut href / src.
 *
 * htmlspecialchars() empêche de sortir de l'attribut, mais pas d'y mettre « javascript:… » :
 * seuls les schémas de la liste blanche et les chemins relatifs sont donc acceptés.
 */
final class UrlGuard
{
    /** Schémas autorisés dans un lien (bloque javascript:, data:, vbscript:…). */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    /**
     * Retourne l'URL si elle est sûre, sinon une chaîne vide.
     *
     * @param mixed $url URL brute (saisie du back-office ou valeur de la BDD).
     * @return string URL d'origine (sans espaces autour) ou chaîne vide.
     */
    public static function safe(mixed $url): string
    {
        $url = is_scalar($url) ? trim((string)$url) : '';

        // Les navigateurs ignorent tabulations et retours à la ligne dans un schéma (« java\tscript: ») :
        // le contrôle se fait donc sur une copie débarrassée de tous les caractères de contrôle et espaces
        $compact = (string)preg_replace('/[\x00-\x20]+/', '', $url);

        if ($compact === '') {
            return '';
        }

        // « //site.tld » (URL relative au protocole) et « /\site.tld » mènent hors du site : refusés
        if (str_starts_with($compact, '//') || str_starts_with($compact, '/\\')) {
            return '';
        }

        // Aucun schéma détecté : chemin relatif (« /assets/… », « #ancre », « page.php »)
        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $compact, $match) !== 1) {
            return $url;
        }

        return in_array(strtolower($match[1]), self::ALLOWED_SCHEMES, true) ? $url : '';
    }
}
