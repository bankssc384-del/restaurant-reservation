<?php
/**
 * Module : PWA (Progressive Web App)
 * - Sert l'app en plein écran à l'URL /app
 * - API REST sécurisée pour les actions de l'app
 * - Tokens d'authentification (JWT-like simple)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ─────────────────────────────────────
   1. INSTALLATION : table tokens
───────────────────────────────────── */
function rr_pwa_install() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rr_tokens (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        token VARCHAR(64) NOT NULL UNIQUE,
        user_id BIGINT UNSIGNED NOT NULL,
        device_name VARCHAR(100) DEFAULT '',
        last_used DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_token (token)
    ) $charset;" );

    // Slug par défaut
    if ( ! get_option( 'rr_pwa_slug' ) ) {
        add_option( 'rr_pwa_slug', 'app' );
    }
}

/* ─────────────────────────────────────
   SLUG (URL) DE L'APP
───────────────────────────────────── */
function rr_pwa_slug() {
    $slug = get_option( 'rr_pwa_slug', 'app' );
    $slug = sanitize_title( $slug );
    return $slug ?: 'app';
}

function rr_pwa_url() {
    return home_url( '/' . rr_pwa_slug() );
}

/* ─────────────────────────────────────
   2. URL DE L'APP : slug configurable
───────────────────────────────────── */
add_action( 'init', 'rr_pwa_add_rewrite' );
function rr_pwa_add_rewrite() {
    $slug = rr_pwa_slug();
    add_rewrite_rule( '^' . $slug . '/?$',                'index.php?rr_pwa=1',                'top' );
    add_rewrite_rule( '^' . $slug . '/manifest\.json$',   'index.php?rr_pwa=manifest',         'top' );
    add_rewrite_rule( '^' . $slug . '/sw\.js$',            'index.php?rr_pwa=sw',              'top' );
    add_rewrite_rule( '^' . $slug . '/icon-(\d+)\.png$',   'index.php?rr_pwa=icon&size=$matches[1]', 'top' );
    add_rewrite_rule( '^' . $slug . '/sound\.mp3$',        'index.php?rr_pwa=sound',           'top' );
}

add_filter( 'query_vars', function( $vars ) {
    $vars[] = 'rr_pwa';
    $vars[] = 'size';
    return $vars;
});

// Force flush des règles à l'activation du plugin
register_activation_hook( RR_DIR . 'restaurant-reservation.php', function() {
    rr_pwa_add_rewrite();
    flush_rewrite_rules();
});
register_deactivation_hook( RR_DIR . 'restaurant-reservation.php', 'flush_rewrite_rules' );

/* ─────────────────────────────────────
   3. SERVEUR DE L'APP / RESSOURCES
───────────────────────────────────── */
add_action( 'template_redirect', 'rr_pwa_serve' );

function rr_pwa_serve() {
    $what = get_query_var( 'rr_pwa' );
    if ( ! $what ) return;

    // Manifest PWA
    if ( $what === 'manifest' ) {
        header( 'Content-Type: application/manifest+json; charset=utf-8' );
        $base = rr_pwa_url();
        echo wp_json_encode([
            'name'             => 'Réservations — ' . get_bloginfo('name'),
            'short_name'       => 'Réservations',
            'description'      => 'Gérer les réservations du restaurant',
            'start_url'        => $base,
            'scope'            => $base,
            'display'          => 'standalone',
            'orientation'      => 'any',
            'background_color' => '#0F172A',
            'theme_color'      => '#0F766E',
            'lang'             => 'fr-FR',
            'icons' => [
                [ 'src' => $base . '/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable' ],
                [ 'src' => $base . '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable' ],
            ],
        ], JSON_UNESCAPED_SLASHES );
        exit;
    }

    // Service Worker
    if ( $what === 'sw' ) {
        header( 'Content-Type: application/javascript; charset=utf-8' );
        header( 'Service-Worker-Allowed: /' );
        // Injecte le slug dynamique dans le SW
        $slug = rr_pwa_slug();
        $sw = file_get_contents( RR_DIR . 'pwa/sw.js' );
        $sw = str_replace( '__SLUG__', $slug, $sw );
        echo $sw;
        exit;
    }

    // Icônes générées dynamiquement (emoji 🍽 sur fond)
    if ( $what === 'icon' ) {
        $size = max( 64, min( 1024, intval( get_query_var('size') ?: 192 ) ) );
        rr_pwa_serve_icon( $size );
        exit;
    }

    // Son de notification
    if ( $what === 'sound' ) {
        $path = RR_DIR . 'pwa/notify.mp3';
        if ( file_exists( $path ) ) {
            header( 'Content-Type: audio/mpeg' );
            readfile( $path );
        }
        exit;
    }

    // App principale (1)
    if ( $what === '1' || $what === 1 ) {
        nocache_headers();
        include RR_DIR . 'pwa/app.php';
        exit;
    }
}

