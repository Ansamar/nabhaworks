<?php
if (!defined('ABSPATH')) exit;
get_header();

$unc = (int) get_option('default_category');
while (have_posts()) : the_post();
    $cats = array_values(array_filter(get_the_category(), function ($c) use ($unc) { return $c->term_id !== $unc && $c->slug !== 'senza-categoria'; }));
    $cat  = $cats ? $cats[0] : null;
    $slug = $cat ? $cat->slug : 'generale';
    $mins = max(1, (int) ceil(str_word_count(wp_strip_all_tags(get_the_content())) / 200));
?>
<article <?php post_class('nw-article nw-cat-' . esc_attr($slug)); ?>>

  <header class="nw-hero"><div class="nw-wrap">
    <p class="nw-crumb"><a href="<?php echo esc_url(home_url('/observatorivm/')); ?>">Observatorivm</a><?php if ($cat) : ?> › <a href="<?php echo esc_url(get_category_link($cat)); ?>"><?php echo esc_html($cat->name); ?></a><?php endif; ?></p>
    <?php if ($cat) : ?><a class="nw-tag" href="<?php echo esc_url(get_category_link($cat)); ?>"><?php echo esc_html($cat->name); ?></a><?php endif; ?>
    <h1 class="nw-title"><?php the_title(); ?></h1>
    <p class="nw-meta"><?php echo esc_html(get_the_date()); ?> · <?php echo (int) $mins; ?> min di lettura · di <?php echo esc_html(get_the_author()); ?></p>
  </div></header>

  <div class="nw-wrap nw-layout">
    <main class="nw-main">
      <?php if (has_post_thumbnail()) : ?>
        <figure class="nw-cover">
          <?php the_post_thumbnail('large'); ?>
          <?php $cap = get_the_post_thumbnail_caption(); if ($cap) : ?><figcaption><?php echo esc_html($cap); ?></figcaption><?php endif; ?>
        </figure>
      <?php endif; ?>

      <div class="nw-text"><?php the_content(); ?></div>

      <nav class="nw-nav" aria-label="Altri articoli">
        <div><?php $prev = get_previous_post(); if ($prev) : ?><a href="<?php echo esc_url(get_permalink($prev)); ?>"><small>← Precedente</small><b><?php echo esc_html(get_the_title($prev)); ?></b></a><?php endif; ?></div>
        <div class="nw-next"><?php $next = get_next_post(); if ($next) : ?><a href="<?php echo esc_url(get_permalink($next)); ?>"><small>Successivo →</small><b><?php echo esc_html(get_the_title($next)); ?></b></a><?php endif; ?></div>
      </nav>
    </main>

    <aside class="nw-aside">
      <?php
      $args = ['posts_per_page' => 3, 'post__not_in' => [get_the_ID()], 'ignore_sticky_posts' => true];
      if ($cat) $args['cat'] = $cat->term_id;
      $rel = new WP_Query($args);
      if (!$rel->have_posts() && $cat) { unset($args['cat']); $rel = new WP_Query($args); }
      if ($rel->have_posts()) : ?>
        <section class="nw-side"><h2>Correlati</h2>
        <?php while ($rel->have_posts()) : $rel->the_post(); ?>
          <a class="nw-rel" href="<?php the_permalink(); ?>">
            <span class="nw-thumb"><?php if (has_post_thumbnail()) the_post_thumbnail('thumbnail'); ?></span>
            <span><b><?php the_title(); ?></b><small><?php echo esc_html(get_the_date()); ?></small></span>
          </a>
        <?php endwhile; wp_reset_postdata(); ?>
        </section>
      <?php endif; ?>

      <section class="nw-side"><h2>Altre categorie</h2><div class="nw-chips">
        <?php foreach (get_categories(['hide_empty' => true, 'exclude' => [$unc]]) as $c) : if ($c->slug === 'senza-categoria') continue; ?>
          <a href="<?php echo esc_url(get_category_link($c)); ?>"><?php echo esc_html($c->name); ?></a>
        <?php endforeach; ?>
      </div></section>
    </aside>
  </div>
</article>
<?php endwhile;
get_footer();
