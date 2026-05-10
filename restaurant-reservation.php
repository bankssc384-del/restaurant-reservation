<?php
/**
 * Plugin Name: Réservation Restaurant
 * Plugin URI:  https://github.com/TON_USERNAME_GITHUB/restaurant-reservation
 * Description: Système complet de réservation : design personnalisable, archivage RGPD, SMS, gestion d'équipe, application mobile (PWA). Mises à jour auto via GitHub.
 * Version:     4.1.1
 * Author:      Votre Restaurant
 * License:     GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'RR_VERSION',     '4.1.1' );
define( 'RR_DIR',         plugin_dir_path( __FILE__ ) );
define( 'RR_URL',         plugin_dir_url( __FILE__ ) );
define( 'RR_SESSION_KEY', 'rr_admin_user' );

// Modules
require_once RR_DIR . 'includes/archive.php';
require_once RR_DIR . 'includes/sms.php';
require_once RR_DIR . 'includes/updater.php';
require_once RR_DIR . 'includes/pwa.php';

/* ─────────────────────────────────────
   1. ACTIVATION
───────────────────────────────────── */
register_activation_hook( __FILE__, 'rr_activate' );

function rr_activate() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rr_reservations (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        date DATE NOT NULL,
        time VARCHAR(10) NOT NULL,
        guests VARCHAR(30) NOT NULL,
        notes TEXT,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;" );

    dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rr_users (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        username VARCHAR(60) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT 'employee',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;" );

    if ( ! $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}rr_users WHERE username='manager'" ) ) {
        $wpdb->insert( "{$wpdb->prefix}rr_users", [
            'username'  => 'manager',
            'password'  => password_hash( 'Admin1234!', PASSWORD_DEFAULT ),
            'full_name' => 'Manager Principal',
            'role'      => 'manager',
        ]);
    }

    // Réglages par défaut
    add_option( 'rr_settings', rr_default_settings() );

    // Modules : tables + cron
    if ( function_exists( 'rr_archive_install' ) ) rr_archive_install();
    if ( function_exists( 'rr_pwa_install' ) )     rr_pwa_install();

    // Force flush des permaliens pour la route /app
    if ( function_exists( 'rr_pwa_add_rewrite' ) ) rr_pwa_add_rewrite();
    flush_rewrite_rules();
}

// Désactivation : nettoyer le cron
register_deactivation_hook( __FILE__, function() {
    if ( function_exists( 'rr_archive_uninstall_cleanup' ) ) rr_archive_uninstall_cleanup();
});

function rr_default_settings() {
    return [
        // Mode
        'mode'              => 'auto',           // 'auto' ou 'manual'
        // Canaux de notification
        'notify_channels'   => [ 'email' ],      // ['email'], ['sms'] ou ['email','sms']
        // Couleurs
        'color_primary'     => '#0F766E',        // accent principal
        'color_text'        => '#1A1A1A',
        'color_background'  => '#FFFFFF',
        'color_border'      => '#E5E7EB',
        // Bouton
        'btn_bg'            => '#0F766E',
        'btn_text'          => '#FFFFFF',
        'btn_radius'        => '10',
        'btn_size'          => 'medium',         // small / medium / large
        'btn_border_width'  => '0',
        'btn_border_color'  => '#0F766E',
        'btn_text_label'    => 'Envoyer ma demande',
        // Champs
        'input_radius'      => '8',
        'input_size'        => 'medium',
        // Textes
        'form_title'        => 'Réserver une table',
        'form_subtitle'     => 'Remplissez le formulaire. Notre équipe vous confirmera rapidement.',
        'success_title'     => 'Demande envoyée !',
        'success_text'      => 'Votre demande a bien été enregistrée. Nous vous contactons rapidement.',
        // E-mails
        'email_received_subject'  => '🍽️ Nous avons bien reçu votre demande',
        'email_received_body'     => "Bonjour {name},\n\nNous avons bien reçu votre demande de réservation pour le {date} à {time} pour {guests} couvert(s).\n\nNous vous confirmerons celle-ci dans les meilleurs délais.\n\nÀ très bientôt,\n{site_name}",
        'email_confirmed_subject' => '✅ Votre réservation est confirmée',
        'email_confirmed_body'    => "Bonjour {name},\n\nNous avons le plaisir de vous confirmer votre réservation :\n\n• Date : {date}\n• Heure : {time}\n• Couverts : {guests}\n\nÀ très bientôt,\n{site_name}",
        'email_cancelled_subject' => 'Concernant votre demande de réservation',
        'email_cancelled_body'    => "Bonjour {name},\n\nNous sommes désolés, nous ne pouvons malheureusement pas honorer votre demande de réservation pour le {date} à {time}.\n\nN'hésitez pas à nous recontacter pour une autre date.\n\nCordialement,\n{site_name}",
        // Créneaux
        'slots' => "11:00,11:30,12:00,12:30,13:00,13:30,14:00,19:00,19:30,20:00,20:30,21:00,21:30",
    ];
}

