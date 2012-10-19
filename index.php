<?php
$need_install = false;
$root = "./"; // so functions.php knows where we are
@include_once("functions.php"); // supress errors because the file & directory check below will handle it


/***** PREPERATIONS *********************************************************************/

/***** CHECK FOR INSTALLATION *****/
if(!file_exists("config.php")) $need_install = true;
else require_once("config.php");

/***** CHECK FOR REQUIRED FILES/DIRECTORIES *****/
if(!$need_install) {
// only check for these paths if we're already installed
  $required_paths = array("admin/", "admin/index.php", "templates/", "posts/", "docs/", "functions.php");
  $success = true;
  foreach($required_paths as $path) {
    if(!file_exists($path)) {
      error("File or directory <tt>$path</tt> is missing.", false);
      $success = false;
    }
  }
  if(!$success)
    exit("<br />Either remove <tt>config.php</tt> to perform a fresh install, or replace any missing files/directories above.");
}

/***** HEADER *****/
if($need_install)
  exit("<span style=\"font-family: trebuchet ms; font-size: 14px;\">It looks like you haven't installed mavrik yet!<br />Proceed to <a href=\"install.php\">installation</a> if you need to install.<br />If you don't, be sure to make sure your mavRIK-related directories exist.</span>\n");
else runtemplate("header");


/***** DISPLAY **************************************************************************/

/***** DOCS *****/
if(isset($_GET['docs'])) { // if we're at the docs page
  runtemplate("doclist_header");
  $alldocs = glob("docs/*.txt");
  if(!$config['abcdocs']) { // if we're NOT sorting alphabetically
    usort($alldocs, "sort_by_mtime");
  }
  if(count($alldocs) == 0) {
    print("<i>&lt;no docs to display&gt;</i>");
  } else {
    foreach($alldocs as $file) {
      $title = $file;
      $title = str_replace("docs/", "", $title);
      $title = str_replace(".txt", "", $title);
      $uftitle = $title;
      $id = md5($title); // dynamic hook identifier
      $title = deparse_title($title);
      runtemplate("doclist", array("id" => $id, "DOCTITLE" => $title, "DOCADDRESS" => $uftitle, "DOCDATE" => date("F jS, Y", filemtime($file))));
    }
  }
  runtemplate("doclist_footer");
}

/***** VIEWPOST/VIEWDOC *****/
elseif(isset($_GET['post']) || isset($_GET['doc'])) { // if a post/doc has been requested
  if(isset($_GET['post'])) {
    $type = "post";
    $filename = $_GET['post'];
  } else {
    $type = "doc";
    $filename = $_GET['doc'];
  }
  $filename = sanitize($filename);
  print plug($type, "top");
  if(!file_exists($type . "s/" . $filename . ".txt")) {
    print("The requested file <tt>" . $type . "s/" . $filename . ".txt</tt> does not exist.\n");
  } else {
    $file = $type . "s/" . $filename . ".txt";
    $title = $file;
    $title = str_replace($type . "s/", "", $title);
    $title = str_replace(".txt", "", $title);
    $uftitle = $title;
    $title = str_replace("_", " ", $title);
    $content = str_replace("\n", "<br>\n", file_get_contents($file));
    
    runtemplate("entry", array("ENTRYTYPE" => $type, "ENTRYTITLE" => $title, "ENTRYADDRESS" => $uftitle, "ENTRYDATE" => date("F jS, Y", filemtime($file)), "ENTRYCONTENT" => $content));
	}
}

/***** POSTS *****/
else { // if we weren't told to do anything else
  runtemplate("postlist_header");
  if(!isset($_GET['page']))
    $_GET['page'] = 1; // default to page 1
  $allposts = glob("posts/*.txt");
  $total_posts = count($allposts);
  if($total_posts % $config['num_posts'] != 0) {
  // if the total number of posts isn't divisible by the number we want to display,
  // then we want to make $total_posts / $config['num_posts'] round up one. (think it out.) this is for pagination.
    $total_posts += $config['num_posts'];
  }
  if(!$config['abcposts']) // if we're NOT sorting alphabetically
    usort($allposts, "sort_by_mtime");
  $page_firstpost = ($_GET['page'] * $config['num_posts']) - $config['num_posts'];
  $curpost = 0;
  $i = 0; // monitor how many posts we display
  if(count($allposts) == 0) {
    print("<i>&lt;no posts to display&gt;</i>");
  } else {
  foreach($allposts as $file) {
      if($i == $config['num_posts'] && $config['num_posts'] != 0)
        break;
      if(isset($_GET['page']) && ($curpost < $page_firstpost) || ($curpost > ($page_firstpost + $config['num_posts']))) {
        $curpost++;
        continue;
      }
      $title = $file;
      $title = str_replace("posts/", "", $title);
      $title = str_replace(".txt", "", $title);
      $uftitle = $title;
      $id = md5($title); // dynamic hook identifier
      $title = deparse_title($title);
      $content = str_replace("\n", "<br>\n", file_get_contents($file));
      
      runtemplate("postlist", array("id" => $id, "POSTTITLE" => $title, "POSTADDRESS" => $uftitle, "POSTDATE" => date("F jS, Y", filemtime($file)), "POSTCONTENT" => $content));
      
      $i++;
    }
  }

  if($config['num_posts'] != 0 && $total_posts > $config['num_posts']) {
    if($_GET['page'] + 1 <= $total_posts / $config['num_posts']) {
      print("<a class=\"navitem\" href=\"?page=" . ($_GET['page'] + 1) . "\"><font size=\"1\">&lt;&lt;</font>previous posts</a>\n");
    }
    if($_GET['page'] != 1) {
      if($_GET['page'] == 2) {
      // we check if we're on page 2, because the next page will be 1, aka homepage.
        $next = "./";
      } else {
        $next = "?page=" . ($_GET['page'] - 1);
      }
      print("| <a class=\"navitem\" href=\"" . $next . "\">more recent posts<font size=\"1\">&gt;&gt;</font></a>\n");
    }
  }
  runtemplate("postlist_footer");
}

/***** DISASSEMBLY *****/
if(!isset($_GET['install']) && !$need_install) // if we're not installing
  runtemplate("footer");
?>
