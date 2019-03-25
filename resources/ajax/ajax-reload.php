<?php
require_once('../class.seoinput.php');
if (isset($_POST['v1']) && isset($_POST['v2']) && isset($_POST['v3'])) {
	$page_location = $_POST['v1'];
	$page_type = $_POST['v2'];
	$page_id = intval($_POST['v3']);
	$SEOModule = new SEOInput();
	$SEOanalysis = $SEOModule->gradeSEO($page_location,$page_type); 
	    $pull_var = get_option('seoptimize_base_variables');
    	$get_var = unserialize($pull_var);
    	$pull_pages = get_option('seoptimize_pages_added');
    	$get_pages = unserialize($pull_pages);
    	$pull_scores = get_option('seoptimize_page_scores');
    	$get_scores = unserialize($pull_scores);
    	
    	$get_pages[] = $page_id;
    	$pack_pages = serialize($get_pages);
    	update_option('seoptimize_pages_added',$pack_pages);
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
    	
    	$get_var['pages_run'] = intval($get_var['pages_run']) + 1;
    	$site_score = $get_var['site_score'];
    		$pre_page_score = $SEOanalysis[1];
	        $pre_site_score = $site_score;
	        $post_page_score = 100 - $pre_page_score;
	        $crnt_page_score = $pre_site_score - $post_page_score;
    	$readd = serialize($get_var);
    	update_option('seoptimize_base_variables',$readd);
        $SEOModule->add_cache($page_id,$SEOanalysis[1],$SEOanalysis[0]);
        $display_content = "<p class='SEOdash-score-number'>".$crnt_page_score."</p><p class='SEOdash-score-text'>Page SEO Score</p><hr class='SEOdash-hr'>
	        <p class='SEOdash-stat'><span style='font-weight:bold; color:#000;'>Site SEO Score:</span> ".$site_score."/100</p>
	        <p class='SEOdash-stat'><span style='font-weight:bold; color:#000;'>Pre-Weighted Page Score:</span> ".$SEOanalysis[1]."/100</p>".$SEOanalysis[0];
	echo $display_content;
}
?>