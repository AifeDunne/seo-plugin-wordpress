<?php
require_once('class.seoinput.php');
 
class SEOInstall extends SEOInput {

	private function install_plugin($seo_content,$seo_results,$seo_type) {
	    if ($seo_type === 1) {
		  $seo_score = $seo_results;
		  $site_content = $seo_content;
		  $front = get_option('show_on_front');
            if ($front === "page") { $pageID = "page"; }
            else { $pageID = "posts"; }
		  $first_go = serialize(array('setup_added' => 0, 'site_run' => 1, 'home_page_type' => $pageID, 'overall_score' => $seo_score, 
		  'site_id' => 0, 'home_page_id' => 0, 'site_score' => $seo_score, 'pages_run' => 1, 'pages_score' => 0));
		  $add_base = add_option("seoptimize_base_variables", $first_go, "", "no");
		  $customize_table = "page_id mediumint(9) NOT NULL, score mediumint(4) NOT NULL, cache text NOT NULL, ";
		  $this->call_SQL("add","table",array('tablename' => 'seoptimize_cache', 'tablecustom' => $customize_table));
		  $this->add_cache(0,$seo_score,$site_content);
        }
        else if ($seo_type === 2) {
          $page_seo_content = $seo_content;
		  $page_seo_score = intval($seo_results);
		  $front_page = get_option('show_on_front');
		    if ($front_page === "page") { $home_id = get_option('page_on_front'); }
		    else { $home_id = 1; }
		    $add_page_scores = serialize(array($home_id => $page_seo_score));
		    $page_add = serialize(array($home_id));
		    $get_base = get_option('seoptimize_base_variables');
		    $unpack_var = unserialize($get_base);
    		    $unpack_var['setup_added'] = 1;
    		    $unpack_var['home_page_id'] = $home_id;
    		    $unpack_var['pages_score'] = $page_seo_score;
    		    $crntPageScore = 100 - $page_seo_score;
    		    $crntSiteScore = intval($unpack_var['site_score']);
    		    $crntOverall = $crntSiteScore - $crntOverall;
    		    $unpack_var['overall_score'] = $page_seo_score;
		    $pack_var = serialize($unpack_var);
		    update_option('seoptimize_base_variables',$pack_var);
		  $this->add_cache($home_id,$page_seo_score,$page_seo_content);
		  $add_pages = add_option("seoptimize_pages_added", $page_add, "", "no");
		  $add_scores = add_option("seoptimize_page_scores", $add_page_scores, "", "no"); 
        }
	}
	
	private function install_data($install_type) {
	    if ($install_type === 1) {
		    $result1 = $this->gradeSEO("","score");
		    $this->install_plugin($result1[0],$result1[1],$install_type);
	    }
	    else if ($install_type === 2) {
		    $result2 = $this->gradeSEO("","page-score");
    		$this->install_plugin($result2[0],$result2[1],$install_type);
	    }
	}
	
	public function run_install() {  $this->install_data(1);  $this->install_data(2); }
}