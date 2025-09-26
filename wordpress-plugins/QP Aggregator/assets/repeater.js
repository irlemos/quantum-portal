jQuery(document).ready(function($) {
    // Repeater para subdomínios
    $('#add-subdomain').on('click', function() {
        var row = '<div class="subdomain-row">' +
            '<input type="text" name="subdomain_name[]" placeholder="Nome (ex.: Finanças)" required />' +
            '<input type="text" name="subdomain_slug[]" placeholder="Slug (ex.: financas)" required />' +
            '<input type="url" name="subdomain_api_url[]" placeholder="URL da API (ex.: https://https://assunto.portalexemplo.com.br/wp-json/wp/v2/posts)" required />' +
            '<button type="button" class="button remove-row">Remover</button>' +
        '</div>';
        $('#quantumportal-subdomains-repeater').append(row);
    });

    // Repeater para templates
    $('#add-template').on('click', function() {
        var row = '<div class="template-row">' +
            '<input type="text" name="template_name[]" placeholder="Nome do Template (ex.: grid_padrao)" required />' +
            '<textarea name="template_html[]" rows="5" placeholder="HTML do item (ex.: <div><img src=\'{thumbnail_url}\' /><a href=\'{link}\'>{title}</a><p>{excerpt}</p></div>)" required></textarea>' +
            '<button type="button" class="button remove-row">Remover</button>' +
        '</div>';
        $('#quantumportal-templates-repeater').append(row);
    });

    $(document).on('click', '.remove-row', function() {
        $(this).parent().remove();
    });
});