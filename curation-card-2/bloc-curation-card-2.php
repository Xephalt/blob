<?php
/**
 * Bloc Curation Card 2
 * 
 * Enregistrement du bloc Gutenberg + champs ACF
 * À inclure dans functions.php ou via un plugin de blocs custom
 */

add_action('acf/init', function() {
    if (!function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type([
        'name'            => 'curation-card-2',
        'title'           => 'Carte Curation 2',
        'description'     => 'Carte texte sans image : titre, description, lien, date et pictogramme',
        'render_template' => dirname(__FILE__) . '/template-curation-card-2.php',
        'category'        => 'common',
        'icon'            => 'editor-alignleft',
        'keywords'        => ['curation', 'card', 'texte', 'actualité'],
        'post_types'      => ['actualite'],
        'mode'            => 'preview',
        'align'           => 'full',
        'supports'        => [
            'align' => false,
            'mode'  => true,
        ],
    ]);
});

add_action('acf/init', function() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_curation_card_2',
        'title'  => 'Carte Curation 2 — Champs',
        'fields' => [

            // ── Titre ──
            [
                'key'          => 'field_card2_title',
                'name'         => 'card2_title',
                'label'        => 'Titre (2 lignes max)',
                'type'         => 'text',
                'required'     => 1,
                'maxlength'    => 200,
            ],

            // ── Description ──
            [
                'key'          => 'field_card2_description',
                'name'         => 'card2_description',
                'label'        => 'Description (350 caractères max)',
                'type'         => 'textarea',
                'required'     => 1,
                'maxlength'    => 350,
                'rows'         => 4,
                'instructions' => 'Maximum 350 caractères espaces compris.',
            ],

            // ── Lien ──
            [
                'key'          => 'field_card2_link_text',
                'name'         => 'card2_link_text',
                'label'        => 'Texte du lien (40 caractères max)',
                'type'         => 'text',
                'required'     => 1,
                'maxlength'    => 40,
                'instructions' => 'Limité à 40 caractères espaces compris.',
            ],
            [
                'key'          => 'field_card2_link_url',
                'name'         => 'card2_link_url',
                'label'        => 'URL du lien',
                'type'         => 'url',
                'required'     => 1,
            ],

            // ── Date ──
            [
                'key'            => 'field_card2_date',
                'name'           => 'card2_date',
                'label'          => 'Date',
                'type'           => 'date_picker',
                'required'       => 1,
                'return_format'  => 'd M Y',
                'display_format' => 'dd/mm/yyyy',
            ],

            // ── Pictogramme ──
            [
                'key'           => 'field_card2_picto',
                'name'          => 'card2_picto',
                'label'         => 'Pictogramme (coin bas droit)',
                'type'          => 'image',
                'required'      => 1,
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
            ],

            // ── Cliquabilité ──
            [
                'key'           => 'field_card2_clickable',
                'name'          => 'card2_clickable',
                'label'         => 'La carte est cliquable',
                'type'          => 'true_false',
                'required'      => 0,
                'default_value' => 1,
                'ui'            => 1,
            ],
            [
                'key'               => 'field_card2_url',
                'name'              => 'card2_url',
                'label'             => 'URL de redirection (si carte cliquable)',
                'type'              => 'url',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_card2_clickable',
                            'operator' => '==',
                            'value'    => '1',
                        ],
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/curation-card-2',
                ],
            ],
        ],
    ]);
});
