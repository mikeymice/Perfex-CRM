<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Recruitment_portal_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add_campaign($data) {
        // Insert the new campaign into the database
        $this->db->insert('tbl_rec_campaigns', $data);
    
        // Get the ID of the newly inserted campaign
        $campaign_id = $this->db->insert_id();
    
        // Return the campaign ID if the insertion was successful, or false if not
        return $campaign_id ? $campaign_id : false;
    }

    public function get_campaigns($id = null) {
        $this->db->select('*');
        $this->db->from('tbl_rec_campaigns');
        if ($id) {
            $this->db->where('id', $id);
            $query = $this->db->get();
            return $query->row(); // Return a single object instead of an array
        } else {
            $query = $this->db->get();
            return $query->result(); // Return an array of objects
        }
    }

    public function update_campaign($campaign_data) {
        $this->db->where('id', $campaign_data['campaignId']);
        $data = [
            'title' => $campaign_data['title'],
            'position' => $campaign_data['position'],
            'description' => $campaign_data['description'],
            'start_date' => $campaign_data['start_date'],
            'end_date' => $campaign_data['end_date'],
            'status' => $campaign_data['status'],
            'salary' => $campaign_data['salary']
        ];
    
        return $this->db->update('tbl_rec_campaigns', $data);
    }

    public function delete_campaign($campaignId) {
        $this->db->where('id', $campaignId);
        return $this->db->delete('tbl_rec_campaigns');
    }
    
    public function save_form_fields($campaignId, $formFieldsData) {

        $data = [
            'campaign_id' => $campaignId,
            'fields_data' => $formFieldsData,
        ];
    
        // Check if a record with the given campaign_id exists
        $this->db->where('campaign_id', $campaignId);
        $query = $this->db->get('tbl_rec_campaign_fields');
        
        if ($query->num_rows() > 0) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            // If a record exists, update it
            $this->db->where('campaign_id', $campaignId);
            $this->db->update('tbl_rec_campaign_fields', $data);
        } else {
            // If no record exists, insert a new one
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('tbl_rec_campaign_fields', $data);
        }
    }    
    
    
    public function get_form_fields($campaign_id) {
        // Fetch the form fields data from the database using the campaign ID
        $this->db->where('campaign_id', $campaign_id);
        $query = $this->db->get('tbl_rec_campaign_fields');
    
        // Return the fetched data as an array of objects
        return $query->result();
    }
    
    public function get_active_campaigns() {
        // Define the condition for active campaigns
        $this->db->where('status', 1);

        // Fetch the campaigns from the database
        $query = $this->db->get('tbl_rec_campaigns');

        // Return the active campaigns as an array of objects
        return $query->result();
    }

    public function save_submission($campaign_id, $form_data) {
        $data = [
            'campaign_id' => $campaign_id,
            'form_data' => json_encode($form_data),
            'resume' => $form_data['resume'],
            'created_at' => date('Y-m-d H:i:s')
        ];
    
        $this->db->insert('tbl_rec_form_submissions', $data);
    }

    public function get_submissions() {
        $this->db->select('tbl_rec_form_submissions.*, tbl_rec_form_submissions.id as sub, tbl_rec_campaigns.title, tbl_rec_campaigns.id as campaign_Id');
        $this->db->from('tbl_rec_form_submissions');
        $this->db->join('tbl_rec_campaigns', 'tbl_rec_campaigns.id = tbl_rec_form_submissions.campaign_id');
        $this->db->where('tbl_rec_campaigns.status', 1);
        $query = $this->db->get();
        return $query->result(); // Return an array of objects
    }

    public function get_submission($submission_id) {
        $this->db->select('tbl_rec_form_submissions.*, tbl_rec_campaigns.title, tbl_rec_campaigns.id as campaign_Id');
        $this->db->from('tbl_rec_form_submissions');
        $this->db->join('tbl_rec_campaigns', 'tbl_rec_campaigns.id = tbl_rec_form_submissions.campaign_id');
        $this->db->where('tbl_rec_form_submissions.id', $submission_id);
        $query = $this->db->get();
        return $query->row();
    }

    public function add_email_template($data) {
        $this->db->insert('tbl_rec_email_templates', $data);
        $template_id = $this->db->insert_id();
        return $template_id ? $template_id : false;
    }
    
    public function get_email_templates($id = null) {
        $this->db->select('*');
        $this->db->from('tbl_rec_email_templates');
        if ($id) {
            $this->db->where('id', $id);
            $query = $this->db->get();
            return $query->row();
        } else {
            $query = $this->db->get();
            return $query->result();
        }
    }
    
    public function update_email_template($template_data) {
        $this->db->where('id', $template_data['id']);
        $data = [
            'template_name' => $template_data['template_name'],
            'template_subject' => $template_data['template_subject'],
            'template_body' => $template_data['template_body'],
            'updated_at' => $template_data['updated_at']
        ];
    
        return $this->db->update('tbl_rec_email_templates', $data);
    }
    
    public function delete_email_template($templateId) {
        $this->db->where('id', $templateId);
        return $this->db->delete('tbl_rec_email_templates');
    }    

    public function associate_campaign($email_template_id, $campaign_id) {
        $this->db->insert('tbl_rec_campaign_email_templates', ['template_id' => $email_template_id, 'campaign_id' => $campaign_id]);
    }
    
    public function delete_associations($email_template_id) {
        $this->db->where('template_id', $email_template_id);
        $this->db->delete('tbl_rec_campaign_email_templates');
    }

    public function get_email_template_campaigns($templateId) {
        $this->db->select('campaign_id');
        $this->db->from('tbl_rec_campaign_email_templates');
        $this->db->where('template_id', $templateId);
        $query = $this->db->get();
    
        if($query->num_rows() > 0) {
            // Extract the campaign_id column from each row and return as an array
            return array_map(function($row) {
                return $row->campaign_id;
            }, $query->result());
        } else {
            return [];
        }
    }
    
    

    
}
