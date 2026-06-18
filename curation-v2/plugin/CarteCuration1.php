<?php
namespace Bnpp\Blocks;

class CarteCuration1 {

    public function __construct() {
        add_action('acf/init', array($this, 'bnpp_acf_init'));
    }

    public function bnpp_acf_init() {
        if (function_exists('acf_register_block')) {
            acf_register_block(array(
                'name'            => 'carte-curation-1',
                'title'           => __('Carte Curation 1'),
                'description'     => __('Carte avec image, titre, lien, date et pictogramme.'),
                'render_template' => get_template_directory() . '/templates-parts/block/carte-curation-1.php',
                'category'        => 'BNPP',
                'icon'            => 'format-image',
                'keywords'        => array('curation', 'carte', 'image', 'card'),
                'supports'        => array('align' => false),
            ));
        }

        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group(array(
                'key'    => 'group_carte_curation_1',
                'title'  => 'Carte Curation 1 — Champs',
                'fields' => array(
                    array(
                        'key'           => 'field_cc1_image',
                        'name'          => 'cc1_image',
                        'label'         => 'Image (visuel horizontal)',
                        'type'          => 'image',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                    ),
                    array(
                        'key'       => 'field_cc1_title',
                        'name'      => 'cc1_title',
                        'label'     => 'Titre (2 lignes max)',
                        'type'      => 'text',
                        'required'  => 1,
                        'maxlength' => 200,
                    ),
                    array(
                        'key'          => 'field_cc1_link_text',
                        'name'         => 'cc1_link_text',
                        'label'        => 'Texte du lien (40 car. max)',
                        'type'         => 'text',
                        'required'     => 1,
                        'maxlength'    => 40,
                        'instructions' => 'Maximum 40 caractères espaces compris.',
                    ),
                    array(
                        'key'      => 'field_cc1_link_url',
                        'name'     => 'cc1_link_url',
                        'label'    => 'URL du lien',
                        'type'     => 'url',
                        'required' => 1,
                    ),
                    array(
                        'key'            => 'field_cc1_date',
                        'name'           => 'cc1_date',
                        'label'          => 'Date',
                        'type'           => 'date_picker',
                        'required'       => 1,
                        'return_format'  => 'd M Y',
                        'display_format' => 'dd/mm/yyyy',
                    ),
                    array(
                        'key'           => 'field_cc1_picto',
                        'name'          => 'cc1_picto',
                        'label'         => 'Pictogramme (coin bas droit)',
                        'type'          => 'image',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'thumbnail',
                    ),
                    array(
                        'key'           => 'field_cc1_clickable',
                        'name'          => 'cc1_clickable',
                        'label'         => 'La carte est cliquable',
                        'type'          => 'true_false',
                        'default_value' => 1,
                        'ui'            => 1,
                    ),
                    array(
                        'key'               => 'field_cc1_url',
                        'name'              => 'cc1_url',
                        'label'             => 'URL de redirection (si cliquable)',
                        'type'              => 'url',
                        'required'          => 0,
                        'conditional_logic' => array(array(array(
                            'field'    => 'field_cc1_clickable',
                            'operator' => '==',
                            'value'    => '1',
                        ))),
                    ),
                ),
                'location' => array(array(array(
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/carte-curation-1',
                ))),
            ));
        }
    }
}