function rr_settings() {
    $s = get_option( 'rr_settings', [] );
    return array_merge( rr_default_settings(), is_array($s) ? $s : [] );
}

/* ─────────────────────────────────────
   2. SESSION
───────────────────────────────────── */
add_action( 'init', function() {
    if ( ! headers_sent() && session_status() === PHP_SESSION_NONE ) {
        @session_start();
    }
}, 1 );

function rr_logged_in()    { return ! empty( $_SESSION[ RR_SESSION_KEY ] ); }
function rr_current_user() { return $_SESSION[ RR_SESSION_KEY ] ?? null; }
function rr_is_manager()   { $u = rr_current_user(); return $u && $u['role'] === 'manager'; }

/* ─────────────────────────────────────
   3. CSS DYNAMIQUE (variables)
───────────────────────────────────── */
function rr_inline_css() {
    $s = rr_settings();
    $sizes = [
        'small'  => ['pad' => '7px 11px',  'font' => '13px'],
        'medium' => ['pad' => '10px 13px', 'font' => '14px'],
        'large'  => ['pad' => '13px 16px', 'font' => '15px'],
    ];
    $btn_sizes = [
        'small'  => ['pad' => '9px 18px',  'font' => '13px'],
        'medium' => ['pad' => '12px 22px', 'font' => '14px'],
        'large'  => ['pad' => '15px 28px', 'font' => '16px'],
    ];
    $is = $sizes[ $s['input_size'] ] ?? $sizes['medium'];
    $bs = $btn_sizes[ $s['btn_size'] ] ?? $btn_sizes['medium'];

    return ":root{
        --rr-primary: {$s['color_primary']};
        --rr-text:    {$s['color_text']};
        --rr-bg:      {$s['color_background']};
        --rr-border:  {$s['color_border']};
        --rr-btn-bg:        {$s['btn_bg']};
        --rr-btn-text:      {$s['btn_text']};
        --rr-btn-radius:    {$s['btn_radius']}px;
        --rr-btn-pad:       {$bs['pad']};
        --rr-btn-font:      {$bs['font']};
        --rr-btn-bw:        {$s['btn_border_width']}px;
        --rr-btn-bc:        {$s['btn_border_color']};
        --rr-input-radius:  {$s['input_radius']}px;
        --rr-input-pad:     {$is['pad']};
        --rr-input-font:    {$is['font']};
    }";
}

/* ─────────────────────────────────────
   4. SHORTCODES
───────────────────────────────────── */
add_shortcode( 'reservation', 'rr_shortcode_form' );
function rr_shortcode_form() {
    wp_enqueue_style(  'rr-style',  RR_URL . 'assets/style.css', [], RR_VERSION );
    wp_enqueue_script( 'rr-front',  RR_URL . 'assets/front.js',  [], RR_VERSION, true );
    wp_add_inline_style( 'rr-style', rr_inline_css() );
    wp_localize_script( 'rr-front', 'RR', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'rr_nonce' ),
    ]);
    ob_start();
    include RR_DIR . 'templates/form.php';
    return ob_get_clean();
}

