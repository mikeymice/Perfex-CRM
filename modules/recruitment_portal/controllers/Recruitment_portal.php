<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Recruitment_portal extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('recruitment_portal_model');
    }

    public function campaigns()
    {
        $this->load->view('campaigns/manage');
    }

    public function add_campaign() {
        if ($this->input->is_ajax_request()) {
            // Load the required model
            $this->load->model('recruitment_portal_model');
    
            // Get the form data from the AJAX request
            $data = array(
                'title' => $this->input->post('title'),
                'position' => $this->input->post('position'),
                'description' => $this->input->post('description'),
                'start_date' => $this->input->post('start_date'),
                'end_date' => $this->input->post('end_date'),
                'status' => $this->input->post('status'),
                'salary' => $this->input->post('salary'),
                'created_at' => date('Y-m-d H:i:s')
            );
    
            // Add the new campaign to the database using the model
            $campaign_id = $this->recruitment_portal_model->add_campaign($data);
    
            // Check if the campaign was added successfully
            if ($campaign_id) {
                echo json_encode(array('success' => true, 'message' => 'Campaign added successfully.'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to add the campaign.'));
            }
        } else {
            show_error('No direct script access allowed.');
        }
    }

    public function get_campaigns() {
        $id = $this->input->get("id");
        $campaigns = $this->recruitment_portal_model->get_campaigns($id);
    
        $data = [
            "campaigns" => $campaigns
        ];
    
        echo json_encode($data);
    }

    public function update_campaign() {
        $campaign_data = [
            'campaignId' => $this->input->post('campaignId'),
            'title' => $this->input->post('title'),
            'position' => $this->input->post('position'),
            'description' => $this->input->post('description'),
            'start_date' => $this->input->post('start_date'),
            'end_date' => $this->input->post('end_date'),
            'status' => $this->input->post('status'),
            'salary' => $this->input->post('salary')
        ];
    
        $result = $this->recruitment_portal_model->update_campaign($campaign_data);
    
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update campaign.']);
        }
    }

    public function delete_campaign() {
        $campaignId = $this->input->post('campaignId');
        $result = $this->recruitment_portal_model->delete_campaign($campaignId);
      
        if ($result) {
          echo json_encode(['success' => true]);
        } else {
          echo json_encode(['success' => false, 'error' => 'Failed to delete campaign.']);
        }
    }

    public function edit_form($campaign_id)
    {
        if (!$campaign_id || !$this->recruitment_portal_model->get_campaigns($campaign_id)) {
            show_404();
        }

        $data['campaign'] = $this->recruitment_portal_model->get_campaigns($campaign_id);
        $data['form_fields'] = $this->recruitment_portal_model->get_form_fields($campaign_id);
        $this->load->view('forms/edit', $data);
    }

    public function save_form_fields() {
        $formFieldsData = $this->input->post('form_fields_data');
        $campaignId = $this->input->post('campaign_id');// Get the campaign ID
    
        // Save the form fields data to the database
        $this->recruitment_portal_model->save_form_fields($campaignId, $formFieldsData);
    }

    public function career(){
        $data['activeCampaigns'] = $this->recruitment_portal_model->get_active_campaigns();
        $this->load->view('career/index', $data);
    }

    public function apply($id){
        $data['campaign'] = $this->recruitment_portal_model->get_campaigns($id);
        $data['form_fields'] = $this->recruitment_portal_model->get_form_fields($id);
        $data['csrf_name'] = $this->security->get_csrf_token_name();
        $data['csrf_token'] = $this->security->get_csrf_hash();
        $this->load->view('career/apply', $data);
    }

    public function submissions(){
        $this->load->view('submissions/submissions');
    }
    
    public function handle_submission() {
        // Retrieve submitted data
        $campaign_id = $this->input->post('campaign_id');
        $form_data = $this->input->post();
    
        // Handle file upload
        $config['upload_path'] = './uploads/resumes/';
        $config['allowed_types'] = 'pdf|doc|docx';
        $config['max_size'] = 2048; // 2MB
        $config['encrypt_name'] = TRUE;

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }
        
        $this->load->library('upload', $config);

        header('Content-Type: application/json');
    
        if (!$this->upload->do_upload('resume')) {
            // Handle file upload error
            $error = $this->upload->display_errors();
            // Show an error message or redirect to an error page
            echo json_encode(['success' => false, 'message' => $error]);
        } else {
            $upload_data = $this->upload->data();
            $form_data['resume'] = $upload_data['file_name'];
    
            // Save the submitted data
            $this->recruitment_portal_model->save_submission($campaign_id, $form_data);
    
            // Return success status
            echo json_encode(['success' => true]);
        }
    }
    
    public function get_submissions() {
        $submissions = $this->recruitment_portal_model->get_submissions();

        $dataSubmissions = [];
        foreach ($submissions as $submission) {
            $fields_data = json_decode($submission->form_data, true);
            
            $name = '';

            if($fields_data['Name']){
                $name = $fields_data['Name'];
            }

            $date = DateTime::createFromFormat('Y-m-d H:i:s', $submission->created_at);
            // Format the date as needed
            $readable_date = $date->format('F j, Y, g:i a');

        
            $dataSubmissions[] = [
                'id' => $submission->campaign_id,
                'sub' => $submission->id,
                'campaign' => $submission->title,
                'name' => $name,
                'submission_date' => $readable_date,
                'resume'  => $submission->resume,
                // Add other columns as needed
            ];
        }
    
        $data = [
            "submissions" => $dataSubmissions
        ];
    
        echo json_encode($data);
    }

    public function view_resume($file_name)
    {
        $this->load->view('submissions/view_resume', ['file_name' => $file_name]);
    }

    public function view_submission($submission_id) {
        $data['submission'] = $this->recruitment_portal_model->get_submission($submission_id);
        $this->load->view('submissions/view', $data);
    }

    public function email_templates()
    {
        $data['campaigns'] = $this->recruitment_portal_model->get_campaigns();
        $this->load->view('email_templates/manage', $data);
    }
    
    public function add_email_template() {
        if ($this->input->is_ajax_request()) {
    
            $data = array(
                'template_name' => $this->input->post('name'),
                'template_subject' => $this->input->post('subject'),
                'template_body' => $this->input->post('body'),
                'created_at' => date('Y-m-d H:i:s')
            );

            $campaign_ids = $this->input->post('campaign_ids');
    
            $template_id = $this->recruitment_portal_model->add_email_template($data);
    
            if ($template_id) {
                foreach ($campaign_ids as $campaign_id) {
                    $this->recruitment_portal_model->associate_campaign($template_id, $campaign_id);
                }
                echo json_encode(array('success' => true, 'message' => 'Email template added successfully.'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Failed to add the email template.'));
            }
        } else {
            show_error('No direct script access allowed.');
        }
    }
    
    public function get_email_templates() {
        $id = $this->input->get("id");
        $templates = $this->recruitment_portal_model->get_email_templates($id);
        $data = [
            "email_templates" => $templates
        ];
    
        echo json_encode($data);
    }
    
    public function update_email_template() {
        $template_id = $this->input->post('templateId');
        $template_data = [
            'id' => $template_id,
            'template_name' => $this->input->post('name'),
            'template_subject' => $this->input->post('subject'),
            'template_body' => $this->input->post('body'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $campaign_ids = ($this->input->post('campaign_ids')) ? $this->input->post('campaign_ids') : [];

        $result = $this->recruitment_portal_model->update_email_template($template_data);
        
        if ($result) {
            $this->recruitment_portal_model->delete_associations($template_id);
            foreach ($campaign_ids as $campaign_id) {
                $this->recruitment_portal_model->associate_campaign($template_id, $campaign_id);
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update email template.']);
        }
    }
    
    public function delete_email_template() {
        $templateId = $this->input->post('templateId');
        $result = $this->recruitment_portal_model->delete_email_template($templateId);
      
        if ($result) {
          echo json_encode(['success' => true]);
        } else {
          echo json_encode(['success' => false, 'error' => 'Failed to delete email template.']);
        }
    }

    public function get_email_template_campaigns() {
        $templateId = $this->input->get('id');
        $this->load->model('recruitment_portal_model');
        $campaignIds = $this->recruitment_portal_model->get_email_template_campaigns($templateId);
      
        echo json_encode(['campaignIds' => $campaignIds]);
    }
    
    


    
}
