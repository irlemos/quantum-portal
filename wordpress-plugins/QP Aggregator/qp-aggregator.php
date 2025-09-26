<?php
/**
 * Plugin Name: QP Aggregator
 * Plugin URI: https://github.com/irlemos/quantum-portal
 * Description: Agregador de matérias dos subdomínios via API REST, com shortcode, tela de configuração, templates HTML personalizados, paginação e filtros por categoria/tag.
 * Version: 1.3
 * Author: Rodrigo Lemos Del Poço
 * Author URI: https://www.linkedin.com/in/irlemos
 * License: GPL-2.0+
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Evitar acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Verificar dependências
if (!function_exists('wp_remote_get') || !function_exists('add_shortcode') || !function_exists('add_options_page')) {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>QP Aggregator: Funções essenciais do WordPress não encontradas. Atualize o WordPress para 5.0 ou superior.</p></div>';
    });
    return;
}

// Registrar a tela de configuração
function quantumportal_register_settings() {
    add_options_page(
        'QP Aggregator Configurações',
        'QP Aggregator',
        'manage_options',
        'quantumportal-aggregator',
        'quantumportal_settings_page'
    );
}
add_action('admin_menu', 'quantumportal_register_settings');

// Enqueue scripts para campos repetíveis
function quantumportal_admin_scripts($hook) {
    if ($hook !== 'settings_page_quantumportal-aggregator') {
        return;
    }
    wp_enqueue_script('jquery');
    wp_enqueue_script('quantumportal-repeater', plugin_dir_url(__FILE__) . 'assets/repeater.js', array('jquery'), '1.3', true);
    wp_enqueue_style('quantumportal-admin', plugin_dir_url(__FILE__) . 'assets/admin.css', array(), '1.3');
}
add_action('admin_enqueue_scripts', 'quantumportal_admin_scripts');

// Tela de configuração
function quantumportal_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Você não tem permissão para acessar esta página.', 'quantumportal-aggregator'), 'Acesso Negado', array('response' => 403));
    }

    $error_message = '';
    $success_message = '';

    if (isset($_POST['quantumportal_save_settings'])) {
        if (!check_admin_referer('quantumportal_settings_nonce')) {
            $error_message = 'Erro de segurança: Nonce inválido. Tente novamente.';
        } else {
            try {
                // Salvar subdomínios
                $subdomains = array();
                if (isset($_POST['subdomain_name']) && is_array($_POST['subdomain_name'])) {
                    foreach ($_POST['subdomain_name'] as $key => $name) {
                        $name = sanitize_text_field($name ?? '');
                        $slug = sanitize_key($_POST['subdomain_slug'][$key] ?? '');
                        $api_url = esc_url_raw($_POST['subdomain_api_url'][$key] ?? '');
                        if (!empty($name) && !empty($slug) && !empty($api_url) && filter_var($api_url, FILTER_VALIDATE_URL)) {
                            $subdomains[] = array(
                                'name' => $name,
                                'slug' => $slug,
                                'api_url' => rtrim($api_url, '/'),
                            );
                        } else {
                            $error_message .= 'Subdomínio inválido ou incompleto (linha ' . ($key + 1) . '). ';
                        }
                    }
                }
                update_option('quantumportal_subdomains', $subdomains);

                // Salvar templates HTML
                $templates = array();
                if (isset($_POST['template_name']) && is_array($_POST['template_name'])) {
                    foreach ($_POST['template_name'] as $key => $name) {
                        $name = sanitize_key($name ?? '');
                        $html = wp_kses_post(stripslashes($_POST['template_html'][$key] ?? ''));
                        if (!empty($name) && !empty($html) && preg_match('/{title}|{link}|{excerpt}|{thumbnail_url}/', $html)) {
                            $templates[$name] = $html;
                        } else {
                            $error_message .= 'Template inválido ou sem placeholders válidos (linha ' . ($key + 1) . '). ';
                        }
                    }
                }
                update_option('quantumportal_templates', $templates);

                // Salvar configurações globais
                $max_posts_default = max(1, intval($_POST['quantumportal_max_posts_default'] ?? 5));
                $columns_default = max(1, min(6, intval($_POST['quantumportal_columns_default'] ?? 3)));
                $excerpt_length = max(10, intval($_POST['quantumportal_excerpt_length'] ?? 30));

                update_option('quantumportal_max_posts_default', $max_posts_default);
                update_option('quantumportal_columns_default', $columns_default);
                update_option('quantumportal_excerpt_length', $excerpt_length);

                if (empty($error_message)) {
                    $success_message = 'Configurações salvas com sucesso!';
                } else {
                    $success_message = 'Configurações salvas, mas com avisos: ';
                }
            } catch (Exception $e) {
                $error_message = 'Erro ao salvar configurações: ' . esc_html($e->getMessage());
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('QP Aggregator: Erro ao salvar configurações - ' . $e->getMessage());
                }
            }
        }
    }

    $subdomains = get_option('quantumportal_subdomains', array());
    $templates = get_option('quantumportal_templates', array());
    $max_posts_default = get_option('quantumportal_max_posts_default', 5);
    $columns_default = get_option('quantumportal_columns_default', 3);
    $excerpt_length = get_option('quantumportal_excerpt_length', 30);

    if (!empty($error_message)) {
        echo '<div class="error"><p>' . esc_html($success_message . $error_message) . '</p></div>';
    } elseif (!empty($success_message)) {
        echo '<div class="updated"><p>' . esc_html($success_message) . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>QP Aggregator Configurações</h1>
        <form method="post" action="">
            <?php wp_nonce_field('quantumportal_settings_nonce'); ?>
            <h2>Configurações Globais</h2>
            <table class="form-table">
                <tr>
                    <th><label for="quantumportal_max_posts_default">Máximo de Posts Padrão</label></th>
                    <td>
                        <input type="number" id="quantumportal_max_posts_default" name="quantumportal_max_posts_default" value="<?php echo esc_attr($max_posts_default); ?>" min="1" />
                        <p class="description">Número padrão de posts exibidos no shortcode, se não especificado.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="quantumportal_columns_default">Colunas no Grid Padrão</label></th>
                    <td>
                        <input type="number" id="quantumportal_columns_default" name="quantumportal_columns_default" value="<?php echo esc_attr($columns_default); ?>" min="1" max="6" />
                        <p class="description">Número de colunas no grid, de 1 a 6.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="quantumportal_excerpt_length">Comprimento do Resumo (em palavras)</label></th>
                    <td>
                        <input type="number" id="quantumportal_excerpt_length" name="quantumportal_excerpt_length" value="<?php echo esc_attr($excerpt_length); ?>" min="10" />
                        <p class="description">Número de palavras no resumo dos posts.</p>
                    </td>
                </tr>
            </table>

            <h2>Subdomínios</h2>
            <p>Adicione ou remova subdomínios. Cada um precisa de Nome (para exibição), Slug (para o shortcode) e URL da API REST (ex.: https://assunto.portalexemplo.com.br/wp-json/wp/v2/posts).</p>
            <div id="quantumportal-subdomains-repeater">
                <?php if (!empty($subdomains) && is_array($subdomains)): ?>
                    <?php foreach ($subdomains as $index => $subdomain): ?>
                        <?php if (is_array($subdomain) && isset($subdomain['name'], $subdomain['slug'], $subdomain['api_url'])): ?>
                            <div class="subdomain-row">
                                <input type="text" name="subdomain_name[]" placeholder="Nome (ex.: Finanças)" value="<?php echo esc_attr($subdomain['name']); ?>" required />
                                <input type="text" name="subdomain_slug[]" placeholder="Slug (ex.: financas)" value="<?php echo esc_attr($subdomain['slug']); ?>" required />
                                <input type="url" name="subdomain_api_url[]" placeholder="URL da API (ex.: https://assunto.portalexemplo.com.br/wp-json/wp/v2/posts)" value="<?php echo esc_attr($subdomain['api_url']); ?>" required />
                                <button type="button" class="button remove-row">Remover</button>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhum subdomínio configurado.</p>
                <?php endif; ?>
            </div>
            <button type="button" id="add-subdomain" class="button">Adicionar Subdomínio</button>

            <h2>Templates HTML Personalizados</h2>
            <p>Adicione ou remova templates HTML. Use placeholders: {thumbnail_url}, {title}, {link}, {excerpt}. Exemplo: <code>&lt;div&gt;{thumbnail_url}&lt;a href="{link}"&gt;{title}&lt;/a&gt;&lt;p&gt;{excerpt}&lt;/p&gt;&lt;/div&gt;</code>. O nome do template é usado no shortcode como <code>template="nome"</code>.</p>
            <div id="quantumportal-templates-repeater">
                <?php if (!empty($templates) && is_array($templates)): ?>
                    <?php foreach ($templates as $name => $html): ?>
                        <div class="template-row">
                            <input type="text" name="template_name[]" placeholder="Nome do Template (ex.: grid_padrao)" value="<?php echo esc_attr($name); ?>" required />
                            <textarea name="template_html[]" rows="5" placeholder="HTML do item (ex.: <div><img src='{thumbnail_url}' /><a href='{link}'>{title}</a><p>{excerpt}</p></div>)" required><?php echo esc_textarea($html); ?></textarea>
                            &nbsp;<button type="button" class="button remove-row">Remover</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhum template configurado.</p>
                <?php endif; ?>
            </div>
            <button type="button" id="add-template" class="button">Adicionar Template</button>

            <p class="submit"><input type="submit" name="quantumportal_save_settings" class="button-primary" value="Salvar Configurações" /></p>
        </form>
    </div>
    <?php
}

// Shortcode para exibir posts
function quantumportal_display_subdomain_posts($atts) {
    $atts = shortcode_atts(array(
        'subdomain' => '',
        'max_posts' => get_option('quantumportal_max_posts_default', 5),
        'columns' => get_option('quantumportal_columns_default', 3),
        'offset' => 0,
        'category' => '',
        'tag' => '',
        'template' => '',
    ), $atts, 'quantumportal_posts');

    // Validações de atributos
    $atts['max_posts'] = max(1, min(100, intval($atts['max_posts'])));
    $atts['columns'] = max(1, min(6, intval($atts['columns'])));
    $atts['offset'] = max(0, intval($atts['offset']));
    $atts['subdomain'] = sanitize_key($atts['subdomain']);
    $atts['category'] = sanitize_text_field($atts['category']);
    $atts['tag'] = sanitize_text_field($atts['tag']);
    $atts['template'] = sanitize_key($atts['template']);

    if (empty($atts['subdomain'])) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('QP Aggregator: Shortcode chamado sem subdomínio especificado.');
        }
        return '<p>Erro: Slug do subdomínio não especificado.</p>';
    }

    $subdomains = get_option('quantumportal_subdomains', array());
    if (!is_array($subdomains)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('QP Aggregator: Configuração de subdomínios inválida.');
        }
        return '<p>Erro: Configuração de subdomínios inválida.</p>';
    }

    $api_base_url = '';
    foreach ($subdomains as $subdomain) {
        if (!is_array($subdomain) || !isset($subdomain['slug'], $subdomain['api_url'])) {
            continue;
        }
        if ($subdomain['slug'] === $atts['subdomain']) {
            $api_base_url = $subdomain['api_url'];
            break;
        }
    }

    if (empty($api_base_url)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('QP Aggregator: Subdomínio não encontrado - ' . $atts['subdomain']);
        }
        return '<p>Erro: Subdomínio não encontrado nas configurações.</p>';
    }

    // Construir URL da API com parâmetros
    $api_params = array(
        'per_page' => $atts['max_posts'],
        'offset' => $atts['offset'],
        '_embed' => true,
        'order' => 'desc',
        'orderby' => 'date',
    );
    if (!empty($atts['category'])) {
        $api_params['categories'] = $atts['category'];
    }
    if (!empty($atts['tag'])) {
        $api_params['tags'] = $atts['tag'];
    }
    $api_url = esc_url_raw(add_query_arg($api_params, rtrim($api_base_url, '/')));

    // Cache
    $cache_key = 'quantumportal_posts_' . md5($api_url);
    $output = get_transient($cache_key);
    if ($output !== false) {
        return $output;
    }

    // Requisição à API
    $response = wp_remote_get($api_url, array(
        'timeout' => 15,
        'sslverify' => true,
    ));
    if (is_wp_error($response)) {
        $error = $response->get_error_message();
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('QP Aggregator: Erro na requisição API - ' . $error . ' | URL: ' . $api_url);
        }
        return '<p>Erro ao carregar posts: Falha na conexão com a API.</p>';
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('QP Aggregator: Erro HTTP na API - Código: ' . $response_code . ' | URL: ' . $api_url);
        }
        return '<p>Erro HTTP ao carregar posts: ' . esc_html($response_code) . '</p>';
    }

    $body = wp_remote_retrieve_body($response);
    $posts = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('QP Aggregator: Erro ao decodificar JSON - ' . json_last_error_msg() . ' | URL: ' . $api_url);
        }
        return '<p>Erro ao processar resposta da API: Formato inválido.</p>';
    }

    if (empty($posts) || !is_array($posts)) {
        return '<p>Nenhuma matéria encontrada.</p>';
    }

    // Carregar template
    $templates = get_option('quantumportal_templates', array());
    if (!is_array($templates)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('QP Aggregator: Configuração de templates inválida.');
        }
        return '<p>Erro: Configuração de templates inválida.</p>';
    }

    $item_template = '<div class="quantumportal-post" style="border: 1px solid #eee; padding: 15px; box-sizing: border-box;"><img src="{thumbnail_url}" alt="{title}" style="max-width: 100%; height: auto; margin-bottom: 10px;"><h2 style="font-size: 1.5em; margin: 10px 0;"><a href="{link}" target="_blank" style="text-decoration: none; color: #333;">{title}</a></h2><p style="font-size: 0.9em; color: #666;">{excerpt}</p></div>';
    if (!empty($atts['template']) && isset($templates[$atts['template']])) {
        $item_template = $templates[$atts['template']];
    }

    // Validar template
    if (!preg_match('/{title}|{link}|{excerpt}|{thumbnail_url}/', $item_template)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('QP Aggregator: Template inválido, sem placeholders - Template: ' . $atts['template']);
        }
        return '<p>Erro: Template selecionado não contém placeholders válidos.</p>';
    }

    $excerpt_length = max(10, intval(get_option('quantumportal_excerpt_length', 30)));
    $items_html = '';
    foreach ($posts as $index => $post) {
        if (!is_array($post) || empty($post['title']['rendered']) || empty($post['link'])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('QP Aggregator: Post inválido na posição ' . $index . ' | URL: ' . $api_url);
            }
            continue;
        }

        $thumbnail_url = !empty($post['_embedded']['wp:featuredmedia'][0]['source_url']) ? esc_url($post['_embedded']['wp:featuredmedia'][0]['source_url']) : '';
        $title = esc_html($post['title']['rendered']);
        $link = esc_url($post['link']);
        $excerpt = wp_trim_words(strip_tags($post['excerpt']['rendered'] ?? ''), $excerpt_length, '...');

        $item_html = str_replace(
            array('{thumbnail_url}', '{title}', '{link}', '{excerpt}'),
            array($thumbnail_url ? '<img src="' . $thumbnail_url . '" alt="' . $title . '" style="max-width:100%;height:auto;">' : '', $title, $link, $excerpt),
            $item_template
        );
        $items_html .= wp_kses_post($item_html);
    }

    if (empty($items_html)) {
        return '<p>Nenhuma matéria válida encontrada.</p>';
    }

    $output = '<div class="quantumportal-posts-grid" style="display: grid; grid-template-columns: repeat(' . esc_attr($atts['columns']) . ', 1fr); gap: 20px; padding: 20px;">' . $items_html . '</div>';

    $output .= '<style>
        @media (max-width: 768px) {
            .quantumportal-posts-grid { grid-template-columns: 1fr; }
        }
    </style>';

    // Salvar no cache
    if (!set_transient($cache_key, $output, HOUR_IN_SECONDS)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('QP Aggregator: Falha ao salvar cache para ' . $cache_key);
        }
    }

    return $output;
}
add_shortcode('quantumportal_posts', 'quantumportal_display_subdomain_posts');

// Limpar cache ao salvar configurações
function quantumportal_clear_cache() {
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_quantumportal_posts_%'");
}
add_action('update_option_quantumportal_subdomains', 'quantumportal_clear_cache');
add_action('update_option_quantumportal_templates', 'quantumportal_clear_cache');
add_action('update_option_quantumportal_max_posts_default', 'quantumportal_clear_cache');
add_action('update_option_quantumportal_columns_default', 'quantumportal_clear_cache');
add_action('update_option_quantumportal_excerpt_length', 'quantumportal_clear_cache');
?>