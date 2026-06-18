<?php
/**
 * Template : Bloc Curation Card 2
 * Carte texte sans image : titre + description + lien + date + picto
 */

$card2_title       = get_field('card2_title');
$card2_description = get_field('card2_description');
$card2_link_text   = get_field('card2_link_text');
$card2_link_url    = get_field('card2_link_url');
$card2_date        = get_field('card2_date');
$card2_picto       = get_field('card2_picto');
$card2_clickable   = get_field('card2_clickable');
$card2_url         = get_field('card2_url');

$card_class = 'curation-card-2';
if ($card2_clickable) {
    $card_class .= ' is-clickable';
}

$card_href    = $card2_clickable && $card2_url ? $card2_url : $card2_link_url;
$tag_open     = $card2_clickable ? '<a' : '<div';
$tag_close    = $card2_clickable ? '</a>' : '</div>';
$anchor_attrs = $card2_clickable ? sprintf(
    ' href="%s" target="_blank" rel="noopener noreferrer"',
    esc_url($card_href)
) : '';
?>

<style>
  .curation-card-2 {
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

  .curation-card-2.is-clickable {
    cursor: pointer;
  }
  .curation-card-2.is-clickable:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.18);
  }

  .card2__content {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex: 1;
    min-height: 0;
    gap: 8px;
  }

  .card2__textbox {
    display: flex;
    flex-direction: column;
    gap: 8px;
    overflow: hidden;
  }

  .card2__title {
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

  .card2__description {
    font-size: 14px;
    font-weight: 400;
    color: #555;
    line-height: 1.5;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 7;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .card2__link {
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

  .card2__link::before {
    content: '';
    display: inline-block;
    flex-shrink: 0;
    width: 0;
    height: 0;
    border-top: 4px solid transparent;
    border-bottom: 4px solid transparent;
    border-left: 6px solid #2e7d32;
  }

  .card2__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
  }

  .card2__date {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    color: #666;
    white-space: nowrap;
  }

  .card2__picto {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #2e7d32;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
  }

  .card2__picto img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
</style>

<?php echo $tag_open; ?> class="<?php echo esc_attr($card_class); ?>"<?php echo $anchor_attrs; ?>>

  <div class="card2__content">

    <div class="card2__textbox">
      <p class="card2__title"><?php echo esc_html($card2_title); ?></p>
      <p class="card2__description"><?php echo esc_html($card2_description); ?></p>
    </div>

    <span class="card2__link" role="text">
      <?php echo esc_html($card2_link_text); ?>
    </span>

  </div>

  <div class="card2__footer">

    <div class="card2__date">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
           stroke="#666" stroke-width="2" stroke-linecap="round"
           stroke-linejoin="round" aria-hidden="true">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      <?php echo esc_html($card2_date); ?>
    </div>

    <div class="card2__picto" aria-hidden="true">
      <?php if ($card2_picto): ?>
        <img src="<?php echo esc_url($card2_picto['url']); ?>" alt="" />
      <?php endif; ?>
    </div>

  </div>

<?php echo $tag_close; ?>