/**
 * Génère une icône PNG avec un emoji.
 * Utilise GD si dispo, sinon redirige vers un SVG en data-URI base.
 */
function rr_pwa_serve_icon( $size ) {
    header( 'Content-Type: image/png' );
    header( 'Cache-Control: public, max-age=86400' );

    if ( function_exists( 'imagecreatetruecolor' ) ) {
        $img = imagecreatetruecolor( $size, $size );
        $bg  = imagecolorallocate( $img, 15, 118, 110 );    // #0F766E
        $fg  = imagecolorallocate( $img, 255, 255, 255 );
        imagefill( $img, 0, 0, $bg );

        // Cercle blanc au centre
        $cx = $cy = $size / 2;
        $r  = $size * 0.35;
        // Texte simple : "R"
        $font_size = $size * 0.5;
        if ( function_exists( 'imagettftext' ) ) {
            // Pas de TTF garanti — on utilise imagestring
        }
        $box = $size * 0.6;
        imagefilledellipse( $img, $cx, $cy, $box, $box, $fg );

        // R au centre
        imagestring( $img, 5, $cx - 4, $cy - 7, 'R', $bg );

        imagepng( $img );
        imagedestroy( $img );
    } else {
        // Fallback : PNG d'un pixel
        echo base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==' );
    }
}

/* ─────────────────────────────────────
   4. API REST
───────────────────────────────────── */
add_action( 'rest_api_init', 'rr_pwa_register_routes' );

function rr_pwa_register_routes() {
    $ns = 'rr/v1';

    // Login → renvoie un token
    register_rest_route( $ns, '/login', [
        'methods'             => 'POST',
        'callback'            => 'rr_api_login',
        'permission_callback' => '__return_true',
    ]);

    // Tout le reste demande un token valide
    register_rest_route( $ns, '/me', [
        'methods'             => 'GET',
        'callback'            => 'rr_api_me',
        'permission_callback' => 'rr_api_check_token',
    ]);

    register_rest_route( $ns, '/reservations', [
        'methods'             => 'GET',
        'callback'            => 'rr_api_get_reservations',
        'permission_callback' => 'rr_api_check_token',
    ]);

    register_rest_route( $ns, '/reservations/(?P<id>\d+)/status', [
        'methods'             => 'POST',
        'callback'            => 'rr_api_update_status',
        'permission_callback' => 'rr_api_check_token',
        'args' => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
    ]);

    register_rest_route( $ns, '/poll', [
        'methods'             => 'GET',
        'callback'            => 'rr_api_poll',
        'permission_callback' => 'rr_api_check_token',
    ]);

    register_rest_route( $ns, '/logout', [
        'methods'             => 'POST',
        'callback'            => 'rr_api_logout',
        'permission_callback' => 'rr_api_check_token',
    ]);
}

/* ─────────────────────────────────────
   5. AUTHENTIFICATION
───────────────────────────────────── */
function rr_api_get_token_from_request() {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ( ! $auth && function_exists( 'getallheaders' ) ) {
        $h = getallheaders();
        $auth = $h['Authorization'] ?? $h['authorization'] ?? '';
    }
    if ( preg_match( '/Bearer\s+([a-f0-9]{64})/i', $auth, $m ) ) return $m[1];
    return '';
}

