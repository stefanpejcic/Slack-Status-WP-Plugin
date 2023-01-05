<?php
/*
Plugin Name: Slack Status
Plugin URI: https://wpxss.com/slack-status
Description: Displays my Slack status on the website.
Version: 1.0
Author: Stefan Pejcic
Author URI: https://pejcic.rs
*/

function slack_status_get_status() {
  // Replace YOUR_SLACK_TOKEN with your Slack API token
  $token = get_option( 'slack_status_token' );
  // Replace YOUR_SLACK_USER_ID with your Slack user ID
  $user_id = get_option( 'slack_status_user_id' );
  // Send a GET request to the Slack API to get the user's presence
  $response = wp_remote_get( "https://slack.com/api/users.info?token=$token&user=$user_id&pretty=1" );
  // Decode the response
  $response_data = json_decode( wp_remote_retrieve_body( $response ), true );
  // Check if the request was successful
  if ( ! is_wp_error( $response ) && $response_data['ok'] ) {
    // Return the user's presence
    return $response_data['user']['presence'];
  } else {
    return 'error';
  }
}


function slack_status_display() {
  // Get the user's Slack status
  $status = slack_status_get_status();
  // Check the user's Slack status
  if ( $status === 'active' ) {
    echo '<p>🟢 I am currently active on Slack.</p>';
  } elseif ( $status === 'away' ) {
    echo '<p>🔴 I am currently away from Slack.</p>';
  } else {
    echo '<p>There was an error retrieving my Slack status.</p>';
  }
}

function slack_status_shortcode() {
  ob_start();
  slack_status_display();
return ob_get_clean();
}
add_shortcode( 'slack_status', 'slack_status_shortcode' );

function slack_status_settings_init() {
  add_settings_section(
    'slack_status_section', // ID
    'Slack Status Settings', // Title
    'slack_status_settings_section_callback', // Callback function
    'general' // Page
  );

  add_settings_field(
    'slack_status_token', // ID
    'Slack API Token', // Title
    'slack_status_token_callback', // Callback function
    'general', // Page
    'slack_status_section' // Section
  );

  add_settings_field(
    'slack_status_user_id', // ID
    'Slack User ID', // Title
    'slack_status_user_id_callback', // Callback function
    'general', // Page
    'slack_status_section' // Section
  );

  register_setting( 'general', 'slack_status_token', 'sanitize_text_field' );
  register_setting( 'general', 'slack_status_user_id', 'sanitize_text_field' );
}
add_action( 'admin_init', 'slack_status_settings_init' );

function slack_status_settings_section_callback() {
  echo '<p>Enter your Slack API token and user ID below:</p>';
}

function slack_status_token_callback() {
  $token = get_option( 'slack_status_token' );
  echo '<input type="text" name="slack_status_token" value="' . esc_attr( $token ) . '" size="40">';
}

function slack_status_user_id_callback() {
  $user_id = get_option( 'slack_status_user_id' );
  echo '<input type="text" name="slack_status_user_id" value="' . esc_attr( $user_id ) . '" size="40">';
}

function slack_status_settings_page() {
  ?>
  <div class="wrap">
    <h1>Slack Status Settings</h1>
    <form action="options.php" method="post">
      <?php
      settings_fields( 'general' );
      do_settings_sections( 'general' );
      submit_button();
      ?>
    </form>
  </div>
  <?php
}

function slack_status_add_settings_link( $links ) {
  $settings_link = '<a href="options-general.php?page=slack-status">Settings</a>';
  array_push( $links, $settings_link );
  return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'slack_status_add_settings_link' );