add_shortcode( 'reservation_admin', 'rr_shortcode_admin' );
function rr_shortcode_admin() {
    wp_enqueue_style(  'rr-style', RR_URL . 'assets/style.css', [], RR_VERSION );
    wp_enqueue_script( 'rr-admin', RR_URL . 'assets/admin.js',  [], RR_VERSION, true );
    wp_add_inline_style( 'rr-style', rr_inline_css() );
    wp_localize_script( 'rr-admin', 'RR', [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'rr_nonce' ),
    ]);
    ob_start();
    if ( ! rr_logged_in() ) include RR_DIR . 'templates/login.php';
    else                    include RR_DIR . 'templates/dashboard.php';
    return ob_get_clean();
}

/* ─────────────────────────────────────
   5. PAGES RÉGLAGES (admin WordPress)
───────────────────────────────────── */
add_action( 'admin_menu', function() {
    add_menu_page( 'Réservation Restaurant', 'Réservations', 'manage_options',
        'rr-settings', 'rr_settings_page', 'dashicons-calendar-alt', 25 );
    add_submenu_page( 'rr-settings', 'Réglages', 'Réglages', 'manage_options',
        'rr-settings', 'rr_settings_page' );
    add_submenu_page( 'rr-settings', 'Notifications SMS', '📱 SMS', 'manage_options',
        'rr-sms', 'rr_sms_page' );
    add_submenu_page( 'rr-settings', 'Application mobile', '📲 App mobile', 'manage_options',
        'rr-pwa', 'rr_pwa_admin_page' );
    add_submenu_page( 'rr-settings', 'Archivage & données', '🗄️ Archivage', 'manage_options',
        'rr-archive', 'rr_archive_page' );
});

function rr_settings_page() {
    if ( isset( $_POST['rr_save_settings'] ) && check_admin_referer( 'rr_save_settings' ) ) {
        $defaults = rr_default_settings();
        $new = [];
        foreach ( $defaults as $k => $v ) {
            if ( $k === 'notify_channels' ) {
                $val = $_POST['notify_channels'] ?? [];
                if ( ! is_array( $val ) ) $val = [];
                $val = array_intersect( $val, [ 'email', 'sms' ] );
                $new[ $k ] = empty( $val ) ? [ 'email' ] : array_values( $val );
            } elseif ( isset( $_POST[ $k ] ) ) {
                $new[ $k ] = is_array($_POST[$k]) ? $v : sanitize_textarea_field( wp_unslash( $_POST[$k] ) );
            } else {
                $new[ $k ] = $v;
            }
        }
        update_option( 'rr_settings', $new );
        if ( function_exists('rr_log') ) rr_log( 'settings_updated', 'Réglages principaux modifiés.' );
        echo '<div class="notice notice-success is-dismissible"><p><strong>Réglages enregistrés.</strong></p></div>';
    }

    if ( isset( $_POST['rr_reset'] ) && check_admin_referer( 'rr_save_settings' ) ) {
        update_option( 'rr_settings', rr_default_settings() );
        echo '<div class="notice notice-success is-dismissible"><p><strong>Réglages réinitialisés.</strong></p></div>';
    }

    $s = rr_settings();
    include RR_DIR . 'templates/settings.php';
}

/* ─────────────────────────────────────
   6. AJAX — formulaire
───────────────────────────────────── */
add_action( 'wp_ajax_rr_submit',        'rr_ajax_submit' );
add_action( 'wp_ajax_nopriv_rr_submit', 'rr_ajax_submit' );

function rr_ajax_submit() {
    check_ajax_referer( 'rr_nonce', 'nonce' );

    foreach ( ['name','email','phone','date','time','guests'] as $f ) {
        if ( empty( $_POST[$f] ) ) wp_send_json_error( 'Champ manquant : ' . $f );
    }

    $settings = rr_settings();
    $auto     = $settings['mode'] === 'auto';
    $status   = $auto ? 'confirmed' : 'pending';

    global $wpdb;
    $data = [
        'name'   => sanitize_text_field( $_POST['name'] ),
        'email'  => sanitize_email( $_POST['email'] ),
        'phone'  => sanitize_text_field( $_POST['phone'] ),
        'date'   => sanitize_text_field( $_POST['date'] ),
        'time'   => sanitize_text_field( $_POST['time'] ),
        'guests' => sanitize_text_field( $_POST['guests'] ),
        'notes'  => sanitize_textarea_field( $_POST['notes'] ?? '' ),
        'status' => $status,
    ];
    $ok = $wpdb->insert( "{$wpdb->prefix}rr_reservations", $data, ['%s','%s','%s','%s','%s','%s','%s','%s'] );
    if ( ! $ok ) wp_send_json_error( 'Erreur base de données.' );

    // Notification au resto
    wp_mail(
        get_option( 'admin_email' ),
        '🍽️ Nouvelle réservation — ' . $data['name'],
        rr_admin_notif_body( $data, $auto )
    );

    // Notifications au client (selon canaux choisis)
    $type = $auto ? 'confirmed' : 'received';
    rr_notify_client( $data, $type );

    wp_send_json_success( [ 'auto' => $auto ] );
}

