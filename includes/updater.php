<?php
/**
 * Module : Mise à jour automatique depuis GitHub
 *
 * Ce module fait croire à WordPress que le plugin vient de l'API officielle
 * et lui sert les infos depuis GitHub Releases à la place.
 *
 * USAGE :
 * Modifier les 2 constantes ci-dessous avec ton username + nom du repo GitHub.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ─────────────────────────────────────
   ⚠️ CONFIGURATION — À MODIFIER
───────────────────────────────────── */
define( 'RR_GITHUB_USER', 'TON_USERNAME_GITHUB' );  // ex: 'jean-dupont'
define( 'RR_GITHUB_REPO', 'restaurant-reservation' ); // ex: 'restaurant-reservation'

/* ─────────────────────────────────────
   1. INTERCEPTE LA VÉRIFICATION DES MAJ
───────────────────────────────────── */
add_filter( 'site_transient_update_plugins', 'rr_check_for_update' );

function rr_check_for_update( $transient ) {
    if ( empty( $transient->checked ) ) return $transient;
    if ( RR_GITHUB_USER === 'TON_USERNAME_GITHUB' ) return $transient; // pas configuré

    $plugin_slug = plugin_basename( RR_DIR . 'restaurant-reservation.php' );
    $current     = RR_VERSION;

    $remote = rr_get_github_release();
    if ( ! $remote || ! isset( $remote->tag_name ) ) return $transient;

    // Nettoie le numéro de version (ex: "v4.1.0" → "4.1.0")
    $latest = ltrim( $remote->tag_name, 'vV' );

    if ( version_compare( $current, $latest, '<' ) ) {
        // URL du ZIP de la release
        $zip_url = '';
        if ( ! empty( $remote->assets ) && is_array( $remote->assets ) ) {
            foreach ( $remote->assets as $asset ) {
                if ( substr( $asset->name, -4 ) === '.zip' ) {
                    $zip_url = $asset->browser_download_url;
                    break;
                }
            }
        }
        // Fallback : ZIP auto-généré par GitHub
        if ( ! $zip_url ) {
            $zip_url = sprintf(
                'https://github.com/%s/%s/archive/refs/tags/%s.zip',
                RR_GITHUB_USER, RR_GITHUB_REPO, $remote->tag_name
            );
        }

        $transient->response[ $plugin_slug ] = (object) [
            'slug'        => 'restaurant-reservation',
            'plugin'      => $plugin_slug,
            'new_version' => $latest,
            'url'         => "https://github.com/" . RR_GITHUB_USER . "/" . RR_GITHUB_REPO,
            'package'     => $zip_url,
            'tested'      => get_bloginfo( 'version' ),
            'icons'       => [],
            'banners'     => [],
            'banners_rtl' => [],
        ];
    }

    return $transient;
}

/* ─────────────────────────────────────
   2. AFFICHE LE CHANGELOG
───────────────────────────────────── */
add_filter( 'plugins_api', 'rr_plugin_info', 20, 3 );

function rr_plugin_info( $result, $action, $args ) {
    if ( $action !== 'plugin_information' ) return $result;
    if ( empty( $args->slug ) || $args->slug !== 'restaurant-reservation' ) return $result;
    if ( RR_GITHUB_USER === 'TON_USERNAME_GITHUB' ) return $result;

    $remote = rr_get_github_release();
    if ( ! $remote ) return $result;

    return (object) [
        'name'           => 'Réservation Restaurant',
        'slug'           => 'restaurant-reservation',
        'version'        => ltrim( $remote->tag_name, 'vV' ),
        'tested'         => get_bloginfo( 'version' ),
        'requires'       => '5.5',
        'author'         => 'Votre Restaurant',
        'homepage'       => "https://github.com/" . RR_GITHUB_USER . "/" . RR_GITHUB_REPO,
        'last_updated'   => $remote->published_at ?? '',
        'sections'       => [
            'description' => 'Système complet de réservation pour restaurant : design personnalisable, archivage RGPD, SMS, gestion d\'équipe.',
            'changelog'   => '<pre>' . esc_html( $remote->body ?? 'Aucune note de version.' ) . '</pre>',
        ],
        'download_link'  => $remote->zipball_url ?? '',
    ];
}

/* ─────────────────────────────────────
   3. RÉCUPÈRE LA DERNIÈRE RELEASE GITHUB
   (avec cache 6h pour pas spammer l'API)
───────────────────────────────────── */
function rr_get_github_release() {
    $cache_key = 'rr_github_release_v1';
    $cached = get_transient( $cache_key );
    if ( $cached !== false ) return $cached;

    $url = sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', RR_GITHUB_USER, RR_GITHUB_REPO );
    $response = wp_remote_get( $url, [
        'timeout' => 10,
        'headers' => [
            'Accept'     => 'application/vnd.github+json',
            'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ),
        ],
    ]);

    if ( is_wp_error( $response ) ) return false;
    if ( wp_remote_retrieve_response_code( $response ) !== 200 ) return false;

    $body = json_decode( wp_remote_retrieve_body( $response ) );
    if ( ! $body ) return false;

    set_transient( $cache_key, $body, 6 * HOUR_IN_SECONDS );
    return $body;
}

/* ─────────────────────────────────────
   4. APRÈS MAJ : RENOMMER LE DOSSIER
   GitHub télécharge avec un nom étrange
   (ex: restaurant-reservation-4.1.0)
   On le renomme en "restaurant-reservation"
───────────────────────────────────── */
add_filter( 'upgrader_source_selection', 'rr_fix_folder_name', 10, 4 );

function rr_fix_folder_name( $source, $remote_source, $upgrader, $hook_extra = null ) {
    if ( ! isset( $hook_extra['plugin'] ) ) return $source;
    if ( strpos( $hook_extra['plugin'], 'restaurant-reservation' ) === false ) return $source;

    $expected = trailingslashit( $remote_source ) . 'restaurant-reservation/';
    if ( $source === $expected ) return $source;

    if ( @rename( $source, $expected ) ) return $expected;
    return $source;
}

/* ─────────────────────────────────────
   5. BOUTON "VÉRIFIER MAINTENANT"
───────────────────────────────────── */
add_action( 'admin_post_rr_check_update', function() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );
    if ( ! check_admin_referer( 'rr_check_update' ) ) wp_die( 'Nonce invalide.' );

    delete_transient( 'rr_github_release_v1' );
    wp_update_plugins();

    wp_redirect( admin_url( 'plugins.php?rr_check=1' ) );
    exit;
});

add_action( 'admin_notices', function() {
    if ( ! empty( $_GET['rr_check'] ) ) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>✓ Vérification effectuée.</strong> Si une nouvelle version existe, elle apparaîtra dans la liste des extensions.</p></div>';
    }
});
