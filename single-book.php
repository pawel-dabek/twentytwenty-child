<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
  <div class="book-single">
    <h1 class="book-title"><?php the_title(); ?></h1>

    <?php if (has_post_thumbnail()) : ?>
      <div class="book-thumbnail">
        <?php the_post_thumbnail(); ?>
      </div>
    <?php endif; ?>

    <div class="book-genres">
      <?php echo get_the_term_list(get_the_ID(), 'genre', __('Genre: ', 'my-child-theme'), ', '); ?>
    </div>

    <div class="book-date">
      <?php printf(__('Published on: %s', 'my-child-theme'), get_the_date()); ?>
    </div>
  </div>
<?php endwhile; ?>

<?php get_footer(); ?>