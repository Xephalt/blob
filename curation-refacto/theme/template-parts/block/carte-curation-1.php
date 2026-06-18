<?php
$cc1_image     = get_field('cc1_image');
$cc1_title     = get_field('cc1_title');
$cc1_link_text = get_field('cc1_link_text');
$cc1_link_url  = get_field('cc1_link_url');
$cc1_date      = get_field('cc1_date');
$cc1_picto     = get_field('cc1_picto');
$cc1_clickable = get_field('cc1_clickable');
$cc1_url       = get_field('cc1_url');

$card_class   = 'cc1-card';
if ($cc1_clickable) $card_class .= ' is-clickable';

$card_href    = $cc1_clickable && $cc1_url ? $cc1_url : $cc1_link_url;
$tag_open     = $cc1_clickable ? '<a' : '<div';
$tag_close    = $cc1_clickable ? '</a>' : '</div>';
$anchor_attrs = $cc1_clickable
    ? sprintf(' href="%s" target="_blank" rel="noopener noreferrer"', esc_url($card_href))
    : '';
?>

<?php echo $tag_open; ?> class="<?php echo esc_attr($card_class); ?>"<?php echo $anchor_attrs; ?>>

  <?php if ($cc1_image): ?>
    <img class="cc1-card__image"
         src="<?php echo esc_url($cc1_image['url']); ?>"
         alt="<?php echo esc_attr($cc1_image['alt'] ?? $cc1_title); ?>" />
  <?php endif; ?>

  <div class="cc1-card__body">

    <p class="cc1-card__title"><?php echo esc_html($cc1_title); ?></p>

    <span class="cc1-card__link" role="text">
      <?php echo esc_html($cc1_link_text); ?>
    </span>

    <div class="cc1-card__footer">
      <div class="cc1-card__date">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#666"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <?php echo esc_html($cc1_date); ?>
      </div>
      <div class="cc1-card__picto" aria-hidden="true">
        <?php if ($cc1_picto): ?>
          <img src="<?php echo esc_url($cc1_picto['url']); ?>" alt="" />
        <?php endif; ?>
      </div>
    </div>

  </div>

<?php echo $tag_close; ?>
