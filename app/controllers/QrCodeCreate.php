<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Date;
use Altum\Uploads;

class QrCodeCreate extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!settings()->links->qr_codes_is_enabled) {
            redirect();
        }

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.qr_codes')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('qr-codes');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `qr_codes` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->qr_codes_limit != -1 && $total_rows >= $this->user->plan_settings->qr_codes_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('qr-codes');
        }

        $qr_code_settings = require APP_PATH . 'includes/qr_code.php';
        $frames = require APP_PATH . 'includes/qr_codes_frames.php';
        $frames_fonts = require APP_PATH . 'includes/qr_codes_frames_text_fonts.php';

        /* Existing projects */
        $projects = (new \Altum\Models\Projects())->get_projects_by_user_id($this->user->user_id);

        $settings = [
            'style' => 'square',
            'inner_eye_style' => 'square',
            'outer_eye_style' => 'square',
            'foreground_type' => 'color',
            'qr_code_background_transparency' => 0,

            'frame' => null,
            'frame_text' => null,
            'frame_text_size' => 0,
            'frame_text_font' => array_key_first($frames_fonts),
            'frame_custom_colors' => false,
            'frame_color' => '#000000',
            'frame_text_color' => '#ffffff',
        ];

        if(!empty($_POST)) {
            $required_fields = ['name', 'type'];

            $_POST['name'] = trim(query_clean($_POST['name']));
            $_POST['project_id'] = !empty($_POST['project_id']) && array_key_exists($_POST['project_id'], $projects) ? (int) $_POST['project_id'] : null;
            $_POST['embedded_data'] = input_clean($_POST['embedded_data'], 10000);
            $_POST['type'] = isset($_POST['type']) && array_key_exists($_POST['type'], $qr_code_settings['type']) ? $_POST['type'] : 'text';
            $settings['inner_eye_style'] = $_POST['inner_eye_style'] = isset($_POST['inner_eye_style']) && in_array($_POST['inner_eye_style'], ['square', 'dot', 'rounded', 'diamond', 'flower', 'leaf',]) ? $_POST['inner_eye_style'] : 'square';
            $settings['outer_eye_style'] = $_POST['outer_eye_style'] = isset($_POST['outer_eye_style']) && in_array($_POST['outer_eye_style'], ['square', 'circle', 'rounded', 'flower', 'leaf',]) ? $_POST['outer_eye_style'] : 'square';
            $settings['style'] = $_POST['style'] = isset($_POST['style']) && in_array($_POST['style'], ['square', 'dot', 'round', 'diamond', 'heart']) ? $_POST['style'] : 'square';
            $settings['foreground_type'] = $_POST['foreground_type'] = isset($_POST['foreground_type']) && in_array($_POST['foreground_type'], ['color', 'gradient']) ? $_POST['foreground_type'] : 'color';
            switch($_POST['foreground_type']) {
                case 'color':
                    $settings['foreground_color'] = $_POST['foreground_color'] = isset($_POST['foreground_color']) && preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['foreground_color']) ? $_POST['foreground_color'] : '#000000';
                    break;

                case 'gradient':
                    $settings['foreground_gradient_style'] = $_POST['foreground_gradient_style'] = isset($_POST['foreground_gradient_style']) && in_array($_POST['foreground_gradient_style'], ['vertical', 'horizontal', 'diagonal', 'inverse_diagonal', 'radial']) ? $_POST['foreground_gradient_style'] : 'horizontal';
                    $settings['foreground_gradient_one'] = $_POST['foreground_gradient_one'] = isset($_POST['foreground_gradient_one']) && preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['foreground_gradient_one']) ? $_POST['foreground_gradient_one'] : '#000000';
                    $settings['foreground_gradient_two'] = $_POST['foreground_gradient_two'] = isset($_POST['foreground_gradient_two']) && preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['foreground_gradient_two']) ? $_POST['foreground_gradient_two'] : '#000000';
                    break;
            }
            $settings['background_color'] = $_POST['background_color'] = isset($_POST['background_color']) && preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['background_color']) ? $_POST['background_color'] : '#ffffff';
            $settings['background_color_transparency'] = $_POST['background_color_transparency'] = isset($_POST['background_color_transparency']) && in_array($_POST['background_color_transparency'], range(0, 100)) ? (int) $_POST['background_color_transparency'] : 0;
            $settings['custom_eyes_color'] = $_POST['custom_eyes_color'] = (int) isset($_POST['custom_eyes_color']);
            if($_POST['custom_eyes_color']) {
                $settings['eyes_inner_color'] = $_POST['eyes_inner_color'] = isset($_POST['eyes_inner_color']) && preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['eyes_inner_color']) ? $_POST['eyes_inner_color'] : '#000000';
                $settings['eyes_outer_color'] = $_POST['eyes_outer_color'] = isset($_POST['eyes_outer_color']) && preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['eyes_outer_color']) ? $_POST['eyes_outer_color'] : '#000000';
            }

            $_POST['qr_code_logo'] = !empty($_FILES['qr_code_logo']['name']) && !(int) isset($_POST['qr_code_logo_remove']);
            $settings['qr_code_logo_size'] = $_POST['qr_code_logo_size'] = isset($_POST['qr_code_logo_size']) && in_array($_POST['qr_code_logo_size'], range(5, 40)) ? (int) $_POST['qr_code_logo_size'] : 25;

            $_POST['qr_code_background'] = !empty($_FILES['qr_code_background']['name']) && !(int) isset($_POST['qr_code_background_remove']);
            $settings['qr_code_background_transparency'] = $_POST['qr_code_background_transparency'] = isset($_POST['qr_code_background_transparency']) && in_array($_POST['qr_code_background_transparency'], range(0, 100)) ? (int) $_POST['qr_code_background_transparency'] : 0;

            $settings['size'] = $_POST['size'] = isset($_POST['size']) && in_array($_POST['size'], range(50, 2000)) ? (int) $_POST['size'] : 500;
            $settings['margin'] = $_POST['margin'] = isset($_POST['margin']) && in_array($_POST['margin'], range(0, 25)) ? (int) $_POST['margin'] : 1;
            $settings['ecc'] = $_POST['ecc'] = isset($_POST['ecc']) && in_array($_POST['ecc'], ['L', 'M', 'Q', 'H']) ? $_POST['ecc'] : 'M';

            /* Frame */
            $settings['frame'] = $_POST['frame'] = isset($_POST['frame']) && array_key_exists($_POST['frame'], $frames) ? input_clean($_POST['frame']) : null;
            $settings['frame_text'] = $_POST['frame_text'] = input_clean($_POST['frame_text'], 64);
            $settings['frame_text_font'] = $_POST['frame_text_font'] = isset($_POST['frame_text_font']) && array_key_exists($_POST['frame_text_font'], $frames_fonts) ? $_POST['frame_text_font'] : array_key_first($frames_fonts);
            $settings['frame_text_size'] = $_POST['frame_text_size'] = in_array($_POST['frame_text_size'] ?? 0, range(-5, 5)) ? (int) $_POST['frame_text_size'] : 0;

            $settings['custom_frame_colors'] = $_POST['custom_frame_colors'] = (int) isset($_POST['custom_frame_colors']);
            if($_POST['custom_frame_colors']) {
                $settings['frame_color'] = $_POST['frame_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['frame_color']) ? null : $_POST['frame_color'];
                $settings['frame_text_color'] = $_POST['frame_text_color'] = !preg_match('/#([A-Fa-f0-9]{3,4}){1,2}\b/i', $_POST['frame_text_color']) ? null : $_POST['frame_text_color'];
            }

            /* Type dependant vars */
            switch($_POST['type']) {
                case 'text':
                    $required_fields[] = 'text';
                    $settings['text'] = $_POST['text'] = mb_substr(input_clean($_POST['text']), 0, $qr_code_settings['type']['text']['max_length']);
                    break;

                case 'url':
                    $required_fields[] = 'url';
                    $settings['url'] = $_POST['url'] = mb_substr(input_clean($_POST['url']), 0, $qr_code_settings['type']['url']['max_length']);
                    break;

                case 'phone':
                    $required_fields[] = 'phone';
                    $settings['phone'] = $_POST['phone'] = mb_substr(input_clean($_POST['phone']), 0, $qr_code_settings['type']['phone']['max_length']);
                    break;

                case 'sms':
                    $required_fields[] = 'sms';
                    $settings['sms'] = $_POST['sms'] = mb_substr(input_clean($_POST['sms']), 0, $qr_code_settings['type']['sms']['max_length']);
                    $settings['sms_body'] = $_POST['sms_body'] = mb_substr(input_clean($_POST['sms_body']), 0, $qr_code_settings['type']['sms']['body']['max_length']);
                    break;

                case 'email':
                    $required_fields[] = 'email';
                    $settings['email'] = $_POST['email'] = mb_substr(input_clean($_POST['email']), 0, $qr_code_settings['type']['email']['max_length']);
                    $settings['email_subject'] = $_POST['email_subject'] = mb_substr(input_clean($_POST['email_subject']), 0, $qr_code_settings['type']['email']['subject']['max_length']);
                    $settings['email_body'] = $_POST['email_body'] = mb_substr(input_clean($_POST['email_body']), 0, $qr_code_settings['type']['email']['body']['max_length']);
                    break;

                case 'whatsapp':
                    $required_fields[] = 'whatsapp';
                    $settings['whatsapp'] = $_POST['whatsapp'] = (int) input_clean($_POST['whatsapp'], $qr_code_settings['type']['whatsapp']['max_length']);
                    $settings['whatsapp_body'] = $_POST['whatsapp_body'] = input_clean($_POST['whatsapp_body'], $qr_code_settings['type']['whatsapp']['body']['max_length']);
                    break;

                case 'facetime':
                    $required_fields[] = 'facetime';
                    $settings['facetime'] = $_POST['facetime'] = mb_substr(input_clean($_POST['facetime']), 0, $qr_code_settings['type']['facetime']['max_length']);
                    break;

                case 'location':
                    $required_fields[] = 'location_latitude';
                    $required_fields[] = 'location_longitude';
                    $settings['location_latitude'] = $_POST['location_latitude'] = (float) mb_substr(input_clean($_POST['location_latitude']), 0, $qr_code_settings['type']['location']['latitude']['max_length']);
                    $settings['location_longitude'] = $_POST['location_longitude'] = (float) mb_substr(input_clean($_POST['location_longitude']), 0, $qr_code_settings['type']['location']['longitude']['max_length']);
                    break;

                case 'wifi':
                    $required_fields[] = 'wifi_ssid';
                    $settings['wifi_ssid'] = $_POST['wifi_ssid'] = mb_substr(input_clean($_POST['wifi_ssid']), 0, $qr_code_settings['type']['wifi']['ssid']['max_length']);
                    $settings['wifi_encryption'] = $_POST['wifi_encryption'] = isset($_POST['wifi_encryption']) && in_array($_POST['wifi_encryption'], ['nopass', 'WEP', 'WPA/WPA2']) ? $_POST['wifi_encryption'] : 'nopass';
                    $settings['wifi_password'] = $_POST['wifi_password'] = mb_substr(input_clean($_POST['wifi_password']), 0, $qr_code_settings['type']['wifi']['password']['max_length']);
                    $settings['wifi_is_hidden'] = $_POST['wifi_is_hidden'] = (int) $_POST['wifi_is_hidden'];
                    break;

                case 'event':
                    $required_fields[] = 'event';
                    $settings['event'] = $_POST['event'] = mb_substr(input_clean($_POST['event']), 0, $qr_code_settings['type']['event']['max_length']);
                    $settings['event_location'] = $_POST['event_location'] = mb_substr(input_clean($_POST['event_location']), 0, $qr_code_settings['type']['event']['location']['max_length']);
                    $settings['event_url'] = $_POST['event_url'] = mb_substr(input_clean($_POST['event_url']), 0, $qr_code_settings['type']['event']['url']['max_length']);
                    $settings['event_note'] = $_POST['event_note'] = mb_substr(input_clean($_POST['event_note']), 0, $qr_code_settings['type']['event']['note']['max_length']);
                    $settings['event_timezone'] = $_POST['event_timezone'] = in_array($_POST['event_timezone'], \DateTimeZone::listIdentifiers()) ? input_clean($_POST['event_timezone']) : Date::$default_timezone;
                    $settings['event_start_datetime'] = $_POST['event_start_datetime'] = (new \DateTime($_POST['event_start_datetime']))->format('Y-m-d\TH:i:s');
                    $settings['event_end_datetime'] = $_POST['event_end_datetime'] = (new \DateTime($_POST['event_end_datetime']))->format('Y-m-d\TH:i:s');
                    break;

                case 'crypto':
                    $required_fields[] = 'crypto_address';
                    $settings['crypto_coin'] = $_POST['crypto_coin'] = isset($_POST['crypto_coin']) && array_key_exists($_POST['crypto_coin'], $qr_code_settings['type']['crypto']['coins']) ? $_POST['crypto_coin'] : array_key_first($qr_code_settings['type']['crypto']['coins']);
                    $settings['crypto_address'] = $_POST['crypto_address'] = mb_substr(input_clean($_POST['crypto_address']), 0, $qr_code_settings['type']['crypto']['address']['max_length']);
                    $settings['crypto_amount'] = $_POST['crypto_amount'] = isset($_POST['crypto_amount']) ? (float) $_POST['crypto_amount'] : null;
                    break;

                case 'vcard':
                    $settings['vcard_first_name'] = $_POST['vcard_first_name'] = mb_substr(input_clean($_POST['vcard_first_name']), 0, $qr_code_settings['type']['vcard']['first_name']['max_length']);
                    $settings['vcard_last_name'] = $_POST['vcard_last_name'] = mb_substr(input_clean($_POST['vcard_last_name']), 0, $qr_code_settings['type']['vcard']['last_name']['max_length']);
                    $settings['vcard_email'] = $_POST['vcard_email'] = mb_substr(input_clean($_POST['vcard_email']), 0, $qr_code_settings['type']['vcard']['email']['max_length']);
                    $settings['vcard_url'] = $_POST['vcard_url'] = mb_substr(input_clean($_POST['vcard_url']), 0, $qr_code_settings['type']['vcard']['url']['max_length']);
                    $settings['vcard_company'] = $_POST['vcard_company'] = mb_substr(input_clean($_POST['vcard_company']), 0, $qr_code_settings['type']['vcard']['company']['max_length']);
                    $settings['vcard_job_title'] = $_POST['vcard_job_title'] = mb_substr(input_clean($_POST['vcard_job_title']), 0, $qr_code_settings['type']['vcard']['job_title']['max_length']);
                    $settings['vcard_birthday'] = $_POST['vcard_birthday'] = mb_substr(input_clean($_POST['vcard_birthday']), 0, $qr_code_settings['type']['vcard']['birthday']['max_length']);
                    $settings['vcard_street'] = $_POST['vcard_street'] = mb_substr(input_clean($_POST['vcard_street']), 0, $qr_code_settings['type']['vcard']['street']['max_length']);
                    $settings['vcard_city'] = $_POST['vcard_city'] = mb_substr(input_clean($_POST['vcard_city']), 0, $qr_code_settings['type']['vcard']['city']['max_length']);
                    $settings['vcard_zip'] = $_POST['vcard_zip'] = mb_substr(input_clean($_POST['vcard_zip']), 0, $qr_code_settings['type']['vcard']['zip']['max_length']);
                    $settings['vcard_region'] = $_POST['vcard_region'] = mb_substr(input_clean($_POST['vcard_region']), 0, $qr_code_settings['type']['vcard']['region']['max_length']);
                    $settings['vcard_country'] = $_POST['vcard_country'] = mb_substr(input_clean($_POST['vcard_country']), 0, $qr_code_settings['type']['vcard']['country']['max_length']);
                    $settings['vcard_note'] = $_POST['vcard_note'] = mb_substr(input_clean($_POST['vcard_note']), 0, $qr_code_settings['type']['vcard']['note']['max_length']);

                    /* Phone numbers */
                    if(!isset($_POST['vcard_phone_number_label'])) {
                        $_POST['vcard_phone_number_label'] = [];
                        $_POST['vcard_phone_number_value'] = [];
                    }
                    $vcard_phone_numbers = [];
                    foreach($_POST['vcard_phone_number_label'] as $key => $value) {
                        if(empty(trim($value))) continue;
                        if($key >= 20) continue;

                        $vcard_phone_numbers[] = [
                            'label' => mb_substr(input_clean($value), 0, $qr_code_settings['type']['vcard']['phone_number_value']['max_length']),
                            'value' => mb_substr(input_clean($_POST['vcard_phone_number_value'][$key]), 0, $qr_code_settings['type']['vcard']['phone_number_value']['max_length'])
                        ];
                    }
                    $settings['vcard_phone_numbers'] = $vcard_phone_numbers;

                    /* Socials */
                    if(!isset($_POST['vcard_social_label'])) {
                        $_POST['vcard_social_label'] = [];
                        $_POST['vcard_social_value'] = [];
                    }

                    $vcard_socials = [];
                    foreach($_POST['vcard_social_label'] as $key => $value) {
                        if(empty(trim($value))) continue;
                        if($key >= 20) continue;

                        $vcard_socials[] = [
                            'label' => mb_substr(input_clean($value), 0, $qr_code_settings['type']['vcard']['social_value']['max_length']),
                            'value' => mb_substr(input_clean($_POST['vcard_social_value'][$key]), 0, $qr_code_settings['type']['vcard']['social_value']['max_length'])
                        ];
                    }
                    $settings['vcard_socials'] = $vcard_socials;
                    break;

                case 'paypal':
                    $required_fields[] = 'paypal_email';
                    $required_fields[] = 'paypal_title';
                    $required_fields[] = 'paypal_currency';
                    $required_fields[] = 'paypal_price';
                    $settings['paypal_type'] = $_POST['paypal_type'] = isset($_POST['paypal_type']) && array_key_exists($_POST['paypal_type'], $qr_code_settings['type']['paypal']['type']) ? $_POST['paypal_type'] : array_key_first($qr_code_settings['type']['paypal']['type']);
                    $settings['paypal_email'] = $_POST['paypal_email'] = mb_substr(input_clean($_POST['paypal_email']), 0, $qr_code_settings['type']['paypal']['email']['max_length']);
                    $settings['paypal_title'] = $_POST['paypal_title'] = mb_substr(input_clean($_POST['paypal_title']), 0, $qr_code_settings['type']['paypal']['title']['max_length']);
                    $settings['paypal_currency'] = $_POST['paypal_currency'] = mb_substr(input_clean($_POST['paypal_currency']), 0, $qr_code_settings['type']['paypal']['currency']['max_length']);
                    $settings['paypal_price'] = $_POST['paypal_price'] = (float) $_POST['paypal_price'];
                    $settings['paypal_thank_you_url'] = $_POST['paypal_thank_you_url'] = mb_substr(input_clean($_POST['paypal_thank_you_url']), 0, $qr_code_settings['type']['paypal']['thank_you_url']['max_length']);
                    $settings['paypal_cancel_url'] = $_POST['paypal_cancel_url'] = mb_substr(input_clean($_POST['paypal_cancel_url']), 0, $qr_code_settings['type']['paypal']['cancel_url']['max_length']);
                    break;

                case 'upi':
                    $required_fields[] = 'upi_payee_id';
                    $required_fields[] = 'upi_payee_name';
                    $settings['upi_payee_id'] = $_POST['upi_payee_id'] = mb_substr(input_clean($_POST['upi_payee_id']), 0, $qr_code_settings['type']['upi']['payee_id']['max_length']);
                    $settings['upi_payee_name'] = $_POST['upi_payee_name'] = mb_substr(input_clean($_POST['upi_payee_name']), 0, $qr_code_settings['type']['upi']['payee_name']['max_length']);
                    $settings['upi_currency'] = $_POST['upi_currency'] = in_array($_POST['upi_currency'], ['INR']) ? $_POST['upi_currency'] : 'INR';
                    $settings['upi_amount'] = isset($_POST['upi_amount']) ? (float) $_POST['upi_amount'] : null;
                    $settings['upi_transaction_id'] = $_POST['upi_transaction_id'] = mb_substr(input_clean($_POST['upi_transaction_id']), 0, $qr_code_settings['type']['upi']['transaction_id']['max_length']);
                    $settings['upi_transaction_note'] = $_POST['upi_transaction_note'] = mb_substr(input_clean($_POST['upi_transaction_note']), 0, $qr_code_settings['type']['upi']['transaction_note']['max_length']);
                    $settings['upi_transaction_reference'] = $_POST['upi_transaction_reference'] = mb_substr(input_clean($_POST['upi_transaction_reference']), 0, $qr_code_settings['type']['upi']['transaction_reference']['max_length']);
                    $settings['upi_thank_you_url'] = $_POST['upi_thank_you_url'] = mb_substr(input_clean($_POST['upi_thank_you_url']), 0, $qr_code_settings['type']['upi']['thank_you_url']['max_length']);
                    break;

                case 'epc':
                    $required_fields[] = 'epc_iban';
                    $required_fields[] = 'epc_payee_name';
                    $settings['epc_iban'] = $_POST['epc_iban'] = mb_substr(input_clean($_POST['epc_iban']), 0, $qr_code_settings['type']['epc']['iban']['max_length']);
                    $settings['epc_payee_name'] = $_POST['epc_payee_name'] = mb_substr(input_clean($_POST['epc_payee_name']), 0, $qr_code_settings['type']['epc']['payee_name']['max_length']);
                    $settings['epc_currency'] = $_POST['epc_currency'] = in_array($_POST['epc_currency'], ['EUR']) ? $_POST['epc_currency'] : 'EUR';
                    $settings['epc_amount'] = isset($_POST['epc_amount']) ? (float) $_POST['epc_amount'] : null;
                    $settings['epc_bic'] = $_POST['epc_bic'] = mb_substr(input_clean($_POST['epc_bic']), 0, $qr_code_settings['type']['epc']['bic']['max_length']);
                    $settings['epc_remittance_reference'] = $_POST['epc_remittance_reference'] = mb_substr(input_clean($_POST['epc_remittance_reference']), 0, $qr_code_settings['type']['epc']['remittance_reference']['max_length']);
                    $settings['epc_remittance_text'] = $_POST['epc_remittance_text'] = mb_substr(input_clean($_POST['epc_remittance_text']), 0, $qr_code_settings['type']['epc']['remittance_text']['max_length']);
                    $settings['information'] = $_POST['information'] = mb_substr(input_clean($_POST['information']), 0, $qr_code_settings['type']['epc']['information']['max_length']);
                    break;
            }

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $qr_code_logo = \Altum\Uploads::process_upload(null, 'qr_code_logo', 'qr_code_logo', 'qr_code_logo_remove', $qr_code_settings['qr_code_logo_size_limit']);
            $qr_code_background = \Altum\Uploads::process_upload(null, 'qr_code_background', 'qr_code_background', 'qr_code_background_remove', $qr_code_settings['qr_code_background_size_limit']);

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $qr_code = null;

                /* QR Code image */
                if($_POST['qr_code']) {
                    $_POST['qr_code'] = base64_decode(mb_substr($_POST['qr_code'], mb_strlen('data:image/svg+xml;base64,')));

                    /* Generate new name for image */
                    $image_new_name = md5(time() . rand()) . '.svg';

                    /* Offload uploading */
                    if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
                        try {
                            $s3 = new \Aws\S3\S3Client(get_aws_s3_config());

                            /* Upload image */
                            $result = $s3->putObject([
                                'Bucket' => settings()->offload->storage_name,
                                'Key' =>  UPLOADS_URL_PATH . Uploads::get_path('qr_code') . $image_new_name,
                                'ContentType' => 'image/svg+xml',
                                'Body' => $_POST['qr_code'],
                                'ACL' => 'public-read'
                            ]);
                        } catch (\Exception $exception) {
                            Alerts::add_error($exception->getMessage());
                        }
                    }

                    /* Local uploading */
                    else {
                        /* Upload the original */
                        file_put_contents(Uploads::get_full_path('qr_code') . $image_new_name, $_POST['qr_code']);
                    }

                    $qr_code = $image_new_name;
                }

                $settings = json_encode($settings);

                /* Database query */
                $qr_code_id = db()->insert('qr_codes', [
                    'user_id' => $this->user->user_id,
                    'project_id' => $_POST['project_id'],
                    'name' => $_POST['name'],
                    'type' => $_POST['type'],
                    'settings' => $settings,
                    'embedded_data' => $_POST['embedded_data'],
                    'qr_code' => $qr_code,
                    'qr_code_logo' => $qr_code_logo,
                    'qr_code_background' => $qr_code_background,
                    'datetime' => \Altum\Date::$date,
                ]);

                /* Clear the cache */
                \Altum\Cache::$adapter->deleteItem('qr_codes_total?user_id=' . $this->user->user_id);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                redirect('qr-code-update/' . $qr_code_id);
            }
        }

        /* Set default values */
        $settings['text'] = $settings['text'] ?? $_GET['text'] ?? null;
        $settings['url'] = $settings['url'] ?? $_GET['url'] ?? null;

        $values = [
            'name' => $_POST['name'] ?? $_GET['name'] ?? '',
            'type' => $_POST['type'] ?? $_GET['type'] ?? array_key_first($qr_code_settings['type']),
            'url' => $_POST['url'] ?? $_GET['url'] ?? '',
            'project_id' => $_POST['project_id'] ?? $_GET['project_id'] ?? '',
            'embedded_data' => $_POST['embedded_data'] ?? $_GET['embedded_data'] ?? '',
            'settings' => $settings
        ];

        /* Prepare the View */
        $data = [
            'qr_code_settings' => $qr_code_settings,
            'frames_fonts' => $frames_fonts,
            'frames' => $frames,
            'projects' => $projects,
            'values' => $values
        ];

        $view = new \Altum\View('qr-code-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}