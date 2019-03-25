<?php
require_once('class.seoinput.php');
if (isset($_POST['v4']) && isset($_POST['v5'])) {
    $type_req = intval($_POST['v4']);
    $ajax_dir = $_POST['v5'];
    $root_dir = $_SERVER['DOCUMENT_ROOT'];
    $path_dir = $root_dir."/wp-load.php";
    include_once($root_dir."/wp-load.php");
    if ($type_req === 1 || $type_req === 2) {
    if (isset($_POST['v2']) && isset($_POST['v3'])) {
	$page_location = $_POST['v1']; 
	$page_type = $_POST['v2'];
	$page_id = intval($_POST['v3']);
	$load_type = $type_req;
	$ajax_dir = $_POST['v5'];
	$pull_var = get_option('seoptimize_base_variables');
    $get_var = unserialize($pull_var);
    $SEOModule = new SEOInput();
    $home_page_id = intval($get_var['home_page_id']);
    if ($page_id === $home_page_id) { $SEOanalysis = $SEOModule->gradeSEO("","page-score"); }
    else { $SEOanalysis = $SEOModule->gradeSEO($page_location,$page_type); }
    $pull_pages = get_option('seoptimize_pages_added');
    	$get_pages = unserialize($pull_pages);
    	$pull_scores = get_option('seoptimize_page_scores');
    	$get_scores = unserialize($pull_scores);
    	
    	$get_scores[$page_id] = $SEOanalysis[1];
    	$new_average = 0; $average_count = 0;
	        foreach($get_scores as $scores) {
	            $scores = intval($scores);
	            $new_average = $new_average + $scores;
	            $average_count++; }
	        $new_average = $new_average/$average_count;
	        round($new_average);
	        $base_options['pages_score'] = $new_average;
    	$pack_scores = serialize($get_scores);
    	update_option('seoptimize_page_scores',$pack_scores);
    	
    	$site_score = $get_var['site_score'];
    		$pre_page_score = $SEOanalysis[1];
	        $pre_site_score = $site_score;
	        $post_page_score = 100 - $pre_page_score;
	        $crnt_page_score = $pre_site_score - $post_page_score;
    	
    	if ($load_type === 1) {
    	    $get_pages[] = $page_id;
    	    $get_var['pages_run'] = intval($get_var['pages_run']) + 1;
    	    $SEOModule->add_cache($page_id,$SEOanalysis[1],$SEOanalysis[0]);
    	    $pack_pages = serialize($get_pages);
    	    $readd = serialize($get_var);
    	update_option('seoptimize_pages_added',$pack_pages);
    	update_option('seoptimize_base_variables',$readd);
    	} else if ($load_type === 2) { $SEOModule->update_cache($page_id,$SEOanalysis[1],$SEOanalysis[0]); }
    	
        $display_content = "<p class='SEOdash-score-number'>".$crnt_page_score."</p><p class='SEOdash-score-text'>Page SEO Score</p><hr class='SEOdash-hr'>
	        <p class='SEOdash-stat'><span style='font-weight:bold; color:#000;'>Site SEO Score:</span> ".$site_score."/100</p>
	        <p class='SEOdash-stat'><span style='font-weight:bold; color:#000;'>Pre-Weighted Page Score:</span> ".$SEOanalysis[1]."/100</p>".$SEOanalysis[0];
	echo $display_content; }
    } else if ($type_req === 3) {
        $SEOModule = new SEOInput();
        $SEOanalysis = $SEOModule->gradeSEO("","score");
	    $pull_var = get_option('seoptimize_base_variables');
    	$get_var = unserialize($pull_var);
    	$pull_pages = get_option('seoptimize_pages_added');
    	$get_pages = unserialize($pull_pages);
    	$pull_scores = get_option('seoptimize_page_scores');
    	$get_scores = unserialize($pull_scores);
    	
    	$old_score = intval($get_var['site_score']);
    	$remove_score = 100 - $old_score;
    	$overall_score = intval($get_var['overall_score']);
    	$original_score = $overall_score + $remove_score;
    	$remove_new = intval($SEOanalysis[1]);
    	$remove_new = 100 - $remove_new;
    	$new_score = $original_score - $remove_new;
    	$get_var['overall_score'] = $new_score;
    	$get_var['site_score'] = $SEOanalysis[1];
    	$page_score = $get_var['pages_score'];
    	$site_id = intval($get_var['site_id']);
    	$SEOModule->update_cache($site_id,$SEOanalysis[1],$SEOanalysis[0]);
    	
    	 $show_all_scores = "";
	        foreach($get_pages as $each_page) {
	            $loop_score = $get_scores;
	            $loop_score = intval($loop_score[$each_page]);
	            $circle_color = "";
	                if ($loop_score < 60) { $circle_color = 'R'; }
	                else if ($loop_score > 60 && $loop_score < 80) { $circle_color = 'Y'; }
	                else if ($loop_score > 80) { $circle_color = 'G'; }
	            $crnt_post = get_post($each_page, ARRAY_A);
	            $crnt_title = $crnt_post['post_title'];
	            $show_all_scores.= '<div class="SEOdash-site"><div class="SEO-score-circle SEOdash-circle'.$circle_color.'"></div><div class="SEOdash-circleT">'.$loop_score.'% - '.$crnt_title.'</div></div>';
                $loop_score = ""; }
    	
    	$display_content = "<p class='SEOdash-score-number'>".$new_score."</p><p class='SEOdash-score-text'>SEO Score</p><hr class='SEOdash-hr'>
	        <p class='SEOdash-sectext'>Site SEO</p>
	        <p class='SEOdash-stat'><span style='font-weight:bold; color:#000;'>Score:</span> ".$SEOanalysis[1]."/100</p>
	        ".$SEOanalysis[0]."<hr class='SEOdash-hr'>
	        <p class='SEOdash-sectext'>Page SEO</p>
	        <p class='SEOdash-stat'><span style='font-weight:bold; color:#000;'>Average Score:</span> ".$page_score."/100</p>
	        <p class='SEOdash-stat' style='font-weight:bold; color:#000;'>Breakdown By Page</p>".$show_all_scores;
    	
    	$readd = serialize($get_var);
    	update_option('seoptimize_base_variables',$readd);
    	echo $display_content; }
}
?>