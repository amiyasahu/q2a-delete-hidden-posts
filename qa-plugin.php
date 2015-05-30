<?php

    /*
            Plugin Name: Q2A Delete Hidden Posts
            Plugin URI: https://github.com/amiyasahu/q2a-delete-hidden-posts
            Plugin Update Check URI: https://raw.github.com/amiyasahu/q2a-delete-hidden-posts/master/qa-plugin.php
            Plugin Description: To All Delete Hidden Posts with dependencies
            Plugin Version: 1.1
            Plugin Date: 2015-05-30
            Plugin Author: Amiya Sahu
            Plugin Author URI: http://amiyasahu.com
            Plugin License: GPLv2
            Plugin Minimum Question2Answer Version: 1.6
    */


    if ( !defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
        header('Location: ../../');
        exit;
    }

    /*global variable declaration */
    $ami_dhp_posts_deleted = array();

    @define('AMI_DHP_DIR', dirname(__FILE__));
    @define('AMI_DHP_DIR_NAME', basename(dirname(__FILE__)));

    require_once AMI_DHP_DIR . '/qa-dhp-utils.php';
    require_once AMI_DHP_DIR . '/qa-dhp-admin.php';

    qa_register_plugin_module('module', 'qa-dhp-admin.php', 'qa_dhp_admin', 'Delete Hidden Posts Admin');
    qa_register_plugin_layer('qa-dhp-layer.php', 'Delete Hidden Posts Layer');
    qa_register_plugin_phrases('lang/qa-dhp-lang-*.php', 'ami_dhp');


    /*
        Omit PHP closing tag to help avoid accidental output
    */
