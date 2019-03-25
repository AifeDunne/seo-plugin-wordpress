<?php
require_once('class.seoinput.php');
 
class SEOData extends SEOInput {

	protected function add_page($seo_content,$seo_results,$seo_type) {
	    if ($seo_type === 1) {
		  $seo_score = $seo_results;
		  $site_content = $seo_content;
		  $front = get_option('show_on_front');
            if ($front === "page") { $pageID = "page"; }
            else { $pageID = "posts"; }
          $home_id = 1;
		  $first_go = serialize(array('pages_run' => 1, 'site_run' => 1, 'setup_added' => 0, 'site_id' => $home_id, 'home_page_type' => $pageID, 'home_page_id' => 2));
		  $add_base = add_option("seoptimize_base_variables", $first_go, "", "no");
		  $add_oscore = add_option("seoptimize_overall_score", $seo_score, "", "no");
		  $add_sscore = add_option("seoptimize_site_score", $seo_score, "", "no");
		  $customize_table = "page_id mediumint(9) NOT NULL, score mediumint(4) NOT NULL, cache text NOT NULL, ";
		  $this->call_SQL("add","table",array('tablename' => 'seoptimize_cache', 'tablecustom' => $customize_table));
		  $site_score_key = array('page_id','score','cache');
		  $site_score_data = array($home_id,$seo_score,$site_content);
		  $this->call_SQL("add","data",array('tablename' => 'seoptimize_cache', 'meta' => $site_score_key, 'data_array' => $site_score_data, 'arraycount' => 3));
        }
        else if ($seo_type === 2) {
          $page_seo_content = $seo_content;
		  $page_seo_score = $seo_results;
		  $front_page = get_option('show_on_front');
		    if ($front_page === "page") { $home_id = get_option('page_on_front'); }
		    else { $home_id = 2; }
		    $get_base = get_option('seoptimize_base_variables');
		    $unpack_var = unserialize($get_base);
		    $unpack_var['setup_added'] = 1;
		    $unpack_var['home_page_id'] = $home_id;
		    $pack_var = serialize($unpack_var);
		    update_option('seoptimize_base_variables',$pack_var);
		  $page_score_key = array('page_id','score','cache');
		  $page_score_data = array($home_id,$page_seo_score,$page_seo_content);
		  $this->call_SQL("add","data",array('tablename' => 'seoptimize_cache', 'meta' => $page_score_key, 'data_array' => $page_score_data, 'arraycount' => 3));
		  $page_add = serialize(array($home_id));
		  $add_pages = add_option("seoptimize_pages_added", $page_add, "", "no");
		    $crntPageScore = intval($page_seo_score);
		    $pscores = array($homeID => $crntPageScore);
		    $pserial = serialize($pscores);
		    $add_opages = add_option("seoptimize_overall_pages", $pserial, "", "no");
		    $add_pscore = add_option("seoptimize_pages_score", $crntPageScore, "", "no");
		    $crntPageScore = 100 - $crntPageScore;
		    $crntSiteScore = get_option('seoptimize_site_score');
		        $crntSiteScore = intval($crntSiteScore);
		    $crntOverall = $crntSiteScore - $crntPageScore;
		    update_option('seoptimize_overall_score',$crntOverall);
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