<?php

function local_forum2pdf_extends_settings_navigation(settings_navigation $nav, context $context) {
    global $PAGE;
    
    // 教師にのみPDF出力を許可
    if(!has_capability('moodle/course:manageactivities', $context)) return;
    //echo "<pre>"; var_dump($PAGE->cm->instance); echo "</pre>";
    
    if ($PAGE->context->contextlevel == CONTEXT_MODULE && $PAGE->cm->modname === 'forum') {
        $pdflink = navigation_node::create(get_string('pdflink', 'local_forum2pdf'));
        $pdflink->key = 'forum2pdf';
        $pdflink->action = new moodle_url('/local/forum2pdf/export.php', array('f' => $PAGE->cm->instance));
        
        $modulesettings = $nav->get('modulesettings');
        $modulesettings->add_node($pdflink);
    }

}