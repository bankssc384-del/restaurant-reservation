<?php
/**
 * Module : Notifications SMS via Brevo (ex-Sendinblue)
 * Documentation : https://developers.brevo.com/reference/sendtransacsms
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function rr_sms_defaults() {
    return [
        'sms_enabled'    => 0,
        'sms_provider'   => 'brevo',
        'sms_api_key'    => '',
        'sms_sender'     => 'Resto',          // 11 char max, alphanumérique
        'sms_msg_received'  => "Bonjour {name}, nous avons bien reçu votre demande de réservation pour le {date} à {time}. À bientôt !",
        'sms_msg_confirmed' => "Bonjour {name}, votre réservation pour le {date} à {time} ({guests} couverts) est confirmée. À bientôt !",
        'sms_msg_cancelled' => "Bonjour {name}, nous ne pouvons pas honorer votre réservation du {date}. N'hésitez pas à nous recontacter.",
    ];
}

function rr_sms_settings() {
    $s = get_option( 'rr_sms_settings', [] );
    return array_merge( rr_sms_defaults(), is_array($s) ? $s : [] );
}

/* Quel canal de notification utiliser ? */
function rr_notification_channels() {
    $s = get_option( 'rr_settings', [] );
    $channels = isset( $s['notify_channels'] ) ? $s['notify_channels'] : [ 'email' ];
    return is_array( $channels ) ? $channels : [ 'email' ];
}

/* ─────────────────────────────────────
   FORMATER LE NUMÉRO POUR L'API
───────────────────────────────────── */
function rr_format_phone( $phone ) {
    // Nettoyage : ne garde que les chiffres et le +
    $clean = preg_replace( '/[^\d+]/', '', $phone );

    // Si le numéro commence par 0 (FR), on le convertit en +33
    if ( substr( $clean, 0, 1 ) === '0' ) {
        $clean = '+33' . substr( $clean, 1 );
    }
    // Si pas de + au début, on en ajoute un
    if ( substr( $clean, 0, 1 ) !== '+' ) {
        $clean = '+' . $clean;
    }
    return $clean;
}

/* ─────────────────────────────────────
   ENVOI SMS via Brevo
───────────────────────────────────── */
function rr_send_sms( $phone, $message ) {
    $cfg = rr_sms_settings();

    if ( ! $cfg['sms_enabled'] || empty( $cfg['sms_api_key'] ) ) {
        return new WP_Error( 'sms_disabled', 'SMS désactivé ou clé API manquante.' );
    }

    $to = rr_format_phone( $phone );

    $response = wp_remote_post( 'https://api.brevo.com/v3/transactionalSMS/sms', [
        'timeout' => 15,
        'headers' => [
            'accept'       => 'application/json',
            'api-key'      => $cfg['sms_api_key'],
            'content-type' => 'application/json',
        ],
        'body' => wp_json_encode([
            'sender'    => substr( $cfg['sms_sender'], 0, 11 ),
            'recipient' => $to,
            'content'   => $message,
            'type'      => 'transactional',
        ]),
    ]);

    if ( is_wp_error( $response ) ) {
        if ( function_exists('rr_log') ) rr_log( 'sms_failed', $response->get_error_message() );
        return $response;
    }

    $code = wp_remote_retrieve_response_code( $response );
    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( $code >= 200 && $code < 300 ) {
        if ( function_exists('rr_log') ) rr_log( 'sms_sent', "Envoyé à {$to}" );
        return true;
    }

    $err = $body['message'] ?? 'Erreur API ' . $code;
    if ( function_exists('rr_log') ) rr_log( 'sms_failed', "À {$to} : {$err}" );
    return new WP_Error( 'sms_api_error', $err );
}

/* ─────────────────────────────────────
   ENVOI D'UNE NOTIFICATION (template)
───────────────────────────────────── */
function rr_send_sms_template( $data, $type ) {
    $cfg = rr_sms_settings();
    $key = "sms_msg_{$type}";
    if ( empty( $cfg[ $key ] ) ) return false;

    $vars = [
        '{name}'   => $data['name']   ?? '',
        '{date}'   => isset($data['date']) ? date_i18n( 'j/m/Y', strtotime( $data['date'] ) ) : '',
        '{time}'   => $data['time']   ?? '',
        '{guests}' => $data['guests'] ?? '',
    ];
    $message = strtr( $cfg[ $key ], $vars );
    return rr_send_sms( $data['phone'] ?? '', $message );
}

/* ─────────────────────────────────────
   AJAX — test d'envoi
───────────────────────────────────── */
add_action( 'wp_ajax_rr_test_sms', 'rr_ajax_test_sms' );
function rr_ajax_test_sms() {
    check_ajax_referer( 'rr_sms_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Non autorisé.' );

    $phone = sanitize_text_field( $_POST['phone'] ?? '' );
    if ( ! $phone ) wp_send_json_error( 'Numéro requis.' );

    $r = rr_send_sms( $phone, 'Test SMS depuis votre site WordPress. Si vous recevez ce message, la configuration est OK !' );
    if ( is_wp_error( $r ) ) wp_send_json_error( $r->get_error_message() );
    wp_send_json_success();
}

/* ─────────────────────────────────────
   PAGE D'ADMINISTRATION SMS
───────────────────────────────────── */
function rr_sms_page() {
    if ( isset( $_POST['rr_save_sms'] ) && check_admin_referer( 'rr_save_sms' ) ) {
        $defaults = rr_sms_defaults();
        $new = [];
        foreach ( $defaults as $k => $v ) {
            if ( $k === 'sms_enabled' ) {
                $new[$k] = isset( $_POST[$k] ) ? 1 : 0;
            } else {
                $new[$k] = isset( $_POST[$k] ) ? sanitize_textarea_field( wp_unslash( $_POST[$k] ) ) : $v;
            }
        }
        update_option( 'rr_sms_settings', $new );
        if ( function_exists('rr_log') ) rr_log( 'settings_updated', 'Réglages SMS modifiés.' );
        echo '<div class="notice notice-success is-dismissible"><p><strong>Réglages SMS enregistrés.</strong></p></div>';
    }
    $s = rr_sms_settings();
    include RR_DIR . 'templates/sms-page.php';
}
