<?php
//include necessary files
include dirname(__FILE__) . "/incl/getytcaptions.php";
//include dirname(__FILE__) . "/tempinfo.php";

//define constants
//this fixes the file_get_contents
define("FIXFILEGET", serialize(array("ssl" => array("verify_peer" => false, "verify_peer_name" => false))));

//todo automatically choose category based on title
function autoselectcategory($titleinfunc)
{

    $thiscat = 0;
    $titleinfunc = strtolower($titleinfunc);
    if (strpos($titleinfunc, 'bryan') !== false) {
        $thiscat = 157;
    } elseif (strpos($titleinfunc, 'talk') !== false) {
        $thiscat = 24;
    } else {
        $thiscat = 0;
    }

    if ($thiscat != 0) {
        $defaultcat = array(2, 6, $thiscat);
    } else { $defaultcat = array(2, 6);}

    return $defaultcat;

//echo "result".
}

// get all the yt ids from all wp posts to not repost again
function scs_ytap_getYtIdsFromPosts()
{
    $allwpytids = array();
    $postargs = array('post_format' => 'post-format-video', 'posts_per_page' => 999);

    $loop = new WP_Query($postargs);
    while ($loop->have_posts()): $loop->the_post();
        $postmeta = get_post_meta(get_the_ID());
        $tempytid = unserialize($postmeta['scs_ytap_video_id'][0]);
        array_push($allwpytids, $tempytid[0]);
    endwhile;

    return $allwpytids;
}

// adding yt thumbnail to media library
function scs_ytap_addPostFeatImgFromYt($currvidid, $curthumb, $the_post_id)
{

    $somerand = "-" . rand(1000, 9999);
    $filename = $currvidid . $somerand . ".jpg";
    $uploaddir = wp_upload_dir();
    $uploadfile = $uploaddir['path'] . '/' . $filename;

    $contents = file_get_contents($curthumb, false, stream_context_create(unserialize(FIXFILEGET)));
    $savefile = fopen($uploadfile, 'w');
    fwrite($savefile, $contents);
    fclose($savefile);

    $wp_filetype = wp_check_filetype(basename($filename), null);

    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => $filename,
        'post_content' => '',
        'post_status' => 'inherit',
    );

    $attach_id = wp_insert_attachment($attachment, $uploadfile);

    set_post_thumbnail($the_post_id, $attach_id);
}

//this gets the latest 20 videos from youtube
function scs_ytap_getYtVideoListData($scs_apikey, $scs_channelId, $scs_noofvids, $scs_publishedAfter, $scs_publishedBefore)
{
    //var_dump($scs_publishedAfter);
    //var_dump($scs_publishedBefore);
    $orderbydate = "&order=date";
    if ($scs_publishedAfter != "") {$scs_publishedAfter = "&publishedAfter=" . $scs_publishedAfter . "T00%3A00%3A00.0Z";
        $orderbydate = "";}
    if ($scs_publishedBefore != "") {$scs_publishedBefore = "&publishedBefore=" . $scs_publishedBefore . "T00%3A00%3A00.0Z";
        $orderbydate = "";}

    $yturl = "https://www.googleapis.com/youtube/v3/search?key=" . $scs_apikey . "&channelId=" . $scs_channelId . "&part=snippet,id" . $orderbydate . "&maxResults=" . $scs_noofvids . $scs_publishedAfter . $scs_publishedBefore;
    echo $yturl;
    $json = file_get_contents($yturl, false, stream_context_create(unserialize(FIXFILEGET)));
    $data = json_decode($json, true);
    return $data;
}

//now we get more info on each individual video
function scs_ytap_getYtVideoIndividualData($scs_apikey, $currvidid)
{
    $ytvidurl = "https://www.googleapis.com/youtube/v3/videos?part=id%2C+snippet&id=" . $currvidid . "&key=" . $scs_apikey;
    $jsonvid = file_get_contents($ytvidurl, false, stream_context_create(unserialize(FIXFILEGET)));
    $viddata = json_decode($jsonvid, true);
    return $viddata;

}

