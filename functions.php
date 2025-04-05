<?php

/**
 * Enqueue parent and child theme styles.
 * 
 * @return void
 */
function my_child_enqueue_styles()
{
  wp_enqueue_style('twentytwenty-style', get_template_directory_uri() . '/style.css');
  wp_enqueue_style('twentytwenty-child-style', get_stylesheet_uri(), array('twentytwenty-style'));
}
add_action('wp_enqueue_scripts', 'my_child_enqueue_styles');

/**
 * Enqueue custom scripts and localize the script for AJAX.
 * 
 * @return void
 */
function my_child_enqueue_scripts()
{
  wp_enqueue_script('custom-scripts', get_stylesheet_directory_uri() . '/assets/js/scripts.js', array('jquery'), '1.0', true);
  wp_localize_script('custom-scripts', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'nonce'    => wp_create_nonce('my_books_nonce')));
}
add_action('wp_enqueue_scripts', 'my_child_enqueue_scripts');

/**
 * Register a custom post type and a custom taxonomy with translatable labels.
 * 
 * @return void
 */
function my_child_register_books()
{
  $labels = array(
    'name'               => __('Books', 'my-child-theme'),
    'singular_name'      => __('Book', 'my-child-theme'),
    'add_new'            => __('Add New Book', 'my-child-theme'),
    'add_new_item'       => __('Add New Book', 'my-child-theme'),
    'edit_item'          => __('Edit Book', 'my-child-theme'),
    'new_item'           => __('New Book', 'my-child-theme'),
    'all_items'          => __('All Books', 'my-child-theme'),
    'view_item'          => __('View Book', 'my-child-theme'),
    'search_items'       => __('Search Books', 'my-child-theme'),
    'not_found'          => __('No books found', 'my-child-theme'),
    'not_found_in_trash' => __('No books found in Trash', 'my-child-theme'),
  );
  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'has_archive'        => true,
    'rewrite'            => array('slug' => 'library'),
    'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
    'show_in_rest'       => true,
  );
  register_post_type('book', $args);

  $tax_labels = array(
    'name'              => __('Genres', 'my-child-theme'),
    'singular_name'     => __('Genre', 'my-child-theme'),
    'search_items'      => __('Search Genres', 'my-child-theme'),
    'all_items'         => __('All Genres', 'my-child-theme'),
    'parent_item'       => __('Parent Genre', 'my-child-theme'),
    'parent_item_colon' => __('Parent Genre:', 'my-child-theme'),
    'edit_item'         => __('Edit Genre', 'my-child-theme'),
    'update_item'       => __('Update Genre', 'my-child-theme'),
    'add_new_item'      => __('Add New Genre', 'my-child-theme'),
    'new_item_name'     => __('New Genre Name', 'my-child-theme'),
  );
  $tax_args = array(
    'labels'            => $tax_labels,
    'hierarchical'      => true,
    'rewrite'           => array('slug' => 'book-genre'),
    'show_in_rest'      => true,
  );
  register_taxonomy('genre', 'book', $tax_args);
}
add_action('init', 'my_child_register_books');

/**
 * Set the number of books per page for the custom taxonomy archive.
 * 
 * @param WP_Query $query Main query.
 * @return void
 */
function my_child_set_books_per_page($query)
{
  if (!is_admin() && $query->is_main_query() && is_tax('genre')) {
    $query->set('posts_per_page', 5);
  }
}
add_action('pre_get_posts', 'my_child_set_books_per_page');

/**
 * Add a custom shortcode to display the most recent book title.
 * 
 * @return string The book title.
 */
function my_child_recent_book_title()
{
  $args = array(
    'post_type'      => 'book',
    'posts_per_page' => 1,
    'orderby'        => 'date',
    'order'          => 'DESC',
  );
  $recent_book = new WP_Query($args);
  $title = '';

  if ($recent_book->have_posts()) {
    $recent_book->the_post();
    $title = esc_html(get_the_title());
    wp_reset_postdata();
  }
  return $title;
}
add_shortcode('recent_book_title', 'my_child_recent_book_title');

/**
 * Add a custom shortcode to display books by genre.
 * 
 * @param array $atts Shortcode attributes.
 * @return string HTML output with the list of books.
 */
function my_child_books_by_genre($atts)
{
  $atts = shortcode_atts(array(
    'genre' => 0,
  ), $atts, 'books_by_genre');

  $args = array(
    'post_type'      => 'book',
    'posts_per_page' => 5,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'tax_query'      => array(
      array(
        'taxonomy' => 'genre',
        'field'    => 'term_id',
        'terms'    => intval($atts['genre']),
      ),
    ),
  );

  $query = new WP_Query($args);
  $output = '';

  if ($query->have_posts()) {
    $output .= '<ul>';
    while ($query->have_posts()) {
      $query->the_post();
      $output .= '<li>' . esc_html(get_the_title()) . '</li>';
    }
    $output .= '</ul>';
    wp_reset_postdata();
  } else {
    $output = esc_html__('No books found for this genre.', 'my-child-theme');
  }
  return $output;
}
add_shortcode('books_by_genre', 'my_child_books_by_genre');

/**
 * Add an AJAX action to fetch 20 books.
 * Returns only the fields: name, date, genre, excerpt.
 *
 * @return void Outputs JSON response.
 */
function my_child_ajax_get_books()
{

  if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'my_books_nonce')) {
    wp_send_json_error(array('message' => esc_html__('Invalid nonce.', 'my-child-theme')));
  }

  $args = array(
    'post_type'      => 'book',
    'posts_per_page' => 20,
    'orderby'        => 'date',
    'order'          => 'DESC',
  );
  $query = new WP_Query($args);
  $books = array();

  if ($query->have_posts()) {
    while ($query->have_posts()) {
      $query->the_post();

      $book_genres = get_the_terms(get_the_ID(), 'genre');
      $genres = array();
      if ($book_genres && ! is_wp_error($book_genres)) {
        foreach ($book_genres as $genre) {
          $genres[] = esc_html($genre->name);
        }
      }

      $books[] = array(
        'name'    => esc_html(get_the_title()),
        'date'    => esc_html(get_the_date()),
        'genre'   => $genres,
        'excerpt' => esc_html(get_the_excerpt()),
      );
    }
    wp_reset_postdata();
  }
  wp_send_json_success($books);
}
add_action('wp_ajax_get_books', 'my_child_ajax_get_books');
add_action('wp_ajax_nopriv_get_books', 'my_child_ajax_get_books');
