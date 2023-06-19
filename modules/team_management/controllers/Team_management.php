<?php defined('BASEPATH') or exit('No direct script access allowed');

use Orhanerday\OpenAi\OpenAi;
class Team_management extends AdminController {

    

    public function __construct() {
        parent::__construct();
        $this->load->model('team_management_model');
        $this->load->model('staff_model');
        $this->load->library('webhook_library', null, 'webhook_lib');

        //hooks()->add_action('task_assignee_added', 'notify_task_allocation');
    }

    public function checking(){
        echo 'BRO';
    }
    
    public function index() {
        $this->individual_stats();
    }

    public function individual_stats()
    {

        $data['staff_members'] = $this->team_management_model->get_all_staff();
        //$data['timers'] = $this->team_management_model->get_all_timers();

        $this->load->view('individual_stats', $data);
    }
    

    public function team_stats()
    {
        $data['staff_members'] = $this->team_management_model->get_all_staff();
        $data['timers'] = $this->team_management_model->get_all_timers();

        $this->load->view('team_stats', $data);
    }

    public function applications()
    {
        $staff_id = $this->session->userdata('staff_user_id');
        $data['paid_no'] = $this->team_management_model->get_leaves_count($staff_id, 'Paid Leave', 'Approved')[0]['total_leaves'];
        $data['unpaid_no'] = $this->team_management_model->get_leaves_count($staff_id, 'Unpaid Leave', 'Approved')[0]['total_leaves'];
        $data['gaz_no'] = $this->team_management_model->get_leaves_count($staff_id, 'Gazetted Leave', 'Approved')[0]['total_leaves'];
        $this->load->view('applications', $data);
    }

    public function all_applications()
    {
        if (!is_admin()) {
            //access_denied('You do not have permission to access this page.');
        }
        $data['staff_members'] = $this->team_management_model->get_all_staff();
        $this->load->view('all_applications', $data);
    }

    public function test_query()
    {
        echo $now = date('Y-m-d H:i:s');
        echo '<br>';

        // Clock out the staff member by updating the latest open session
        //$this->db->set('clock_out', $now);
        //$this->db->where('staff_id', 19);
        //$this->db->where('clock_out IS NULL', null, false);
        //$this->db->update(db_prefix().'_staff_time_entries');

        //return $this->db->affected_rows() > 0;

        //echo $this->team_management_model->test_query("ALTER TABLE tbl_applications ADD COLUMN shift ENUM('1', '2', 'all') DEFAULT 'all' AFTER end_date;");
        //echo $this->team_management_model->test_query("ALTER TABLE tbl_staff_leaves ADD COLUMN shift ENUM('1', '2', 'all') DEFAULT 'all' AFTER end_date;");
    }

    public function staff_shifts() {
        //if (!is_admin()) {
        //    access_denied('You do not have permission to access this page.');
        //}
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('staff_shifts', $data);
    }

    public function control_room($staff_id) {

        if (!is_admin()) {
            //access_denied('You do not have permission to access this page.');
        }

        $data['staff_id'] = $staff_id;
        $data['staff_name'] = $this->team_management_model->id_to_name($staff_id, 'tblstaff', 'staffid', 'firstname');
        //$data['title'] = _l('control_room');
        $this->load->view('control_room', $data);
    }

    public function activity_log($staffId, $month)
    {

        $data['activities'] = $this->team_management_model->get_user_activities($staffId, $month);
        $data['staff'] = $this->team_management_model->id_to_name($staffId, 'tblstaff', 'staffid', 'firstname');

        $this->load->view('activity_log', $data);
    }

    public function staff_stats($staffId, $month)
    {
        $data['staff_id'] = $staffId;

        $data['monthly_stats'] = $this->team_management_model->get_monthly_stats($staffId, $month)['data'];
        $data['monthly_total_clocked_time'] = $this->team_management_model->get_monthly_stats($staffId, $month)['monthly_total_clocked_time'];
        $data['monthly_shift_duration'] = $this->team_management_model->get_monthly_stats($staffId, $month)['monthly_shift_duration'];
        $data['punctuality_rate'] = $this->team_management_model->get_monthly_stats($staffId, $month)['punctuality_rate'];
        $data['month_this'] = $month;
        $data['staff_id_this'] = $staffId;
        $data['staff_name_this'] =  $this->team_management_model->id_to_name($staffId, 'tblstaff', 'staffid', 'firstname');;

        $data['pen_paid_no'] = $this->team_management_model->get_leaves_count($staffId, 'Paid Leave', 'Pending')[0]['total_leaves'];
        $data['pen_unpaid_no'] = $this->team_management_model->get_leaves_count($staffId, 'Unpaid Leave', 'Pending')[0]['total_leaves'];
        $data['pen_gaz_no'] = $this->team_management_model->get_leaves_count($staffId, 'Gazetted Leave', 'Pending')[0]['total_leaves'];

        $data['app_paid_no'] = $this->team_management_model->get_leaves_count($staffId, 'Paid Leave', 'Approved')[0]['total_leaves'];
        $data['app_unpaid_no'] = $this->team_management_model->get_leaves_count($staffId, 'Unpaid Leave', 'Approved')[0]['total_leaves'];
        $data['app_gaz_no'] = $this->team_management_model->get_leaves_count($staffId, 'Gazetted Leave', 'Approved')[0]['total_leaves'];

        $data['dis_paid_no'] = $this->team_management_model->get_leaves_count($staffId, 'Paid Leave', 'Disapproved')[0]['total_leaves'];
        $data['dis_unpaid_no'] = $this->team_management_model->get_leaves_count($staffId, 'Unpaid Leave', 'Disapproved')[0]['total_leaves'];
        $data['dis_gaz_no'] = $this->team_management_model->get_leaves_count($staffId, 'Gazetted Leave', 'Disapproved')[0]['total_leaves'];

        $data['all_applications'] = $this->team_management_model->get_applications_by_staff_id($staffId);

        $this->load->view('staff_stats', $data);
    }

