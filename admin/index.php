<?php

$root = "../"; // so functions.php knows where we are
require_once("../config.php");
require_once("../functions.php");
session_start();

// logout
if(isset($_GET['logout'])) {
  unset($_SESSION['admin']);
  session_destroy();
  header("Location: ../");
}

// login
if($_SESSION['admin'] != $config['adminpass']) {
  if(isset($_POST['login'])) {
    if(md5($_POST['password']) == $config['adminpass']) {
      $_SESSION['admin'] = $config['adminpass'];
      header("Location: ./");
    } else {
      runtemplate("admin_header");
      print("<div style=\"position: absolute; left: 400px; top: 180px;\">");
      error("Invalid password.", false);
    }
  } else runtemplate("admin_header");
  
  print("<div style=\"position: absolute; left: 400px; top: 200px;\">");
  if(isset($_SESSION['admin'])) // bad session
    print("<span class=\"error\">Session invalid; please login again.</span><br />");
  print("<span class=\"sitetitle\">administrate</span> <span class=\"sitesub\">" . $config['blog_name'] . "</span><br /><br />");
  print("<form action=\"./\" method=\"post\">");
  print("<a type=\"blog_title\">enter password</a><br /><input type=\"password\" name=\"password\">");
  print("&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"submit\" name=\"login\" value=\"login\"></form>");
  
  exit(); // don't allow any further access of administration unless logged in
}

runtemplate("admin_header");

if(isset($_GET['config'])) {
  print("<span class=\"pagesub\">config</span><br /><br />\n");
  if(isset($_POST['config_submit'])) {
    $options = array(
      'blog_name'     => $_POST['blog_name'],
      'blog_sub'      => $_POST['blog_sub'],
      'num_posts'     => $_POST['num_posts'],
      'docdates'      => $_POST['docdates'],
      'docspagedates' => $_POST['docspagedates'],
      'abcdocs'       => $_POST['abcdocs'],
      'abcposts'      => $_POST['abcposts'],
      'showadmin'     => $_POST['showadmin']
    );
    if(!write_config($options))
      exit("<span class=\"error\">Couldn't write to <tt>config.php</tt>; check permissions and try again.</span>");
    print("<span class=\"success\">Configuration saved.</span>");
  } else {
  ?>
  <form action="?config" method="post">
  blog name<br /><input type="text" name="blog_name" value="<?php print($config['blog_name']); ?>" class="form_text"><br /><br />
  subname<br /><input type="text" name="blog_sub" value="<?php print($config['blog_sub']); ?>" class="form_text"><br /><br />
  posts per page<br /><input type="text" name="num_posts" value="<?php print($config['num_posts']); ?>" class="form_text" size="3"> <span class="note">0 for unlimited</span><br /><br />
  <select name="docdates">
    <option value="true" <?php if($config['docdates']) print("selected"); ?>>Yes</option>
    <option value="false" <?php if(!$config['docdates']) print("selected"); ?>>No</option>
  </select> display dates on docs<br /><br />
  <select name="docspagedates">
    <option value="true" <?php if($config['docspagedates']) print("selected"); ?>>Yes</option>
    <option value="false" <?php if(!$config['docspagedates']) print("selected"); ?>>No</option>
  </select> display dates on doc listing<br /><br />
  <select name="abcdocs">
    <option value="true" <?php if($config['abcdocs']) print("selected"); ?>>Alphabetical</option>
    <option value="false" <?php if(!$config['abcdocs']) print("selected"); ?>>Date descending</option>
  </select> doc order<br /><br />
  <select name="abcposts">
    <option value="true" <?php if($config['abcposts']) print("selected"); ?>>Alphabetical</option>
    <option value="false" <?php if(!$config['abcposts']) print("selected"); ?>>Date descending</option>
  </select> post order<br /><br />
  <select name="showadmin">
    <option value="true" <?php if($config['showadmin']) print("selected"); ?>>Yes</option>
    <option value="false" <?php if(!$config['showadmin']) print("selected"); ?>>No</option>
  </select> show admin panel link in sidebar<br /><br />
  <input type="submit" name="config_submit" value="save" class="form_submit">

<?php
  }
}

elseif(isset($_GET['plugins'])) {
  print("<span class=\"pagesub\">plugins</span><br />\n");
  print("<div style=\"position: relative; left: 8px;\">");
  plug("admplugins", "listing");
  print("</div>");
}

elseif(isset($_GET['plugin'])) {
  // blank page for plugins to use as a config/about page
  plug("admplugins", "page");
}

