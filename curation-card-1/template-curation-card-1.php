<?php
/**
 * Template : Bloc Curation Card
 * 
 * Variables disponibles :
 * - $block : l'objet du bloc
 * - $content : contenu du bloc (vide ici, on génère tout)
 * - $is_preview : true si on est en mode preview Gutenberg
 * - $post_id : l'ID du post actuellement édité
 */

// Récupère les champs ACF du bloc
$card_image = get_field('card_image');
$card_title = get_field('card_title');
$card_link_text = get_field('card_link_text');
$card_link_url = get_field('card_link_url');
$card_date = get_field('card_date');
$card_picto = get_field('card_picto');
$card_clickable = get_field('card_clickable');
$card_url = get_field('card_url');

// Classe CSS conditionnelle : is-clickable si la carte est cliquable
$card_class = 'curation-card';
if ($card_clickable) {
    $card_class .= ' is-clickable';
}

// URL de destination pour la carte entière (ou lien du contenu si pas clickable)
$card_href = $card_clickable && $card_url ? $card_url : $card_link_url;

// Si la carte est cliquable, on utilise un <a> pour la balise racine
$tag_open = $card_clickable ? '<a' : '<div';
$tag_close = $card_clickable ? '</a>' : '</div>';
$anchor_attrs = $card_clickable ? sprintf(
    ' href="%s" target="_blank" rel="noopener noreferrer"',
    esc_url($card_href)
) : '';
?>

<style>
  /* ── CARD ── */
  .curation-card {
    width: clamp(6rem, 100%, 320px);
    aspect-ratio: 1 / 1;
    min-height: 6rem;

    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.10);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    cursor: default;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    color: inherit;
  }

  .curation-card.is-clickable {
    cursor: pointer;
  }
  .curation-card.is-clickable:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.18);
  }

  /* ── IMAGE : 50% ── */
  .curation-card__image {
    width: 100%;
    flex: 0 0 50%;
    min-height: 3rem;
    object-fit: cover;
    display: block;
    background: #bdbdbd;
  }

  /* ── BODY : 50% restant ── */
  .curation-card__body {
    flex: 1 1 0;
    min-height: 3rem;
    padding: 12px 16px 8px 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
  }

  /* Titre — 2 lignes max avec ellipsis */
  .curation-card__title {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.4;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Lien — vert, souligné, flèche, 1 ligne ellipsis */
  .curation-card__link {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    color: #2e7d32;
    text-decoration: underline;
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex-shrink: 0;
  }

  .curation-card__link:hover {
    color: #1b5e20;
    text-decoration-thickness: 2px;
  }

  .curation-card__link::before {
    content: '';
    display: inline-block;
    flex-shrink: 0;
    width: 0;
    height: 0;
    border-top: 4px solid transparent;
    border-bottom: 4px solid transparent;
    border-left: 6px solid #2e7d32;
  }

  /* Footer */
  .curation-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
  }

  .curation-card__date {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: #666;
    white-space: nowrap;
  }

  .curation-card__picto {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e53935;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
  }

  .curation-card__picto img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .curation-card__picto svg {
    width: 14px;
    height: 14px;
    fill: #fff;
  }
</style>

<!-- Carte -->
<?php echo $tag_open; ?> class="<?php echo esc_attr($card_class); ?>"<?php echo $anchor_attrs; ?>>

  <!-- Image -->
  <?php if ($card_image): ?>
    <img
      class="curation-card__image"
      src="<?php echo esc_url($card_image['url']); ?>"
      alt="<?php echo esc_attr($card_image['alt'] ?? $card_title); ?>"
    />
  <?php endif; ?>

  <!-- Contenu -->
  <div class="curation-card__body">

    <!-- Titre -->
    <p class="curation-card__title">
      <?php echo esc_html($card_title); ?>
    </p>

    <!-- Lien interne -->
    <span class="curation-card__link" role="text">
      <?php echo esc_html($card_link_text); ?>
    </span>

    <!-- Footer : date + picto -->
    <div class="curation-card__footer">

      <div class="curation-card__date">
        <!-- Icône calendrier SVG inline -->
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
             stroke="#666" stroke-width="2" stroke-linecap="round"
             stroke-linejoin="round" aria-hidden="true">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <?php echo esc_html($card_date); ?>
      </div>

      <!-- Picto custom uploadé -->
      <div class="curation-card__picto" aria-hidden="true">
        <?php if ($card_picto): ?>
          <img
            src="<?php echo esc_url($card_picto['url']); ?>"
            alt=""
          />
        <?php endif; ?>
      </div>

    </div>
  </div>

<?php echo $tag_close; ?>
