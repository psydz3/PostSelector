<?php
/**
 * The template for displaying a single PostSelector
 */
defined('ABSPATH') or die('No script kiddies please!');
$ajax_nonce = wp_create_nonce('postselector-ajax');
$use_union = get_post_meta($post->ID, '_postselector_use_union', true) ? true : false;
$union_url = get_post_meta($post->ID, '_postselector_union_url', true);
$time_out = get_post_meta($post->ID, '_postselector_vote_duration', true);
if (empty($union_url)) {
    $union_url = 'tryunion.com';
}
$readonly = isset($_REQUEST['readonly']);
echo '<?' ?>xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <title>PostSelector</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <script src="<?php echo plugins_url('vendor/modernizr/modernizr.js', __FILE__) ?>"></script>
    <script type="text/javascript">window.postselector = {
            nonce: '<?php echo $ajax_nonce ?>',
            ajaxurl: '<?php echo admin_url('admin-ajax.php') ?>', ids: [],
            vote_result: []
        };</script>
    <link rel="stylesheet" href="<?php echo plugins_url('postselector.css', __FILE__) ?>"/>
    <script src="<?php echo plugins_url('vendor/d3/d3.min.js', __FILE__) ?>" charset="utf-8"></script>
    <script src="<?php echo plugins_url('vendor/jquery/dist/jquery.min.js', __FILE__) ?>"></script>
</head>


<!--
< ?php echo "<p>PostSelector / " ?>
< ?php echo (esc_html(the_title()) . "</p>")?> -->

<?php 
$home_url = site_url();
?>

<div id='nav'>
<ul>
    <li class=logo><img src=<?php echo plugins_url('logo.png', __FILE__) ?>></li>
    <div style="float:right">
        <li>
            <a href="<?php echo $home_url ?>">
                <span>HOME</span>
            </a>

        </li>

        <li>
            <a class=active href="#Vote">
                <span>VOTE</span>
            </a>
        </li>

        <li>
            <a href=<?php echo plugins_url('about.html', __FILE__) ?>>
                <span>ABOUT</span>
            </a>
        </li>
    </div>

</ul>


</div>

<body>
<?php
$voted = get_post_meta($post->ID, '_postselector_vote_list', true);
$uid = get_current_user_id();
if (empty($voted)) {
     $postselector_input_category = get_post_meta($post->ID, '_postselector_input_category', true);
 //$posts = array();
     if ($postselector_input_category) {
         $postselector_status_mode = intval(get_post_meta($post->ID, '_postselector_status_mode', true));
 //echo $postselector_status_mode;
         $include_status = array('publish');
 
         if (STATUS_MODE_PUBLISH == $postselector_status_mode) {
             $include_status = array('publish', 'pending', 'draft', 'trash');
         }
         $args = array(
             'category' => $postselector_input_category,
             'post_type' => array('post', 'page', 'anywhere_map_post'),
             'post_status' => $include_status,
         );
 
         $ps = get_posts($args);
 //echo '<table>';
 //echo '<tr><th>Options</th><th>Yes</th><th>No</th></tr>';
         $connection = mysqli_connect('localhost', 'root', '123456', 'wp_database');
         foreach ($ps as $p) {
             $id = $p->ID;
             $sql = "SELECT ID FROM wp_vote WHERE ID = '$id'";
             //echo $sql;
             $result = mysqli_query($connection, $sql);
             if (mysqli_num_rows($result) <= 0) {
                 $sql = "INSERT INTO wp_vote (ID, Yes, NA, No) VALUES ('$id',0,0,0)";
                 mysqli_query($connection, $sql);
             } else {
                 //echo "<p>'$sql'</p>";
                 $sql = "UPDATE wp_vote SET Yes = 0, NA = 0, No = 0 WHERE ID = '$id'";
                 mysqli_query($connection, $sql);
             }
             //echo $sql;
         }
     }
 }