elseif(isset($_GET['templates'])) {
  print("<span class=\"pagesub\">templates</span><br /><br />\n");
  if(isset($_POST['template_submit'])) {
    if(!write_config(array("template" => $_POST['template'])))
      exit("<span class=\"error\">Error writing to <tt>config.php</tt>. Check permissions and try again.</span>");
    print("<span class=\"success\">Template changed.</span>");
  } else {
    print("<form action=\"?templates\" method=\"post\">\n");
    $templates = glob($root . "templates/*", GLOB_ONLYDIR);
    foreach($templates as $template) {
      $template = str_replace($root . "templates/", "", $template);
      print("<input type=\"radio\" name=\"template\" value=\"$template\"");
      if($config['template'] == $template) print(" checked");
      print("> <tt>$template</tt><br />\n");
    }
    print("<br /><input type=\"submit\" name=\"template_submit\" value=\"save\" class=\"form_submit\"></form>");
  }
}

elseif(isset($_GET['create'])) {
  print("<span class=\"pagesub\">create</span><br /><br />\n");
  if(isset($_POST['submit_post'])) {
    if(create_entry($_POST['title'], $_POST['content'], $_POST['type']))
      print("<span class=\"success\">Entry created.</span>");
  } else {
    plug("admcreate", "top");
    print("<form action=\"?create\" method=\"post\">\n");
    print("title<br /><input class=\"form_text\" name=\"title\" size=\"50\" type=\"text\"><br><br>\n");
    plug("admcreate", "title_after");
    print("content<br /><textarea class=\"form_textarea\" cols=\"80\" name=\"content\" rows=\"12\"></textarea><br><br>\n");
    plug("admcreate", "content_after");
    print("<input checked name=\"type\" type=\"radio\" value=\"posts\">post\n");
    print("<input name=\"type\" type=\"radio\" value=\"docs\">doc\n");
    plug("admcreate", "type_after");
    print("<br><br>\n");
    print("<input class=\"form_submit\" name=\"submit_post\" type=\"submit\" value=\"post\">\n");
    plug("admcreate", "button_after");
    print("</form>\n\n");
  }
}

elseif(isset($_GET['modify'])) {
  print("<span class=\"pagesub\">modify</span><br /><br />\n");
  if($_GET['modify'] != null) {
    if(isset($_POST['modify_post'])) {
      $oldname = $_POST['oldfile'];
      if(strstr($oldname, "docs/")) {
        $type = "docs";
        $oldname = str_replace("docs/", "", $oldname);
      } elseif(strstr($oldname, "posts/")) {
        $type = "posts";
        $oldname = str_replace("posts/", "", $oldname);
      }
      if(!delete_entry($oldname, $type))
        exit("<span class=\"error\">Old entry could not be removed. Check permissions and try again.</span>");
      if(create_entry($_POST['title'], $_POST['content'], $_POST['type']))
        print("<span class=\"success\">Entry modified.</span>");
    } else {
      if(substr($_GET['modify'], 0, 5) == "posts") {
        $oldtype = "posts";
        $oldtitle = str_replace("posts/", "", $_GET['modify']);
      } elseif(substr($_GET['modify'], 0, 4) == "docs") {
        $oldtype = "docs";
        $oldtitle = str_replace("docs/", "", $_GET['modify']);
      }
      else exit("<span class=\"error\">Bad entry type.</span>");
      
      $oldtitle = deparse_title($oldtitle);
      $oldcontent = file_get_contents($root . $_GET['modify'] . ".txt");
      
      plug("admmodify", "top");
      print("<form action=\"?modify=submit\" method=\"post\">\n");
      print("title<br /><input class=\"form_text\" name=\"title\" size=\"50\" type=\"text\" value=\"$oldtitle\"><br><br>\n");
      plug("admmodify", "title_after");
      print("content<br /><textarea class=\"form_textarea\" cols=\"80\" name=\"content\" rows=\"12\">$oldcontent</textarea><br><br>\n");
      plug("admmodify", "content_after");
      
      print("<input ");
      if($oldtype == "posts") print("checked ");
      print("name=\"type\" type=\"radio\" value=\"posts\">post\n");
      
      print("<input ");
      if($oldtype == "docs") print("checked ");
      print("name=\"type\" type=\"radio\" value=\"docs\">doc\n");
      
      plug("admmodify", "type_after");
      print("<br><br>\n");
      print("<input type=\"hidden\" name=\"oldfile\" value=\"" . $_GET['modify'] . "\">\n");
      print("<input class=\"form_submit\" name=\"modify_post\" type=\"submit\" value=\"modify\">\n");
      plug("admmodify", "button_after");
      print("</form>\n\n");
    }
  } else {
    $posts = glob($root . "posts/*.txt");
    $docs = glob($root . "docs/*.txt");
    
    usort($posts, "sort_by_mtime");
    usort($docs, "sort_by_mtime");
  
    $poststr = "";
    $docstr = "";
    
    foreach($posts as $post) {
      $post = str_replace("../posts/", "", $post);
      $post = str_replace(".txt", "", $post);
      $post_title = deparse_title($post);
      $poststr .= "&nbsp;&nbsp;<a href=\"?del=posts/$post\" class=\"small\">[del]</a>&nbsp;<a href=\"?modify=posts/$post\">$post_title</a><br />";
    }
    
    foreach($docs as $doc) {
      $doc = str_replace("../docs/", "", $doc);
      $doc = str_replace(".txt", "", $doc);
      $doc_title = deparse_title($doc);
      $docstr .= "&nbsp;&nbsp;<a href=\"?del=docs/$doc\" class=\"small\">[del]</a>&nbsp;<a href=\"?modify=docs/$doc\">$doc_title</a><br />";
    }
    
    $poststr = str_replace("'", "\\'", $poststr);
    $docstr = str_replace("'", "\\'", $docstr); // escape the ' character so it doesn't interefere with the javascript

?>
<div id="tabs"></div>
<script src="../js/tabs.js" type="text/javascript"></script>
<script type="text/javascript">
var tabs = new Tabs(document.getElementById('tabs'));
tabs.Add('posts', postsTabSwitch);
tabs.Add('docs', docsTabSwitch);
function postsTabSwitch(paneElement) {
if(paneElement.innerHTML == '')
  paneElement.innerHTML = '<?php print($poststr); ?>'
}
function docsTabSwitch(paneElement) {
if(paneElement.innerHTML == '')
  paneElement.innerHTML = '<?php print($docstr); ?>'
}
</script>

<?php
  }
}

