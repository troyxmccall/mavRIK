<?php
$mavrik_ver = "0.1.1";
@include_once("functions.php"); // supress errors because the file & directory check below will handle it

$head = "<html><head><title>mavRIK $mavrik_ver</title><style type=\"text/css\">body { font-family: trebuchet ms; font-size: 14px; } .pagetitle { color: #404040; font-family: trebuchet ms, arial; font-size: 20px; letter-spacing: -1px; }</style></head><body>
  <span class=\"pagetitle\">Install mavRIK $mavrik_ver </span><br>
  <p>Detecting tasks...</p>";

$required_paths = array("admin/", "admin/index.php", "functions.php", "index.php");
$success = true;
foreach($required_paths as $path) {
  if(!file_exists($path)) {
    print("<b>ERROR:</b> File or directory <tt>$path</tt> is missing.<br />");
    $success = false;
  }
}
if(!$success)
  exit("<br />Please upload any missing files before continuing.");

if(!isset($_POST['create'])) // so that we can use header() to refresh later
  print($head);

// find out what needs to be done

clearstatcache(); // continue

if(!is__writable("./"))
  $todo_user[] = "make the current directory (<tt>" . dirname($_SERVER['SCRIPT_NAME']) . "</tt>) writeable";

if(!file_exists("config.php"))
  $todo_mavrik['config'] = "generate <tt>config.php</tt>";

if(!file_exists("posts/"))
  $todo_mavrik['posts'] = "create a <tt>posts</tt> directory";
elseif(!is__writable("posts"))
  $todo_user[] = "make the <tt>posts</tt> directory writeable";

if(!file_exists("docs/"))
  $todo_mavrik['docs'] = "create a <tt>docs</tt> directory";
elseif(!is__writable("docs"))
  $todo_user[] = "make the <tt>docs</tt> directory writeable";

if(isset($_POST['create'])) {
  if(isset($todo_mavrik['config'])) {
    if($_POST['pass1'] != $_POST['pass2'] || $_POST['pass1'] == "")
      exit("$head Passwords did not match or were not entered. <a href=\"?\">Try again</a>.");
    $config = array(
      "mavrik_ver" => $mavrik_ver,
      "adminpass" => md5($_POST['pass1']),
      "blog_name" => "mavrik",
      "blog_sub" => "beta",
      "num_posts" => 8,
      "docdates" => true,
      "docspagedates" => true,
      "abcdocs" => true,
      "abcposts" => false,
      "showadmin" => true,
      "template" => "default"
    );
    if(!write_config($config))
      exit("$head <span class=\"error\">Couldn't write to <tt>config.php</tt>!</span>");
  }
  if(isset($todo_mavrik['posts'])) {
    if(!mkdir("posts/"))
      exit("$head <span class=\"error\">Couldn't create directory <tt>posts</tt></span>");
  }
  if(isset($todo_mavrik['docs'])) {
    if(!mkdir("docs/"))
      exit("$head <span class=\"error\">Couldn't create directory <tt>docs</tt></span>");
  }
  header("Location: ?"); // refresh the page to refresh the tasks
}

if(!isset($todo_user) && !isset($todo_mavrik))
  exit("<b>All done!</b> Be sure to DELETE THIS FILE before moving on to your <a href=\"admin\">admin panel</a>.<br /><br />Keep in mind that <b>MAVRIK is still in beta</b>. This means that there may (and probably will) be some bugs and possible security holes. In most cases, security holes in MAVRIK will only affect MAVRIK's directory, but this does not rule out the possbile risk of other files on your server. It is a good practice in general, even if you're not using MAVRIK, to backup important files and information on your server regularly.");

print("Okay. ");
if(isset($todo_user))
  print("Let's see what's on the agenda for today:<br /><br />");

if(isset($todo_user)) {
  print("<b>You</b> need to:<br /><br />");
  foreach($todo_user as $task)
    print("&bull; $task<br />");
  print("<br />");
  if(isset($todo_mavrik))
    print("So that <b>MAVRIK</b> can:");
  else
    print("Then you'll be done.");
} else
  print("Looks like mavRIK is ready to do the following.");

print("<br /><br />");

if(isset($todo_mavrik)) {
  foreach($todo_mavrik as $task)
    print("&bull; $task<br />");
}
print("<br />\n");
if(isset($todo_user)) {
  print("Refresh this page when you've completed your tasks");
  if(isset($todo_mavrik))
    print(", and mavRIK will be ready to complete its own");
  print(".");
} else {
  if(isset($todo_mavrik['config'])) {
    print("Before the config file is generated, however, <b>you must set a password with which you will access the administration interface</b>.<br />It will be encrypted and stored in <tt>config.php</tt> with all other configuration variables, which is why you need to set it now.<br />You can change this later.<br />\n");
    print("<form action=\"?\" method=\"post\"><p><input type=\"password\" name=\"pass1\"> (enter)</p>\n<p><input type=\"password\" name=\"pass2\"> (confirm)</p>\n");
  }
  print("Ready to go?<br /><br /><input type=\"submit\" name=\"create\" value=\"Let's do it\"></form>");
}

function is__writable($path) { // from http://php.net/manual/en/function.is-writable.php#73596
//will work despite of Windows ACLs bug
//NOTE: use a trailing slash for folders!!!
//see http://bugs.php.net/bug.php?id=27609
//see http://bugs.php.net/bug.php?id=30931
  if($path{strlen($path) - 1} == '/') // recursively return a temporary file path
    return is__writable($path . uniqid(mt_rand()) . '.tmp');
  elseif(is_dir($path))
    return is__writable($path . '/' . uniqid(mt_rand()) . '.tmp');
  // check tmp file for read/write capabilities
  $rm = file_exists($path);
  $f = @fopen($path, 'a');
  if($f === false)
    return false;
  fclose($f);
  if(!$rm)
    unlink($path);
  return true;
}

?>