function scs_ytap_createPost($currvidtitle, $scs_post_status, $currvidid, $currviddes, $autogencaptions, $currvidtags, $curthumb, $scs_ytap_shortcodes, $scs_post_category, $scs_post_author, $scs_post_date, $scs_yt_post_date)
{
    //$currvidtitle [scs_ytap_video-title]
    //$currvidid [scs_ytap_video-id]
    //$currvididembed [scs_ytap_video-embed]
    //$currviddes [scs_ytap_video-description]
    //$autogencaptions [scs_ytap_video-captions]
    //$currvidtags [scs_ytap_video-tags]
    //$curthumb [scs_ytap_video-thumbnail]

    $currvididembed = "[embed]https://www.youtube.com/watch?v=" . $currvidid . "[/embed]";
    $currvidtags = implode(",",$currvidtags);

    //here we replace the shortcodes with the actal variables
    $scs_variables_array = [$currvidtitle, $currvidid, $currvididembed, $currviddes, $autogencaptions, $currvidtags, $curthumb];
    $scs_shortcodes_array = ["[scs_ytap_video-title]", "[scs_ytap_video-id]", "[scs_ytap_video-embed]", "[scs_ytap_video-description]", "[scs_ytap_video-captions]", "[scs_ytap_video-tags]", "[scs_ytap_video-thumbnail]"];
    //var_dump($scs_ytap_shortcodes);
    for ($i = 0; $i < count($scs_variables_array); $i++) {
        $scs_ytap_shortcodes = str_replace($scs_shortcodes_array[$i], $scs_variables_array[$i], $scs_ytap_shortcodes);
        
    }

    if ($scs_post_date == "When post is made") {$scs_post_date = the_date();} else { $scs_post_date = $scs_yt_post_date;}
    //echo "<pre>";
    //var_dump($scs_ytap_shortcodes);
    //echo "</pre>";

    //then we create the post with minimum information, we will update the post later with more info
    $my_post = array(
        'post_title' => $currvidtitle,
        'post_date' => $scs_post_date,
        'post_status' => $scs_post_status,
        'post_type' => 'post',
        //'post_content' => "[embed]https://www.youtube.com/watch?v=" . $currvidid . "[/embed]" . $currviddes . "<br> <h3>Auto Generated Captions</h3>" . $autogencaptions,
        'post_content' => $scs_ytap_shortcodes,
        'post_category' => array($scs_post_category),
        'tags_input' => $currvidtags,
        'meta_input' => array($currvidid),
        'post_author' => $scs_post_author,

    );
    $the_post_id = wp_insert_post($my_post);

    scs_ytap_addPostFeatImgFromYt($currvidid, $curthumb, $the_post_id);

    //todo ability to add post type of standard or video
    $tag = 'post-format-video';
    $taxonomy = 'post_format';
    wp_set_post_terms($the_post_id, $tag, $taxonomy);
    //add the video url to database
    $meta_key = 'scs_ytap_video_id';

    $meta_value = array(
        //'td_video' => 'https://www.youtube.com/watch?v='.$currvidid,
        $currvidid,
    );

    add_post_meta($the_post_id, $meta_key, $meta_value);

    return $the_post_id;
}

function post_status_array_loop($current)
{
    $post_status_array = array("draft", "publish", "pending", "private", "trash", "inherit");
    $result = "";
    $selected = "";
    foreach ($post_status_array as $post_status) {
        if ($current == $post_status) {$selected = "selected='selected'";} else { $selected = "";}
        $result .= "<option value='$post_status' $selected>$post_status</option>";
    }
    return $result;
}

function post_date_array_loop($current)
{
    $post_date_array = array("When post is made", "When YouTube video was published");
    $result = "";
    $selected = "";
    foreach ($post_date_array as $post_date) {
        if ($current == $post_date) {$selected = "selected='selected'";} else { $selected = "";}
        $result .= "<option value='" . trim($post_date) . "' $selected>$post_date</option>";
    }
    return $result;
}

function scs_ytap_outputcss()
{
    $css = "
<style>
.scs_ytap_ytbutton{
    background-color: #FF0000;
    border-radius: 2px;
    color: white;
    padding: 10px 16px;
    margin: 4px;
    white-space: nowrap;
    font-size: 1.4rem;
    font-weight: 500;
    letter-spacing: .007px;
    text-transform: uppercase;}

    input:out-of-range { 
        border: 2px solid red;
    }

    /* Start CSS of the accordion */

.accordion label {
  display:block;
  background-color: #ffffff;
  padding: 0 15px;
  height: 3em;
  line-height: .5em;
  color: #424242;
  cursor: pointer;
  border-bottom: 1px solid #000000;
  border-top: 1px solid #ffffff;
}
.accordion div {
  color: #424242;
  padding: 10px;
  font-size: 0.8em;
  line-height: 1.7em;
  opacity: 0;
  display: none;
  text-align: left;
  background-color: #fff;
  margin: 0px;
}
#tm:checked ~ .hiddentext {
  display: block;
  opacity: 1;
}
input#tm {
  display: none;
  position: relative;
}
#tn:checked ~ .hiddentext {
  display: block;
  opacity: 1;
}
input#tn {
  display: none;
  position: relative;
}
#to:checked ~ .hiddentext {
  display: block;
  opacity: 1;
}
input#to {
  display: none;
  position: relative;
}
.arrow{
  color: #666666;
}
/*end accordion code*/
form > h2 {font-size: 200%;}

#scs_ytap_accordion > div > form > table:nth-child(6){background-color: #ffd4d4;}
#scs_ytap_accordion > div > form > h2:nth-child(5){color: #FF0000;}
#scs_ytap_accordion > div > form > table:nth-child(8){background-color: #d4ebff;}
#scs_ytap_accordion > div > form > h2:nth-child(7){color: #0073AA;}
#scs_ytap_accordion > div > form > table:nth-child(10){background-color: #d4ffe9;}
#scs_ytap_accordion > div > form > h2:nth-child(9){color: #0F9D58;}
.form-table th,.form-table td{padding: 5px;}

.scsytapeg{font-size:70%;}
</style>";
    echo $css;

}
function scs_ytap_outputjs()
{
    $js = "
<script>
jQuery(document).ready(function($) {

   
})
</script>";
    echo $js;

}