elseif(isset($_GET['del'])) {
  if(strstr($_GET['del'], "docs/")) {
    $type = "doc";
    $title = str_replace("docs/", "", $_GET['del']);
  } elseif(strstr($_GET['del'], "posts/")) {
    $type = "post";
    $title = str_replace("posts/", "", $_GET['del']);
  }
  if(isset($_POST['confirm_delete'])) {
    if(delete_entry($title, $type))
      print("<span class=\"success\">Entry deleted.</span>");
    else
      print("<span class=\"error\">Couldn't delete $type <tt>" . deparse_title($title) . "</tt>. Check permissions and try again.</span>");
  } else {
    print("<span class=\"pagesub\">delete entry</span><br /><br />\n");
    print("Are you sure you want to delete the $type <b><tt>" . deparse_title($title) . "</tt></b>? This cannot be undone.<br /><br />\n");
    print("<div align=\"right\"><form action=\"?del=" . $_GET['del'] . "\" method=\"post\"><input type=\"submit\" name=\"confirm_delete\" value=\"Yes, delete this " . $type . "\"></form>\n");
    print("<a href=\"?modify\" class=\"navitem\">Go back</a></div>");
  }
}

elseif(isset($_GET['password'])) {
  print("<span class=\"pagesub\">change password</span><br /><br />\n");
  if(isset($_POST['pass_submit'])) {
    if($_POST['newpass1'] != $_POST['newpass2'] || $_POST['newpass1'] == "")
      exit("Passwords did not match or were not entered. <a href=\"?password\">Try again</a>.");
    if(md5($_POST['curpass']) != $config['adminpass'])
      exit("Incorrect current password. <a href=\"?password\">Try again</a>.");
    if(!write_config(array("adminpass" => md5($_POST['newpass1']))))
      exit("<span class=\"error\">Error writing to <tt>config.php</tt>. Check permissions and try again.</span>");
    print("<span class=\"success\">Password changed.</span>");
  } else {
?>
  <form action="?password" method="post">
  current password<br /><input type="password" name="curpass" class="form_text"><br /><br />
  new password<br /><input type="password" name="newpass1" class="form_text"><br /><br />
  confirm<br /><input type="password" name="newpass2" class="form_text"><br /><br />
  <input type="submit" name="pass_submit" value="change password" class="form_submit"></form>
<?php 
  }
}

else { // main
?>
<p>&gt;&gt; <a href="?config" class="pagesub">config</a> &bull;
change site options + variables</p>
<p>&gt;&gt; <a href="?plugins" class="pagesub">plugins</a> &bull;
enable, disable, and manage plugins</p>
<p>&gt;&gt; <a href="?templates" class="pagesub">templates</a> &bull;
swap templates</p>
<br />
<p>&gt;&gt; <a href="?create" class="pagesub">create</a> &bull;
make a new post or doc</p>
<p>&gt;&gt; <a href="?modify" class="pagesub">modify</a> &bull;
edit or delete posts and docs</p>
<br />
<p>&gt;&gt; <a href="?password" class="pagesub">change password</a> &bull;
change your administration password</p>
<p>&gt;&gt; <a href="?logout" class="pagesub">logout</a> &bull;
destroy your administration session and return to your blog</p>
<br />
<p>&lt;&lt; <a href="../" class="pagesub">back to site</a> &bull;
return to your blog</p>
<?php
}
runtemplate("admin_footer");
?>