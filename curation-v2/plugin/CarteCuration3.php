<?php
namespace Bnpp\Blocks;

class CarteCuration3 {

    public function __construct() {
        add_action('acf/init', array($this, 'bnpp_acf_init'));
    }

    public function bnpp_acf_init() {
        if (function_exists('acf_register_block')) {
            acf_register_block(array(
                'name'            => 'carte-curation-3',
                'title'           => __('Carte Curation 3'),
                'description'     => __('Carte avec médaillon photo, nom, poste, citation, lien, date et pictogramme.'),
                'render_template' => get_template_directory() . '/templates-parts/block/carte-curation-3.php',
                'category'        => 'BNPP',
                'icon'            => 'admin-users',
                'keywords'        => array('curation', 'carte', 'citation', 'personne', 'card'),
                'supports'        => array('align' => false),
            ));
        }

        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group(array(
                'key'    => 'group_carte_curation_3',
                'title'  => 'Carte Curation 3 — Champs',
                'fields' => array(
                    array(
                        'key'           => 'field_cc3_avatar',
                        'name'          => 'cc3_avatar',
                        'label'         => 'Photo (médaillon)',
                        'type'          => 'image',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'thumbnail',
                        'instructions'  => 'Image carrée recommandée. Affichée en cercle.',
                    ),
                    array(
                        'key'       => 'field_cc3_name',
                        'name'      => 'cc3_name',
                        'label'     => 'Nom (2 lignes max)',
                        'type'      => 'text',
                        'required'  => 1,
                        'maxlength' => 100,
                    ),
                    array(
                        'key'          => 'field_cc3_role',
                        'name'         => 'cc3_role',
                        'label'        => 'Poste / Fonction (optionnel)',
                        'type'         => 'text',
                        'required'     => 0,
                        'maxlength'    => 100,
                        'instructions' => 'Optionnel. Si vide, le nom prend toute la place.',
                    ),
                    array(
                        'key'          => 'field_cc3_quote',
                        'name'         => 'cc3_quote',
                        'label'        => 'Citation (150 car. max)',
                        'type'         => 'textarea',
                        'required'     => 1,
                        'maxlength'    => 150,
                        'rows'         => 3,
                        'instructions' => 'Maximum 150 caractères espaces compris.',
                    ),
                    array(
                        'key'          => 'field_cc3_link_text',
                        'name'         => 'cc3_link_text',
                        'label'        => 'Texte du lien (40 car. max)',
                        'type'         => 'text',
                        'required'     => 1,
                        'maxlength'    => 40,
                        'instructions' => 'Maximum 40 caractères espaces compris.',
                    ),
                    array(
                        'key'      => 'field_cc3_link_url',
                        'name'     => 'cc3_link_url',
                        'label'    => 'URL du lien',
                        'type'     => 'url',
                        'required' => 1,
                    ),
                    array(
                        'key'            => 'field_cc3_date',
                        'name'           => 'cc3_date',
                        'label'          => 'Date',
                        'type'           => 'date_picker',
                        'required'       => 1,
                        'return_format'  => 'd M Y',
                        'display_format' => 'dd/mm/yyyy',
                    ),
                    array(
                        'key'           => 'field_cc3_picto',
                        'name'          => 'cc3_picto',
                        'label'         => 'Pictogramme (coin bas droit)',
                        'type'          => 'image',
                        'required'      => 1,
                        'return_format' => 'array',
                        'preview_size'  => 'thumbnail',
                    ),
                    array(
                        'key'           => 'field_cc3_clickable',
                        'name'          => 'cc3_clickable',
                        'label'         => 'La carte est cliquable',
                        'type'          => 'true_false',
                        'default_value' => 1,
                        'ui'            => 1,
                    ),
                    array(
                        'key'               => 'field_cc3_url',
                        'name'              => 'cc3_url',
                        'label'             => 'URL de redirection (si cliquable)',
                        'type'              => 'url',
                        'required'          => 0,
                        'conditional_logic' => array(array(array(
                            'field'    => 'field_cc3_clickable',
                            'operator' => '==',
                            'value'    => '1',
                        ))),
                    ),
                ),
                'location' => array(array(array(
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/carte-curation-3',
                ))),
            ));
        }
    }
}
