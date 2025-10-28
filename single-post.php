<?php
/* Optimise Learning — Single Post (inline CSS + JS, sticky TOC, hero spans grid) */
get_header();

/* ---------- Helpers ---------- */
function ol_slugify($text){
  $text = wp_strip_all_tags($text);
  $text = remove_accents($text);
  $text = preg_replace('/[^A-Za-z0-9]+/', '-', strtolower($text));
  return trim($text, '-');
}
/* Inject id attributes into H2/H3 and build a TOC array. */
function ol_inject_ids_and_toc($html){
  $toc = [];
  $html = preg_replace_callback('#<h2([^>]*)>(.*?)</h2>#is', function($m) use (&$toc){
    $attrs = $m[1]; $inner = $m[2];
    if ( preg_match('/id="([^"]+)"/', $attrs, $idMatch) ) { $id = $idMatch[1]; }
    else { $id = ol_slugify( wp_strip_all_tags($inner) ); $attrs .= ' id="'.esc_attr($id).'"'; }
    $toc[] = ['level'=>2,'id'=>$id,'title'=>wp_strip_all_tags($inner)];
    return '<h2'.$attrs.'>'.$inner.'</h2>';
  }, $html);
  $html = preg_replace_callback('#<h3([^>]*)>(.*?)</h3>#is', function($m) use (&$toc){
    $attrs = $m[1]; $inner = $m[2];
    if ( preg_match('/id="([^"]+)"/', $attrs, $idMatch) ) { $id = $idMatch[1]; }
    else { $id = ol_slugify( wp_strip_all_tags($inner) ); $attrs .= ' id="'.esc_attr($id).'"'; }
    $toc[] = ['level'=>3,'id'=>$id,'title'=>wp_strip_all_tags($inner)];
    return '<h3'.$attrs.'>'.$inner.'</h3>';
  }, $html);
  return [$html, $toc];
}
?>

<main id="primary" class="site-main single-post ol-single">

<?php while ( have_posts() ) : the_post();

  $thumb_id  = get_post_thumbnail_id();
  $thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'full' ) : '';

  $rendered  = apply_filters('the_content', get_the_content());
  list($content_with_ids, $toc_items) = ol_inject_ids_and_toc($rendered);

  // Reading time (~200wpm)
  $words = str_word_count( wp_strip_all_tags( get_post_field('post_content', get_the_ID() ) ) );
  $mins  = max(1, ceil($words / 200));

  // Back link: use posts page if set, otherwise /blog/
  $blog_url = get_permalink( get_option('page_for_posts') );
  if ( empty($blog_url) ) { $blog_url = home_url('/blog/'); }
