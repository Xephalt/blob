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

<style>
.cc1-card {
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
.cc1-card.is-clickable { cursor: pointer; }
.cc1-card.is-clickable:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 28px rgba(0,0,0,0.18);
}
.cc1-card__image {
  width: 100%;
  flex: 0 0 50%;
  min-height: 3rem;
  object-fit: cover;
  display: block;
  background: #bdbdbd;
}
.cc1-card__body {
  flex: 1 1 0;
  min-height: 3rem;
  padding: 12px 16px 8px 16px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
}
.cc1-card__title {
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
.cc1-card__link {
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
.cc1-card__link:hover { color: #1b5e20; text-decoration-thickness: 2px; }
.cc1-card__link::before {
  content: '';
  display: inline-block;
  flex-shrink: 0;
  width: 0; height: 0;
  border-top: 4px solid transparent;
  border-bottom: 4px solid transparent;
  border-left: 6px solid #2e7d32;
}
.cc1-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}
.cc1-card__date {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  color: #666;
  white-space: nowrap;
}
.cc1-card__picto {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}
.cc1-card__picto img { width: 100%; height: 100%; object-fit: cover; }
</style>

<?php echo $tag_open; ?> class="<?php echo esc_attr($card_class); ?>"<?php echo $anchor_attrs; ?>>

  <?php if ($cc1_image): ?>
    <img class="cc1-card__image"
         src="<?php echo esc_url($cc1_image['url']); ?>"
         alt="<?php echo esc_attr($cc1_image['alt'] ?? $cc1_title); ?>" />
  <?php endif; ?>

  <div class="cc1-card__body">
    <p class="cc1-card__title"><?php echo esc_html($cc1_title); ?></p>
    <span class="cc1-card__link" role="text"><?php echo esc_html($cc1_link_text); ?></span>
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
