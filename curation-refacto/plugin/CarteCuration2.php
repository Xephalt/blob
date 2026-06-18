<?php
namespace Bnpp\Blocks;

class CarteCuration2 {

    public function __construct() {
        add_action('acf/init', array($this, 'bnpp_acf_init'));
    }

    public function bnpp_acf_init() {
        if (function_exists('acf_register_block')) {
            acf_register_block(array(
                'name'            => 'carte-curation-2',
                'title'           => __('Carte Curation 2'),
                'description'     => __('Carte texte : titre, description, lien, date et pictogramme.'),
                'render_template' => get_template_directory() . '/template-parts/block/carte-curation-2.php',
                'category'        => 'BNPP',
                'icon'            => 'editor-alignleft',
                'keywords'        => array('curation', 'carte', 'texte', 'card'),
                'enqueue_assets'  => function() {
                    wp_enqueue_style('carte-curation-2-css', BNPP_URL . '/assets/css/carte-curation-2.css');
                },
                'supports' => array(
                    'align' => false,
                ),
            ));
        }

        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group(array(
                'key'    => 'group_carte_curation_2',
                'title'  => 'Carte Curation 2 — Champs',
                'fields' => array(
                    array(
                        'key'       => 'field_cc2_title',
                        'name'      => 'cc2_title',
                        'label'     => 'Titre (2 lignes max)',
                        'type'      => 'text',
                        'required'  => 1,
                        'maxlength' => 200,
                    ),
                    array(
                        'key'          => 'field_cc2_description',
                        'name'         => 'cc2_description',
                        'label'        => 'Description (350 car. max)',
                        'type'         => 'textarea',
                        'required'     => 1,
                        'maxlength'    => 350,
                        'rows'         => 4,
                        'instructions' => 'Maximum 350 caractères espaces compris.',
                    ),
                    array(
                        'key'          => 'field_cc2_link_text',
                        'name'         => 'cc2_link_text',
                        'label'        => 'Texte du lien (40 car. max)',
                        'type'         => 'text',
                        'required'     => 1,
                        'maxlength'    => 40,
                        'instructions' => 'Maximum 40 caractères espaces compris.',
                    ),
                    array(
                        'key'      => 'field_cc2_link_url',
                        'name'     => 'cc2_link_url',
                        'label'    => 'URL du lien',
                        'type'     => 'url',
                        'required' => 1,
                    ),
                    array(
                        'key'            => 'field_cc2_date',
                        'name'           => 'cc2_date',
                        'label'          => 'Date',
                        'type'           => 'date_picker',
                        'required'       => 1,
                        'return_format'  => 'd M Y',
                        'display_format' => 'dd/mm/yyyy',
                    ),
                    array(
                        'key'           => 'field_cc2_picto',
                        'name'          => 'cc2_picto',
                        'label'         => 'Pictogramme (coin bas droit)',
                        'type'          => 'image',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'thumbnail',
                    ),
                    array(
                        'key'           => 'field_cc2_clickable',
                        'name'          => 'cc2_clickable',
                        'label'         => 'La carte est cliquable',
                        'type'          => 'true_false',
                        'default_value' => 1,
                        'ui'            => 1,
                    ),
                    array(
                        'key'               => 'field_cc2_url',
                        'name'              => 'cc2_url',
                        'label'             => 'URL de redirection (si cliquable)',
                        'type'              => 'url',
                        'required'          => 0,
                        'conditional_logic' => array(array(array(
                            'field'    => 'field_cc2_clickable',
                            'operator' => '==',
                            'value'    => '1',
                        ))),
                    ),
                ),
                'location' => array(array(array(
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/carte-curation-2',
                ))),
            ));
        }
    }
}
