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

<style>
.cc3-card {
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
.cc3-card.is-clickable { cursor: pointer; }
.cc3-card.is-clickable:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 28px rgba(0,0,0,0.18);
}
.cc3-card__content {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  flex: 1;
  min-height: 0;
  gap: 10px;
}
.cc3-card__header {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-shrink: 0;
}
.cc3-card__avatar {
  width: 30%;
  aspect-ratio: 1 / 1;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
  background: #bdbdbd;
  display: block;
}
.cc3-card__identity {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
  overflow: hidden;
  min-width: 0;
}
.cc3-card__name {
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
.cc3-card__role {
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
.cc3-card__quote {
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
.cc3-card__link {
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
.cc3-card__link:hover { color: #1b5e20; text-decoration-thickness: 2px; }
.cc3-card__link::before {
  content: '';
  display: inline-block;
  flex-shrink: 0;
  width: 0; height: 0;
  border-top: 4px solid transparent;
  border-bottom: 4px solid transparent;
  border-left: 6px solid #2e7d32;
}
.cc3-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}
.cc3-card__date {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  color: #666;
  white-space: nowrap;
}
.cc3-card__picto {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}
.cc3-card__picto img { width: 100%; height: 100%; object-fit: cover; }
</style>

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

    <span class="cc3-card__link" role="text"><?php echo esc_html($cc3_link_text); ?></span>
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
