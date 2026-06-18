<?php
$cc3_avatar    = get_field('cc3_avatar');
$cc3_name      = get_field('cc3_name');
$cc3_role      = get_field('cc3_role');
$cc3_quote     = get_field('cc3_quote');
$cc3_link_text = get_field('cc3_link_text');
$cc3_link_url  = get_field('cc3_link_url');
$cc3_date      = get_field('cc3_date');
$cc3_picto     = get_field('cc3_picto');
$cc3_clickable = get_field('cc3_clickable');
$cc3_url       = get_field('cc3_url');

$card_class   = 'cc3-card';
if ($cc3_clickable) $card_class .= ' is-clickable';

$card_href    = $cc3_clickable && $cc3_url ? $cc3_url : $cc3_link_url;
$tag_open     = $cc3_clickable ? '<a' : '<div';
$tag_close    = $cc3_clickable ? '</a>' : '</div>';
$anchor_attrs = $cc3_clickable
    ? sprintf(' href="%s" target="_blank" rel="noopener noreferrer"', esc_url($card_href))
    : '';
?>

<?php echo $tag_open; ?> class="<?php echo esc_attr($card_class); ?>"<?php echo $anchor_attrs; ?>>

  <div class="cc3-card__content">

    <div class="cc3-card__header">
      <?php if ($cc3_avatar): ?>
        <img class="cc3-card__avatar"
             src="<?php echo esc_url($cc3_avatar['url']); ?>"
             alt="<?php echo esc_attr($cc3_avatar['alt'] ?? $cc3_name); ?>" />
      <?php endif; ?>
      <div class="cc3-card__identity">
        <p class="cc3-card__name"><?php echo esc_html($cc3_name); ?></p>
        <?php if ($cc3_role): ?>
          <p class="cc3-card__role"><?php echo esc_html($cc3_role); ?></p>
        <?php endif; ?>
      </div>
    </div>

    <p class="cc3-card__quote"><?php echo esc_html($cc3_quote); ?></p>

    <span class="cc3-card__link" role="text">
      <?php echo esc_html($cc3_link_text); ?>
    </span>

  </div>

  <div class="cc3-card__footer">
    <div class="cc3-card__date">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#666"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      <?php echo esc_html($cc3_date); ?>
    </div>
    <div class="cc3-card__picto" aria-hidden="true">
      <?php if ($cc3_picto): ?>
        <img src="<?php echo esc_url($cc3_picto['url']); ?>" alt="" />
      <?php endif; ?>
    </div>
  </div>

<?php echo $tag_close; ?>
