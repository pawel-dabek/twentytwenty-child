<?php get_header(); ?>

<div class="genre">
  <h1 class="genre-title"><?php single_term_title(); ?></h1>
  <?php if (have_posts()) : ?>
    <ul class="books-list">
      <?php while (have_posts()) : the_post(); ?>
        <li class="single-book">
          <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </li>
      <?php endwhile; ?>
    </ul>
    <div class="pagination">
      <?php
      the_posts_pagination(array(
        'mid_size'  => 2,
        'prev_text' => __('Previous', 'my-child-theme'),
        'next_text' => __('Next', 'my-child-theme'),
      ));
      ?>
    </div>
  <?php else : ?>
    <p class="not-found-text"><?php _e('No books found in this genre.', 'my-child-theme'); ?></p>
  <?php endif; ?>
</div>

<?php get_footer(); ?>