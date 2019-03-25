<?php
class SEOptimize {
	public function __construct() {
	   require_once("/home/uzudev/".$_SERVER['HTTP_HOST']."/wp-load.php");
	   $path_plugin = plugin_dir_url( __DIR__ );
	   $this->base_path = $path_plugin;
	   $absPath = "/home/uzudev/".$_SERVER['HTTP_HOST']."/wp-admin/";
	   $this->abs_path = $absPath;
	   $this->resource_path = $this->base_path . "resources/";
	   $this->class_path = $this->resource_path . "load_data.php";
	   $this->hasTags = 0;
	   $this->listTags = array("meta","title","h1","h2","h3","h4","h5");
	   $this->tagCount = 6;	
	   $this->fullScore = 100; }
	   	
	public function getPage($fetch_page) {
	$pageURL = 'http'; $rootURL = ''; $this->http ="http";
		$server1 = $_SERVER["SERVER_PORT"];
		$server2 = $_SERVER["SERVER_NAME"];
		    if(isset($_SERVER['HTTPS'])) {
                if ($_SERVER['HTTPS'] == "on") { $pageURL .= "s"; $this->http = "https"; } }
			$pageURL .= "://".$server2;
			if ($server1 != "80") { $pageURL .= ":".$server1; }
			$rootURL = $pageURL; $pageURL.= $fetch_page;
			$this->url = $pageURL;
			$this->rootURL = $rootURL;
			$ch=curl_init();
			$timeout=5;
			curl_setopt($ch, CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$this->htmlObject = curl_exec($ch);
		curl_close($ch);
		require_once('modules/phpQuery.php');
		$fullPage = phpQuery::newDocument($this->htmlObject);
		$this->fullPage = $fullPage; }

	public function getURL($return_switch,$check_page) {
		$check_if_front = get_option('show_on_front');
		if ($check_if_front === "page") { $homepage = intval(get_option('page_on_front')); }
		else { $homepage = 0; }
		$page_string = "";
		if ($return_switch === "eliminate") {
			$crnt_page = intval($check_page);
			if ($homepage === 0) { $page_string = get_post_field( 'post_name', $crnt_page ); }
			else if ($page_string !== "" && $page_string !== null && $homepage !== $crnt_page) { $page_string = get_post_field( 'post_name', $crnt_page ); }
			return $page_string; }
		else if ($return_switch === "homepage") { return $homepage; }
	}
	
	private function addTable($wpdb,$table_name,$table_custom) {
        $table = $wpdb->prefix . $table_name; 
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table (id mediumint(9) NOT NULL AUTO_INCREMENT, $table_custom PRIMARY KEY(id)) $charset_collate;";
        $upgradePath = $this->abs_path.'includes/upgrade.php';
        require_once($upgradePath);
        dbDelta($sql);
	}
	
	private function addData($wpdb,$table_name,$metaArray,$dataArray,$arrayCount) {
	    $table_name = $wpdb->prefix . $table_name;
	    $arrayCount = intval($arrayCount);
	    $real_count = $arrayCount - 1;
	    $dataArr = array('id' => '');
	    for ($d = 0; $d <= $real_count; $d++) { $metaKey = $metaArray[$d]; $dataArr[$metaKey] = $dataArray[$d]; }
	    $wpdb->insert($table_name, $dataArr);
	}
	
	private function selectRow($wpdb,$table_name,$select_data,$find_with,$data_format) {
	    $table_name = $wpdb->prefix . $table_name;
	    $search_string = "SELECT $select_data FROM $table_name WHERE $find_with";
	    $find_data = $wpdb->get_row($search_string, $data_format);
	    return $find_data; }
	    
    private function updateData($wpdb,$dataArray) {
        $post_id = $dataArray[0];
        $new_score = $dataArray[1];
        $new_content = $dataArray[2];
	    $table_name = $wpdb->prefix . 'seoptimize_cache';
	    $wpdb->update( $table_name, array( 'score' => $new_score, 'cache' => $new_content), array('page_id' => $post_id));
	}
	    
    private function deleteData($wpdb,$postID) {
	    $base_options = get_option('seoptimize_base_variables');
	    $base_options = unserialize($base_options);
	    $pages_added = get_option('seoptimize_pages_added');
	    $pages_added = unserialize($pages_added);
	    if (in_array($postID, $pages_added)) {
	        $scores_added = get_option('seoptimize_page_scores');
	        $scores_added = unserialize($scores_added);
	        unset($scores_added[$postID]);
	        $rmv_list = array_search($postID, $pages_added);
	        unset($pages_added[$rmv_list]);
	        $new_average = 0; $average_count = 0;
	        foreach($scores_added as $scores) { 
	            $scores = intval($scores);
	            $new_average = $new_average + $scores;
	            $average_count++; }
	        $new_average = $new_average/$average_count;
	        round($new_average);
	        $base_options['pages_score'] = $new_average;
	        $page_removed = serialize($pages_added);
	        update_option('seoptimize_pages_added',$pages_added);
	        $scores_added = serialize($scores_added);
	        update_option('seoptimize_page_scores',$scores_added);
	        $base_minus = intval($base_options['pages_run']);
	        $base_minus = $base_minus - 1;
	        $base_options = serialize($base_options);
	        update_option('seoptimize_base_variables',$base_options);
	        $table_name = $wpdb->prefix . 'seoptimize_cache';
	        $wpdb->delete( $table_name, array( 'page_id' => $postID) );
	    }
	}
	
	protected function call_SQL($method_type,$target_group,$dataPackage) {
	    global $wpdb;
	    if ($method_type === "add") {
	        if ($target_group === "table") { $this->addTable($wpdb,$dataPackage["tablename"],$dataPackage["tablecustom"]); }
	        else if ($target_group === "data") { $this->addData($wpdb,$dataPackage["tablename"],$dataPackage["meta"],$dataPackage["data-array"], $dataPackage["arraycount"]); }
	    } else if ($method_type === "select") { 
	        if ($target_group === "row") { $return_data = $this->selectRow($wpdb,$dataPackage["tablename"],$dataPackage["select-data"],$dataPackage["find-with"],$dataPackage["data-format"]); }
	        return $return_data;
	    } else if ($method_type === "update") {
	        if ($target_group === "data") { $return_data = $this->updateData($wpdb,$dataPackage); }
	    } else if ($method_type === "delete") {
	        if ($target_group === "data") { $return_data = $this->deleteData($wpdb,$dataPackage); }
	    }
	}
	
	public function add_cache($d1,$d2,$d3) {
	    $this->call_SQL("add","data",array('tablename' => 'seoptimize_cache', 'meta' => array('page_id','score','cache'), 'data-array' => array($d1,$d2,$d3), 'arraycount' => 3)); }
	
	public function update_cache($u1,$u2,$u3) { $this->call_SQL("update","data",array($u1,$u2,$u3)); }
	    
	public function remove_page($p1) { $this->call_SQL("delete","data",$p1); }
	
}
?>