/**
 * Notifie le client selon les canaux activés (email / sms / les deux)
 */
function rr_notify_client( $data, $type ) {
    $channels = rr_notification_channels();

    if ( in_array( 'email', $channels, true ) && ! empty( $data['email'] ) ) {
        rr_send_template_email( $data, $type );
    }
    if ( in_array( 'sms', $channels, true ) && ! empty( $data['phone'] ) && function_exists( 'rr_send_sms_template' ) ) {
        rr_send_sms_template( $data, $type );
    }
}

function rr_admin_notif_body( $d, $auto ) {
    $msg  = "Nouvelle " . ($auto ? "réservation confirmée automatiquement" : "demande de réservation") . " :\n\n";
    $msg .= "Nom : {$d['name']}\nE-mail : {$d['email']}\nTél : {$d['phone']}\n";
    $msg .= "Date : {$d['date']}\nHeure : {$d['time']}\nCouverts : {$d['guests']}\n";
    if ( ! empty( $d['notes'] ) ) $msg .= "Notes : {$d['notes']}\n";
    return $msg;
}

function rr_send_template_email( $data, $type ) {
    $s = rr_settings();
    $subject_key = "email_{$type}_subject";
    $body_key    = "email_{$type}_body";
    if ( empty( $s[$subject_key] ) || empty( $s[$body_key] ) ) return;

    $vars = [
        '{name}'      => $data['name'],
        '{date}'      => date_i18n( 'l j F Y', strtotime( $data['date'] ) ),
        '{time}'      => $data['time'],
        '{guests}'    => $data['guests'],
        '{notes}'     => $data['notes'] ?? '',
        '{site_name}' => get_bloginfo( 'name' ),
    ];
    $subject = strtr( $s[ $subject_key ], $vars );
    $body    = strtr( $s[ $body_key ],    $vars );
    wp_mail( $data['email'], $subject, $body );
}

/* ─────────────────────────────────────
   7. AJAX — connexion
───────────────────────────────────── */
add_action( 'wp_ajax_rr_login',        'rr_ajax_login' );
add_action( 'wp_ajax_nopriv_rr_login', 'rr_ajax_login' );
function rr_ajax_login() {
    check_ajax_referer( 'rr_nonce', 'nonce' );
    $username = sanitize_text_field( $_POST['username'] ?? '' );
    $password = $_POST['password'] ?? '';
    if ( ! $username || ! $password ) wp_send_json_error( 'Identifiants manquants.' );

    global $wpdb;
    $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rr_users WHERE username=%s", $username ) );
    if ( ! $user || ! password_verify( $password, $user->password ) ) {
        wp_send_json_error( 'Identifiant ou mot de passe incorrect.' );
    }
    $_SESSION[ RR_SESSION_KEY ] = [
        'id'        => $user->id,
        'username'  => $user->username,
        'full_name' => $user->full_name,
        'role'      => $user->role,
    ];
    wp_send_json_success();
}

add_action( 'wp_ajax_rr_logout', 'rr_ajax_logout' );
function rr_ajax_logout() { unset( $_SESSION[ RR_SESSION_KEY ] ); wp_send_json_success(); }

/* ─────────────────────────────────────
   8. AJAX — réservations admin
───────────────────────────────────── */
add_action( 'wp_ajax_rr_get_reservations', 'rr_ajax_get_reservations' );
function rr_ajax_get_reservations() {
    check_ajax_referer( 'rr_nonce', 'nonce' );
    if ( ! rr_logged_in() ) wp_send_json_error( 'Non connecté.' );
    global $wpdb;
    $rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rr_reservations ORDER BY date ASC, time ASC" );
    wp_send_json_success( $rows );
}