function rr_api_check_token( $request ) {
    $token = rr_api_get_token_from_request();
    if ( ! $token ) return new WP_Error( 'no_token', 'Token manquant.', [ 'status' => 401 ] );

    global $wpdb;
    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}rr_tokens WHERE token = %s AND expires_at > NOW()",
        $token
    ));
    if ( ! $row ) return new WP_Error( 'invalid_token', 'Token invalide ou expiré.', [ 'status' => 401 ] );

    // Met à jour last_used
    $wpdb->update( "{$wpdb->prefix}rr_tokens", [ 'last_used' => current_time('mysql') ], [ 'id' => $row->id ] );

    // Charge l'utilisateur
    $user = $wpdb->get_row( $wpdb->prepare(
        "SELECT id, username, full_name, role FROM {$wpdb->prefix}rr_users WHERE id = %d",
        $row->user_id
    ));
    if ( ! $user ) return new WP_Error( 'user_gone', 'Utilisateur supprimé.', [ 'status' => 401 ] );

    // Stocke pour les callbacks
    $GLOBALS['rr_api_user'] = $user;
    return true;
}

function rr_api_current_user() { return $GLOBALS['rr_api_user'] ?? null; }

/* ─────────────────────────────────────
   6. ENDPOINTS
───────────────────────────────────── */
function rr_api_login( $req ) {
    $username = sanitize_text_field( $req->get_param( 'username' ) );
    $password = $req->get_param( 'password' );
    $device   = sanitize_text_field( $req->get_param( 'device' ) ?: 'Tablette' );

    if ( ! $username || ! $password ) {
        return new WP_Error( 'missing', 'Identifiants manquants.', [ 'status' => 400 ] );
    }

    global $wpdb;
    $user = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}rr_users WHERE username = %s", $username
    ));
    if ( ! $user || ! password_verify( $password, $user->password ) ) {
        return new WP_Error( 'bad_credentials', 'Identifiants incorrects.', [ 'status' => 401 ] );
    }

    // Token valide 30 jours
    $token = bin2hex( random_bytes( 32 ) );
    $expires = date( 'Y-m-d H:i:s', strtotime( '+30 days' ) );

    $wpdb->insert( "{$wpdb->prefix}rr_tokens", [
        'token'       => $token,
        'user_id'     => $user->id,
        'device_name' => $device,
        'expires_at'  => $expires,
    ]);

    if ( function_exists('rr_log') ) rr_log( 'pwa_login', "Utilisateur : {$user->username} ({$device})" );

    return [
        'token' => $token,
        'user'  => [
            'id'        => (int) $user->id,
            'username'  => $user->username,
            'full_name' => $user->full_name,
            'role'      => $user->role,
        ],
        'site_name' => get_bloginfo( 'name' ),
        'expires'   => $expires,
    ];
}

function rr_api_logout( $req ) {
    $token = rr_api_get_token_from_request();
    global $wpdb;
    $wpdb->delete( "{$wpdb->prefix}rr_tokens", [ 'token' => $token ] );
    return [ 'success' => true ];
}

function rr_api_me() {
    return rr_api_current_user();
}

function rr_api_get_reservations() {
    global $wpdb;
    $rows = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}rr_reservations
         WHERE date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
         ORDER BY date ASC, time ASC"
    );
    return [
        'reservations' => $rows,
        'server_time'  => current_time( 'c' ),
    ];
}

function rr_api_update_status( $req ) {
    $id     = intval( $req['id'] );
    $status = sanitize_text_field( $req->get_param( 'status' ) );
    if ( ! in_array( $status, [ 'pending', 'confirmed', 'cancelled' ] ) ) {
        return new WP_Error( 'bad_status', 'Statut invalide.', [ 'status' => 400 ] );
    }

    global $wpdb;
    $wpdb->update( "{$wpdb->prefix}rr_reservations", [ 'status' => $status ], [ 'id' => $id ] );

    if ( in_array( $status, [ 'confirmed', 'cancelled' ] ) ) {
        $r = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rr_reservations WHERE id=%d", $id ) );
        if ( $r && function_exists( 'rr_notify_client' ) ) {
            rr_notify_client( (array) $r, $status );
        }
    }

    if ( function_exists('rr_log') ) {
        $u = rr_api_current_user();
        rr_log( 'pwa_status_change', "ID {$id} → {$status} par {$u->username}" );
    }

    return [ 'success' => true ];
}

