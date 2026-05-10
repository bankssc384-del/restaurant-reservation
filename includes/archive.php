<?php
/**
 * Module : Archivage & données (RGPD)
 * Gère l'archivage automatique CSV, le cron, les logs et l'export manuel.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ─────────────────────────────────────
   1. INITIALISATION : tables + dossier
───────────────────────────────────── */

function rr_archive_install() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Historique des exports
    dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rr_exports (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        type VARCHAR(20) NOT NULL DEFAULT 'auto',
        period_start DATE NULL,
        period_end DATE NULL,
        count INT UNSIGNED NOT NULL DEFAULT 0,
        filename VARCHAR(255) NOT NULL,
        filesize INT UNSIGNED NOT NULL DEFAULT 0,
        deleted_count INT UNSIGNED NOT NULL DEFAULT 0,
        emailed TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;" );

    // Logs RGPD (qui a fait quoi, quand)
    dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rr_logs (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        action VARCHAR(50) NOT NULL,
        user_id BIGINT UNSIGNED NULL,
        username VARCHAR(60) NULL,
        details TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_action (action),
        KEY idx_created (created_at)
    ) $charset;" );

    // Crée le dossier d'archives sécurisé
    rr_archive_setup_directory();

    // Programme le cron quotidien
    if ( ! wp_next_scheduled( 'rr_daily_archive' ) ) {
        wp_schedule_event( strtotime( 'tomorrow 03:00:00' ), 'daily', 'rr_daily_archive' );
    }
}

function rr_archive_uninstall_cleanup() {
    wp_clear_scheduled_hook( 'rr_daily_archive' );
}

/* ─────────────────────────────────────
   2. SÉCURITÉ DU DOSSIER D'ARCHIVES
───────────────────────────────────── */

function rr_archive_dir() {
    $up = wp_upload_dir();
    return trailingslashit( $up['basedir'] ) . 'rr-archives';
}

function rr_archive_setup_directory() {
    $dir = rr_archive_dir();
    if ( ! file_exists( $dir ) ) {
        wp_mkdir_p( $dir );
    }
    // .htaccess : bloque tout accès direct
    $ht = $dir . '/.htaccess';
    if ( ! file_exists( $ht ) ) {
        file_put_contents( $ht,
            "Order Deny,Allow\nDeny from all\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n"
        );
    }
    // index.html vide : empêche le listing si .htaccess ignoré
    $idx = $dir . '/index.html';
    if ( ! file_exists( $idx ) ) {
        file_put_contents( $idx, '' );
    }
}

/* ─────────────────────────────────────
   3. LOGS
───────────────────────────────────── */

function rr_log( $action, $details = '' ) {
    global $wpdb;
    $u = function_exists( 'rr_current_user' ) ? rr_current_user() : null;
    $wpdb->insert( "{$wpdb->prefix}rr_logs", [
        'action'   => sanitize_text_field( $action ),
        'user_id'  => $u['id']       ?? null,
        'username' => $u['username'] ?? 'system',
        'details'  => is_string( $details ) ? $details : wp_json_encode( $details ),
    ]);
}

/* ─────────────────────────────────────
   4. RÉGLAGES PAR DÉFAUT
───────────────────────────────────── */

function rr_archive_defaults() {
    return [
        'archive_enabled'        => 1,
        'archive_retention_days' => 90,            // 30/60/90/180/365
        'archive_delete_after'   => 0,             // supprimer après export ?
        'archive_email_to_resto' => 0,             // envoyer par e-mail au resto ?
        'archive_email_address'  => '',            // adresse e-mail (sinon admin_email)
    ];
}

function rr_archive_settings() {
    $s = get_option( 'rr_archive_settings', [] );
    return array_merge( rr_archive_defaults(), is_array($s) ? $s : [] );
}

/* ─────────────────────────────────────
   5. EXPORT CSV
───────────────────────────────────── */

/**
 * Génère un fichier CSV pour les réservations dans une période
 * @return array|WP_Error  ['filepath','filename','count','filesize'] ou WP_Error
 */
function rr_export_csv( $start_date, $end_date, $type = 'manual' ) {
    global $wpdb;

    // SÉCURITÉ : Ne JAMAIS exporter les réservations futures, en attente ou confirmées à venir
    $today = current_time( 'Y-m-d' );

    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}rr_reservations
         WHERE date >= %s AND date <= %s
         AND date < %s
         ORDER BY date ASC, time ASC",
        $start_date, $end_date, $today
    ));

    if ( empty( $rows ) ) {
        return new WP_Error( 'no_data', 'Aucune réservation à exporter sur cette période.' );
    }

    rr_archive_setup_directory();
    $filename = sprintf(
        'archive_%s_%s_to_%s_%s.csv',
        $type,
        $start_date,
        $end_date,
        wp_generate_password( 8, false )
    );
    $filepath = rr_archive_dir() . '/' . $filename;

    $fp = @fopen( $filepath, 'w' );
    if ( ! $fp ) return new WP_Error( 'file_error', 'Impossible de créer le fichier d\'archive.' );

    // BOM UTF-8 pour Excel
    fwrite( $fp, "\xEF\xBB\xBF" );

    fputcsv( $fp, [
        'ID', 'Nom', 'E-mail', 'Téléphone', 'Date', 'Heure',
        'Couverts', 'Notes', 'Statut', 'Créé le'
    ], ';' );

    foreach ( $rows as $r ) {
        fputcsv( $fp, [
            $r->id, $r->name, $r->email, $r->phone, $r->date, $r->time,
            $r->guests, $r->notes, $r->status, $r->created_at
        ], ';' );
    }
    fclose( $fp );

    if ( ! file_exists( $filepath ) || filesize( $filepath ) === 0 ) {
        return new WP_Error( 'file_error', 'Fichier d\'archive vide ou non créé.' );
    }

    return [
        'filepath' => $filepath,
        'filename' => $filename,
        'count'    => count( $rows ),
        'filesize' => filesize( $filepath ),
        'rows'     => $rows,
    ];
}

