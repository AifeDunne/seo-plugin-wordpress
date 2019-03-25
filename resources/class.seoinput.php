<?php
require_once('class.seoptimize.php');

class SEOInput extends SEOptimize {

	private function searchTags() {
		$tag_array = array();
		for ($t = 0; $t <= $this->tagCount; $t++) {
		    $tagCounter = 0;
			$current_tag = $this->listTags[$t];
			foreach( $this->fullPage->find($current_tag) as $tag ) {
				 if ($current_tag === "meta") { $tag_temp_name = pq($tag)->attr("name"); $tag_temp_content = pq($tag)->attr("content"); $tag_array[$current_tag][] = $tag_temp_name; $tag_array[$current_tag][$tag_temp_name] = $tag_temp_content;
				 } else { $tag_temp_content = pq($tag)->text(); $tag_array[$current_tag][] = $tag_temp_content; }
				$tagCounter++; }
			$tag_array[$current_tag]['tag_count'] = $tagCounter; }
		$this->tag_array = $tag_array; $this->hasTags = 1; }
		
	private function searchContent($format_type) {
		$word_string = ""; $fullScore = $this->fullScore;
			$allText = pq("body")->text();
			$countSpace = explode(" ",$allText);
			$word_count = count($countSpace);
			if ($word_count < 500) {  $fullScore = $fullScore - 20;
				if ($format_type === "string") { $word_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Content is well below the suggested 2000 word content length.</p>"; } }
			else if ($word_count > 500 && $word_count < 1000) { $fullScore = $fullScore - 10;
				if ($format_type === "string") { $too_short = 2000 - $word_count; $word_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Content is ".$too_short." words below the suggested length of page content. You should have a minimum length of 2,000 words.</p>"; } }
			else if ($word_count > 1000 && $word_count < 2000) { $fullScore = $fullScore - 5;
				if ($format_type === "string") { $too_short = 2000 - $word_count; $word_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Content is ".$too_short." words below the suggested length of page content. You should have a minimum length of 2,000 words.</p>"; } }
			else if ($format_type === "string") { $word_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> Content is 2,000 words or above.</p>"; }
			$this->fullScore = $fullScore;
		return $word_string; }
		
	private function searchLinks($format_type) {
		$fullScore = $this->fullScore; $link_string = "";
		$track_social = 0; $full_links = array(); $social_array = array("facebook","pinterest","twitter","instagram"); $social_networks = array("facebook" => 0, "google" => 0, "pinterest" => 0, "instagram" => 0);
		foreach( $this->fullPage->find("a") as $link ) {
				$getHREF = pq($link)->attr("href"); $getALink = $getHREF;
				if(strpos($getHREF, "://") !== false) { $getHREF = explode("://",$getHREF); $getHREF = $getHREF[1]; }
				if(strpos($getHREF, "www.") !== false) { $getHREF = explode("www.",$getHREF); $getHREF = $getHREF[1]; }
				if(strpos($getHREF, ".") === false) { $newURL = $this->rootURL.$getALink; $full_links[] = $newURL; }
				else { $full_links[] = $getALink;
				$dotcom = explode(".",$getHREF);
				$domain_root = $dotcom[0];
					if ($domain_root === "plus" && $dotcom[1] === "google") { $getSocial = $social_networks['google']; $getSocial = $getSocial + 1; $social_networks['google'] = $getSocial; $track_social = $track_social + 1; }
					else if (in_array($domain_root,$social_array) === true) { $getSocial = $social_networks[$domain_root]; $getSocial = $getSocial + 1; $social_networks[$domain_root] = $getSocial; $track_social = $track_social + 1; } } }
			if ($track_social === 0) { $fullScore = $fullScore - 10;
				if ($format_type === "string") { $link_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Website has no social media links.</p>"; } }
				else if ($format_type === "string") { $link_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> Website has social media links.</p>"; }
		$this->fullScore = $fullScore; 
		return $link_string; }
		
	private function checkRobots($scope,$format_type) {
		$has_robot_file = $this->rootURL."/robots.txt";
		$robots_score = 0;
		if ($format_type === "string") { $robots_string = ""; $robots_meta_string = ""; $robots_txt_string = "";
		$robots_txt_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> robots.txt is not set to disallow all</p>"; }
		if (file_exists($has_robot_file)) { $open_robots = file_get_contents($has_robot_file); 
			if ($open_robots !== NULL) { 
				if(strpos($open_robots, "Disallow: /") !== FALSE) { $robots_score = -50; 
					if ($format_type === "string") { $robots_txt_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span>  robots.txt is set to disallow all</p>"; } }
			} }
		$meta_group = $this->tag_array['meta'];
		if ($scope === 1) {
		if (array_key_exists("robots",$meta_group)) {
		$robots_text = $meta_group['robots'];
		$robots_text = explode(",",$robots_text);
		$check_robots = array("noindex","index","follow","nofollow");
		$robots_value = array("noindex" => -50, "index" => 0, "follow" => 0, "nofollow" => -3);
		$compare_robots = array_intersect($robots_text,$check_robots);
		foreach ($compare_robots as $robots) {
			$robots_grade = intval($robots_value[$robots]); $robots_score = $robots_score + $robots_grade;
			if ($format_type === "string") {
				if ($robots_grade < 0) { $robots_meta_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> robots META tag is set to ".$robots."</p>"; }
				else if ($robots_grade === 0) { $robots_meta_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> robots META tag is set to ".$robots."</p>"; }
				} }
			} else if ($format_type === "string") { $robots_meta_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> robots META tag is not set to noindex.</p>"; } }
		if ($robots_score < -53) { $robots_score = $robots_score + 50; }
		$fullScore = $this->fullScore; $fullScore = $fullScore + $robots_score; $this->fullScore = $fullScore;
			if ($format_type === "string") { $robots_string.= $robots_txt_string.$robots_meta_string; return $robots_string; }
		}
		
	private function checkTitle() {
		$title_string = ""; $fullScore = $this->fullScore;
		$title_group = $this->tag_array['title'];
		if ($title_group['tag_count'] !== 0) { $title_content = $title_group[0];
		$this->siteTitle = $title_content;
		$title_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> Page has title tag: <i>".$title_content."</i></p>";
		$title_length = intval(strlen($title_content));
			if ($title_length > 65) { $too_long = $title_length - 65; $title_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Page title is ".$too_long." characters above the length limit. Most search engines will truncate site titles to 70 characters.</p>"; $fullScore = $fullScore - 3; }
			else if ($title_length < 45) { $too_short = 45 - $title_length; $title_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Page title is ".$too_short." characters below the suggested length. You should have a minimum length of 45 characters.</p>"; $fullScore = $fullScore - 3; }
			else { $title_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> Page title has an appropriate length of ".$title_length." characters.</p>"; }
		} else { $title_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Page does not have title tag.</p>"; $fullScore = $fullScore - 25; }
		$this->fullScore = $fullScore;
		return $title_string; }
		
	private function checkDescript() {
		$description_string = ""; $fullScore = $this->fullScore;
		$description_group = $this->tag_array['meta'];
		if (array_key_exists("description",$description_group)) { $description_content = $description_group[0];
		$description_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> Page has a meta description tag: <i>".$description_content."</i></p>";
		$description_length = intval(strlen($description_length));
			if ($description_length > 156) { $too_long = $description_length - 156; $description_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Page description is ".$too_long." characters above the length limit. Most search engines will truncate site descriptions to 156 characters.</p>"; $fullScore = $fullScore - 3; }
			else if ($description_length < 100) { $too_short = 100 - $description_length; $description_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Page description is ".$too_short." characters below the suggested length. You should have a minimum length of 45 characters.</p>"; $fullScore = $fullScore - 3; }
			else { $description_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> Page description has an appropriate length of ".$description_length." characters.</p>"; }
		} else { $description_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Page does not have a meta description tag.</p>"; $fullScore = $fullScore - 20; }
		$this->fullScore = $fullScore;
		return $description_string; }
	
	private function checkH1() {
		$h1_string = ""; $fullScore = $this->fullScore; 
		$h1_group = $this->tag_array['h1'];
		if ($h1_group['tag_count'] !== 0) { $h1_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> Page has an h1 tag: <i>".$h1_group[0]."</i></p>"; 
		if ($h1_group['tag_count'] > 1) { $h1_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Page has more than one h1 tag. (Page Contains: ".$h1_group['tag_count']." h1 tags)</p>"; $fullScore = $fullScore - 10; }
		else { $h1_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> Page has only one h1 tag.</p>"; } }
		else { $h1_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Page does not have an h1 tag.</p>"; $fullScore = $fullScore - 20; }
		$this->fullScore = $fullScore;
		return $h1_string; }
	
	private function checkHeaders($header_range) {
		$h_string = ""; $fullScore = $this->fullScore;
		$deduction_array = array("0","0","5","2","1","1");
		for ($h = 2; $h <= $header_range; $h++) {
		$h_group = $this->tag_array['h'.$h];
		if ($h_group['tag_count'] !== 0) { $h_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> Page has an h".$h." tag.</p>"; } 
		else { $h_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Page does not have an h".$h." tag.</p>"; $fullScore = $fullScore - $deduction_array[$h]; } }
		$this->fullScore = $fullScore;
		return $h_string; }
		
	private function checkBasicTags() {
	    $basic_tags = "";
		$basic_tags.= $this->checkTitle();
		$basic_tags.= "<p class='".$this->preCSS[1]."-big'>Meta Description</p>";
		$basic_tags.= $this->checkDescript();
		$basic_tags.= "<p class='".$this->preCSS[1]."-big'>Header Tags</p>";
		$basic_tags.= $this->checkH1();
		$basic_tags.= $this->checkHeaders(5);
		return $basic_tags;	}
		
	private function getPageSpeed() {
		$ch=curl_init();
		$timeout=60;
		curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/pagespeedonline/v2/runPagespeed?url='.$this->rootURL.'&key=AIzaSyBf8v3MUJ6P1halQp5JO0MmkCFje2J0rdk');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$page_speed = curl_exec($ch);
		curl_close($ch);
		$speed_array = json_decode($page_speed,TRUE);
		$this->pageSpeed = $speed_array;
		return $speed_array; }

	private function checkPageSpeed($format_type) {
		$speed_string = ""; $load_string = ""; $fullScore = $this->fullScore; $http = $this->http;
		$speed_test = $this->getPageSpeed();
		$checkResponse = intval($speed_test['responseCode']);
		if ($format_type === "string") { $failed_response = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Page takes longer than 10 seconds to load. Search engines will not be able to crawl.</p>"; }
		if ($checkResponse !== 500) {
			$all_fail = 0;
			$overall_score = intval($speed_test['ruleGroups']['SPEED']['score']);
			$overall_minus = $overall_score/10; $overall_minus = 10 - $overall_minus; $overall_minus = round($overall_minus); $overall_minus = intval($overall_minus);
				if ($format_type === "string") { $speed_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-big'>Page Speed Score:</span> ".$overall_score."</p>"; }
			$responseTime = $speed_test['formattedResults']['ruleResults']['MainResourceServerResponseTime'];
			$seconds_string = $responseTime['urlBlocks'][0]['header']['args'][0]['value'];
			$getSeconds = explode(" ",$seconds_string);
			$getSeconds = $getSeconds[0];
				if ($format_type === "string") { $speed_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-big'>Load Time:</span> ".$seconds_string."</p>";
				if ($getSeconds < 4) { $load_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> Page takes less than 4 seconds to load.</p>"; }
				else if ($getSeconds > 4 && $getSeconds < 10) { $load_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> Page takes longer than 4 seconds to load. This is above average.</p>"; } }
				else if ($getSeconds > 10) { 
					if ($format_type === "string") { $load_string = $failed_response; }
					$fullScore = $fullScore - 20; $all_fail = 1; }
			$dataArr = array('AvoidLandingPageRedirects','EnableGzipCompression','LeverageBrowserCaching','MinifyCss','MinifyHTML','MinifyJavaScript','MinimizeRenderBlockingResources','OptimizeImages');
			$messageArr1 = array('Your page has no redirects.','GZIP is enabled.','Browser Caching is enabled.','CSS is minified.','HTML is minified.','JavaScript is minified.','Render Blocking is minimized.','Images are optimized.',);
			$messageArr2 = array('Avoid landing page redirects.','GZIP is not enabled.','Browser Caching is not enabled.','CSS is not minified.','HTML is not minified.','JavaScript is not minified.','Render Blocking is not minimized.','Images are not optimized.');
			if ($format_type === "string") {
				foreach($dataArr as $key => $data) { $dataVar = $speed_test['formattedResults']['ruleResults'][$data]['ruleImpact'];
						if ($dataVar > 0) { $color1 = "-red"; $symbol = $this->preCSS[3]; $messagePrint = $messageArr2[$key]; } else { $color1 = "-green"; $symbol = $this->preCSS[2]; $messagePrint = $messageArr1[$key]; }
					$load_string.= "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1].$color1."'>".$symbol."</span> ".$messagePrint."</p>"; } }
			if ($all_fail === 0) { $fullScore = $fullScore - $overall_minus; }
		} else if ($format_type === "string") { { $load_string = $failed_response; } }
			if ($http !== "https") { $health_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-red'>".$this->preCSS[3]."</span> No HTTPS. Site is not secure.</p>"; $fullScore = $fullScore - 5; }
				else if ($format_type === "string") { $health_string = "<p class='".$this->preCSS[1]."-stat'><span class='".$this->preCSS[1]."-green'>".$this->preCSS[2]."</span> Site is secure. (HTTPS)</p>"; }
		$this->fullScore = $fullScore;
		if ($format_type === "string") { $all_speed = array($speed_string,$load_string,$health_string); return $all_speed; }
		}

	private function gradeContent($grade_type,$formatType1,$formatType2,$formatType3) {
	if ($grade_type === "site") { 
		$this->site_speed = $this->checkPageSpeed($formatType1);
		$this->site_robots = $this->checkRobots(1,$formatType2);
		$this->site_links = $this->searchLinks($formatType3); }
	else if ($grade_type === "page") {
		$this->page_tags = $this->checkBasicTags();
		$this->page_content = $this->searchContent($formatType1); }
	}
	
	private function getGrade($grade_type) {
	if ($grade_type === "site") {
		$this->preCSS = array($this->resource_path."css/seoptimize-dash.css","SEOdash","&#x2713;","&#x2718;");
		$addString = "<link rel='stylesheet' type='text/css' href='".$this->preCSS[0]."'>";
		$this->gradeContent("site","string","string","string");
		$addString.= $this->site_speed[0]."<p class='".$this->preCSS[1]."-big'>Optimization</p>".$this->site_speed[2].$this->site_speed[1]."<hr class='".$this->preCSS[1]."-hr'>";
		    $addString.= "<p class='".$this->preCSS[1]."-big'>Robots.txt</p>".$this->site_robots; 
		    $addString.= "<p class='".$this->preCSS[1]."-big'>Social Media Links</p>".$this->site_links; }
	else if ($grade_type === "page") {
		$this->preCSS = array($this->resource_path."css/seoptimize-dash.css","SEOdash","&#x2713;","&#x2718;");
		    $addString = "<link rel='stylesheet' type='text/css' href='".$this->preCSS[0]."'>";
		    $this->gradeContent("page","string","string","string");
		    $addString.= "<p class='".$this->preCSS[1]."-big'>Title Tag</p>".$this->page_tags."<p class='".$this->preCSS[1]."-big'>Page Content</p>".$this->page_content."<hr class='".$this->preCSS[1]."-hr'>"; }
	$this->finalScore = $this->fullScore;
	$this->fullScore = 100;
	return $addString; }
	
	public function gradeSEO($requestPage,$outputType) {
	$this->getPage($requestPage);
	$this->searchTags();
	if ( $outputType === "dashboard" || $outputType === "score") {
	    $final_analysis = $this->getGrade("site"); 
	    if ($outputType === "score") { $final_analysis = array($final_analysis, $this->finalScore); } }
	if ( $outputType === "page" || $outputType === "page-score" ) { $final_analysis = $this->getGrade("page");
        if ($outputType === "page-score") { $final_analysis = array($final_analysis, $this->finalScore); }
	} return $final_analysis; }
}

?>