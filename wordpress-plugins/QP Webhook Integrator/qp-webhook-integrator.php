<?php
/**
 * Plugin Name: QP Webhook Integrator for Make
 * Plugin URI: https://github.com/irlemos/quantum-portal
 * Description: Adds up to three configurable webhook buttons to the post list to call Make webhooks with post ID, and a settings page to configure and trigger additional fixed webhooks. Designed for robustness and compatibility with Make (https://make.com).
 * Version: 1.2
 * Author: Rodrigo Lemos Del Poço
 * Author URI: https://www.linkedin.com/in/irlemos
 * License: GPL-2.0+
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants for better organization and to avoid conflicts
define('QP_WEBHOOK_INTEGRATOR_VERSION', '1.2');
define('QP_WEBHOOK_INTEGRATOR_SLUG', 'qp_webhook_integrator');
define('QP_WEBHOOK_INTEGRATOR_OPTION_GROUP', 'qp_webhook_integrator_group');
define('QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_1', 'qp_webhook_integrator_webhook_url_1');
define('QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_1', 'qp_webhook_integrator_webhook_name_1');
define('QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_2', 'qp_webhook_integrator_webhook_url_2');
define('QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_2', 'qp_webhook_integrator_webhook_name_2');
define('QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_3', 'qp_webhook_integrator_webhook_url_3');
define('QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_3', 'qp_webhook_integrator_webhook_name_3');

// Load text domain for translations
function qp_webhook_integrator_load_textdomain() {
    load_plugin_textdomain('qp-webhook-integrator', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'qp_webhook_integrator_load_textdomain');

// Load plugin only if in admin or when needed, to reduce conflicts
if (is_admin()) {
    // Add settings page
    function qp_webhook_integrator_add_settings_page() {
        add_options_page(
            __('QP Webhook Integrator Settings', 'qp-webhook-integrator'),
            __('QP Webhook Integrator', 'qp-webhook-integrator'),
            'manage_options',
            QP_WEBHOOK_INTEGRATOR_SLUG,
            'qp_webhook_integrator_settings_page'
        );
    }
    add_action('admin_menu', 'qp_webhook_integrator_add_settings_page');

    // Register settings with validation callback for security
    function qp_webhook_integrator_register_settings() {
        register_setting(QP_WEBHOOK_INTEGRATOR_OPTION_GROUP, QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_1, [
            'sanitize_callback' => 'esc_url_raw',
            'default' => '',
        ]);
        register_setting(QP_WEBHOOK_INTEGRATOR_OPTION_GROUP, QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_1, [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('Webhook 1', 'qp-webhook-integrator'),
        ]);
        register_setting(QP_WEBHOOK_INTEGRATOR_OPTION_GROUP, QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_2, [
            'sanitize_callback' => 'esc_url_raw',
            'default' => '',
        ]);
        register_setting(QP_WEBHOOK_INTEGRATOR_OPTION_GROUP, QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_2, [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('Webhook 2', 'qp-webhook-integrator'),
        ]);
        register_setting(QP_WEBHOOK_INTEGRATOR_OPTION_GROUP, QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_3, [
            'sanitize_callback' => 'esc_url_raw',
            'default' => '',
        ]);
        register_setting(QP_WEBHOOK_INTEGRATOR_OPTION_GROUP, QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_3, [
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('Webhook 3', 'qp-webhook-integrator'),
        ]);
    }
    add_action('admin_init', 'qp_webhook_integrator_register_settings');

    // Settings page content with error handling
    function qp_webhook_integrator_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'qp-webhook-integrator'));
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('QP Webhook Integrator Settings', 'qp-webhook-integrator'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields(QP_WEBHOOK_INTEGRATOR_OPTION_GROUP);
                do_settings_sections(QP_WEBHOOK_INTEGRATOR_OPTION_GROUP);
                ?>
                <h2><?php esc_html_e('Post List Webhooks', 'qp-webhook-integrator'); ?></h2>
                <p><?php esc_html_e('Configure up to three webhooks to be available in the post list.', 'qp-webhook-integrator'); ?></p>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Webhook 1 URL', 'qp-webhook-integrator'); ?></th>
                        <td><input type="url" name="<?php echo esc_attr(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_1); ?>" value="<?php echo esc_attr(get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_1)); ?>" style="width: 400px;" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Webhook 1 Name (displayed in post list)', 'qp-webhook-integrator'); ?></th>
                        <td><input type="text" name="<?php echo esc_attr(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_1); ?>" value="<?php echo esc_attr(get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_1)); ?>" style="width: 400px;" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Webhook 2 URL', 'qp-webhook-integrator'); ?></th>
                        <td><input type="url" name="<?php echo esc_attr(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_2); ?>" value="<?php echo esc_attr(get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_2)); ?>" style="width: 400px;" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Webhook 2 Name (displayed in post list)', 'qp-webhook-integrator'); ?></th>
                        <td><input type="text" name="<?php echo esc_attr(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_2); ?>" value="<?php echo esc_attr(get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_2)); ?>" style="width: 400px;" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Webhook 3 URL', 'qp-webhook-integrator'); ?></th>
                        <td><input type="url" name="<?php echo esc_attr(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_3); ?>" value="<?php echo esc_attr(get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_3)); ?>" style="width: 400px;" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Webhook 3 Name (displayed in post list)', 'qp-webhook-integrator'); ?></th>
                        <td><input type="text" name="<?php echo esc_attr(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_3); ?>" value="<?php echo esc_attr(get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_3)); ?>" style="width: 400px;" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <h2><?php esc_html_e('Additional Fixed Webhooks', 'qp-webhook-integrator'); ?></h2>
            <p><?php esc_html_e('These are fixed webhooks you can trigger from here.', 'qp-webhook-integrator'); ?></p>

            <?php
            // Fixed webhooks defined here - make this filterable for extensibility
            $fixed_webhooks = apply_filters('qp_webhook_integrator_fixed_webhooks', [
                [
                    'title' => 'Webhook 1',
                    'description' => 'This webhook does something specific, like syncing data.',
                    'url' => 'https://hook.eu2.make.com/your-fixed-webhook-1-url', // Replace with actual URL
                ],
                [
                    'title' => 'Webhook 2',
                    'description' => 'This webhook handles another task, e.g., cleanup.',
                    'url' => 'https://hook.eu2.make.com/your-fixed-webhook-2-url', // Replace with actual URL
                ],
            ]);

            foreach ($fixed_webhooks as $index => $hook) {
                if (empty($hook['url']) || !filter_var($hook['url'], FILTER_VALIDATE_URL)) {
                    continue; // Skip invalid hooks
                }
                ?>
                <div style="margin-bottom: 20px;">
                    <h3><?php echo esc_html($hook['title']); ?></h3>
                    <p><?php echo esc_html($hook['description']); ?></p>
                    <form method="post" action="">
                        <input type="hidden" name="webhook_action" value="call_fixed_webhook" />
                        <input type="hidden" name="webhook_index" value="<?php echo esc_attr($index); ?>" />
                        <?php wp_nonce_field('call_fixed_webhook_nonce', 'nonce'); ?>
                        <input type="submit" class="button button-primary" value="<?php echo esc_attr(sprintf(__('Execute %s', 'qp-webhook-integrator'), $hook['title'])); ?>" />
                    </form>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }

    // Handle fixed webhook calls with robust error handling
    function qp_webhook_integrator_handle_fixed_webhook() {
        if (isset($_POST['webhook_action']) && $_POST['webhook_action'] === 'call_fixed_webhook' && current_user_can('manage_options')) {
            if (!check_admin_referer('call_fixed_webhook_nonce', 'nonce')) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error"><p>' . esc_html__('Nonce verification failed. Action aborted for security.', 'qp-webhook-integrator') . '</p></div>';
                });
                return;
            }

            $fixed_webhooks = apply_filters('qp_webhook_integrator_fixed_webhooks', [
                [
                    'title' => 'Webhook 1',
                    'description' => 'This webhook does something specific, like syncing data.',
                    'url' => 'https://hook.eu2.make.com/your-fixed-webhook-1-url',
                ],
                [
                    'title' => 'Webhook 2',
                    'description' => 'This webhook handles another task, e.g., cleanup.',
                    'url' => 'https://hook.eu2.make.com/your-fixed-webhook-2-url',
                ],
            ]);

            $index = isset($_POST['webhook_index']) ? intval($_POST['webhook_index']) : -1;
            if ($index < 0 || !isset($fixed_webhooks[$index])) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error"><p>' . esc_html__('Invalid webhook index.', 'qp-webhook-integrator') . '</p></div>';
                });
                return;
            }

            $url = $fixed_webhooks[$index]['url'];
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error"><p>' . esc_html__('Invalid webhook URL.', 'qp-webhook-integrator') . '</p></div>';
                });
                return;
            }

            $data = apply_filters('qp_webhook_integrator_fixed_webhook_data', ['triggered_from' => 'WordPress QP Webhook Integrator'], $index);
            $args = apply_filters('qp_webhook_integrator_wp_remote_args', [
                'body' => wp_json_encode($data),
                'headers' => ['Content-Type' => 'application/json'],
                'timeout' => 30,
                'sslverify' => true,
            ], $url);

            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                error_log('QP Webhook Integrator Error: ' . $error_message);
                add_action('admin_notices', function() use ($error_message) {
                    echo '<div class="notice notice-error"><p>' . esc_html__('Error calling webhook: ', 'qp-webhook-integrator') . esc_html($error_message) . '</p></div>';
                });
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                if ($response_code >= 200 && $response_code < 300) {
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-success"><p>' . esc_html__('Webhook called successfully!', 'qp-webhook-integrator') . '</p></div>';
                    });
                } else {
                    $body = wp_remote_retrieve_body($response);
                    error_log('QP Webhook Integrator: Non-success response - Code: ' . $response_code . ' Body: ' . $body);
                    add_action('admin_notices', function() use ($response_code) {
                        echo '<div class="notice notice-warning"><p>' . esc_html__('Webhook called but returned non-success status: ', 'qp-webhook-integrator') . esc_html($response_code) . '</p></div>';
                    });
                }
            }
        }
    }
    add_action('admin_init', 'qp_webhook_integrator_handle_fixed_webhook');

    // Add action links to post row actions with checks
    function qp_webhook_integrator_post_row_actions($actions, $post) {
        if (current_user_can('edit_posts') && $post->post_type === 'post') {
            $webhooks = [
                1 => [
                    'url' => get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_1),
                    'name' => get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_1) ?: __('Webhook 1', 'qp-webhook-integrator'),
                ],
                2 => [
                    'url' => get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_2),
                    'name' => get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_2) ?: __('Webhook 2', 'qp-webhook-integrator'),
                ],
                3 => [
                    'url' => get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_3),
                    'name' => get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_3) ?: __('Webhook 3', 'qp-webhook-integrator'),
                ],
            ];

            foreach ($webhooks as $index => $webhook) {
                if (!empty($webhook['url']) && filter_var($webhook['url'], FILTER_VALIDATE_URL)) {
                    $nonce = wp_create_nonce('call_post_webhook_' . $post->ID . '_' . $index);
                    $actions['call_webhook_' . $index] = '<a href="' . esc_url(admin_url('admin-post.php?action=call_post_webhook&post_id=' . $post->ID . '&webhook_index=' . $index . '&_wpnonce=' . $nonce)) . '" onclick="return confirm(\'' . esc_attr(sprintf(__('Are you sure you want to call %s for this post?', 'qp-webhook-integrator'), $webhook['name'])) . '\');">' . esc_html($webhook['name']) . '</a>';
                }
            }
        }
        return $actions;
    }
    add_filter('post_row_actions', 'qp_webhook_integrator_post_row_actions', 10, 2);

    // Handle post webhook call with robust error handling
    function qp_webhook_integrator_handle_post_webhook() {
        if (!current_user_can('edit_posts') || !isset($_GET['post_id']) || !isset($_GET['webhook_index'])) {
            wp_die(__('Invalid request or insufficient permissions.', 'qp-webhook-integrator'));
        }

        $post_id = intval($_GET['post_id']);
        if ($post_id <= 0 || get_post($post_id) === null) {
            wp_die(__('Invalid post ID.', 'qp-webhook-integrator'));
        }

        $webhook_index = intval($_GET['webhook_index']);
        if ($webhook_index < 1 || $webhook_index > 3) {
            wp_die(__('Invalid webhook index.', 'qp-webhook-integrator'));
        }

        $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
        if (!wp_verify_nonce($nonce, 'call_post_webhook_' . $post_id . '_' . $webhook_index)) {
            wp_die(__('Nonce verification failed. Action aborted for security.', 'qp-webhook-integrator'));
        }

        $webhooks = [
            1 => get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_1),
            2 => get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_2),
            3 => get_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_3),
        ];

        $webhook_url = $webhooks[$webhook_index];
        if (empty($webhook_url) || !filter_var($webhook_url, FILTER_VALIDATE_URL)) {
            wp_die(__('Webhook URL not configured or invalid.', 'qp-webhook-integrator'));
        }

        $data = apply_filters('qp_webhook_integrator_post_webhook_data', ['post_id' => $post_id], $post_id, $webhook_index);
        $args = apply_filters('qp_webhook_integrator_wp_remote_args', [
            'body' => wp_json_encode($data),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 30,
            'sslverify' => true,
        ], $webhook_url);

        $response = wp_remote_post($webhook_url, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('QP Webhook Integrator Post Error: ' . $error_message);
            wp_die(__('Error calling webhook: ', 'qp-webhook-integrator') . esc_html($error_message));
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code >= 200 && $response_code < 300) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success"><p>' . esc_html__('Webhook called successfully for post!', 'qp-webhook-integrator') . '</p></div>';
                });
            } else {
                $body = wp_remote_retrieve_body($response);
                error_log('QP Webhook Integrator Post: Non-success response - Code: ' . $response_code . ' Body: ' . $body);
                add_action('admin_notices', function() use ($response_code) {
                    echo '<div class="notice notice-warning"><p>' . esc_html__('Webhook called but returned non-success status: ', 'qp-webhook-integrator') . esc_html($response_code) . '</p></div>';
                });
            }
            wp_safe_redirect(admin_url('edit.php'));
            exit;
        }
    }
    add_action('admin_post_call_post_webhook', 'qp_webhook_integrator_handle_post_webhook');

    // Enqueue scripts if needed
    function qp_webhook_integrator_enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_' . QP_WEBHOOK_INTEGRATOR_SLUG) {
            return;
        }
        wp_enqueue_style('qp-webhook-integrator-admin-css', plugin_dir_url(__FILE__) . 'css/admin.css', [], QP_WEBHOOK_INTEGRATOR_VERSION);
    }
    add_action('admin_enqueue_scripts', 'qp_webhook_integrator_enqueue_admin_scripts');

    // Activation hook
    function qp_webhook_integrator_activate() {
        // Future: Add database schema or checks if needed
    }
    register_activation_hook(__FILE__, 'qp_webhook_integrator_activate');

    // Deactivation hook
    function qp_webhook_integrator_deactivate() {
        // Future: Clean up transients or options if needed
    }
    register_deactivation_hook(__FILE__, 'qp_webhook_integrator_deactivate');

    // Uninstall hook
    function qp_webhook_integrator_uninstall() {
        delete_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_1);
        delete_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_1);
        delete_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_2);
        delete_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_2);
        delete_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_URL_3);
        delete_option(QP_WEBHOOK_INTEGRATOR_WEBHOOK_NAME_3);
    }
    register_uninstall_hook(__FILE__, 'qp_webhook_integrator_uninstall');
}