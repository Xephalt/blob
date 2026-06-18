<?php
/**
 * Template : Bloc Curation Card 3
 * Médaillon photo + nom + poste (optionnel) + citation + lien + date + picto
 */

$card3_avatar     = get_field('card3_avatar');
$card3_name       = get_field('card3_name');
$card3_role       = get_field('card3_role');       // optionnel
$card3_quote      = get_field('card3_quote');
$card3_link_text  = get_field('card3_link_text');
$card3_link_url   = get_field('card3_link_url');
$card3_date       = get_field('card3_date');
$card3_picto      = get_field('card3_picto');
$card3_clickable  = get_field('card3_clickable');
$card3_url        = get_field('card3_url');

$card_class   = 'curation-card-3';
if ($card3_clickable) {
    $card_class .= ' is-clickable';
}

$card_href    = $card3_clickable && $card3_url ? $card3_url : $card3_link_url;
$tag_open     = $card3_clickable ? '<a' : '<div';
$tag_close    = $card3_clickable ? '</a>' : '</div>';
$anchor_attrs = $card3_clickable ? sprintf(
    ' href="%s" target="_blank" rel="noopener noreferrer"',
    esc_url($card_href)
) : '';
?>

<style>
  .curation-card-3 {
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
    padding: 16px;
    box-sizing: border-box;
  }

  .curation-card-3.is-clickable { cursor: pointer; }
  .curation-card-3.is-clickable:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.18);
  }

  .card3__content {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex: 1;
    min-height: 0;
    gap: 10px;
  }

  /* ── HEADER : médaillon 30% / identité 70% ── */
  .card3__header {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-shrink: 0;
  }

  .card3__avatar {
    width: 30%;
    aspect-ratio: 1 / 1;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    background: #bdbdbd;
    display: block;
  }

  .card3__identity {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
    overflow: hidden;
    min-width: 0;
  }

  /* Zone texte 1 : nom */
  .card3__name {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.3;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Zone texte 2 : poste (optionnel) */
  .card3__role {
    font-size: 13px;
    font-weight: 400;
    color: #777;
    line-height: 1.3;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Zone texte 3 : citation — même taille que titres actualités */
  .card3__quote {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    line-height: 1.45;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Lien */
  .card3__link {
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
    cursor: pointer;
  }

  .card3__link:hover {
    color: #1b5e20;
    text-decoration-thickness: 2px;
  }

  .card3__link::before {
    content: '';
    display: inline-block;
    flex-shrink: 0;
    width: 0; height: 0;
    border-top: 4px solid transparent;
    border-bottom: 4px solid transparent;
    border-left: 6px solid #2e7d32;
  }

  /* Footer */
  .card3__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
  }

  .card3__date {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: #666;
    white-space: nowrap;
  }

  .card3__picto {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
  }

  .card3__picto img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
</style>

<?php echo $tag_open; ?> class="<?php echo esc_attr($card_class); ?>"<?php echo $anchor_attrs; ?>>

  <div class="card3__content">

    <!-- Header : médaillon + nom + poste -->
    <div class="card3__header">

      <?php if ($card3_avatar): ?>
        <img
          class="card3__avatar"
          src="<?php echo esc_url($card3_avatar['url']); ?>"
          alt="<?php echo esc_attr($card3_avatar['alt'] ?? $card3_name); ?>"
        />
      <?php endif; ?>

      <div class="card3__identity">
        <p class="card3__name"><?php echo esc_html($card3_name); ?></p>
        <?php if ($card3_role): ?>
          <p class="card3__role"><?php echo esc_html($card3_role); ?></p>
        <?php endif; ?>
      </div>

    </div>

    <!-- Citation -->
    <p class="card3__quote"><?php echo esc_html($card3_quote); ?></p>

    <!-- Lien -->
    <span class="card3__link" role="text">
      <?php echo esc_html($card3_link_text); ?>
    </span>

  </div>

  <!-- Footer -->
  <div class="card3__footer">

    <div class="card3__date">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
           stroke="#666" stroke-width="2" stroke-linecap="round"
           stroke-linejoin="round" aria-hidden="true">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      <?php echo esc_html($card3_date); ?>
    </div>

    <div class="card3__picto" aria-hidden="true">
      <?php if ($card3_picto): ?>
        <img src="<?php echo esc_url($card3_picto['url']); ?>" alt="" />
      <?php endif; ?>
    </div>

  </div>

<?php echo $tag_close; ?>
