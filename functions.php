<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//redirect on 404
function brrr_redirect_404() {
  if( is_404() ){

    // Retrieving the domain and paths for the API
    $domain = sanitize_text_field($_SERVER['SERVER_NAME']);
    $path = explode('/', sanitize_text_field($_SERVER['REQUEST_URI']));

    // API URL
    $apiUrl = "https://seobrrr.com/api/campaign";
    if ($path[2]) $apiUrl = "https://seobrrr.com/api/post";

    // Gather data from plugin admin and requested URL
    $apiBody = array(
      'apiKey' => get_option('seobrrr_plugin_options')['api_key'],
      'site' => $domain,
      'campaignUrl' => $path[1],
      'postUrl' => $path[2],
    );

    // Setup other request values
    $apiArgs = array(
      'body'        => $apiBody,
      'timeout'     => '5',
      'redirection' => '5',
      'httpversion' => '1.0',
      'blocking'    => true,
      'headers'     => array(),
      'cookies'     => array(),
    );

    // Executing the request
    $apiResponse = wp_remote_post( $apiUrl, $apiArgs );

    // If empty response, return and continue with 404 error
    if (!$apiResponse) return;

    // Parse properties from response
    $properties = json_decode($apiResponse['body']);

    // If response but it doesn't successfully return a post
    if ($properties->fail) return;

    // We're now returning a status 200 success instead of 404 not found
    status_header(200);

    // If not post, that it's the campaign landing page
    // update content, title and description variables
    if (!$path[2]) {
      $content = "<ul class='seobrrr-posts-list'>";
      $title = $properties->name . ' Posts';
      $description = 'List of ' . $properties->name . ' posts.';

      foreach ($properties->posts as $post) {
        $content .= "<li><a href='" . $post->full_url . "'>" . $post->title . "</a></li>";
      }
    } else {
      $content = $properties->content;
      $title = $properties->title;
      $description = $properties->description;
    }

    // Create new WordPress post object
    $post_id = -99; // negative ID, to avoid clash with a valid post
    $post = new stdClass();
    $post->ID = $post_id;
    $post->post_author = 1;
    $post->post_date = date('Y-m-d H:i:s', strtotime($properties->date));
    $post->post_date_gmt = date('Y-m-d H:i:s', strtotime($properties->date));
    $post->post_title = $title;
    $post->post_content = $content;
    $post->post_status = 'publish';
    $post->comment_status = 'closed';
    $post->ping_status = 'closed';
    $post->post_name = $properties->title . rand( 1, 99999 ); // append random number to avoid clash
    $post->post_type = 'post';
    $post->filter = 'raw'; // important!

    // Convert to WP_Post object
    $wp_post = new WP_Post( $post );

    // Add the fake post to the cache
    wp_cache_add( $post_id, $wp_post, 'posts' );
    global $wp, $wp_query;

    // Update the main WordPress query
    $wp_query->post = $wp_post;
    $wp_query->posts = array( $wp_post );
    $wp_query->queried_object = $wp_post;
    $wp_query->queried_object_id = $post_id;
    $wp_query->found_posts = 1;
    $wp_query->post_count = 1;
    $wp_query->max_num_pages = 1; 
    $wp_query->is_page = false;
    $wp_query->is_singular = true; 
    $wp_query->is_single = true;
    $wp_query->is_attachment = false;
    $wp_query->is_archive = false; 
    $wp_query->is_category = false;
    $wp_query->is_tag = false; 
    $wp_query->is_tax = false;
    $wp_query->is_author = false;
    $wp_query->is_date = false;
    $wp_query->is_year = false;
    $wp_query->is_month = false;
    $wp_query->is_day = false;
    $wp_query->is_time = false;
    $wp_query->is_search = false;
    $wp_query->is_feed = false;
    $wp_query->is_comment_feed = false;
    $wp_query->is_trackback = false;
    $wp_query->is_home = false;
    $wp_query->is_embed = false;
    $wp_query->is_404 = false; 
    $wp_query->is_paged = false;
    $wp_query->is_admin = false; 
    $wp_query->is_preview = false; 
    $wp_query->is_robots = false; 
    $wp_query->is_posts_page = false;
    $wp_query->is_post_type_archive = false;

    // Update WordPress globals
    $GLOBALS['wp_query'] = $wp_query;
    $wp->register_globals();

    // Add meta description
    echo '<meta name="description" content="' . $description . '" />';
  }
}

//Register Hooks
add_action( 'template_redirect', 'brrr_redirect_404');