<?php
/*
Plugin Name: Knowledge Base
Description: Knowledge Base plugin.
License: GNU General Public License v3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Version: 1.0
Author: Nomad Capitalist
Text Domain: knowledge-base
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function knowledgebase_script() {
    wp_enqueue_script('knowledgebase', plugin_dir_url(__FILE__) . 'js/knowledgebase.js', array('jquery'), '1.0', true);
    
}
add_action('wp_enqueue_scripts', 'knowledgebase_script');

function knowledgebase_style() {
    wp_enqueue_style('knowledgebase', plugin_dir_url(__FILE__) . 'css/knowledgebase.css', array(), '1.0');}
add_action('mepr_account_nav', 'knowledgebase_style');

include plugin_dir_path(__FILE__) . 'department.php';




