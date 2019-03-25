<?php
require_once('class.seoinput.php');
 
class SEOLoad extends SEOInput {

	public function loadSEO($page_type,$page_id) {
	  $retrieve_options = get_option('seoptimize_base_variables');
	    if ($retrieve_options !== false) {
	     $run_options = unserialize($retrieve_options);
	     $this->load_options = $run_options;
	     $this->setup_check = intval($run_options['setup_added']);
	     $this->site_check = intval($run_options['site_run']);
	     $this->page_check = intval($run_options['pages_run']); 
	     $this->overall = intval($run_options['overall_score']);
	     $this->site_id = intval($run_options['site_id']);
	     $this->site_score = intval($run_options['site_score']);
	     $this->page_score = intval($run_options['pages_score']);
	     $this->home_id = intval($run_options['home_page_id']);
	     } else { $this->setup_check = 0; $this->site_check = 0; }
	   $SEOPagesAdded = get_option('seoptimize_pages_added');
	   $all_added_pages = unserialize($SEOPagesAdded);
	   $this->pages_added = $all_added_pages;
	   $SEOPageScores = get_option('seoptimize_page_scores');
	   $all_page_scores = unserialize($SEOPageScores);
	   $this->scores_added = $all_page_scores;
	   $load_type = "ajax";
	   
	    if ($this->setup_check === 1 && $this->site_check === 1) {
	        if ($page_type === "dashboard") { $load_type = "sql"; }
	        else if ($this->page_check > 0 && in_array($page_id, $this->pages_added) === true) { $load_type = "sql"; }
	    }
	if ($load_type === "ajax") {
	    $crnt_new_post = get_post($page_id, ARRAY_A);
	    $page_location = "/".$crnt_new_post['post_name']."/";
	    $load_content = "<div class='SEOdash-loading'><img class='SEOdash-loadpic' src='".$this->resource_path."/images/loading.gif'/><p class='SEOdash-loadtext'>Loading...</p></div>";
	return '
	<script>
	(function($) { 
		$(document).ready(function() {
			var n1 = "'.$page_location.'";
			var n2 = "'.$page_type.'";
			var n3 = "'.$page_id.'";
			var n4 = 1;
			var n5 = "'.$this->resource_path.'";
			var fDir = "'.$this->resource_path.'ajax-load.php";
			$( "#seoptimize-widget" ).find(".inside").append("'.$load_content.'");
			var request = $.ajax({ url:fDir, method:"POST", data: { v1:n1, v2:n2, v3:n3, v4:n4, v5:n5 }, dataType:"html"});
			request.done(function( msg ) { var inside = $( "#seoptimize-widget" ).find(".inside");
			inside.empty();
			inside.append( msg ); });
		});
	})( jQuery );
	</script>'; }
	else if ($load_type === "sql") {
	        $find_home_cache = $this->call_SQL('select','row',array('tablename' => 'seoptimize_cache','select-data' => '*','find-with' => 'page_id = '.$this->site_id,'data-format' => 'ARRAY_A'));
	        $cache_content = $find_home_cache['cache'];
	        $this->cache_home = $cache_content;
	        $show_all_scores = "";
	        foreach($this->pages_added as $each_page) {
	            $loop_score = $this->scores_added;
	            $loop_score = intval($loop_score[$each_page]);
	            $circle_color = "";
	                if ($loop_score < 60) { $circle_color = 'R'; }
	                else if ($loop_score > 60 && $loop_score < 80) { $circle_color = 'Y'; }
	                else if ($loop_score > 80) { $circle_color = 'G'; }
	            $crnt_post = get_post($each_page, ARRAY_A);
	            $crnt_title = $crnt_post['post_title'];
	            $show_all_scores.= '<div class="SEOdash-site"><div class="SEO-score-circle SEOdash-circle'.$circle_color.'"></div><div class="SEOdash-circleT">'.$loop_score.'% - '.$crnt_title.'</div></div>';
                $loop_score = ""; }
	    if ($page_type === "dashboard") {
	        $miniLoad = "<div class='SEOdash-smallload'><img src='".$this->resource_path."/images/loading.gif' style='float:left; width:20px; height:20px;'/><p class='SEOdash-smalltxt'>Loading...</p></div>";
	        $new_button = '<hr class="SEOdash-hr"><div class="end-section"><button class="SEOdash-rebutton">Rerun Analysis</button>'.$miniLoad.'</div><script> (function($) { $(document).ready(function() {
		    	var n1 = ""; var n2 = ""; var n3 = ""; var n4 = 3; var n5 = "'.$this->resource_path.'"; var fDir = "'.$this->resource_path.'ajax-load.php"; var now_running = 0;
		    	$(".SEOdash-rebutton").mousedown(function() { 
		    	if (now_running === 0) {
				now_running = 1; var holdHeight = $("#seoptimize-widget").height(); $("#seoptimize-widget").css({"minHeight":holdHeight});
				$(".SEOdash-rebutton").fadeOut(300, function() { $(".SEOdash-smallload").fadeIn(300); });
				$("#seoptimize-widget").find(".inside").fadeOut(300, function() { $(".info-holder").empty(); });
				var request = $.ajax({ url:fDir, method:"POST", data: { v1:n1, v2:n2, v3:n3, v4:n4, v5:n5 }, dataType:"html"});
				request.done(function( msg ) { $(".info-holder").append(msg); $("#seoptimize-widget").find(".inside").fadeIn(300); 
				$(".SEOdash-smallload").fadeOut(300, function() { $(".SEOdash-rebutton").fadeIn(300); });
				now_running = 1; }); } }); }); })( jQuery ); </script>';
	        $display_content = "<div class='info-holder'><p class='SEOdash-score-number'>".$this->overall."</p><p class='SEOdash-score-text'>SEO Score</p><hr class='SEOdash-hr'>
	        <p class='SEOdash-sectext'>Site SEO</p>
	        <p class='SEOdash-stat'><span style='font-weight:bold; color:#000;'>Score:</span> ".$this->site_score."/100</p>
	        ".$this->cache_home."<hr class='SEOdash-hr'>
	        <p class='SEOdash-sectext'>Page SEO</p>
	        <p class='SEOdash-stat'><span style='font-weight:bold; color:#000;'>Average Score:</span> ".$this->page_score."/100</p>
	        <p class='SEOdash-stat' style='font-weight:bold; color:#000;'>Breakdown By Page</p>".$show_all_scores."</div>".$new_button; }
	    else if ($page_type === "page" || $page_type === "page-score") {
	        $find_page_cache = $this->call_SQL('select','row',array('tablename' => 'seoptimize_cache','select-data' => '*','find-with' => 'page_id = '.$page_id,'data-format' => 'ARRAY_A'));
	        $page_cache_content = $find_page_cache['cache'];
	        $pre_page_score = intval($find_page_cache['score']);
	        $page_score = $pre_page_score;
	        $pre_site_score = $this->site_score;
	        $post_page_score = 100 - $pre_page_score;
	        $crnt_page_score = $pre_site_score - $post_page_score;
	        $page_id = intval($page_id);
	        if ($this->home_id !== $page_id) {
	        $crnt_post = get_post($page_id, ARRAY_A);
	        $crnt_title = "/".$crnt_post['post_name']."/"; } else { $crnt_title = ""; }
	        $new_button = '<hr class="SEOdash-hr"><div class="end-section"><button class="SEOdash-rebutton">Rerun Analysis</button></div> <script> (function($) { 
		    $(document).ready(function() {
			var n1 = "'.$crnt_title.'"; var n2 = "'.$page_type.'"; var n3 = "'.$page_id.'";	var n4 = 2;	var n5 = "'.$this->resource_path.'";
			var fDir = "'.$this->resource_path.'ajax-load.php"; var now_running = 0;
			$(".SEOdash-rebutton").mousedown(function() { 
			if (now_running === 0) { now_running = 1;
			var request = $.ajax({ url:fDir, method:"POST", data: { v1:n1, v2:n2, v3:n3, v4:n4, v5:n5 }, dataType:"html"});
			request.done(function( msg ) { now_running = 0; }); 
				} }); });
	        })( jQuery ); </script>';
	        $display_content = "<p class='SEOdash-score-number'>".$crnt_page_score."</p><p class='SEOdash-score-text'>Page SEO Score</p><hr class='SEOdash-hr'>
	        <p class='SEOdash-stat'><span style='font-weight:bold; color:#000;'>Site SEO Score:</span> ".$this->site_score."/100</p>
	        <p class='SEOdash-stat'><span style='font-weight:bold; color:#000;'>Pre-Weighted Page Score:</span> ".$page_score."/100</p>".$page_cache_content.$new_button; }
    	return $display_content;
	    }
	}
	
	public function SEOptimize_Dashboard_Widgets() {
		add_action( 'wp_dashboard_setup', function() {
		wp_add_dashboard_widget( 'seoptimize-widget', 'SEOptimize', function() { echo $this->loadSEO('dashboard',''); }); 
		});
	}
	
	public function SEOptimize_Page_Widget() {
			add_action( "add_meta_boxes_page", function($post) { add_meta_box('seoptimize-widget','SEOptimize', function($post) { echo $this->loadSEO('page-score',$post->ID); }, 'page', 'normal', 'high'); }); 
		}
		   
	public function yoasttobottom() { add_filter( 'wpseo_metabox_prio', function() { return 'low'; }); }
	}
?>