<?php
/**
 * Bloc Curation Card 3
 * 
 * Enregistrement du bloc Gutenberg + champs ACF
 * À inclure dans functions.php ou via un plugin de blocs custom
 */

add_action('acf/init', function() {
    if (!function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type([
        'name'            => 'curation-card-3',
        'title'           => 'Carte Curation 3',
        'description'     => 'Carte avec médaillon photo, nom, poste optionnel, citation, lien, date et pictogramme',
        'render_template' => dirname(__FILE__) . '/template-curation-card-3.php',
        'category'        => 'common',
        'icon'            => 'admin-users',
        'keywords'        => ['curation', 'card', 'citation', 'personne', 'actualité'],
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
        'key'    => 'group_curation_card_3',
        'title'  => 'Carte Curation 3 — Champs',
        'fields' => [

            // ── Médaillon ──
            [
                'key'           => 'field_card3_avatar',
                'name'          => 'card3_avatar',
                'label'         => 'Photo (médaillon)',
                'type'          => 'image',
                'required'      => 1,
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
                'instructions'  => 'Image carrée recommandée. Sera affichée en cercle.',
            ],

            // ── Zone texte 1 : nom ──
            [
                'key'       => 'field_card3_name',
                'name'      => 'card3_name',
                'label'     => 'Nom (zone texte 1 — 2 lignes max)',
                'type'      => 'text',
                'required'  => 1,
                'maxlength' => 100,
            ],

            // ── Zone texte 2 : poste (optionnel) ──
            [
                'key'          => 'field_card3_role',
                'name'         => 'card3_role',
                'label'        => 'Poste / Fonction (zone texte 2 — optionnel)',
                'type'         => 'text',
                'required'     => 0,
                'maxlength'    => 100,
                'instructions' => 'Optionnel. Si vide, le nom prend toute la place.',
            ],

            // ── Zone texte 3 : citation ──
            [
                'key'          => 'field_card3_quote',
                'name'         => 'card3_quote',
                'label'        => 'Citation (zone texte 3 — 150 caractères max)',
                'type'         => 'textarea',
                'required'     => 1,
                'maxlength'    => 150,
                'rows'         => 3,
                'instructions' => 'Maximum 150 caractères espaces compris.',
            ],

            // ── Lien ──
            [
                'key'          => 'field_card3_link_text',
                'name'         => 'card3_link_text',
                'label'        => 'Texte du lien (40 caractères max)',
                'type'         => 'text',
                'required'     => 1,
                'maxlength'    => 40,
                'instructions' => 'Limité à 40 caractères espaces compris.',
            ],
            [
                'key'      => 'field_card3_link_url',
                'name'     => 'card3_link_url',
                'label'    => 'URL du lien',
                'type'     => 'url',
                'required' => 1,
            ],

            // ── Date ──
            [
                'key'            => 'field_card3_date',
                'name'           => 'card3_date',
                'label'          => 'Date',
                'type'           => 'date_picker',
                'required'       => 1,
                'return_format'  => 'd M Y',
                'display_format' => 'dd/mm/yyyy',
            ],

            // ── Pictogramme ──
            [
                'key'           => 'field_card3_picto',
                'name'          => 'card3_picto',
                'label'         => 'Pictogramme (coin bas droit)',
                'type'          => 'image',
                'required'      => 1,
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
            ],

            // ── Cliquabilité ──
            [
                'key'           => 'field_card3_clickable',
                'name'          => 'card3_clickable',
                'label'         => 'La carte est cliquable',
                'type'          => 'true_false',
                'required'      => 0,
                'default_value' => 1,
                'ui'            => 1,
            ],
            [
                'key'               => 'field_card3_url',
                'name'              => 'card3_url',
                'label'             => 'URL de redirection (si carte cliquable)',
                'type'              => 'url',
                'required'          => 0,
                'conditional_logic' => [
                    [
                        [
                            'field'    => 'field_card3_clickable',
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
                    'value'    => 'acf/curation-card-3',
                ],
            ],
        ],
    ]);
});
