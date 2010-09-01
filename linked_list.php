<?php
/**
 * @package DF-Style Linked List
 * @author YJ Soon
 * @version 1.1
 */
/*
Plugin Name: DF-Style Linked List
Plugin URI: http://github.com/yjsoon/wordpress-linked-list-plugin
Description: Adapted from Jonathan Penn's <a href="http://github.com/jonathanpenn/wordpress-linked-list-plugin">linked-list plugin</a> to make your blog's RSS feed behave even more like <a href="http://daringfireball.net">Daring Fireball</a>. To use, set the custom field "linked_list_url" to the desired location on a link post. In your RSS feed, the following will happen: (1) the permalink becomes the link destination; (ii) the actual permalink to your post is inserted as a star glyph at the end of your post; (iii) a star glyph is added to before your non-linked-list post titles. Also provides functions to customise your design by checking if the item is a linked list item, getting a permalink with glyph, etc. 
Author: Yinjie Soon
Version: 1.1
Author URI: http://yjsoon.com
*/

// Called to see if the current post in the loop is a linked list
function is_linked_list()
{
  global $wp_query;
  $postid = $wp_query->post->ID;
  $url = get_post_meta($postid, 'linked_list_url', true);
  return (!empty($url));
}

function get_the_linked_list_link()
{
  $url = get_post_custom_values("linked_list_url");
  return $url[0];
}

function the_linked_list_link()
{
  echo get_the_linked_list_link();
}

// This just echoes the chosen line, we'll position it later
function ensure_rss_linked_list($value) {
  if (is_linked_list()) {
    echo get_the_linked_list_link();
  } else {
    echo $value;
  }
}

// Now we set that function up to execute when the admin_footer action is called
add_action('the_permalink_rss', 'ensure_rss_linked_list');


/*-----------------------------------------------------------------------------
  Enhanced linked list functions
-----------------------------------------------------------------------------*/

// To display a permalink glyph
function the_permalink_glyph()
{
  echo get_the_permalink_glyph();
}

function get_the_permalink_glyph()
{
  return '<a href="' . get_permalink() . '" rel="bookmark" title="Permanent link to \''.get_the_title().'\'" class="glyph">&nbsp;'. get_glyph() .'&nbsp;</a>';
}

function get_glyph() {
  return '&#9733;';
}

// Inject permalink glyph into RSS feed contents
function insert_permalink_glyph_rss($content)
{
  if (is_linked_list() && is_feed()) 
  {
    $content = $content . "<p>" . get_the_permalink_glyph() . "</p>\n";
  }
  return $content;
}
add_filter('the_content', 'insert_permalink_glyph_rss');
add_filter('the_excerpt_rss', 'insert_permalink_glyph_rss');

// Inject permalink glyph into RSS title
function insert_title_glyph_rss($title)
{
  if (!is_linked_list()) 
  {
    $title = get_glyph() . " " . $title;
  }
  return $title;
}
add_filter('the_title_rss', 'insert_title_glyph_rss');


/*-----------------------------------------------------------------------------
  Options menu functions
-----------------------------------------------------------------------------*/

// Add the menu 
function dfll_menu() {
  add_options_page('DF-Style Linked List Options', 'DF-Style Linked List', 'manage_options', 'dfll', 'dfll_options_page');
}
add_action('admin_menu', 'dfll_menu');

// Initialise the settings
function dfll_init() {
  register_setting("dfll_options", "dfll_options");
  add_settings_section("dfll_ll", "", "dfll_text", "dfll");
}
add_action('admin_init', 'dfll_init');