add_action( 'wp_ajax_rr_update_status', 'rr_ajax_update_status' );
function rr_ajax_update_status() {
    check_ajax_referer( 'rr_nonce', 'nonce' );
    if ( ! rr_logged_in() ) wp_send_json_error( 'Non connecté.' );

    $id     = intval( $_POST['id'] );
    $status = sanitize_text_field( $_POST['status'] );
    if ( ! in_array( $status, ['pending','confirmed','cancelled'] ) ) wp_send_json_error( 'Statut invalide.' );

    global $wpdb;
    $wpdb->update( "{$wpdb->prefix}rr_reservations", ['status'=>$status], ['id'=>$id], ['%s'], ['%d'] );

    if ( in_array( $status, ['confirmed','cancelled'] ) ) {
        $r = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rr_reservations WHERE id=%d", $id ) );
        if ( $r ) {
            rr_notify_client( (array) $r, $status );
        }
    }
    wp_send_json_success();
}

/* ─────────────────────────────────────
   9. AJAX — utilisateurs
───────────────────────────────────── */
add_action( 'wp_ajax_rr_get_users', function() {
    check_ajax_referer( 'rr_nonce', 'nonce' );
    if ( ! rr_is_manager() ) wp_send_json_error( 'Accès refusé.' );
    global $wpdb;
    wp_send_json_success( $wpdb->get_results(
        "SELECT id, username, full_name, role, created_at FROM {$wpdb->prefix}rr_users ORDER BY role, full_name"
    ));
});

add_action( 'wp_ajax_rr_add_user', function() {
    check_ajax_referer( 'rr_nonce', 'nonce' );
    if ( ! rr_is_manager() ) wp_send_json_error( 'Accès refusé.' );

    $username  = sanitize_text_field( $_POST['username'] ?? '' );
    $password  = $_POST['password'] ?? '';
    $full_name = sanitize_text_field( $_POST['full_name'] ?? '' );
    $role      = in_array( $_POST['role']??'', ['manager','employee'] ) ? $_POST['role'] : 'employee';

    if ( ! $username || ! $password || ! $full_name ) wp_send_json_error( 'Tous les champs sont requis.' );
    if ( strlen($password) < 8 ) wp_send_json_error( 'Mot de passe trop court (8 car. min).' );

    global $wpdb;
    if ( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}rr_users WHERE username=%s", $username ) ) ) {
        wp_send_json_error( 'Identifiant déjà utilisé.' );
    }
    $wpdb->insert( "{$wpdb->prefix}rr_users", [
        'username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT),
        'full_name'=> $full_name, 'role' => $role,
    ]);
    wp_send_json_success();
});

add_action( 'wp_ajax_rr_delete_user', function() {
    check_ajax_referer( 'rr_nonce', 'nonce' );
    if ( ! rr_is_manager() ) wp_send_json_error( 'Accès refusé.' );
    $id = intval( $_POST['id'] );
    $me = rr_current_user();
    if ( $id === intval($me['id']) ) wp_send_json_error( 'Vous ne pouvez pas supprimer votre propre compte.' );
    global $wpdb;
    $wpdb->delete( "{$wpdb->prefix}rr_users", ['id'=>$id], ['%d'] );
    wp_send_json_success();
});

add_action( 'wp_ajax_rr_change_password', function() {
    check_ajax_referer( 'rr_nonce', 'nonce' );
    if ( ! rr_logged_in() ) wp_send_json_error( 'Non connecté.' );
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    if ( strlen($new) < 8 ) wp_send_json_error( 'Nouveau mot de passe trop court.' );
    $me = rr_current_user();
    global $wpdb;
    $u = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rr_users WHERE id=%d", $me['id'] ) );
    if ( ! password_verify( $old, $u->password ) ) wp_send_json_error( 'Ancien mot de passe incorrect.' );
    $wpdb->update( "{$wpdb->prefix}rr_users", ['password' => password_hash($new, PASSWORD_DEFAULT)], ['id'=>$me['id']], ['%s'], ['%d'] );
    wp_send_json_success();
});
