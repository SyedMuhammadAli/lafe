<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Commons_model extends CI_Model {

    function insert_item($item){
        $this->db->insert("item", $item);
    }
    
    function get_items($limit, $offset){
        $weeks=8;
    
        $this->db->select("*");
        $this->db->from("item");
        $this->db->join("member", "member.member_id = item.member_id");
        $this->db->where("date > ", (time()-($weeks * 7 * 24 * 60 * 60)) );
        $this->db->limit($limit, $offset);
        return $this->db->get();
    }
    
    function get_autocomplete_data($term){
        $this->db->select("summary");
        $this->db->like("summary", $term);
        return $this->db->get("item");
    }
    
    function get_member_id($email){
        $query = $this->db->get_where('member', array('email' => $email));
        
        if($query->num_rows() == 0){
            $this->db->insert('member', array('email' => $email));
            $query = $this->db->get_where('member', array('email' => $email));
        }
        
        if($query->num_rows() > 1)
            die("Something really bad happened!");
        
        return $query->row()->member_id;
    }
    
}

?>