?>

  <!-- ONE container + ONE grid for the whole page -->
  <div class="ol-container">
    <div class="ol-grid">

      <!-- HERO spans both columns -->
      <section class="ol-hero ol-hero-row">
        <div class="ol-hero__grid">
          <div class="ol-hero__inner">
            <p class="ol-back"><a href="<?php echo esc_url($blog_url); ?>">&larr; Back to the blog</a></p>
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <div class="entry-meta">
              <span class="meta__author"><?php echo esc_html( get_the_author() ); ?></span>
              <time datetime="<?php echo esc_attr( get_the_date('c') ); ?>">
                <?php echo esc_html( get_the_date() ); ?>
              </time>
              <span class="meta__read"><?php echo esc_html($mins); ?> min read</span>
              <?php if ( get_the_modified_time() !== get_the_time() ) : ?>
                <span class="meta__updated">Updated <?php echo esc_html( get_the_modified_date() ); ?></span>
              <?php endif; ?>
            </div>
          </div>

          <?php if ( $thumb_url ) : ?>
            <figure class="ol-hero__media">
              <img src="<?php echo esc_url($thumb_url); ?>"
                   alt="<?php echo esc_attr(get_the_title()); ?>" loading="eager">
            </figure>
          <?php endif; ?>
        </div>
      </section>

      <!-- ARTICLE (left column) -->
      <article <?php post_class('ol-entry flow'); ?>>
        <div class="entry-content">
          <?php echo $content_with_ids; ?>
          <?php wp_link_pages(['before'=>'<div class="page-links">Pages:','after'=>'</div>']); ?>
        </div>

            <!-- AUTHOR (lean footer) -->
            <section class="ol-author ol-author--bare" aria-label="Author">
          <p class="ol-author__line" style="font-weight: 600;">TRACEY HAND | CO‑FOUNDER</p>
          <p class="ol-author__brand"><a href="/" style="font-weight: 600;">OPTIMISE LEARNING</a></p>
        </section>

        <!-- CTA (conversion band) -->
        <section class="ol-cta ol-cta--band" aria-labelledby="cta-title">
          <h2 id="cta-title" class="ol-cta__title">Start Optimising Your Child’s School Achievements Today!</h2>
          <p class="ol-cta__sub">
            Our academic tutoring services have assisted hundreds of students throughout Australia 
            to develop their self‑confidence and improve their achievements at school.<br>
            Contact us to discuss your child’s learning needs and academic goals.
          </p>
          <a class="btn ol-cta__btn" href="/free-trial-booking/">Book a Free Trial Lesson</a>
        </section>

    

        <footer class="entry-footer">
          <!-- <div class="ol-tax">
            <?php the_category(' '); ?>
            <?php the_tags('<span class="ol-tags">', ' ', '</span>'); ?>
          </div> -->
        </footer>
      </article>

      <!-- STICKY TOC (right column) -->
      <aside class="ol-sidebar">
        <?php if ( !empty($toc_items) ) : ?>
          <nav class="ol-toc" aria-label="On this page">
            <div class="ol-toc__title">On this page</div>
            <ul>
              <?php foreach ( $toc_items as $item ) : ?>
                <li class="lvl-<?php echo (int) $item['level']; ?>">
                  <a href="#<?php echo esc_attr($item['id']); ?>"><?php echo esc_html($item['title']); ?></a>
                </li>
              <?php endforeach; ?>
            </ul>
          </nav>
        <?php endif; ?>
      </aside>

    </div><!-- /.ol-grid -->
  </div><!-- /.ol-container -->

  <!-- RELATED -->
  <section class="ol-related">
    <h3>Related posts</h3>
    <?php
      $rel = new WP_Query([
        'post_type'           => 'post',
        'posts_per_page'      => 3,
        'post__not_in'        => [ get_the_ID() ],
        'ignore_sticky_posts' => 1,
        'tax_query'           => [[
          'taxonomy' => 'category',
          'field'    => 'term_id',
          'terms'    => wp_get_post_categories(get_the_ID()),
        ]],
      ]);
      if ( $rel->have_posts() ) :
        echo '<div class="ol-related__grid">';
        while ( $rel->have_posts() ) : $rel->the_post(); ?>
          <a class="ol-card" href="<?php the_permalink(); ?>">
            <?php if ( has_post_thumbnail() ) the_post_thumbnail('medium_large'); ?>
            <h4 class="ol-card__title"><?php the_title(); ?></h4>
          </a>
        <?php endwhile;
        echo '</div>';
        wp_reset_postdata();
      endif;
    ?>
  </section>

<?php endwhile; ?>
</main>

<script>
/* Smooth scrolling + active section highlight */
document.documentElement.style.scrollBehavior = 'smooth';
(function(){
  const tocLinks = document.querySelectorAll('.ol-toc a');
  if (!tocLinks.length) return;
  const targets = Array.from(tocLinks).map(a => document.getElementById(a.hash.slice(1))).filter(Boolean);
  const setActive = id => tocLinks.forEach(a => a.classList.toggle('active', a.hash === '#'+id));
  const io = new IntersectionObserver(es => es.forEach(e => e.isIntersecting && setActive(e.target.id)), {
    rootMargin: '0px 0px -70% 0px', threshold: 0.01
  });
  targets.forEach(t => io.observe(t));
})();
</script>

<?php get_footer();