    public function daily_reports($month, $day)
    {
        
        $report_data = $this->team_management_model->get_daily_report_data($month, $day);

        // Pass the data to your view
        $data['report_data'] = $report_data;

        $day_summary = $this->team_management_model->get_day_summary(date('Y') . '-' . $month . '-' . $day);
        $data['day_summary'] = $day_summary ? $day_summary->summary : '';

        if (!is_admin() && !$day_summary) {
            //access_denied('No Report Found!');
        }
        
        $data['date'] = date('Y') . '-' . $month . '-' . $day;

        $summaries = $this->team_management_model->get_staff_summaries(date('Y') . '-' . $month . '-' . $day);

        // Pass the summaries to the view
        $data['summaries'] = $summaries;

        $this->load->view('daily_reports', $data);
    }

    public function project_management() {
        if (!is_admin()) {
            //access_denied('Project Management');
        }
        $data['projects'] = $this->team_management_model->get_projects();
        foreach ($data['projects'] as &$project) {
            $project['dummy_tasks'] = $this->team_management_model->get_dummy_tasks_by_project($project['id']);
        }

        $data['staff_members'] = $this->team_management_model->get_all_staff();

        $this->load->model('tasks_model');
        
        $this->load->view('project_management', $data);
    }

    public function staff_google_chat() {
        $data['staff'] = $this->team_management_model->get_all_staff_google_chat();
        $this->load->view('staff_google_chat_view', $data);
    }

    
    //Methods