if (!in_array($uid, $voted) && current_time("timestamp") < $time_out) { ?>


    <?php
if ($use_union) {
    ?>
    <script src="<?php echo plugins_url('vendor/unionplatform/Orbiter_Release_min.js', __FILE__) ?>"></script>
    <script type="text/javascript">window.postselector.union_server = '<?php echo esc_attr($union_url) ?>';</script>
<?php
if ($readonly) {
?>
    <script type="text/javascript">window.postselector.union_readonly = true;</script>
    <?php
}
}
?>


    <svg class="postselector" viewBox="0 0 1000 600" preserveAspectRatio="xMidYMin slice">
    <line class="lane" x1="333" y1="0" x2="333" y2="1000"/>
    <line class="lane" x1="667" y1="0" x2="667" y2="1000"/>
    <text class="lane" x="167" y="300" text-anchor="middle">No</text>
    <text class="lane" x="500" y="300" text-anchor="middle">?</text>
    <text class="lane" x="833" y="300" text-anchor="middle">Yes</text>
    <!-- <g class"post" transform="translate(10 10)">
       <rect class="post" x="0" y="0" width="280" height="50" rx="5" ry="5" />
       <text class="post" x="10" y="0" dy="1em" width="260" text-overflow="ellipsis">Some text of interest</text>
    </g> -->
</svg>

<?php
// Start the loop.
while (have_posts()) : the_post();
    ?>
    <script type="text/javascript">window.postselector.ids.push('<?php echo $post->ID; ?>');</script>
    <?php
endwhile;
?>

<?php
if (!$readonly) {
    ?>
    <p class="button">
        <input type="button" value="Refresh" id="refresh"/>
        <input type="submit" value="Vote"
               id="submit-<?php echo $post->ID ?>">
    </p>
    <?php
}
?>
<script src="<?php echo plugins_url('postselector.js', __FILE__) ?>"></script>
    <?php
} else {
    //echo "<p>timeout</p>";
    $connection = mysqli_connect('localhost', 'root', '123456', 'wp_database');
    if (!$connection) {
        die("Invalid Connection" . mysqli_connect_error());
    }
    // get posts
    $postselector_input_category = get_post_meta($post->ID, '_postselector_input_category', true);
    //$posts = array();
    if ($postselector_input_category) {
        $postselector_status_mode = intval(get_post_meta($post->ID, '_postselector_status_mode', true));
        //echo $postselector_status_mode;
        $include_status = array('publish');
        if (STATUS_MODE_PUBLISH == $postselector_status_mode) {
            $include_status = array('publish', 'pending', 'draft', 'trash');
        }
        $args = array(
            'category' => $postselector_input_category,
            'post_type' => array('post', 'page', 'anywhere_map_post'),
            'post_status' => $include_status,
        );
        $ps = get_posts($args);
    //echo '<table>';
    //echo '<tr><th>Options</th><th>Yes</th><th>No</th></tr>';
        foreach ($ps as $p) {
            if (current_user_can('read_post', $p->ID)) {
                $id = $p->ID;
                $sql = "SELECT wp_vote.ID, post_title, Yes, NA, No FROM wp_vote, wp_posts WHERE wp_vote.ID = '$id' AND wp_vote.ID = wp_posts.ID";
                $result = mysqli_query($connection, $sql);
                if (mysqli_num_rows($result) <= 0) {
                    $sql = "INSERT INTO wp_vote (ID, Yes, NA, No) VALUES ('$id',0,0,0)";
                    mysqli_query($connection, $sql);
                }
                //echo "<p>'$sql'</p>";
                $sql = "SELECT wp_vote.ID, post_title, Yes, NA, No FROM wp_vote, wp_posts WHERE wp_vote.ID = '$id' AND wp_vote.ID = wp_posts.ID";
                $result = mysqli_query($connection, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
    //echo '<tr>';# code...
    //echo "<td>" . $row["post_title"] . "</td><td>" . $row["Yes"] . "</td><td>" . $row["No"] . "</td>";?>
    <script type="text/javascript">window.postselector.vote_result.push({
            name: "<?php echo $row["post_title"]?>",
            yes:<?php echo $row["Yes"]?>,
            no:<?php echo $row["No"]?>,
        });</script>
    <?php
    //echo '</tr>';
                }
            }
        }
    //echo '</table>';
    }
?>
    <svg class="postselector"></svg>

    <p style = "float:right; font-size:30px; font-family:Arial; padding:0px 30px 0px 0px;">
    <?php 
    $v = sizeof($voted);
    echo "$v voter";
    if ($v > 1)
         echo "s";
    ?> 
    </p>

    <p style = "float:left; font-size:30px; font-family:Arial; padding:0px 30px 0px 0px;">
         <?php
         $current = current_time("timestamp");
         if ($current < $time_out)
             echo human_time_diff($time_out, $current) . " left";
         else
             echo "vote ended"; ?>
    </p>

<?php
if (current_user_can('edit_post', $p->ID)){ ?>
    <p class="button">
        <input type="submit" value="Save"
               id="submit-<?php echo $post->ID ?>">
    </p>
<?php } ?>

    <script src="<?php echo plugins_url('vote_result.js', __FILE__) ?>"></script>
    <?php
} ?>
</body>
</html>