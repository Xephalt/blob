<?php
$cc2_title       = get_field('cc2_title');
$cc2_description = get_field('cc2_description');
$cc2_link_text   = get_field('cc2_link_text');
$cc2_link_url    = get_field('cc2_link_url');
$cc2_date        = get_field('cc2_date');
$cc2_picto       = get_field('cc2_picto');
$cc2_clickable   = get_field('cc2_clickable');
$cc2_url         = get_field('cc2_url');

$card_class   = 'cc2-card';
if ($cc2_clickable) $card_class .= ' is-clickable';

$card_href    = $cc2_clickable && $cc2_url ? $cc2_url : $cc2_link_url;
$tag_open     = $cc2_clickable ? '<a' : '<div';
$tag_close    = $cc2_clickable ? '</a>' : '</div>';
$anchor_attrs = $cc2_clickable
    ? sprintf(' href="%s" target="_blank" rel="noopener noreferrer"', esc_url($card_href))
    : '';
?>

<?php echo $tag_open; ?> class="<?php echo esc_attr($card_class); ?>"<?php echo $anchor_attrs; ?>>

  <div class="cc2-card__content">

    <div class="cc2-card__textbox">
      <p class="cc2-card__title"><?php echo esc_html($cc2_title); ?></p>
      <p class="cc2-card__description"><?php echo esc_html($cc2_description); ?></p>
    </div>

    <span class="cc2-card__link" role="text">
      <?php echo esc_html($cc2_link_text); ?>
    </span>

  </div>

  <div class="cc2-card__footer">
    <div class="cc2-card__date">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#666"
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      <?php echo esc_html($cc2_date); ?>
    </div>
    <div class="cc2-card__picto" aria-hidden="true">
      <?php if ($cc2_picto): ?>
        <img src="<?php echo esc_url($cc2_picto['url']); ?>" alt="" />
      <?php endif; ?>
    </div>
  </div>

<?php echo $tag_close; ?>