/**
 * Lance l'archivage : export + (optionnel) suppression + (optionnel) email + log
 */
function rr_run_archive( $start_date, $end_date, $type = 'auto', $force_delete = null, $force_email = null ) {
    $cfg = rr_archive_settings();

    $export = rr_export_csv( $start_date, $end_date, $type );
    if ( is_wp_error( $export ) ) {
        rr_log( 'archive_failed', $export->get_error_message() );
        return $export;
    }

    // VÉRIFICATION CRITIQUE : le fichier existe et a une taille > 0
    if ( ! file_exists( $export['filepath'] ) || $export['filesize'] === 0 ) {
        rr_log( 'archive_failed', 'Fichier non créé ou vide.' );
        return new WP_Error( 'file_check_failed', 'Vérification du fichier échouée.' );
    }

    // Suppression conditionnelle (uniquement si export OK)
    $deleted = 0;
    $do_delete = $force_delete !== null ? $force_delete : $cfg['archive_delete_after'];
    if ( $do_delete ) {
        global $wpdb;
        $today = current_time( 'Y-m-d' );
        $ids = wp_list_pluck( $export['rows'], 'id' );
        if ( ! empty( $ids ) ) {
            // Re-vérification : on ne supprime QUE les réservations passées
            $placeholders = implode( ',', array_fill( 0, count($ids), '%d' ) );
            $args = array_merge( $ids, [ $today ] );
            $deleted = $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}rr_reservations
                 WHERE id IN ($placeholders)
                 AND date < %s",
                $args
            ));
            rr_log( 'reservations_deleted', "Supprimé {$deleted} réservations." );
        }
    }

    // Envoi par e-mail
    $emailed = 0;
    $do_email = $force_email !== null ? $force_email : $cfg['archive_email_to_resto'];
    if ( $do_email ) {
        $to = ! empty( $cfg['archive_email_address'] ) ? $cfg['archive_email_address'] : get_option('admin_email');
        $sent = wp_mail(
            $to,
            sprintf( '[%s] Archive de réservations — %s', get_bloginfo('name'), $start_date ),
            sprintf(
                "Bonjour,\n\nVeuillez trouver ci-joint l'archive des réservations du %s au %s.\n\nNombre de réservations : %d\n%s\n\nCordialement.",
                $start_date,
                $end_date,
                $export['count'],
                $do_delete ? "Ces réservations ont été supprimées de la base après export (RGPD)." : "Ces réservations restent stockées dans la base."
            ),
            [],
            [ $export['filepath'] ]
        );
        $emailed = $sent ? 1 : 0;
        rr_log( 'archive_emailed', "Envoyé à {$to} : " . ( $sent ? 'OK' : 'ÉCHEC' ) );
    }

    // Enregistre dans l'historique des exports
    global $wpdb;
    $wpdb->insert( "{$wpdb->prefix}rr_exports", [
        'type'          => $type,
        'period_start'  => $start_date,
        'period_end'    => $end_date,
        'count'         => $export['count'],
        'filename'      => $export['filename'],
        'filesize'      => $export['filesize'],
        'deleted_count' => $deleted,
        'emailed'       => $emailed,
    ]);

    rr_log( 'archive_created', sprintf(
        'Période %s → %s : %d réservations exportées, %d supprimées, e-mail : %s',
        $start_date, $end_date, $export['count'], $deleted, $emailed ? 'oui' : 'non'
    ));

    return [
        'count'    => $export['count'],
        'deleted'  => $deleted,
        'emailed'  => $emailed,
        'filename' => $export['filename'],
    ];
}

/* ─────────────────────────────────────
   6. CRON QUOTIDIEN
───────────────────────────────────── */

add_action( 'rr_daily_archive', 'rr_cron_archive' );

