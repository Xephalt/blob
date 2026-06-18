<?php
/**
 * Bloc Curation Card
 * 
 * Enregistrement du bloc Gutenberg + champs ACF
 * À inclure dans functions.php ou via un plugin de blocs custom
 */

add_action('acf/init', function() {
    if (!function_exists('acf_register_block_type')) {
        return;
    }

    // ── Enregistrement du bloc Gutenberg ──
    acf_register_block_type([
        'name'            => 'curation-card',
        'title'           => 'Carte Curation',
        'description'     => 'Carte d\'actualité cliquable avec image, titre, lien, date et pictogramme',
        'render_template' => dirname(__FILE__) . '/template-curation-card-1.php',
        'category'        => 'common',
        'icon'            => 'layout',
        'keywords'        => ['curation', 'card', 'actualité'],
        'post_types'      => ['actualite'],  // uniquement sur les actualités
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

    // ── Groupe de champs ACF pour le bloc ──
    acf_add_local_field_group([
        'key'    => 'group_curation_card',
        'title'  => 'Carte Curation — Champs',
        'fields' => [
            // ── SECTION : Visuel ──
            [
                'key'   => 'field_card_image',
                'name'  => 'card_image',
                'label' => 'Image (visuel horizontal)',
                'type'  => 'image',
                'required' => 1,
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            [
                'key'   => 'field_card_picto',
                'name'  => 'card_picto',
                'label' => 'Pictogramme (coin bas droit)',
                'type'  => 'image',
                'required' => 1,
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
            ],

            // ── SECTION : Contenu ──
            [
                'key'   => 'field_card_title',
                'name'  => 'card_title',
                'label' => 'Titre (2 lignes max)',
                'type'  => 'text',
                'required' => 1,
                'maxlength' => 200,
            ],
            [
                'key'   => 'field_card_link_text',
                'name'  => 'card_link_text',
                'label' => 'Texte du lien (≤40 caractères)',
                'type'  => 'text',
                'required' => 1,
                'maxlength' => 40,
                'instructions' => 'Limité à 40 caractères pour éviter le débordement.',
            ],
            [
                'key'   => 'field_card_link_url',
                'name'  => 'card_link_url',
                'label' => 'URL du lien',
                'type'  => 'url',
                'required' => 1,
            ],
            [
                'key'   => 'field_card_date',
                'name'  => 'card_date',
                'label' => 'Date',
                'type'  => 'date_picker',
                'required' => 1,
                'return_format' => 'd M Y',
                'display_format' => 'dd/mm/yyyy',
            ],

            // ── SECTION : Cliquabilité ──
            [
                'key'   => 'field_card_clickable',
                'name'  => 'card_clickable',
                'label' => 'La carte est cliquable',
                'type'  => 'true_false',
                'required' => 0,
                'default_value' => 1,
                'ui'    => 1,  // affiche un toggle au lieu d'une checkbox
            ],
            [
                'key'   => 'field_card_url',
                'name'  => 'card_url',
                'label' => 'URL de redirection (si carte cliquable)',
                'type'  => 'url',
                'required' => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_card_clickable',
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
                    'value'    => 'acf/curation-card',
                ],
            ],
        ],
    ]);
});
