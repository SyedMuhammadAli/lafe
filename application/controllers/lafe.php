<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

ini_set('display_errors',1); 
error_reporting(E_ALL);
session_start();

class Lafe extends CI_Controller {
    function __construct(){
        parent::__construct();
		$this->load->model('commons_model');
    }

	function index()
	{
        $this->load->view('lafe_view');
	}
	
	function submit_item(){
	    if(hash('sha256', $this->input->post("email").session_id()) != $this->input->post("lock"))
	        die("Something weird happened. We're working on fixing this issue.");
	    
	    $member_id = $this->commons_model->get_member_id($this->input->post("email"));
	    
	    $this->commons_model->insert_item( array(
	        'summary' => strtolower($this->input->post("summary")),
	        'member_id' => $member_id,
	        'date' => strtotime( $this->input->post("date") ),
	        'report_time' => time()
	    ));
	}
	
	function submit_query(){
	    //form_validation
	    $search_query = strtolower($this->input->post("search_query"));
	    $per_page = $this->input->post("pp");
	    $page = $this->input->post("page");
	    $query_arr = explode(" ", $search_query);
	    
	    $q = $this->commons_model->get_items($per_page, ($page-1)*$per_page); //limit, offset
	    $q_total = $this->commons_model->get_items(9999, 0);
	    
	    //to get match count
	    $result_count = 0;
	    foreach( $q_total->result() as $row ){
	        $score = $this->cosineSimilarity($query_arr, explode(" ", $row->summary));
	        
	        if($score != 0)
	            $result_count++;
	    }
	    
	    //to generate result
	    $result_arr = array();
	    foreach( $q->result() as $row ){
	        $score = $this->cosineSimilarity($query_arr, explode(" ", $row->summary));
	        
	        if($score != 0){
	            $row->score = $score;
	            $result_arr[] = $row;
	        }
	    }
	    
	    function search_result_cmp($a, $b){
	        if ($a->score == $b->score) {
                return 0;
            }
            return ($a->score > $b->score) ? -1 : 1;
	    }
	    
	    usort($result_arr, 'search_result_cmp');
	    
	    echo "[ $result_count, ";
	    echo json_encode($result_arr);
	    echo "]";
	}
	
	function autocomplete_json(){
	    $term = $this->input->post("term");
	    if($term == NULL) $term = "";
	    
	    $q = $this->commons_model->get_autocomplete_data($term);
	    
	    echo "[";
	    
	    $size = count($q->result());
	    
	    $i=0;
	    foreach($q->result() as $row){
	        echo "\"" . $row->summary . "\"";
	        
	        $i++;
	        if($i != $size) echo ", ";
	    }
	    
	    echo "]";
	}
	
	private function cosineSimilarity($tokensA, $tokensB)
    {
        $a = $b = $c = 0;
        $uniqueTokensA = $uniqueTokensB = array();

        $uniqueMergedTokens = array_unique(array_merge($tokensA, $tokensB));

        foreach ($tokensA as $token) $uniqueTokensA[$token] = 0;
        foreach ($tokensB as $token) $uniqueTokensB[$token] = 0;

        foreach ($uniqueMergedTokens as $token) {
            $x = isset($uniqueTokensA[$token]) ? 1 : 0;
            $y = isset($uniqueTokensB[$token]) ? 1 : 0;
            $a += $x * $y;
            $b += $x;
            $c += $y;
        }
        return $b * $c != 0 ? $a / sqrt($b * $c) : 0;
    }
}