/**
 * Endpoint de polling : renvoie le timestamp de la dernière modification
 * et le nombre de demandes en attente. Permet à l'app de détecter qu'il
 * y a du nouveau sans tout retélécharger.
 */
function rr_api_poll() {
    global $wpdb;
    $latest = $wpdb->get_var(
        "SELECT UNIX_TIMESTAMP(MAX(created_at)) FROM {$wpdb->prefix}rr_reservations"
    );
    $pending = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->prefix}rr_reservations WHERE status = 'pending'"
    );
    return [
        'latest'  => (int) $latest,
        'pending' => $pending,
    ];
}

/* ─────────────────────────────────────
   7. CRON : nettoyage des vieux tokens
───────────────────────────────────── */
add_action( 'rr_daily_archive', function() {
    global $wpdb;
    $wpdb->query( "DELETE FROM {$wpdb->prefix}rr_tokens WHERE expires_at < NOW()" );
});

/* ─────────────────────────────────────
   8. PAGE D'ADMIN : aide PWA
───────────────────────────────────── */
function rr_pwa_admin_page() {
    global $wpdb;

    // Sauvegarde du slug
    if ( isset( $_POST['rr_save_slug'] ) && check_admin_referer( 'rr_save_slug' ) ) {
        $new_slug = sanitize_title( wp_unslash( $_POST['rr_pwa_slug'] ?? '' ) );

        // Liste de slugs réservés / dangereux
        $reserved = [ 'wp-admin', 'wp-content', 'wp-includes', 'wp-json', 'admin', 'login', 'feed', 'rss', 'sitemap', '' ];

        if ( ! $new_slug || in_array( $new_slug, $reserved, true ) || strlen( $new_slug ) < 3 ) {
            echo '<div class="notice notice-error inline"><p><strong>Slug invalide.</strong> Au moins 3 caractères, lettres/chiffres/tirets uniquement, pas de mot réservé.</p></div>';
        } else {
            update_option( 'rr_pwa_slug', $new_slug );
            // Révoquer tous les tokens existants par sécurité (les apps installées devront se reconnecter)
            if ( ! empty( $_POST['revoke_all'] ) ) {
                $wpdb->query( "DELETE FROM {$wpdb->prefix}rr_tokens" );
            }
            // Re-flush des règles de réécriture
            rr_pwa_add_rewrite();
            flush_rewrite_rules();
            if ( function_exists('rr_log') ) rr_log( 'pwa_slug_changed', "Nouveau slug : {$new_slug}" );
            echo '<div class="notice notice-success is-dismissible"><p><strong>✓ URL de l\'app mise à jour : <code>' . esc_html( home_url( '/' . $new_slug ) ) . '</code></strong></p></div>';
        }
    }

    $tokens = $wpdb->get_results(
        "SELECT t.*, u.username, u.full_name, u.role
         FROM {$wpdb->prefix}rr_tokens t
         LEFT JOIN {$wpdb->prefix}rr_users u ON u.id = t.user_id
         WHERE t.expires_at > NOW()
         ORDER BY t.last_used DESC"
    );

    if ( isset( $_POST['rr_revoke_token'] ) && check_admin_referer( 'rr_revoke_token' ) ) {
        $tid = intval( $_POST['token_id'] );
        $wpdb->delete( "{$wpdb->prefix}rr_tokens", [ 'id' => $tid ] );
        if ( function_exists('rr_log') ) rr_log( 'pwa_token_revoked', "ID : {$tid}" );
        echo '<div class="notice notice-success is-dismissible"><p><strong>Appareil déconnecté.</strong></p></div>';
        $tokens = $wpdb->get_results(
            "SELECT t.*, u.username, u.full_name, u.role
             FROM {$wpdb->prefix}rr_tokens t
             LEFT JOIN {$wpdb->prefix}rr_users u ON u.id = t.user_id
             WHERE t.expires_at > NOW()
             ORDER BY t.last_used DESC"
        );
    }

    $current_slug = rr_pwa_slug();
    include RR_DIR . 'templates/pwa-page.php';
}