function rr_cron_archive() {
    $cfg = rr_archive_settings();
    if ( ! $cfg['archive_enabled'] ) return;

    // On archive le mois précédent UNE fois par mois
    // Si on est le 1er du mois, on archive le mois dernier
    $today = current_time( 'Y-m-d' );
    $day   = (int) current_time( 'j' );

    if ( $day !== 1 ) return; // exécution mensuelle (le 1er)

    // Mois précédent
    $first_prev = date( 'Y-m-01', strtotime( 'first day of last month' ) );
    $last_prev  = date( 'Y-m-t',  strtotime( 'last day of last month' ) );

    // On filtre selon la durée de rétention
    $retention = (int) $cfg['archive_retention_days'];
    $cutoff    = date( 'Y-m-d', strtotime( "-{$retention} days" ) );

    // On exporte uniquement les résa antérieures au cutoff
    if ( $last_prev > $cutoff ) {
        $last_prev = $cutoff;
    }
    if ( $first_prev > $last_prev ) return;

    rr_run_archive( $first_prev, $last_prev, 'auto' );
}

/* ─────────────────────────────────────
   7. AJAX — actions admin
───────────────────────────────────── */

// Téléchargement sécurisé d'un fichier d'archive
add_action( 'admin_init', 'rr_handle_archive_download' );
function rr_handle_archive_download() {
    if ( ! isset( $_GET['rr_download'] ) ) return;
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Non autorisé.' );

    $file = sanitize_file_name( $_GET['rr_download'] );
    $path = rr_archive_dir() . '/' . $file;

    if ( ! file_exists( $path ) || pathinfo( $path, PATHINFO_EXTENSION ) !== 'csv' ) {
        wp_die( 'Fichier introuvable.' );
    }

    rr_log( 'archive_downloaded', "Fichier : {$file}" );

    nocache_headers();
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="' . $file . '"' );
    header( 'Content-Length: ' . filesize( $path ) );
    readfile( $path );
    exit;
}

// AJAX : export manuel
add_action( 'wp_ajax_rr_manual_export', 'rr_ajax_manual_export' );
function rr_ajax_manual_export() {
    check_ajax_referer( 'rr_archive_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Non autorisé.' );

    $start  = sanitize_text_field( $_POST['start'] ?? '' );
    $end    = sanitize_text_field( $_POST['end']   ?? '' );
    $delete = ! empty( $_POST['delete'] );
    $email  = ! empty( $_POST['email'] );

    if ( ! $start || ! $end ) wp_send_json_error( 'Dates requises.' );
    if ( $start > $end )      wp_send_json_error( 'La date de début doit être avant la date de fin.' );
    if ( $end >= current_time('Y-m-d') ) wp_send_json_error( 'La date de fin doit être antérieure à aujourd\'hui.' );

    $result = rr_run_archive( $start, $end, 'manual', $delete ? 1 : 0, $email ? 1 : 0 );

    if ( is_wp_error( $result ) ) wp_send_json_error( $result->get_error_message() );
    wp_send_json_success( $result );
}

// AJAX : suppression d'un export (fichier + ligne)
add_action( 'wp_ajax_rr_delete_export', 'rr_ajax_delete_export' );
function rr_ajax_delete_export() {
    check_ajax_referer( 'rr_archive_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Non autorisé.' );

    global $wpdb;
    $id = intval( $_POST['id'] );
    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}rr_exports WHERE id=%d", $id ) );
    if ( ! $row ) wp_send_json_error( 'Export introuvable.' );

    $path = rr_archive_dir() . '/' . $row->filename;
    if ( file_exists( $path ) ) @unlink( $path );

    $wpdb->delete( "{$wpdb->prefix}rr_exports", ['id'=>$id], ['%d'] );
    rr_log( 'export_deleted', "Fichier : {$row->filename}" );
    wp_send_json_success();
}

/* ─────────────────────────────────────
   8. PAGE D'ADMINISTRATION
───────────────────────────────────── */

function rr_archive_page() {
    // Sauvegarde des réglages
    if ( isset( $_POST['rr_save_archive'] ) && check_admin_referer( 'rr_save_archive' ) ) {
        $new = [
            'archive_enabled'        => isset( $_POST['archive_enabled'] ) ? 1 : 0,
            'archive_retention_days' => intval( $_POST['archive_retention_days'] ?? 90 ),
            'archive_delete_after'   => isset( $_POST['archive_delete_after'] ) ? 1 : 0,
            'archive_email_to_resto' => isset( $_POST['archive_email_to_resto'] ) ? 1 : 0,
            'archive_email_address'  => sanitize_email( $_POST['archive_email_address'] ?? '' ),
        ];
        if ( ! in_array( $new['archive_retention_days'], [30,60,90,180,365] ) ) $new['archive_retention_days'] = 90;
        update_option( 'rr_archive_settings', $new );
        rr_log( 'settings_updated', 'Réglages archivage modifiés.' );
        echo '<div class="notice notice-success is-dismissible"><p><strong>Réglages enregistrés.</strong></p></div>';
    }

    $s = rr_archive_settings();

    global $wpdb;
    $exports = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rr_exports ORDER BY created_at DESC LIMIT 50" );
    $logs    = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}rr_logs ORDER BY created_at DESC LIMIT 30" );

    $next_cron = wp_next_scheduled( 'rr_daily_archive' );

    include RR_DIR . 'templates/archive-page.php';
}
