<?php
$plugin->component = 'local_forum2pdf';
$plugin->version   = 2017111600;
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = 'v 0.2';
$plugin->requires = 2014111000;  // Moodle 2.8
$plugin->dependencies = array(
    'mod_forum' => ANY_VERSION,
);