function dfll_text() {
?>

  <table class="form-table">

  <tr valign="top">
  <th scope="row" colspan="2">
    <h3>Linked list properties</h3>
    <p>This section defines the behaviour of RSS entries of linked list posts. Default behaviour follows Daring Fireball.</p>
  </th>
  </tr>

  <tr valign="top">
  <th scope="row">
    RSS link goes to linked item 
  </th>
  <td>
    <input type="checkbox" name="link_goes_to" <?php $a = get_option('link_goes_to'); echo ($a=="") ? "checked" : $a;?> /> Linked list entries point to the linked item in question, i.e. when you click on the link title in your RSS reader, your browser opens that link instead of your blog permalink.
  </td>

  <tr valign="top">
  <th scope="row">
    Insert permalink after post
  </th>
  <td>
    <input type="checkbox" name="show_glyph_after_post" <?php $a = get_option('show_glyph_after_post'); echo ($a=="") ? "checked" : $a;?> /> At the bottom of each linked list blog post, show a permalink bringing you back to your blog post. On DF, this is ★. <em>Note for theme customizers</em>: this is the glyph returned in get_glyph() and get_the_permalink_glyph().<br>
    <label for="show_glyph_after_post_text">Text for permalink:</label> <input type="text" name="show_glyph_after_post_text" value="<?php $a = get_option('show_glyph_after_post_text');  echo ($a=="") ? "&#9733;" : $a; ?>" />
  </td>
  </tr>

  <tr valign="top">
  <th scope="row">
    Highlight link posts
  </th>
  <td>
    <input type="checkbox" name="glyph_before_link_title" <?php $a = get_option('glyph_before_link_title'); echo ($a=="") ? "checked" : $a;?> /> Show text <em>before</em> linked-list article titles, e.g. "Link: ". This is useful if you want to distinguish these link posts from your regular blog posts, and may help readers figure out how to get to the link.<br>
    <label for="glyph_before_link_title_text">Text to display:</label> <input type="text" name="glyph_before_link_title_text" value="<?php $a = get_option('glyph_before_link_title_text');  echo ($a=="") ? "Link: " : $a; ?>" />
    <br><br>
    <input type="checkbox" name="glyph_after_link_title" <?php $a = get_option('glyph_after_link_title'); echo ($a=="") ? "checked" : $a;?> /> Show text <em>after</em> linked-list article titles, e.g. "&raquo;". Also helps to distinguish link posts from your regular blog posts.<br>
    <label for="glyph_after_link_title_text">Text to display:</label> <input type="text" name="glyph_after_link_title_text" value="<?php $a = get_option('glyph_after_link_title_text');  echo ($a=="") ? "&raquo;" : $a; ?>" />
  </td>
  </tr>

  <tr valign="top">
  <th scope="row" colspan="2">
    <h3>Blog post properties</h3>
    <p>This section defines the behaviour of RSS entries of blog posts (i.e., not links).</p>
  </th>
  </tr>

  <tr valign="top">
  <th scope="row">
    Highlight blog posts
  </th>
  <td>
    <input type="checkbox" name="glyph_before_blog_title" <?php $a = get_option('glyph_before_blog_title'); echo ($a=="") ? "checked" : $a;?> /> Show text in front of blog article titles in the RSS feed, to distinguish them from link posts &mdash; this is useful if you link more than you post. DF has a ★ in front of such articles.<br>
    <label for="glyph_before_blog_title_text">Text to display:</label> <input type="text" name="glyph_before_blog_title_text" value="<?php $a = get_option('glyph_before_blog_title_text');  echo ($a=="") ? "&#9733;" : $a; ?>" />
  </td>
  </tr>

  <tr>
  <th colspan="2">
  <p class="submit">
  <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
  </p>
  </th>
  </tr>
  </table>
  
<?php
}

function dfll_options_page() {

  ?>
  
  <div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div>
    <h2>Daring Fireball-Style Linked List Plugin Settings</h2>

    <div style="border:1px solid #aaa;margin:2em 1em 0.5em;background-color:#eee;padding:0 1em 1em;">
      <h3>Notes - Read First!</h3>
      <ul style="margin-left: 1.5em; list-style-type:disc;">
  	  <li>Changing the settings on this page <em>only affects the behaviour of your RSS feeds</em>, i.e. it won't change the way your blog is displayed on the web. To change your blog's display properties, edit your theme to use the following functions: is_linked_list(), get_the_linked_list_link(), get_glyph() and get_the_permalink_glyph().</li>
      <li>To enable linked list post behaviour, make sure you create a custom field called <strong>linked_list_url</strong> containing the link you want your post to go to. Other posts without this custom field will be treated as blog, or "regular", posts. If you don't know what custom fields are or how to set them, read the first few sections of <a href="http://www.rlmseo.com/blog/wordpress-custom-fields/">this article</a>.</li>
      <li>Some glyphs (symbols) you can use: &#9733; &#8594; &#8658; &nabla; &loz; &#10004; &#10010; &#10020; &#10022; &#9819; &#9820; &raquo; &laquo; (<a href="http://www.danshort.com/HTMLentities/index.php?w=maths">more here</a>). You can just copy and paste these into the fields below.</li>
      </ul>
    </div>

    <form name="df-form" method="post" action="">
      <?php settings_fields('dfll_options'); ?>
      <?php do_settings_sections('dfll'); ?>
    </form>
  
  </div>

  <?php

}

?>