    public function update_google_chat_id(){
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $postData = $this->input->post();
        
            foreach ($postData as $staffId => $googleChatUserId) {
                // Update each ID
                $this->team_management_model->update_or_insert_google_chat_id($staffId, $googleChatUserId);
            }

            echo json_encode(['status' => 'success']);
        }        
    }

    public function save_day_summary() {
        // Check if the user is an admin
        if (!is_admin()) {
            show_error('Access Denied. Only administrators can perform this action.');
        }
    
        // Check if the form was submitted
        if ($this->input->post()) {

            $staff_members = $this->staff_model->get();

            // Get the submitted data
            $date = $this->input->post('date');
            $summary = $this->input->post('summary');
    
            // Save the day summary using the model
            $result = $this->team_management_model->save_day_summary($date, $summary);
            
            $template = "
                <h2>Daily summary saved for : ".$date."</h2>
                <div style='width:100%;border-bottom: 2px dashed;'></div><br>
                <div>".$summary."</div><br>
                ";

            $this->email->initialize();
            $this->email->set_newline(PHP_EOL);
            $this->email->from(get_option('smtp_email'), get_option('companyname'));

            foreach ($staff_members as $staff) {
                $this->email->to($staff['email']);
            }
            
            $this->email->subject("Daily summary saved!!");
            $this->email->message(get_option('email_header') . $template . get_option('email_footer'));

            $email_sent = $this->email->send();
    
            if ($result && $email_sent) {
                $resultJson = json_encode(['success' => true]);
            } else {
                $resultJson = json_encode(['success' => false]);
            }
        }
    
        // Redirect back to the daily_reports page
        return $resultJson;
    }
    

    public function save_shift_timings() {

        //if (!is_admin()) {
        //    access_denied('Your custom permission message');
        //}

        $staff_id = $this->input->post('staff_id');
        $month = $this->input->post('month');

        $shifts = [];

        for ($i=1; $i <= 31; $i++) { 
            $shifts[$i][1]['start'] = $this->input->post('start_shift1_day_'.$i);
            $shifts[$i][1]['end'] = $this->input->post('end_shift1_day_'.$i);

            $shifts[$i][2]['start'] = $this->input->post('start_shift2_day_'.$i);
            $shifts[$i][2]['end'] = $this->input->post('end_shift2_day_'.$i);
        }


        $result = $this->team_management_model->save_shift_timings($staff_id, $month, $shifts);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function get_shift_timings($staff_id, $month) {

        $shifts = $this->team_management_model->get_shift_timings($staff_id, $month);

        echo json_encode($shifts);
    }
    
    public function get_shift_status() {
        $staff_id = $this->session->userdata('staff_user_id');

        $shift_info = $this->team_management_model->get_shifts_info($staff_id);

        if ($shift_info) {

            $current_timezone = new DateTimeZone(get_option('default_timezone'));
            $current_time_out = new DateTime('now', $current_timezone);
            $current_time_str = $current_time_out->format('Y-m-d H:i:s');
            $current_time = strtotime($current_time_str);

            $current_month = $current_time_out->format('m');
            $current_day = $current_time_out->format('d');
            $current_year = $current_time_out->format('Y');

            $shift_start_time = new DateTime($current_year . '-' .$current_month . '-' . $current_day . ' ' . $shift_info->shift_start_time);
            $shift_end_time = new DateTime($current_year . '-' .$current_month . '-' . $current_day . ' ' . $shift_info->shift_end_time);

            //$shift_start_time = strtotime($shift_info->shift_start_time);
            //$shift_end_time = strtotime($shift_info->shift_end_time);

            $shift_start_time = $shift_start_time->getTimestamp();
            $shift_end_time = $shift_end_time->getTimestamp();


            $shift_info->shift_start_time = $shift_start_time;
            $shift_info->shift_end_time = $shift_end_time;

            $shift_info->current_time = $current_time;

            if ($current_time >= $shift_start_time && $current_time <= $shift_end_time) {
                $shift_info->status = 0;
                $shift_info->statusText = 'Shift Time Ongoing:';
                $shift_info->time_left = $this->convertSecondsToRoundedTime($shift_end_time - $current_time);
            } else if ($current_time < $shift_start_time) {
                $shift_info->status = 1;
                $shift_info->statusText = 'Upcoming shift in:';
                $shift_info->time_left = $this->convertSecondsToRoundedTime($shift_start_time - $current_time);
            } else {
                $shift_info->status = 2;
                $shift_info->statusText = 'none';
                $shift_info->time_left = 0;
            }
        }

        echo json_encode($shift_info);
    }


    public function tasks_table_due() {

        $tasks = $this->team_management_model->get_tasks_records(1);
        echo json_encode(['data' => $tasks]);
    }

    public function tasks_table_due_today() {

        $tasks = $this->team_management_model->get_tasks_records(2);
        echo json_encode(['data' => $tasks]);
    }

    public function tasks_table_all() {

        $tasks = $this->team_management_model->get_tasks_records(3);
        echo json_encode(['data' => $tasks]);
    }

    public function send_shift_reminders() {
        $this->load->library('email');

        $staff_members  = $this->team_management_model->get_staff_with_shifts();
    
        foreach ($staff_members as $staff_member) {
            $subject = 'Your shift timings for today!';

            $message = 'Your shift timings for today are as follows: <br>';
    
            foreach ($staff_member->shifts as $shift) {
                $message .= ' <br> <br>Shift ' . $shift->shift_number . ': ' . $shift->shift_start_time . ' CST - ' . $shift->shift_end_time . ' CST';
            }

            // Send the email using your preferred email library (e.g., PHPMailer, CI Email library, etc.)
            $this->email->initialize();
            $this->email->set_newline(PHP_EOL);
            $this->email->from(get_option('smtp_email'), get_option('companyname'));
            $this->email->to($staff_member->email);
            $this->email->subject($subject);
            $this->email->message($message);
            if(!empty($staff_member->shifts)){
                $this->email->send();
            }
                

            if (!$this->email->send()) {
                log_activity ('Email failed to send. Error: ' . $this->email->print_debugger(), null);
            }

        }

    }

    public function mail_shift_timings() {

        $this->load->library('email');

        $staff_id = $this->input->post('staff_id');
        $staff_email = $this->team_management_model->id_to_name($staff_id, 'tblstaff', 'staffid', 'email');
        $staff_name = $this->team_management_model->id_to_name($staff_id, 'tblstaff', 'staffid', 'firstname');
        $month = $this->input->post('month');
        $month_name = date('F', mktime(0, 0, 0, $month, 1));

        $shift_data = $this->team_management_model->get_staff_shift_details($staff_id, $month);
        

        $email_subject = 'Your shift timings for ' . $month_name;

        $template = file_get_contents(base_url('modules/team_management/assets/template/pdf_temp.html'));

        $template = str_replace('{month_name}', $month_name , $template);
        $template = str_replace('{staff_name}', $staff_name, $template);
 
        $html = '';

        // Loop through the days of the month and generate table rows
        for ($day = 1; $day <= date('t', mktime(0, 0, 0, $month, 1)); $day++) {

            $year = date('Y');
            $thisMonth = date('F', mktime(0, 0, 0, $month, 1));
            $timestamp = mktime(0, 0, 0, $month, $day, $year);

            $date_formatted = date('l, F jS', $timestamp);

            $html .= '

            <tr class="bg-gray-100/80 my-2">
                <th class="border px-4 py-2" colspan="2">'.$date_formatted.'</th>
            </tr>
            <tr class="bg-gray-100/30 my-2">
                <td class="border px-4 py-2 text-center">' . $shift_data[$day][1]['start_time'] . ' - ' . $shift_data[$day][1]['end_time'] . '</td>
                <td class="border px-4 py-2 text-center">' . $shift_data[$day][2]['start_time'] . ' - ' . $shift_data[$day][2]['end_time'] . '</td>
            </tr>
            ';
        }

        $template = str_replace('{shift_rows}', $html, $template);

        $this->email->initialize();
        $this->email->set_newline(PHP_EOL);
        $this->email->from(get_option('smtp_email'), get_option('companyname'));
        $this->email->to($staff_email);
        $this->email->subject($email_subject);
        $this->email->message($template);

        if ($this->email->send()) {
            echo json_encode(['success' => true, 'mail' => $staff_email]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function mail_weekly($staff_id) {

        $this->load->library('email');

        $staff_email = $this->team_management_model->id_to_name($staff_id, 'tblstaff', 'staffid', 'email');
        $staff_name = $this->team_management_model->id_to_name($staff_id, 'tblstaff', 'staffid', 'firstname');
        $month = date('n');
        $month_name = date('F', mktime(0, 0, 0, $month, 1));
    
        $shift_data = $this->team_management_model->get_staff_shift_details($staff_id, $month);
    
        $email_subject = 'Your shift timings for this week';
    
        $template = file_get_contents(base_url('modules/team_management/assets/template/pdf_temp.html'));
    
        $template = str_replace('{month_name}', $month_name , $template);
        $template = str_replace('{staff_name}', $staff_name, $template);
    
        $html = '';
    
        // Find the first and last day of the current week
        $today = date('Y-m-d');
        $first_day_of_week = date('Y-m-d', strtotime('this week', strtotime($today)));
        $last_day_of_week = date('Y-m-d', strtotime('this week +6 days', strtotime($today)));
    
        // Loop through the days of the current week and generate table rows
        for ($date = $first_day_of_week; $date <= $last_day_of_week; $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
            
            $day = date('j', strtotime($date));
            $date_formatted = date('l, F jS', strtotime($date));

            if (date('m', strtotime($date)) == $month) {
            
                if (isset($shift_data[$day][1]['start_time']) && isset($shift_data[$day][1]['end_time'])) {
                $shift1_start_time = $shift_data[$day][1]['start_time'];
                $shift1_end_time = $shift_data[$day][1]['end_time'];
                } else {
                $shift1_start_time = 'N/A';
                $shift1_end_time = 'N/A';
                }
            
                if (isset($shift_data[$day][2]['start_time']) && isset($shift_data[$day][2]['end_time'])) {
                $shift2_start_time = $shift_data[$day][2]['start_time'];
                $shift2_end_time = $shift_data[$day][2]['end_time'];
                } else {
                $shift2_start_time = 'N/A';
                $shift2_end_time = 'N/A';
                }
            
                $html .= '
                <tr class="bg-gray-100/80 my-2">
                    <th class="border px-4 py-2" colspan="2">' . $date_formatted . '</th>
                </tr>
                <tr class="bg-gray-100/30 my-2">
                    <td class="border px-4 py-2 text-center">' . $shift1_start_time . ' - ' . $shift1_end_time . '</td>
                    <td class="border px-4 py-2 text-center">' . $shift2_start_time . ' - ' . $shift2_end_time . '</td>
                </tr>
                ';

            }

        }
    
        $template = str_replace('{shift_rows}', $html, $template);
    
        $this->email->initialize();
        $this->email->set_newline(PHP_EOL);
        $this->email->from(get_option('smtp_email'), get_option('companyname'));
        $this->email->to($staff_email);
        $this->email->subject($email_subject);
        $this->email->message($template);
    
        if ($this->email->send()) {
            echo json_encode(['success' => true, 'mail' => $staff_email]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    
    public function mail_weekly_all()
    {
        $all_staff = $this->team_management_model->get_all_staff();

        // Loop through all staff members
        foreach ($all_staff as $staff_member) {
            // Get the staff member's ID
            $staff_id = $staff_member->staffid;
    
            // Call the mail_weekly function for the current staff member
            if($staff_id == 1 || $staff_id == 20){
                $this->mail_weekly($staff_id);
            }
            
        }
    }

    public function export_shift_details_to_pdf($staff_id, $month)
    {
        $shift_data = $this->team_management_model->get_staff_shift_details($staff_id, $month);
        $staff_name = $this->team_management_model->id_to_name($staff_id, 'tblstaff', 'staffid', 'firstname');
        $monthName = date('F', mktime(0, 0, 0, $month, 1));

        $mpdf = new \Mpdf\Mpdf();
        // Load the pre-modified PDF as a template.
        
        $template = file_get_contents(base_url('modules/team_management/assets/template/pdf_temp.html'));

        $template = str_replace('{month_name}', $monthName , $template);
        $template = str_replace('{staff_name}', $staff_name, $template);
 
        $html = '';

        // Loop through the days of the month and generate table rows
        for ($day = 1; $day <= date('t', mktime(0, 0, 0, $month, 1)); $day++) {

            $year = date('Y');
            $thisMonth = date('F', mktime(0, 0, 0, $month, 1));
            $timestamp = mktime(0, 0, 0, $month, $day, $year);

            $date_formatted = date('l, F jS', $timestamp);

            $html .= '

            <tr class="bg-gray-100/80 my-2">
                <th class="border px-4 py-2" colspan="2">'.$date_formatted.'</th>
            </tr>
            <tr class="bg-gray-100/30 my-2">
                <td class="border px-4 py-2 text-center">' . $shift_data[$day][1]['start_time'] . ' - ' . $shift_data[$day][1]['end_time'] . '</td>
                <td class="border px-4 py-2 text-center">' . $shift_data[$day][2]['start_time'] . ' - ' . $shift_data[$day][2]['end_time'] . '</td>
            </tr>
            ';
        }

        $template = str_replace('{shift_rows}', $html, $template);


        $mpdf->WriteHTML($template);
        $mpdf->Output($staff_name.'\'s_staff_shifts.pdf', 'D');
        //echo $template;
    }

    public function export_all_shift_details_to_pdf($month)
    {
        // Retrieve all staff members from the database.
        $all_staff_members = $this->team_management_model->get_all_staff();

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $year = date('Y');

        $mpdf_config = [
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'orientation' => 'L',
        ];

        $mpdf = new \Mpdf\Mpdf($mpdf_config);

        // Load the pre-modified PDF as a template.
        $template = file_get_contents(base_url('modules/team_management/assets/template/pdf_temp_L.html'));

        $final_html = '';

        $template = str_replace('{month_name}', $monthName , $template);
        $template = str_replace('{staff_name}', "All member", $template);

        $html = '';

        // Get the total number of weeks in the month.
        $weeks_in_month = date('W', mktime(0, 0, 0, $month + 1, 0, $year)) - date('W', mktime(0, 0, 0, $month, 1, $year)) + 1;

        // Loop through each week.
        for ($week = 1; $week <= $weeks_in_month; $week++) {
            
            $html .= '<br><table class="week-table w-full border-collapse" style="page-break-after: always;">
            <thead>
                <tr class="myRow">
                    <th class="myTd">Staff</th>';

            // Generate table head with dates.
            for ($day_of_week = 1; $day_of_week <= 7; $day_of_week++) {
                $day = ($week - 1) * 7 + $day_of_week;
                $timestamp = mktime(0, 0, 0, $month, $day, $year);
                $first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
                $offset = (date('N', $first_day_of_month) - 1) % 7;

                // Adjust the day to start the week from Monday.
                $day -= $offset;
                $timestamp = mktime(0, 0, 0, $month, $day, $year);

                    $date_formatted = date('D, M jS', $timestamp);
                    $html .= '<th class="myTd" colspan="2">' . $date_formatted . '</th>';       
            }

            $html .= '</tr>

            <tr class="myRow">
                    <th class="myTd"></th>';

            // Generate table head with dates.
            for ($day_of_week = 1; $day_of_week <= 7; $day_of_week++) {
                $html .= '<th class="myTd"> Shift 1 </th>';  
                $html .= '<th class="myTd"> Shift 2 </th>';       
            }

            $html .= '</tr>


            </thead>
            <tbody>';

            // Loop through each staff member and generate their shift details.
            foreach ($all_staff_members as $staff) {
                $staff_id = $staff->staffid;
                $shift_data = $this->team_management_model->get_staff_shift_details($staff_id, $month);
                $staff_name = $staff->firstname;

                $html .= '<tr>
                <th class="myTd">' . $staff_name . '</th>';

                // Loop through the days of the week and generate table cells.
                for ($day_of_week = 1; $day_of_week <= 7; $day_of_week++) {
                    $day = ($week - 1) * 7 + $day_of_week;
                    
                    // Adjust the day to start the week from Monday.
                    $day -= $offset;
                    $timestamp = mktime(0, 0, 0, $month, $day, $year);

                    if ($day > 0 && $day <= date('t', $first_day_of_month) && date('m', $timestamp) == $month) {
                        
                        $date = date('Y-m-d', $timestamp);
                        
                        $shift_data_day = isset($shift_data[$day]) ? $shift_data[$day] : [];
                        
                        $shift_1_start = isset($shift_data_day[1]) ? date('ga', strtotime($shift_data_day[1]['start_time'])) : '';
                        $shift_1_end = isset($shift_data_day[1]) ? date('ga', strtotime($shift_data_day[1]['end_time'])) : '';
                        $shift_2_start = isset($shift_data_day[2]) ? date('ga', strtotime($shift_data_day[2]['start_time'])) : '';
                        $shift_2_end = isset($shift_data_day[2]) ? date('ga', strtotime($shift_data_day[2]['end_time'])) : '';
                    
                        $time_entries = $this->team_management_model->get_staff_time_entries($staff_id, date('Y-m-d', $timestamp));
                        
                        $clock_in_1 = isset($time_entries[0]) ? date('ga', strtotime($time_entries[0]['clock_in'])) : '';
                        $clock_out_1 = isset($time_entries[0]) ? date('ga', strtotime($time_entries[0]['clock_out'])) : '';

                        $clock_in_2 = isset($time_entries[1]) ? date('ga', strtotime($time_entries[1]['clock_in'])) : '';
                        $clock_out_2 = isset($time_entries[1]) ? date('ga', strtotime($time_entries[1]['clock_out'])) : '';

                        if(!$this->team_management_model->is_on_leave($staff_id, $date)){
                            $html .= '<td class=" myTd">' . ($shift_1_start && $shift_1_end ? $shift_1_start . '-' . $shift_1_end : '') . ' ' . ($clock_in_1 && $clock_out_1 ? $clock_in_1 . '-' . $clock_out_1 : '') . ' </td>';
                            $html .= '<td class=" myTd">' . ($shift_2_start && $shift_2_end ? $shift_2_start . '-' . $shift_2_end : '') . ' ' . ($clock_in_2 && $clock_out_2 ? $clock_in_2 . '-' . $clock_out_2 : '') . ' </td>';
                        }else{
                            $html .= '<td class="myTd" colspan="2">Leave</td>';
                        }

                    } else {
                        $html .= '<td class="myTd">-</td>';
                        $html .= '<td class="myTd">-</td>';
                    }
                    
                }

                $html .= '</tr>';
            }

            $html .= '</tbody>
            </table>';
        }





        $final_html .= str_replace('{shift_rows}', $html, $template);

        $mpdf->WriteHTML($final_html);
        $mpdf->Output('All_staff_shifts.pdf', 'D');

        //echo $final_html;
    }

    function widget()
    {
        $staff_id = $this->session->userdata('staff_user_id');
        $stats = $this->team_management_model->get_stats($staff_id);

        $data['stats'] = $stats;

        $this->load->view('dashboard_widget');
    }

    function create_thread($webhookUrl, $threadKey, $message) {
        
        $ch = curl_init();
    
        $data = array(
            'text' => $message,
        );
    
        $threadWebhookUrl = "${webhookUrl}&threadKey=${threadKey}&messageReplyOption=REPLY_MESSAGE_FALLBACK_TO_NEW_THREAD";
    
        curl_setopt($ch, CURLOPT_URL, $threadWebhookUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return $response;
    }

    public function base_threads_cron_access($api_key)
    {
        if($api_key != $this->cronAPI()){
            return;
        }

        $today = date("dmY");
        $workingHoursThreadKey = "workingHours-${today}";
        $shiftsThreadKey = "shifts-${today}";
        $afkThreadKey = "afk-${today}";
        $tasksAllThreadKey = "tasks-allocation-${today}";
        $tasksActThreadKey = "tasks-activity-${today}";
        $eosThreadKey = "eos-${today}";

        $today = date("d/m/Y");

        $hourAlerts = "https://chat.googleapis.com/v1/spaces/AAAAsIq3P_g/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=5OC02nE2oxlTecgPi4jV1TGQLhOnhap4KlpQKTx5rzI";

        $taskAlerts = "https://chat.googleapis.com/v1/spaces/AAAA6jknWu4/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=onQidKXA1QDI0IBDMkqU0d_31zwWFZsFE-QPb-jJa5c";

        $scheduleAlerts = "https://chat.googleapis.com/v1/spaces/AAAAsGG8iYM/messages?key=AIzaSyDdI0hCZtE6vySjMm-WEfRq3CPzqKqqsHI&token=Mifshsjgb3HLutqyd8ScfXtpPfkDcykf2d_POhGWN3c";




        echo $this->create_thread($hourAlerts, $shiftsThreadKey, "--- 📆 `DATE: ${today}` 🔄 *SHIFTS-LOG THREAD* 🔄 ---");
            
        echo $this->create_thread($hourAlerts, $afkThreadKey, "--- 📆 `DATE: ${today}` 🚶‍♂️ *AFK THREAD* 🚶‍♀️ ---");
            
        echo $this->create_thread($taskAlerts, $tasksAllThreadKey, "--- 📆 `DATE: ${today}` 📝 *TASKS ALLOCATION THREAD* 📋 ---");
            
        echo $this->create_thread($taskAlerts, $tasksActThreadKey, "--- 📆 `DATE: ${today}` 🏃‍♂️ *TASKS ACTIVITY THREAD* 🏃‍♀️ ---");

        echo $this->create_thread($scheduleAlerts, $workingHoursThreadKey, "--- 📆 `DATE: ${today}` 🕰️ *WORK SCHEDULE THREAD* 🏢 ---");
            
        echo $this->create_thread($scheduleAlerts, $eosThreadKey, "--- 📆 `DATE: ${today}` 📚 *EOS SUMMARIES THREAD* 📖 ---"); 
        
    }

    public function log_shift_timings_cron_access($shift_no, $api_key) {

        if($api_key != $this->cronAPI()){
            return;
        }

        $shifts = $this->team_management_model->get_today_shift_timings();

        // Loop through each shift
        foreach ($shifts as $shift) {

            if($shift['shift_number'] == $shift_no ){

                $staff_id = $shift['staff_id'];

                // Check if the staff member is on leave
                if ($this->team_management_model->is_on_leave($staff_id, date('Y-m-d H:i:s'))) {
                    // Log message that the user is on leave
                    $tag = $this->team_management_model->id_to_name($staff_id, 'tbl_staff_google_chat', 'staff_id', 'google_chat_user_id');
                    $message = sprintf("😴 *<users/%s>* is on leave today. 🌴", $tag);
                    $this->webhook_lib->send_chat_webhook($message, 'workingHours');
                } else {
                    // Log shift timings
                    $tag = $this->team_management_model->id_to_name($staff_id, 'tbl_staff_google_chat', 'staff_id', 'google_chat_user_id');

                    $shift_number = $shift['shift_number'];
                    $start_time = $shift['shift_start_time'];
                    $end_time = $shift['shift_end_time'];

                    $message = sprintf("👩‍💻 *<users/%s>*'s 🕒 *Shift %d Timings Today* \n\n 📅 : `%s - %s`", $tag, $shift_number, date('g:i A', strtotime($start_time)), date('g:i A', strtotime($end_time)));
                    
                    // Send message to 'shifts' thread
                    $this->webhook_lib->send_chat_webhook($message, 'workingHours');
                }

            }
        }
    }

    
    public function clock_in()
    {
        $staff_id = ($this->input->post('staff_id') == null) ? $this->session->userdata('staff_user_id') : $this->input->post('staff_id');
        $clock_in_result = $this->team_management_model->clock_in($staff_id);

        if ($clock_in_result) {
            // format the date for readability
            $formatted_date = date('g:i A');

            $tag = $this->team_management_model->id_to_name($staff_id, 'tbl_staff_google_chat', 'staff_id', 'google_chat_user_id');

            $message = sprintf("👋 <users/%s> is `Clocking In` 🕒 *at*: %s", $tag, $formatted_date);
         
            $this->webhook_lib->send_chat_webhook($message, "shifts");
        }

        echo json_encode(['success' => $clock_in_result, 'resp' => $response]);
        //echo $response;
    }

    public function clock_out()
    {
        $staff_id = ($this->input->post('staff_id') == null) ? $this->session->userdata('staff_user_id') : $this->input->post('staff_id');
        $clock_out_result = $this->team_management_model->clock_out($staff_id);

        if ($clock_out_result) {


            // format the date for readability
            $formatted_date = date('g:i A');

            $tag = $this->team_management_model->id_to_name($staff_id, 'tbl_staff_google_chat', 'staff_id', 'google_chat_user_id');

            $message = sprintf("🏃‍♂️ <users/%s> is `Clocking Out` 🕒 *at*: %s", $tag, $formatted_date);
         
            $this->webhook_lib->send_chat_webhook($message, "shifts");
            
        }

        echo json_encode(['success' => $clock_out_result]);
    }

    public function update_status()
    {
        $staff_id = ($this->input->post('staff_id') == null) ? $this->session->userdata('staff_user_id') : $this->input->post('staff_id');

        $status = $this->input->post('statusValue');
        $current_time = date('Y-m-d H:i:s');
    
        // End the previous status
        $this->team_management_model->update_status($staff_id, $status);

        $this->team_management_model->end_previous_status($staff_id, $current_time);
    
        if ($status === 'Online') {
            // Do not insert a new status entry for the 'online' status
        } else {
            // Insert a new status entry for 'afk' or 'offline'
            $this->team_management_model->insert_status_entry($staff_id, $status, $current_time);
        }

        // format the date for readability
        $formatted_date = date('g:i A');

        $tag = $this->team_management_model->id_to_name($staff_id, 'tbl_staff_google_chat', 'staff_id', 'google_chat_user_id');

        // choose the right phrase depending on the status
        if ($status === 'AFK') {
            $message = sprintf("🚀 <users/%s> went `AFK` 🕒 *at*: %s", $tag, $formatted_date);
        } elseif ($status === 'Online') {
            $message = sprintf("*🎉 Yay!* <users/%s> is back `Online` 🕒 *at*: %s", $tag, $formatted_date);
        }

        if($status != "Leave"){
            $this->webhook_lib->send_chat_webhook($message, "afk");
        }
        

    
        echo json_encode(['success' => true]);
    }

    public function fetch_stats()
    {
        $staff_id = ($this->input->post('staff_id') == null) ? $this->session->userdata('staff_user_id') : $this->input->post('staff_id');
        $stats = $this->team_management_model->get_stats($staff_id);

        echo json_encode($stats);
    }

    public function fetch_staff_time_entries($staff_id) {
        if (!is_admin()) {
            //access_denied('Access Denied!');
        }
    
        $staff_time_entries = $this->team_management_model->get_staff_time($staff_id);
        echo json_encode($staff_time_entries);
    }

    public function edit_staff_time_entry() {
        $entry_id = $this->input->post('entry_id');
        $in_time = $this->input->post('in_time');
        
        $out_time = $this->input->post('out_time');

        if($out_time == "NaN-NaN-NaN NaN:NaN:NaN"){
            $out_time = null;
        }

        $result = $this->team_management_model->update_staff_time_entry($entry_id, $in_time, $out_time);
        
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function delete_staff_time_entry() {
        $entry_id = $this->input->post('entry_id');

        $result = $this->team_management_model->delete_staff_time_entry($entry_id);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    

    function convertSecondsToRoundedTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = round(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } else {
            return "{$minutes}m";
        }
    }

    public function submit_application() {

        $this->load->library('email');

        // Get the submitted form data.
        $application_type = $this->input->post('application_type');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $reason = $this->input->post('reason');
        $shift = $this->input->post('shift');
        $staff_id = $this->session->userdata('staff_user_id');
      
        // Save the application to the database using your model.
        $application_data = array(
            'staff_id' => $staff_id,
            'application_type' => $application_type,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'reason' => $reason,
            'shift' => $shift,
            'status' => 'pending', // Set the initial status to 'pending'
            'created_at' => date('Y-m-d H:i:s'), // Set the created_at column to the current timestamp
        );

        $f_name = $this->team_management_model->id_to_name($staff_id, 'tblstaff', 'staffid', 'firstname');

        $template = "
            <h2>New Application!!</h2>
            <div>Staff Name: ".$f_name."</div><br>
            <div>Date: ".date('Y-m-d')."</div><br>
            <div>Reason: ".$reason."</div><br>
            <a href='https://crm.zikrainfotech.com/admin/team_management/all_applications'> Applications Page! </a>
        ";

        $this->email->initialize();
        $this->email->set_newline(PHP_EOL);
        $this->email->from(get_option('smtp_email'), get_option('companyname'));
        
        $this->email->to(array(get_option('smtp_email'), 'anwar.saad@zikrainfotech.com'));
        $this->email->subject("New Application!");
        $this->email->message(get_option('email_header') . $template . get_option('email_footer'));

        $email_sent = $this->email->send();

        $insert_result = $this->team_management_model->save_application($application_data);

        if (isset($_FILES['attachment']) && $_FILES['attachment']['size'] > 0) {
            // Upload the file to the server.
            $config['upload_path'] = './uploads/applications/'; // Set the upload path.
            $config['allowed_types'] = 'gif|jpg|png|pdf|docx|webp'; // Set the allowed file types.
            $config['file_name'] = 'application_' . $insert_result; // Set the file name to the application id.
            $config['overwrite'] = true; // Overwrite the file if it exists.
            
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0777, true);
            }

            $this->load->library('upload', $config);
    
            if (!$this->upload->do_upload('attachment')) {    
                echo json_encode(['success' => false, 'error' => $this->upload->display_errors()]);
                return;
            }else {
                // Get the uploaded data
                $uploaded_data = $this->upload->data();
            
                // Set the permissions for the uploaded file
                chmod($uploaded_data['full_path'], 0644);
            }
        }

      
        // Redirect the user to a success page or back to the form with a success message.
        echo json_encode(['success' => $insert_result]);
    }

    public function fetch_applications() {

        $staff_id = $this->input->get('staff_id');
        $status = $this->input->get('status');

        if($staff_id != 0){
            $applications = $this->team_management_model->get_applications_by_staff_id($staff_id);
        }else{
            $applications = $this->team_management_model->get_all_applications($status);
        }

        foreach ($applications as $application) {
            $application->user_name = $this->team_management_model->id_to_name($application->staff_id, 'tblstaff', 'staffid', 'firstname');;
            $application->user_pfp = staff_profile_image($application->staff_id, ['object-cover', 'md:h-full' , 'md:w-10 inline' , 'staff-profile-image-thumb'], 'thumb');
        }

               
        $response = array(
            'applications' => $applications
        );
        //header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function change_application_status() {
        $application_id = $this->input->post('id');
        $new_status = $this->input->post('status');
    
        if ($this->team_management_model->update_application_status($application_id, $new_status)) {
            
            $application_data = $this->team_management_model->get_application($application_id);

            $status = $application_data["status"];
            $id = $application_data["id"];
            $staff_id = $application_data["staff_id"];
            $date = $application_data["created_at"];
            $duration = $application_data["start_date"] . ' - ' . $application_data["end_date"];
            $reason = $application_data["reason"];

            $template = "
                <h2>Application Status Changed to: ".$status."</h2>
                <div>Application Id: ".$id."</div><br>
                <div>Sent At: ".$date."</div><br>
                <div>Duration: ".$duration."</div><br>
                <div>Reason: ".$reason."</div><br>
                <a href='https://crm.zikrainfotech.com/admin/team_management/applications'> Applications Page! </a>
            ";

            $this->email->initialize();
            $this->email->set_newline(PHP_EOL);
            $this->email->from(get_option('smtp_email'), get_option('companyname'));
            $this->email->to(get_option('smtp_email'));
            $this->email->to($this->team_management_model->id_to_name($staff_id, 'tblstaff', 'staffid', 'email'));
            $this->email->subject("Application Reviewed!!");
            $this->email->message(get_option('email_header') . $template . get_option('email_footer'));

            $email_sent = $this->email->send();

            
        $response = [
              'success' => true,
              'message' => 'Status changed successfully',
            ];
        } else {
          $response = [
            'success' => false,
            'message' => 'Error changing status',
          ];
        }
    
        echo json_encode($response);
    }

    public function delete_application() {
        $application_id  = $this->input->post('id');
        $result = $this->team_management_model->delete_application($application_id);
    
        // Check if the delete operation was successful and redirect or display an error message accordingly.
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    

    public function view_leaves() {
        $staff_id = $this->input->post('staff_id');
        $leaves = $this->team_management_model->get_leaves($staff_id);
        echo json_encode(['leaves' => $leaves]);
    }

    // Function for adding leaves
    public function add_leave() {
        $staff_id = $this->input->post('staff_id');
        $reason = $this->input->post('reason');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $shift = $this->input->post('shift');

        $result = $this->team_management_model->insert_leave($staff_id, $reason, $start_date, $end_date, $shift);

        if ($result['success']) {
            echo json_encode(['success' => true, 'id' => $result['id']]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    // Function for deleting leaves
    public function delete_leave() {
        $leave_id = $this->input->post('leave_id');
        $result = $this->team_management_model->remove_leave($leave_id);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function process_staff_leaves_cron_access($api_key) {

        if($api_key != $this->cronAPI()){
            return;
        }

        $staff_members = $this->team_management_model->get_all_staff();
    
        foreach ($staff_members as $staff) {
            if ($this->team_management_model->is_on_leave($staff->staffid, date('Y-m-d H:i:s'))) {
                $this->team_management_model->clock_out_and_set_leave_status($staff->staffid); 
            }else{
                if($this->team_management_model->get_stats($staff->staffid)->status == "Leave"){
                    $this->team_management_model->update_status($staff->staffid, "Offline");
                }
            }
        }
    }

    public function check_staff_leave($id = 1)
    {
        echo $this->team_management_model->is_on_leave($id, date('Y-m-d H:i:s'));
    }

    public function fetch_daily_info() {
        if (!$this->input->is_ajax_request()) {
            redirect(admin_url());
        }

        $staff_id = $this->input->post('staff_id');
        $day = $this->input->post('day');
        $month = $this->input->post('month');
        $year = $this->input->post('year');

        $daily_stats = $this->team_management_model->get_daily_stats($staff_id, $day, $month, $year);

        echo json_encode($daily_stats);
    }

    public function get_file_type()
    {
        $filename = $this->input->get('filename');
        $directory = './uploads/applications/';

        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        $pdf_extension = 'pdf';
        $found_file = false;
        $file_type = '';
        $ext = '';

        $files = scandir($directory);
        $files = array_diff($files, array('.', '..'));

        foreach ($image_extensions as $extension) {
            
            if (in_array($filename . '.' . $extension, $files)) {
                $file_type = "image";
                $ext = $extension;
                $found_file = true;
                break;
            }
        }

        if (!$found_file && file_exists($directory . $filename . '.' . $pdf_extension)) {
            $file_type = "pdf";
            $ext = $pdf_extension;
        } elseif (!$found_file) {
            $file_type = "not_found";
        
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['file_type' => $file_type, 'ext' => $ext]));
    }

    public function save_dummy_task()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $project_id = $this->input->post('project_id');
        $task_name = $this->input->post('name');

        $task_id = $this->team_management_model->add_dummy_task($project_id, $task_name);

        if ($task_id) {
            $response = ['task_id' => $task_id];
            echo json_encode($response);
        } else {
            http_response_code(400);
            echo 'Error saving dummy task';
        }
    }

    public function assign_task_to_dummy_task() {
        $taskId = $this->input->post('task_id');
        $dummyTaskId = $this->input->post('dummy_task_id');
    
        if ($this->team_management_model->assign_task_to_dummy_task($taskId, $dummyTaskId)) {
            $taskDetails = $this->team_management_model->fetch_task_details($taskId);
    
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => true, 'message' => 'Task ID assigned to dummy task ID', 'task_details' => $taskDetails]));
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Failed to assign task ID to dummy task ID']));
        }
    }

    public function delete_dummy_task()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $id = $this->input->post('dummy_task_id');

        $result = $this->team_management_model->delete_dummy_task($id);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function staff_summary() {


        $staff_id = $this->session->userdata('staff_user_id');

        $summary = $this->input->post('summary');
      
        if ($summary) {
            
            // Save the summary
            $this->team_management_model->save_staff_summary($staff_id, $summary);

            $formatted_date = date('g:i A');

            $tag = $this->team_management_model->id_to_name($staff_id, 'tbl_staff_google_chat', 'staff_id', 'google_chat_user_id');

            $message = sprintf("📝✨ <users/%s> just shared their daily summary 🕑 *at*: %s\n\n📋*Summary*:\n%s", $tag, $formatted_date, $summary);
         
            $this->webhook_lib->send_chat_webhook($message, "eos");
        
        } else {
          // Get the summary
          $summary_record = $this->team_management_model->get_staff_summary($staff_id);
          if ($summary_record) {
            echo $summary_record->summary;
          } else {
            echo '';
          }
        }
    }

    public function generate_daily_summary($date) {


        // Fetch all staff summaries
        $staff_summaries = $this->team_management_model->get_staff_summaries($date);
        
        // Prepare input data for GPT-3.5
        $input_text = "Generate a daily summary based on the following staff summaries:\n";
        foreach ($staff_summaries as $summary) {
            $input_text .= "- " . $summary['staff_name'] . ": ```" . $summary['summary'] . "``` \n";
        }
        
        // Call GPT-3.5 API with the input text
        $generated_summary = $this->call_gpt_35_api($input_text);
        
        return $generated_summary;
    }

    private function call_gpt_35_api($input_text) {
        
        $open_ai_key = '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';
        $open_ai = new OpenAi($open_ai_key);

        $messages = [
            ["role" => "system", "content" => "You are supposed to generate well defined daily summary of our Organization based on the staff summaries provided."],
            ["role" => "user", "content" => $input_text]
        ];
        

        $response = $open_ai->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'temperature' => 0.8,
            'max_tokens' => 400,
            'frequency_penalty' => 0,
            'presence_penalty' => 0.6
        ]);
    
        // Extract and return the generated summary
        //$generated_summary = $response['choices'][0]['text'];
        $response_array = json_decode($response, true);
        $generated_content = $response_array['choices'][0]['message']['content'];

        echo $generated_content;

    }

    private function cronAPI()
    {
        return "OUIYUGBSCL";
    }
      

}


?>