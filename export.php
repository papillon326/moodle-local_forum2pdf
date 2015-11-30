<?php
require_once('../../config.php');
require_once('lib.php');
require_once('../../mod/forum/lib.php');
require_once('mpdf60/mpdf.php');

global $CFG, $DB;

$f  = optional_param('f',  null, PARAM_INT);  // Forum instance ID

// check cmid
if (! $forum = $DB->get_record('forum', array('id' => $f))) {
    print_error("Forum ID was incorrect or no longer exists");
}
if (! $course = $DB->get_record('course', array('id' => $forum->course))) {
  error("Forum is misconfigured - don't know what course it's from");
}

if (!$cm = get_coursemodule_from_instance('forum', $forum->id, $course->id)) {
    error("Course Module missing");
}

// check capability
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
if(!has_capability('moodle/course:manageactivities', $context)) print_error('capability error!');

$sql = "SELECT FP.id AS id, FP.discussion, FP.parent, FP.subject, FP.message,
               FD.id AS fdid, FD.course, FD.forum, FD.name, FD.firstpost,
               F.name AS title, C.fullname AS coursename
        FROM {forum_discussions} AS FD
        LEFT JOIN {forum} AS F ON FD.forum = F.id
        LEFT JOIN {course} AS C ON FD.course = C.id
        INNER JOIN {forum_posts} AS FP ON FD.id = FP.discussion
        WHERE forum = ?";

$cond = array($f);
$records = $DB->get_records_sql($sql, $cond);

//echo "<pre>"; var_dump($records);die(); echo "</pre>";

// strip tags
$posts = array();
foreach($records as $post){
  $message = $post->message;
  $message = preg_replace("/<\/*\w[^>]*?>|([^<]+)/", '\1', $message);
  $message = str_replace("\n", "<br/>", $message);
  $post->message = $message;
  $posts[$post->id] = $post;
}

$discussions = array();
foreach($posts as $post){
  if($post->parent == 0)
  {
    $discussions[$post->discussion][0] = $post;
  }else{
    $discussions[$post->discussion][] = $post;
  }
}
//echo "<pre>"; var_dump($discussions);die(); echo "</pre>";

$html  = '';
foreach($discussions as $discussion){
  $title = $discussion[0]->coursename . " - " . $discussion[0]->title;
  $html .= "<div style=\"margin-bottom:8px;\">・" . $discussion[0]->subject . "</div>";
  $html .= "<div style=\"border:1px solid #DCDCDC;padding:8px; margin-left:0px;margin-bottom:8px;page-break-inside: avoid\">" . $discussion[0]->message . "</div>";

  $html .= getsubposts($discussion, $discussion[0]->id, 1);
  $html .= "<hr/>";
}

// publish pdf
$mpdf = new mPDF(get_string('langcode', 'local_forum2pdf'), 'A4');

$header = "<div style=\"text-align:center;\">" . $title . "</div>";
$footer = "<div style=\"text-align:right;\">page {PAGENO}/{nb}</div>";

$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);
$mpdf->WriteHTML($html);
$mpdf->Output();

function getsubposts($posts, $id, $level)
{
  $html = '';
  $indent = $level * 20;
  foreach($posts as $post)
  {
    if($post->parent == $id)
    {
      //$html .= $post['message']."<br>";
      $html .= "<div style=\"border-left:8px solid #DCDCDC;margin:8px 0px 16px {$indent}px;padding-left:8px;\">" . $post->message . "</div>";
      $html .= getsubposts($posts, $post->id, $level+1);
    }
  }
  return $html;
}
?>