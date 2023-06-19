<?php defined('BASEPATH') or exit('No direct script access allowed');

class Team_management_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single goal
     */

    public function get_all_staff()
    {
        $CI =& get_instance();

        $this->db->select('*');
        $this->db->from(''.db_prefix().'staff');
        $this->db->join(''.db_prefix().'_staff_status', ''.db_prefix().'staff.staffid = '.db_prefix().'_staff_status.staff_id');
        $this->db->where('is_not_staff', 0);
        $query = $this->db->get();
        $result = $query->result();
     
        // Loop through each row in the result
        foreach ($result as $staff) {

            //Today's Timer Counter
            $staff->live_time_today = $this->get_today_live_timer($staff->staff_id);

            //Task Assigned
            $allTasks = $this->get_tasks_of_staff($staff->staff_id);
            if($allTasks){
                $staff->all_tasks = $allTasks;
            }
            

            //Get current task
            $taskId = $this->get_current_task_by_staff_id($staff->staff_id);
            if($taskId){
                $task = $this->get_task_by_taskid($taskId);
                $staff->currentTaskName = $task->name;
                if($task->rel_type == "project"){
                    $CI->load->model('projects_model');
                    $task_project = $CI->projects_model->get($task->rel_id);
                    $staff->currentTaskProject = $task_project->name;
                }
                
                $currentTaskTime = $this->get_timers($taskId, $staff->staff_id);
                
                if($currentTaskTime){

                    $timestamp = $currentTaskTime->start_time;

                    $given_date = new DateTime();
                    $given_date->setTimestamp($timestamp);

                    $now = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

                    $interval = $now->diff($given_date);
                    $seconds_passed = $interval->s + ($interval->i * 60) + ($interval->h * 3600) + ($interval->days * 86400);

                    $staff->currentTaskTime = $seconds_passed;

                }else{
                    $staff->currentTaskTime = "0";
                }

                $staff->currentTaskId = $task->id;

            }else{
                $staff->currentTaskId = 0;
                $staff->currentTaskName = "None";
                $staff->currentTaskTime = "0";
            }
            
            //Check if Shift is Active or Not
            $current_entry = $this->db->where('staff_id', $staff->staff_id)
                            ->where('clock_out IS NULL', null, false)
                            ->get(''.db_prefix().'_staff_time_entries')
                            ->row();
            if ($current_entry) {
                $staff->working = true;
            }else{
                $staff->working = false;
            }

            //Set Status Color Class
            if($staff->status == "Online"){
                $staff->statusColor = "emerald-200";
            }else if ($staff->status == "AFK"){
                $staff->statusColor = "sky-200";
            }
            else if ($staff->status == "Leave"){
                $staff->statusColor = "amber-200";
            }
            else{
                $staff->statusColor = "gray-200";
            }

         }
     
         return $result;
    }

    public function get_all_timers(){
        $timers = new stdClass();
        $yesterdayTime = 0;
        $weekTime = 0;
        $todayTime = 0;

        $this->db->select('staffid');
        $this->db->from(''.db_prefix().'staff');
        $this->db->where('is_not_staff', 0);
        $query = $this->db->get();

        $staff_members = $query->result();

        foreach ($staff_members as $staff) {
            $yesterdayTime += $this->get_yesterdays_total_time($staff->staffid);
            $weekTime += $this->get_this_weeks_total_time($staff->staffid);
            $todayTime += $this->get_today_live_timer($staff->staffid);
        }

        $timers->todayTime = $todayTime;
        $timers->yesterdayTime = $yesterdayTime;
        $timers->weekTime = $weekTime;

        $timers->totalOngoingTasks = $this->get_ongoing_tasks();
        $maxTasksCompleted = $this->get_staff_with_most_tasks_completed_today();

        if($maxTasksCompleted){
            $timers->maxTasksCompletedName = $maxTasksCompleted->lastname;
            $timers->maxTasksCompletedId = $maxTasksCompleted->staffid;
        }else{
            $timers->maxTasksCompletedName = "None :(";
            $timers->maxTasksCompletedId = null;
        }

        $maxHours = $this->get_staff_with_highest_today_live_timer();
        if($maxHours){
            $timers->maxHoursPutInName = $maxHours->lastname;
            $timers->maxHoursPutInId = $maxHours->staffid;
        }else{
            $timers->maxHoursPutInName = "Nan";
            $timers->maxHoursPutInId = null;
        }
        

        return $timers;
    }

    public function get_ongoing_tasks()
    {
        $this->db->select(''.db_prefix().'tasks.*');
        $this->db->from(''.db_prefix().'tasks');
        $this->db->join(''.db_prefix().'taskstimers', ''.db_prefix().'taskstimers.task_id = '.db_prefix().'tasks.id');
        $this->db->where(''.db_prefix().'taskstimers.end_time IS NULL', NULL, FALSE);
        $query = $this->db->get();

        return $query->num_rows();
    }

    public function get_tasks_records($type) {

        $current_date = date('Y-m-d');

        $this->db->select(''.db_prefix().'tasks.*, '.db_prefix().'projects.name as project_name');
        $this->db->from(''.db_prefix().'tasks');

        
        if($type == 1){
            $this->db->where('duedate <', $current_date);
            $this->db->where(''.db_prefix().'tasks.status !=', 5);
        }else if ($type == 2){
            $this->db->where('duedate', $current_date);
        }else{
            $this->db->where(''.db_prefix().'tasks.status !=', 5);
            $this->db->group_start();
            $this->db->where('duedate >', $current_date);
            $this->db->or_where('duedate IS NULL', null, false);     
            $this->db->order_by('id DESC');
            $this->db->group_end();
        }

        $this->db->join(''.db_prefix().'projects', ''.db_prefix().'tasks.rel_id = '.db_prefix().'projects.id AND '.db_prefix().'tasks.rel_type = "project"', 'left');

        $query = $this->db->get();
        $allTasks = $query->result();

        foreach ($allTasks as $task) {

            $task->assigned = array();

            $this->db->select('staffid');
            $this->db->from(''.db_prefix().'task_assigned');
            $this->db->where('taskid', $task->id);
            $query = $this->db->get();
            $allStaff = $query->result();
            foreach ($allStaff as $staff) {
                array_push($task->assigned, staff_profile_image($staff->staffid, ['object-cover', 'md:h-full' , 'md:w-10 inline' , 'staff-profile-image-thumb'], 'thumb'));
            }
            if($task->duedate == null){
                $task->duedate = "None";
            }

            $task->priority = $this->id_to_name($task->priority, ''.db_prefix().'tickets_priorities', 'priorityid', 'name');

            if($task->status == 1){
                $task->status = "Not Started";
            }
            if($task->status == 2){
                $task->status = "Awaiting Feedback";
            }
            if($task->status == 3){
                $task->status = "Testing";
            }
            if($task->status == 4){
                $task->status = "In Progress";
            }
            if($task->status == 5){
                $task->status = "Completed";
            }
        }

        return $allTasks;
    }

    public function id_to_name($id, $tableName, $idName, $nameName) {
        $this->db->select($nameName);
        $this->db->from($tableName);
        $this->db->where($idName, $id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row();
            return $row->$nameName;
        } else {
            return 'Unknown';
        }
    }

    public function get_tasks_of_staff($staff_id)
    {
        $this->db->select(''.db_prefix().'tasks.*, '.db_prefix().'projects.name as project_name');
        $this->db->from(''.db_prefix().'tasks');
        $this->db->join(''.db_prefix().'task_assigned', ''.db_prefix().'task_assigned.taskid = '.db_prefix().'tasks.id');
        $this->db->join(''.db_prefix().'projects', ''.db_prefix().'tasks.rel_id = '.db_prefix().'projects.id AND '.db_prefix().'tasks.rel_type = "project"', 'left');
        $this->db->where(''.db_prefix().'task_assigned.staffid', $staff_id);
        $this->db->where(''.db_prefix().'tasks.status !=', 5);
        $query = $this->db->get();

        return $query->result();

    }
    
    

    public function get_staff_with_highest_today_live_timer() {
        $all_staff = $this->get_all_staff();

        $highest_timer_staff = null;
        $highest_timer = 0;

        foreach ($all_staff as $staff) {
            $timer = $this->get_today_live_timer($staff->staffid);

            if ($timer > $highest_timer) {
                $highest_timer = $timer;
                $highest_timer_staff = $staff;
            }
        }

        return $highest_timer_staff;
    }

    public function get_staff_with_most_tasks_completed_today($date_start = null, $date_end = null)
    {
        $today_start = ($date_start == null) ? date('Y-m-d 00:00:00') : $date_start;
        $today_end = ($date_end == null) ? date('Y-m-d 23:59:59') : $date_end;

        $this->db->select(''.db_prefix().'staff.*, COUNT('.db_prefix().'tasks.id) as tasks_completed')
            ->from(''.db_prefix().'tasks')
            ->join(''.db_prefix().'task_assigned', ''.db_prefix().'tasks.id = '.db_prefix().'task_assigned.taskid')
            ->join(''.db_prefix().'staff', ''.db_prefix().'task_assigned.staffid = '.db_prefix().'staff.staffid')
            ->where(''.db_prefix().'tasks.status', 5)
            ->where(''.db_prefix().'tasks.datefinished >=', $today_start)
            ->where(''.db_prefix().'tasks.datefinished <=', $today_end)
            ->group_by(''.db_prefix().'task_assigned.staffid')
            ->order_by('tasks_completed', 'DESC')
            ->limit(1);

        $query = $this->db->get();
        $result = $query->row();

        return $result;
    }

    public function get_today_live_timer($staff_id)
    {
        $totalTime = 0;

        $totalTime = $this->get_todays_total_time($staff_id);
        
        $current_entry = $this->db->where('staff_id', $staff_id)
                            ->where('clock_out IS NULL', null, false)
                            ->get(''.db_prefix().'_staff_time_entries')
                            ->row();
        if ($current_entry) {
            //$adjusted_clock_in_string = $current_entry->clock_in;
            //$adjusted_clock_in = DateTime::createFromFormat('Y-m-d H:i:s', $adjusted_clock_in_string);
            $current_shift_start = strtotime($current_entry->clock_in);

            //$adjusted_date_string = date('Y-m-d H:i:s');
            //$adjusted_date = DateTime::createFromFormat('Y-m-d H:i:s', $adjusted_date_string);
            $current_unix_timestamp = strtotime(date('Y-m-d H:i:s'));

            $elapsed_time = $current_unix_timestamp - $current_shift_start;
            $afk_and_offline_time = $this->get_total_afk_and_offline_time($staff_id, $current_entry->clock_in);
            $totalTime += $elapsed_time - $afk_and_offline_time;
        }

        return $totalTime;
    }

    public function get_timers($taskId, $staff_id) {
        $this->db->select('*');
        $this->db->from(''.db_prefix().'taskstimers');
        $this->db->where('task_id', $taskId);
        $this->db->where('staff_id', $staff_id);
        $this->db->where('end_time IS NULL', null, false);
        $query = $this->db->get();
        return $query->row();
    }
    
    
    public function get_current_task_by_staff_id($staff_id) {
        $this->db->select('task_id');
        $this->db->from(''.db_prefix().'taskstimers');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('end_time IS NULL');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row();
    
        if ($result) {
            return $result->task_id;
        } else {
            return null;
        }
    }

    public function get_task_by_taskid($taskid) {
        $this->db->select('*');
        $this->db->from(''.db_prefix().'tasks');
        $this->db->where('id', $taskid);
        $query = $this->db->get();
        return $query->row();
    }

    public function clock_in($staff_id)
    {
        // Check if there's an existing open session for the staff member
        $this->db->where('staff_id', $staff_id);
        $this->db->where('clock_out IS NULL', null, false);
        $query = $this->db->get(db_prefix().'_staff_time_entries');
        
        if ($query->num_rows() > 0) {
            // If there's an open session, return false
            return false;
        }

        // Clock in the staff member
        $data = [
            'staff_id' => $staff_id,
            'clock_in' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert(db_prefix().'_staff_time_entries', $data);

        return $this->db->affected_rows() > 0;
    }

    public function clock_out($staff_id)
    {
        $now = date('Y-m-d H:i:s');
        $now_timestamp = time(); // Get the current Unix timestamp

        // Stop the task timer for the staff member if there's any active timer
        $this->db->set('end_time', $now_timestamp);
        $this->db->where('staff_id', $staff_id);
        $this->db->where('end_time IS NULL', null, false);
        $this->db->update(db_prefix().'taskstimers');

        // Clock out the staff member by updating the latest open session
        $this->db->set('clock_out', $now);
        $this->db->where('staff_id', $staff_id);
        $this->db->where('clock_out IS NULL', null, false);
        $this->db->update(db_prefix().'_staff_time_entries');

        return $this->db->affected_rows() > 0;
    }


    public function update_status($staff_id, $status)
    {
        // Check if the staff_id already exists in the table
        $query = $this->db->select('*')
                          ->from(''.db_prefix().'_staff_status')
                          ->where('staff_id', $staff_id)
                          ->get();

        // If staff_id exists, update the status
        if ($query->num_rows() > 0) {
            $this->db->set('status', $status)
                     ->where('staff_id', $staff_id)
                     ->update(''.db_prefix().'_staff_status');
        } 
        // Otherwise, insert a new row with staff_id and status
        else {
            $data = array(
                'staff_id' => $staff_id,
                'status' => $status
            );
            $this->db->insert(''.db_prefix().'_staff_status', $data);
        }

        return $this->db->affected_rows() > 0;
    }

    public function get_stats($staff_id)
    {
        $stats = new stdClass();

        $current_entry = $this->db->where('staff_id', $staff_id)
                                ->where('clock_out IS NULL', null, false)
                                ->get(''.db_prefix().'_staff_time_entries')
                                ->row();

        if ($current_entry) {

            // Adjust clock_in time to the user's timezone
            //$adjusted_clock_in_string = $current_entry->clock_in;
            //$adjusted_clock_in = DateTime::createFromFormat('Y-m-d H:i:s', $adjusted_clock_in_string);
            $current_shift_start = strtotime($current_entry->clock_in);

            $total_afk_and_offline_time = $this->get_total_afk_and_offline_time($staff_id, $current_entry->clock_in);

            // Convert total_afk_and_offline_time to seconds and add to the current_shift_start
            $new_clock_in_time = $current_shift_start + $total_afk_and_offline_time;

            $stats->clock_in_time = date('Y-m-d H:i:s', $new_clock_in_time);

            //$adjusted_date_string = date('Y-m-d H:i:s');
            //$adjusted_date = DateTime::createFromFormat('Y-m-d H:i:s', $adjusted_date_string);
            $current_unix_timestamp = strtotime(date('Y-m-d H:i:s'));

            $elapsed_time = $current_unix_timestamp - $current_shift_start;


            $stats->total_afk_time = $total_afk_and_offline_time;
            $stats->total_time = $elapsed_time - $total_afk_and_offline_time;

        } else {
            $stats->clock_in_time = null;
            $stats->total_time = 0;
        }

        $current_entry = $this->db->where('staff_id', $staff_id)
                                ->get(''.db_prefix().'_staff_status')
                                ->row();
        if($current_entry){
            $stats->status = $current_entry->status;
        }else{
            $stats->status = "Status record not found!";
        }

        
        $stats->todays_total_time = $this->get_todays_total_time($staff_id);
        $stats->yesterdays_total_time = $this->get_yesterdays_total_time($staff_id);
        $stats->this_weeks_total_time = $this->get_this_weeks_total_time($staff_id);
        $stats->last_weeks_total_time = $this->get_last_weeks_total_time($staff_id);

        return $stats;
    }


    public function end_previous_status($staff_id, $end_time)
    {
        $this->db->set('end_time', $end_time)
                ->where('staff_id', $staff_id)
                ->where('end_time IS NULL', null, false)
                ->update(''.db_prefix().'_staff_status_entries');
    }

    public function insert_status_entry($staff_id, $status, $start_time)
    {
        $data = [
            'staff_id' => $staff_id,
            'status' => $status,
            'start_time' => $start_time,
        ];

        $this->db->insert(''.db_prefix().'_staff_status_entries', $data);
    }

    public function get_total_afk_and_offline_time($staff_id, $current_shift_start)
    {   
        $nowDateTime = new DateTime('now');
        $nowDate = $nowDateTime->format('Y-m-d H:i:s');

        $this->db->select_sum('TIMESTAMPDIFF(SECOND, start_time, IFNULL(end_time, "'.$nowDate.'"))', 'total_time')
        ->where('staff_id', $staff_id)
        ->where('start_time >=', $current_shift_start)
        ->where_in('status', ['AFK', 'Offline']);
        $result = $this->db->get(''.db_prefix().'_staff_status_entries')->row();

        return $result->total_time;
        //return $this->db->last_query();
    }

    public function test_query($query) {
        $result = $this->db->query($query);
        return $result;
    }

    public function get_todays_total_time($staff_id)
    {
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        //error_log("Today start: {$today_start} - Today end: {$today_end}");
        return $this->get_total_time_within_range($staff_id, $today_start, $today_end);
    }

    public function get_yesterdays_total_time($staff_id)
    {
        $yesterday_start = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yesterday_end = date('Y-m-d 23:59:59', strtotime('-1 day'));
        return $this->get_total_time_within_range($staff_id, $yesterday_start, $yesterday_end);
    }

    public function get_this_weeks_total_time($staff_id)
    {
        $week_start = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $week_end = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        return $this->get_total_time_within_range($staff_id, $week_start, $week_end);
    }

    public function get_last_weeks_total_time($staff_id)
    {
        $last_week_start = date('Y-m-d 00:00:00', strtotime('monday last week'));
        $last_week_end = date('Y-m-d 23:59:59', strtotime('sunday last week'));
        return $this->get_total_time_within_range($staff_id, $last_week_start, $last_week_end);
    }

    public function get_total_time_within_range($staff_id, $start_date, $end_date)
    {
        // Calculate total working time
        $current_time = date('Y-m-d H:i:s');

        $this->db->select_sum('TIMESTAMPDIFF(SECOND, clock_in, COALESCE(clock_out, \'' . $current_time . '\'))', 'total_time')
            ->where('staff_id', $staff_id)
            ->where('clock_in >=', $start_date)
            ->where('clock_out <=', $end_date);
            $result = $this->db->get(''.db_prefix().'_staff_time_entries')->row();
            $total_working_time = $result->total_time;


        // Calculate total AFK and offline time
        $this->db->select_sum('TIMESTAMPDIFF(SECOND, '.db_prefix().'_staff_status_entries.start_time, '.db_prefix().'_staff_status_entries.end_time)', 'total_time')
        ->from(''.db_prefix().'_staff_status_entries')
        ->join(''.db_prefix().'_staff_time_entries', ''.db_prefix().'_staff_time_entries.staff_id = '.db_prefix().'_staff_status_entries.staff_id')
        ->where(''.db_prefix().'_staff_status_entries.staff_id', $staff_id)
        ->where(''.db_prefix().'_staff_status_entries.start_time >=', $start_date)
        ->where(''.db_prefix().'_staff_status_entries.end_time <=', $end_date)
        ->where(''.db_prefix().'_staff_status_entries.start_time >= '.db_prefix().'_staff_time_entries.clock_in')
        ->where(''.db_prefix().'_staff_status_entries.end_time <= '.db_prefix().'_staff_time_entries.clock_out')
        ->group_start()
            ->where(''.db_prefix().'_staff_status_entries.status', 'AFK')
            ->or_where(''.db_prefix().'_staff_status_entries.status', 'OFFLINE')
        ->group_end();
        $result = $this->db->get()->row();
        $total_afk_and_offline_time = $result->total_time;

        // Return the difference between total working time and total AFK and offline time
        return $total_working_time - $total_afk_and_offline_time;

    }

    public function save_shift_timings($staff_id, $month, $shift_timings) {
        // Delete existing shift timings for the staff member and month
        $this->db->where('staff_id', $staff_id)->where('month', $month)->delete(''.db_prefix().'_staff_shifts');
    
        // Insert new shift timings
        foreach ($shift_timings as $day => $shifts) {
            foreach ($shifts as $shift_number => $shift_time) {
                $this->db->insert(''.db_prefix().'_staff_shifts', [
                    'staff_id' => $staff_id,
                    'month' => $month,
                    'day' => $day,
                    'shift_number' => $shift_number,
                    'shift_start_time' => $shift_time['start'],
                    'shift_end_time' => $shift_time['end'],
                ]);
            }
        }
        
        return $this->db->affected_rows() > 0;
    }
    
    public function get_shift_timings($staff_id, $month) {
        $query = $this->db->where('staff_id', $staff_id)->where('month', $month)->get(''.db_prefix().'_staff_shifts');
        return $query->result_array();
    }

    public function get_staff_shift_details($staff_id, $month) {
        $this->db->select('day, shift_number, shift_start_time, shift_end_time');
        $this->db->from('tbl_staff_shifts');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('month', $month);
        $this->db->order_by('day', 'ASC');
        $this->db->order_by('shift_number', 'ASC');
        $query = $this->db->get();
    
        $shift_data = array();
        foreach ($query->result() as $row) {
            $shift_data[$row->day][$row->shift_number] = array(
                'start_time' => $row->shift_start_time,
                'end_time' => $row->shift_end_time,
            );
        }
    
        return $shift_data;
    }
    

    public function get_shifts_info($staff_id)
    {
        $dateTime = new DateTime("now", new DateTimeZone(get_option('default_timezone')));
        
        $current_month = $dateTime->format('m');
        $current_day = $dateTime->format('d');
        
        $current_time = $dateTime->format('H:i:s');

        $this->db->select('*');
        $this->db->from(''.db_prefix().'_staff_shifts');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('month', $current_month);
        $this->db->where('day', $current_day);
        $this->db->where('shift_end_time >=', $current_time);
        $this->db->order_by('shift_start_time', 'ASC');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return false;
        }
    }

    public function get_staff_with_shifts() {

        $dateTime = new DateTime("now", new DateTimeZone(get_option('default_timezone')));
        $current_month = $dateTime->format('m');
        $current_day = $dateTime->format('d');

        $this->db->select(''.db_prefix().'staff.staffid, '.db_prefix().'staff.email, '.db_prefix().'_staff_shifts.shift_number, '.db_prefix().'_staff_shifts.shift_start_time, '.db_prefix().'_staff_shifts.shift_end_time');
        $this->db->from(''.db_prefix().'staff');
        $this->db->join(''.db_prefix().'_staff_shifts', ''.db_prefix().'_staff_shifts.staff_id = '.db_prefix().'staff.staffid');
        $this->db->where(''.db_prefix().'_staff_shifts.month', $current_month);
        $this->db->where(''.db_prefix().'_staff_shifts.day', $current_day);
        $query = $this->db->get();
    
        $staff_members = array();
        foreach ($query->result() as $row) {
            if (!isset($staff_members[$row->staffid])) {
                $staff_members[$row->staffid] = new stdClass();
                $staff_members[$row->staffid]->email = $row->email;
                $staff_members[$row->staffid]->shifts = array();
            }
            $shift = new stdClass();
            $shift->shift_number = $row->shift_number;
            $shift->shift_start_time = $row->shift_start_time;
            $shift->shift_end_time = $row->shift_end_time;
            $staff_members[$row->staffid]->shifts[] = $shift;
        }
    
        return $staff_members;
    }

    public function get_staff_shifts_for_month($staff_id, $month) {
        $this->db->select('tbl_staff_shifts.*, tblstaff.*');
        $this->db->from('tbl_staff_shifts');
        $this->db->join('tblstaff', 'tblstaff.staffid = tbl_staff_shifts.staff_id');
        $this->db->where('tbl_staff_shifts.staff_id', $staff_id);
        $this->db->where('tbl_staff_shifts.month', $month);
        $this->db->order_by('tbl_staff_shifts.day', 'ASC');
        $this->db->order_by('tbl_staff_shifts.shift_number', 'ASC');
        $query = $this->db->get();

        return $query->num_rows() > 0 ? $query->result() : false;
    }
    
    
    public function get_user_activities($staffId, $month) {
        // Fetch shift start times from tbl_staff_time_entries
        $this->db->select("clock_in as time, 'Started Shift' as activity_type");
        $this->db->from('tbl_staff_time_entries');
        $this->db->where('staff_id', $staffId);
        $this->db->where('MONTH(clock_in)', $month);
        $this->db->order_by('clock_in', 'ASC');
        $query1 = $this->db->get_compiled_select();

        // Fetch AFK start and end times from tbl_staff_status_entries
        $this->db->select("start_time as time, CONCAT('Set ', status) as activity_type");
        $this->db->from('tbl_staff_status_entries');
        $this->db->where('staff_id', $staffId);
        $this->db->where('MONTH(start_time)', $month);
        $this->db->order_by('start_time', 'ASC');
        $query2 = $this->db->get_compiled_select();

        $this->db->select("end_time as time, 'Back to Online' as activity_type");
        $this->db->from('tbl_staff_status_entries');
        $this->db->where('staff_id', $staffId);
        $this->db->where('MONTH(end_time)', $month);
        $this->db->order_by('end_time', 'ASC');
        $query3 = $this->db->get_compiled_select();

        // Fetch shift end times from tbl_staff_time_entries
        $this->db->select("clock_out as time, 'Ended Shift' as activity_type");
        $this->db->from('tbl_staff_time_entries');
        $this->db->where('staff_id', $staffId);
        $this->db->where('MONTH(clock_out)', $month);
        $this->db->order_by('clock_out', 'ASC');
        $query4 = $this->db->get_compiled_select();

        // Combine queries using UNION
        $query = $this->db->query("($query1) UNION ($query2) UNION ($query3) UNION ($query4) ORDER BY time ASC");

        return $query->result_array();

    }

    public function get_staff_time_entries($staff_id, $date) {
        $this->db->where('staff_id', $staff_id);
        $this->db->where('DATE(clock_in)', $date);
        $query = $this->db->get('tbl_staff_time_entries');
        return $query->result_array();
    }

    public function save_application($application_data) {
        $this->db->insert('tbl_applications', $application_data);
        return $this->db->insert_id();
    }

    public function get_applications_by_staff_id($staff_id) {
        $this->db->select('*');
        $this->db->from('tbl_applications');
        $this->db->where('staff_id', $staff_id);
        $this->db->order_by('id DESC');
        $query = $this->db->get();

        return $query->result();
    }

    public function get_all_applications($status) {
        $this->db->select('*');
        $this->db->from('tbl_applications');
        $this->db->where('status', $status);
        $this->db->order_by('id DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_application($application_id) {
        $this->db->from('tbl_applications');
        $this->db->where('id', $application_id);
        $query = $this->db->get();
        $result = $query->row_array();
    
        return $result;
    } 

    public function update_application_status($application_id, $status) {
        // Get the application data.
        $application_data = $this->get_application($application_id);
    
        if (strpos($application_data['application_type'], 'Leave') !== false) {
            if ($status == 'Approved') {
                // Add a new leave row with the application data.
                $leave_data = array(
                    'application_id' => $application_id,
                    'staff_id' => $application_data['staff_id'],
                    'start_date' => $application_data['start_date'],
                    'end_date' => $application_data['end_date'],
                    'shift' => $application_data['shift'],
                    'reason' => $application_data['reason'],
                    'created_at' => $application_data['created_at'],
                );
                $this->db->insert('tbl_staff_leaves', $leave_data);
            } else {
                // Delete the leave row with the given application_id.
                $this->db->where('application_id', $application_id);
                $this->db->delete('tbl_staff_leaves');
            }
        }
    
        // Update the status.
        $this->db->where('id', $application_id);
        return $this->db->update('tbl_applications', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }
    
    public function delete_application($application_id) {

        $application_data = $this->get_application($application_id);
    
        if (strpos($application_data['application_type'], 'Leave') !== false) {
            // Delete the leave row with the given application_id.
            $this->db->where('application_id', $application_id);
            $this->db->delete('tbl_staff_leaves');
        }

        $this->db->where('id', $application_id);
        return $this->db->delete('tbl_applications');
    }

    public function get_leaves_count($staff_id, $type, $status)
    {
        $this->db->select('COUNT(*) as total_leaves');
        $this->db->from(db_prefix() . '_applications');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('application_type', $type);
        $this->db->where('status', $status);
        $this->db->where('YEAR(start_date)', 'YEAR(CURDATE())', false);
        $query = $this->db->get();
        return $query->result_array();
    }
      
    
    public function get_leaves($staff_id) {
        $this->db->where('staff_id', $staff_id);
        $this->db->order_by('id DESC');
        $query = $this->db->get(db_prefix() . '_staff_leaves');
        return $query->result_array();
    }

    public function insert_leave($staff_id, $reason, $start_date, $end_date, $shift) {
        $data = [
            'staff_id' => $staff_id,
            'reason' => $reason,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'created_at' => date('Y-m-d H:i:s'),
            'shift' => $shift
        ];
        $success = $this->db->insert(db_prefix() . '_staff_leaves', $data);
        $leave_id = $this->db->insert_id();
        return array('success' => $success, 'id' => $leave_id);
    }

    public function remove_leave($leave_id) {
        $this->db->where('id', $leave_id);
        return $this->db->delete(db_prefix() . '_staff_leaves');
    }

    public function get_shift_timings_of_date($date, $staff_id) {
        $this->db->select('*');
        $this->db->from(db_prefix() . '_staff_shifts');
        $this->db->where('month', date('m', strtotime($date)));
        $this->db->where('day', date('d', strtotime($date)));
        $this->db->where('staff_id', $staff_id);
        $query = $this->db->get();
        $result = $query->result_array();
    
        $shift_timings = [
            'first_shift' => ['start' => null, 'end' => null],
            'second_shift' => ['start' => null, 'end' => null]
        ];
    
        foreach ($result as $row) {
            if ($row['shift_number'] == 1) {
                $shift_timings['first_shift']['start'] = $row['shift_start_time'];
                $shift_timings['first_shift']['end'] = $row['shift_end_time'];
            } elseif ($row['shift_number'] == 2) {
                $shift_timings['second_shift']['start'] = $row['shift_start_time'];
                $shift_timings['second_shift']['end'] = $row['shift_end_time'];
            }
        }
    
        return $shift_timings;
    }
    
    
    

    public function is_on_leave($staff_id, $date) {
        
        $shift_timings = $this->get_shift_timings_of_date($date, $staff_id);

        if (empty($shift_timings) || ($shift_timings['first_shift']['start'] == "00:00:00" && $shift_timings['second_shift']['start'] == "00:00:00")) {
            return true;
        }else{
    
        $this->db->select('*');
        $this->db->from(db_prefix() . '_staff_leaves');
        $this->db->where('staff_id', $staff_id);
        $this->db->where("DATE(start_date) <= DATE('{$date}')");
        $this->db->where("DATE(end_date) >= DATE('{$date}')");
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $leave = $query->row_array();

            // Get the current time on the given date.
            $current_time = date('H:i:s', strtotime($date));
    
            // Check if the staff member is on leave during the 1st or 2nd shift.
            if ($leave['shift'] == '1') {
                if ($current_time >= $shift_timings['first_shift']['start'] && $current_time <= $shift_timings['first_shift']['end']) {
                    return true;
                }
            } elseif ($leave['shift'] == '2') {
                if ($current_time >= $shift_timings['second_shift']['start'] && $current_time <= $shift_timings['second_shift']['end']) {
                    return true;
                }
            } elseif ($leave['shift'] == 'all') {
                return true;
            }
        }
        
        return false;
        }
    }
    
    public function is_clocked_in($staff_id) {
        $current_entry = $this->db->where('staff_id', $staff_id)
                        ->where('clock_out IS NULL', null, false)
                        ->get(''.db_prefix().'_staff_time_entries')
                        ->row();
        if ($current_entry) {
            $clock_out = true;
        }else{
            $clock_out = false;
        }

        return $clock_out;
    }
    
    public function clock_out_and_set_leave_status($staff_id) {
        if($this->is_clocked_in($staff_id)){
            $this->clock_out($staff_id);
        }
        
        $this->update_status($staff_id, "Leave");
    }

    public function get_sum_afk_and_offline_times($staff_id, $clock_in_time, $clock_out_time) {
        $this->db->select_sum('TIMESTAMPDIFF(SECOND, start_time, end_time)', 'total_duration');
        $this->db->from(db_prefix() . '_staff_status_entries');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('start_time >=', $clock_in_time);
        $this->db->where('end_time <=', $clock_out_time);
        $this->db->where_in('status', ['afk', 'offline']);
        $query = $this->db->get();
        $result = $query->row();
    
        return $result->total_duration;
    }
    

    public function get_monthly_stats($staff_id, $month) {
        $current_month = $month;
        $current_year = date('Y');

        $monthly_total_clocked_time = 0;
        $monthly_shift_duration = 0;
        $punctuality_rate = 0;
    
        // Get shift timings
        $this->db->select('day, shift_start_time, shift_end_time');
        $this->db->from(db_prefix() . '_staff_shifts');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('month', $current_month);
        $shifts = $this->db->get()->result_array();
    
        // Group shifts by day
        $grouped_shifts = [];
        foreach ($shifts as $shift) {
            $day = $shift['day'];
            if (!isset($grouped_shifts[$day])) {
                $grouped_shifts[$day] = [];
            }

            $shift_start_time = date('h:i A', strtotime($shift['shift_start_time']));
            $shift_end_time = date('h:i A', strtotime($shift['shift_end_time']));


            $shift_start_time_plus_5_seconds = date('H:i:s', strtotime($shift['shift_start_time']) + 5);
            $shiftDateTime = date('Y-m-d H:i:s', strtotime($current_year.'-'.$current_month . '-' . $day . ' ' . $shift_start_time_plus_5_seconds));

            $shift_timings = [
                'start' => $shift_start_time,
                'end' => $shift_end_time,
                'is_shift_leave' => $this->is_on_leave($staff_id, $shiftDateTime) // Replace 'your_model' with the appropriate model name
            ];

            $grouped_shifts[$day][] =  $shift_timings;
        }
    
        // Get clock-in times
        $this->db->select('DATE(clock_in) as date, clock_in, clock_out');
        $this->db->from(db_prefix() . '_staff_time_entries');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('MONTH(clock_in)', $current_month);
        $this->db->where('YEAR(clock_in)', $current_year);
        $clock_ins = $this->db->get()->result_array();
    
        // Group clock-in times by day
        $grouped_clock = [];
        foreach ($clock_ins as $clock_in) {
            $day = date('j', strtotime($clock_in['date']));
            if (!isset($grouped_clock[$day])) {
                $grouped_clock[$day] = [];
            }
            $grouped_clock[$day]['in'][] = date('h:i A', strtotime($clock_in['clock_in']));
            if (isset($clock_in['clock_out'])) {
                $grouped_clock[$day]['out'][] = date('h:i A', strtotime($clock_in['clock_out']));
            }
            
        }
    
        // Combine shift timings and clock-in times for each day
        $data = [];
        for ($day = 1; $day <= date('t'); $day++) {
            $data[] = [
                'day' => $day,
                'shifts' => isset($grouped_shifts[$day]) ? $grouped_shifts[$day] : [],
                'clock' => isset($grouped_clock[$day]) ? $grouped_clock[$day] : [],
            ];
        }

        $total_shifts = 0;
        $sum_difference = 0;

        foreach ($data as &$stat) {
            $day = $stat['day'];
            $total_clock_in_time = 0;
            $total_shift_duration = 0;
            $total_task_time = 0;
    
            // Calculate total_clock_in_time
            $clock_ins = $stat['clock']['in'] ?? [];
            $clock_outs = $stat['clock']['out'] ?? [];
            for ($i = 0; $i < count($clock_ins); $i++) {

                $date_time = DateTime::createFromFormat('Y-m-d h:i A', $current_year . '-' . $current_month . '-' . $day . ' ' . $clock_ins[$i]);
                $clock_in_formatted = $date_time->format('Y-m-d H:i:s');

                if (isset($clock_outs[$i])) {
                    $date_time = DateTime::createFromFormat('Y-m-d h:i A', $current_year . '-' . $current_month . '-' . $day . ' ' . $clock_outs[$i]);
                    $clock_out_formatted = $date_time->format('Y-m-d H:i:s');
                } else {
                    $clock_out_formatted = date('Y-m-d H:i:s');
                }

                $sum_afk_offline_times = $this->team_management_model->get_sum_afk_and_offline_times($staff_id, $clock_in_formatted, $clock_out_formatted);

                $clock_in_time = strtotime($clock_in_formatted);
                $clock_out_time = strtotime($clock_out_formatted);

                if ($clock_out_time < $clock_in_time) {
                    $clock_out_time += 86400; // Add 24 hours (86400 seconds) to the clock_out_time.
                }

                $total_time = $clock_out_time - $clock_in_time;


                // Subtract the sum of AFK and Offline times from $total_time.
                $total_time -= $sum_afk_offline_times;

                $total_clock_in_time += $total_time;
            }
    
            // Calculate total_shift_duration
            foreach ($stat['shifts'] as $shift) {
                $duration = strtotime($shift['end']) - strtotime($shift['start']);
                $total_shift_duration += $duration;
            }
    
            // Calculate total_task_time
            // Assuming you have a function like `get_total_task_time_for_day` in your model
            $total_task_time = $this->get_total_task_time_for_day($staff_id, $day, $current_month, $current_year);
    
            $stat['total_clock_in_time'] = gmdate('H\h i\m', $total_clock_in_time);
            $stat['total_shift_duration'] = gmdate('H\h i\m', $total_shift_duration);
            $stat['total_task_time'] = gmdate('H\h i\m', $total_task_time);

    
            // Concatenate shift timings and clock-in times
            $shift_timings_string = '';

            if (isset($stat['shifts'])) {
                foreach ($stat['shifts'] as $shift) {

                    if($shift['is_shift_leave'] == true){
                        $shiftStr = '<span class="bg-amber-200/50 px-2">Shift Leave</span><br>';
                    }else{
                        $shiftStr = '<span>'.  $shift['start'] . ' - ' . $shift['end'] .'</span><br>';
                    }

                    $shift_timings_string .= $shiftStr;
                }
            }

            $stat['shift_timings'] = $shift_timings_string;


            $clock_times = [];
            for ($i = 0; $i < count($clock_ins); $i++) {
                if (isset($clock_ins[$i]) && isset($clock_outs[$i])) {
                    $clock_times[] = $clock_ins[$i] . ' - ' . $clock_outs[$i];
                }
            }
            
            $stat['clock_times'] = implode('<br> ', $clock_times);

            $monthly_total_clocked_time += $total_clock_in_time;
            $monthly_shift_duration += $total_shift_duration;

            $shift_timings = $stat['shifts'];
            $clock_in_times = isset($stat['clock']['in']) ? $stat['clock']['in'] : [];

            $total_shifts += count($clock_in_times);
            for ($i = 0; $i < $total_shifts; $i++) {
                if (isset($clock_in_times[$i]) && isset($shift_timings[$i]['start'])) {
                    $shift_start = strtotime($shift_timings[$i]['start']);
                    $clock_in_time = strtotime($clock_in_times[$i]);
                    $difference = abs($shift_start - $clock_in_time);
                    $sum_difference += $difference;
                }
                
            }

            $timestampDay = mktime(0, 0, 0, $current_month, $day, $current_year);
            $date = date('Y-m-d', $timestampDay);

            $stat['day_date'] = $date;
        }

        $max_acceptable_difference = 10 * 60 * 60;

        $average_difference = ($total_shifts != 0) ? $sum_difference / $total_shifts : 0;
        $punctuality = 100 * (1 - $average_difference / $max_acceptable_difference);
        $punctuality = max(0, $punctuality); // Ensure punctuality does not go below 0% 
        
        return [
            'data' => $data,
            'monthly_total_clocked_time' => $monthly_total_clocked_time,
            'monthly_shift_duration' => $monthly_shift_duration,
            'punctuality_rate' => sprintf("%.2f%%", $punctuality)
        ];
    }
    
    public function get_total_task_time_for_day($staff_id, $day, $month, $year) {
        $this->db->select('start_time, end_time');
        $this->db->from(db_prefix() . 'taskstimers');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('DAY(FROM_UNIXTIME(start_time))', $day);
        $this->db->where('MONTH(FROM_UNIXTIME(start_time))', $month);
        $this->db->where('YEAR(FROM_UNIXTIME(start_time))', $year);
        $query = $this->db->get();
    
        $task_timers = $query->result_array();
        $total_task_time = 0;

        $dateTime = new DateTime("now", new DateTimeZone(get_option('default_timezone')));
        $currentUnixTimestamp = $dateTime->getTimestamp();
    
        foreach ($task_timers as $timer) {
            $start_time = $timer['start_time'];
            $end_time = isset($timer['end_time']) ? $timer['end_time'] : $currentUnixTimestamp;
            $duration = $end_time - $start_time;
            $total_task_time += $duration;
        }
    
        return $total_task_time;
    }
    
    public function get_daily_stats($staff_id, $day, $month, $year) {
        $this->db->select('*');
        $this->db->from(db_prefix() . '_staff_time_entries');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('DAY(clock_in)', $day);
        $this->db->where('MONTH(clock_in)', $month);
        $this->db->where('YEAR(clock_in)', $year);
        $query = $this->db->get();
        $clock_ins_outs = $query->result_array();
    
        $this->db->select('*');
        $this->db->from(db_prefix() . '_staff_status_entries');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('DAY(start_time)', $day);
        $this->db->where('MONTH(start_time)', $month);
        $this->db->where('YEAR(start_time)', $year);
        $query = $this->db->get();
        $afk_and_offline = $query->result_array();
    
        $this->db->select('*');
        $this->db->from(db_prefix() . '_staff_shifts');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('day', $day);
        $this->db->where('month', $month);
        $query = $this->db->get();
        $shift_timings = $query->result_array();
    
        $this->db->select('t.id as task_id, t.name as task_name, p.id as project_id, p.name as project_name, tt.start_time, tt.end_time');
        $this->db->from(db_prefix() . 'taskstimers tt');
        $this->db->join(db_prefix() . 'tasks t', 'tt.task_id = t.id', 'left');
        $this->db->join(db_prefix() . 'projects p', 't.rel_id = p.id AND t.rel_type = "project"', 'left');
        $this->db->where('tt.staff_id', $staff_id);
        $this->db->where('DAY(FROM_UNIXTIME(tt.start_time))', $day);
        $this->db->where('MONTH(FROM_UNIXTIME(tt.start_time))', $month);
        $this->db->where('YEAR(FROM_UNIXTIME(tt.start_time))', $year);
        $query = $this->db->get();
        $task_timers = $query->result_array();

    
        // Calculate the total clocked in time, shift duration, and total task time
        $total_clocked_in_time = 0;
        $total_shift_duration = 0;
        $total_task_time = 0;
    
        // Calculate total_clocked_in_time and total_shift_duration
        foreach ($clock_ins_outs as $clock_in_out) {

            $clock_in_time = strtotime($clock_in_out['clock_in']);
            $clock_out_time = strtotime($clock_in_out['clock_out']);
            $total_time = $clock_out_time - $clock_in_time;

            $sum_afk_offline_times = $this->team_management_model->get_sum_afk_and_offline_times($staff_id, $clock_in_out['clock_in'], $clock_in_out['clock_out']);

            $total_time -= $sum_afk_offline_times;

            $total_clocked_in_time += $total_time;
        }
    
        // Calculate total_shift_duration
        foreach ($shift_timings as $shift_timing) {
            $shift_start_time = strtotime($shift_timing['shift_start_time']);
            $shift_end_time = strtotime($shift_timing['shift_end_time']);
            $shift_duration = $shift_end_time - $shift_start_time;
            $total_shift_duration += $shift_duration;
        }

        foreach ($afk_and_offline as &$entry) {
            $entry['start_time'] = date('h:i A', strtotime($entry['start_time']));
            $entry['end_time'] = date('h:i A', strtotime($entry['end_time']));
        
            $start_unix = strtotime($entry['start_time']);
            $end_unix = strtotime($entry['end_time']);
        
            if ($end_unix < $start_unix) {
                $end_unix += 86400; // Add 24 hours (86400 seconds) to the end_unix.
            }
        
            $duration_seconds = $end_unix - $start_unix;
        
            $hours = floor($duration_seconds / 3600);
            $minutes = floor(($duration_seconds % 3600) / 60);
        
            $duration_formatted = $hours . 'h ' . $minutes . 'm';
        
            $entry['duration'] = $duration_formatted;
        }        

        foreach ($task_timers as &$timer) {
            $duration_seconds = $timer['end_time'] - $timer['start_time'];
            $hours = floor($duration_seconds / 3600);
            $minutes = floor(($duration_seconds % 3600) / 60);

            $duration_formatted = $hours . 'h ' . $minutes . 'm';

            $timer['duration'] = $duration_formatted;
            $timer['start_time'] = date('g:i A', $timer['start_time']);
            $timer['end_time'] = date('g:i A', $timer['end_time']);
          }
          
    
        $total_task_time = $this->get_total_task_time_for_day($staff_id, $day, $month, $year);
    
        $daily_stats = [
            'clock_ins_outs' => $clock_ins_outs,
            'afk_and_offline' => $afk_and_offline,
            'shift_timings' => $shift_timings,
            'task_timers' => $task_timers,
            'total_clocked_in_time' => gmdate('H\h i\m', $total_clocked_in_time),
            'total_shift_duration' => gmdate('H\h i\m',$total_shift_duration),
            'total_task_time' => gmdate('H\h i\m',$total_task_time),
            ];

        return $daily_stats;
    
    }

    public function get_daily_report_data($month, $day)
    {
        $date = date('Y') . '-' . $month . '-' . $day;

        $report_data = [];

        // Total Loggable Hours
        $this->db->select_sum('TIMESTAMPDIFF(SECOND, shift_start_time, shift_end_time)', 'total_loggable_hours');
        $this->db->from(db_prefix() . '_staff_shifts');
        $this->db->where('month', date('m', strtotime($date)));
        $this->db->where('day', date('d', strtotime($date)));
        $this->db->where("NOT EXISTS (SELECT " . db_prefix() . "_staff_leaves.staff_id FROM " . db_prefix() . "_staff_leaves WHERE " . db_prefix() . "_staff_leaves.staff_id = " . db_prefix() . "_staff_shifts.staff_id AND " . db_prefix() . "_staff_leaves.start_date <= '" . $date . "' AND " . db_prefix() . "_staff_leaves.end_date >= '" . $date . "')");
        $query = $this->db->get();
        $report_data['total_loggable_hours'] = $query->row()->total_loggable_hours;


        
        // Actual Total Logged in Time
        $this->db->select('*');
        $query = $this->db->get(db_prefix() . 'staff');
        $all_staff_global = $query->result_array();
        
        $actual_total_logged_in_time = 0;

        $start_datetime = $date . ' 00:00:00';
        $end_datetime = $date . ' 23:59:59';

        $total_all_tasks = 0;
        $total_completed_tasks = 0;
        $total_tasks_rate = 0;
        
        foreach ($all_staff_global as &$staff) {
            $staff_id = $staff['staffid'];
        
            // Get total time within range
            $total_time = $this->get_total_time_within_range($staff_id, $start_datetime, $end_datetime);
            $staff['total_logged_in_time'] = $total_time;
            
            $actual_total_logged_in_time += $total_time;

            $tasks = $this->team_management_model->get_tasks_by_staff_member($staff_id);
            $total_tasks = 0;
            $completed_tasks = 0;
        
            foreach ($tasks as $task) {
                if (date('Y-m-d', strtotime($task->dateadded)) == $date) {
                    $total_tasks++;
                    if ($task->status == 5) {
                        $completed_tasks++;
                    }
                }else if($task->status != 5){
                    $total_tasks++;
                }else if (($task->status == 5 && date('Y-m-d', strtotime($task->datefinished)) == $today)){
                    $total_tasks++;
                    $completed_tasks++;
                }
            }
        
            $task_rate_percentage = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
            $task_rate = $completed_tasks . '/' . $total_tasks . ' (' . $task_rate_percentage . '%)';
            $staff['task_rate'] = $task_rate;

            $total_all_tasks += $total_tasks;
            $total_completed_tasks += $completed_tasks;
            

            $shift_timings = $this->get_shift_timings_of_date($date, $staff_id);
            $first_shift_seconds = strtotime($shift_timings['first_shift']['end']) - strtotime($shift_timings['first_shift']['start']);
            $second_shift_seconds = strtotime($shift_timings['second_shift']['end']) - strtotime($shift_timings['second_shift']['start']);
            $total_shift_seconds = $first_shift_seconds + $second_shift_seconds;
            // Add total_shift_timings to the staff array
            $staff['total_shift_timings'] = $total_shift_seconds;
        }

        $total_tasks_rate = $total_all_tasks > 0 ? round(($total_completed_tasks / $total_all_tasks) * 100) : 0;

        $report_data['total_all_tasks'] = $total_all_tasks;
        $report_data['total_completed_tasks'] = $total_completed_tasks;
        $report_data['total_tasks_rate'] = $total_tasks_rate;
        
        $report_data['actual_total_logged_in_time'] = $actual_total_logged_in_time;


        // Total Present Staff
        $this->db->select('COUNT(DISTINCT staff_id) as total_present_staff');
        $this->db->select('staff_id, firstname');
        $this->db->from(db_prefix() . '_staff_time_entries');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . '_staff_time_entries.staff_id');
        $this->db->where('DATE(clock_in)', $date);
        $this->db->group_by('staff_id');
        $query = $this->db->get();
        $report_data['total_present_staff'] = $query->num_rows(); // Get the total number of present staff
        $report_data['present_staff_list'] = $query->result_array(); // Get the list of present staff with their id and firstname

        
        $this->db->select('staffid, firstname');
        $this->db->from(db_prefix() . 'staff');
        $all_staff = $this->db->get()->result_array();
        $present_staff = $report_data['present_staff_list'];
        $staff_on_leave = $this->get_staff_on_leave($date);
        $present_staff_ids = array_column($present_staff, 'staff_id');
        $staff_on_leave_ids = array_column($staff_on_leave, 'staff_id');

        $absent_staff = array_filter($all_staff, function ($staff) use ($present_staff_ids, $staff_on_leave_ids) {
            return !in_array($staff['staffid'], $present_staff_ids) && !in_array($staff['staffid'], $staff_on_leave_ids);
        });

        $report_data['absentees'] = $absent_staff;


        // All Tasks Worked On
        $this->db->select('*, SUM(TIMESTAMPDIFF(SECOND, FROM_UNIXTIME(start_time), FROM_UNIXTIME(end_time))) as total_worked_time');
        $this->db->select('IF('.db_prefix().'tasks.rel_type="project", '.db_prefix().'projects.name, NULL) as project_name', false);
        $this->db->select('IF('.db_prefix().'tasks.rel_type="project", '.db_prefix().'projects.id, NULL) as project_id', false);
        $this->db->select(db_prefix().'tasks.name as task_name');
        $this->db->select(db_prefix().'tasks.status as task_status');
        $this->db->distinct();
        $this->db->from(db_prefix() . 'taskstimers');
        $this->db->where('DATE(FROM_UNIXTIME(start_time))', $date);
        $this->db->join(db_prefix() . 'tasks', db_prefix() . 'tasks.id = ' . db_prefix() . 'taskstimers.task_id');
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'tasks.rel_id AND '.db_prefix().'tasks.rel_type = "project"', 'left');
        $this->db->group_by(db_prefix() . 'taskstimers.task_id');
        $query = $this->db->get();
        $report_data['all_tasks_worked_on'] = $query->result_array();

        foreach ($report_data['all_tasks_worked_on'] as &$task) {
            $task['staff'] = $this->get_staff_members_for_task($task['task_id'], $date);
        }





        $report_data['late_joiners'] = $this->get_late_joiners($date);
        $report_data['on_timers'] = $this->get_on_timers($date);
        $report_data['staff_on_leave'] = $this->get_staff_on_leave($date);
        $report_data['most_clocked_in_staff_member'] = $this->get_most_clocked_in_staff_member($date);

        $report_data['all_staff'] = $all_staff_global;

        $maxTasksCompleted = $this->get_staff_with_most_tasks_completed_today($start_datetime, $end_datetime);

        if($maxTasksCompleted){
            $maxTasksCompleted = $maxTasksCompleted;
        }else{
            $maxTasksCompleted = null;
        }


        $report_data['most_eff_staff_member'] = $maxTasksCompleted;


        return $report_data;
    }

    public function get_staff_members_for_task($task_id, $date) {
        $this->db->select(db_prefix() . 'taskstimers.staff_id');
        $this->db->distinct();
        $this->db->from(db_prefix() . 'taskstimers');
        $this->db->where('task_id', $task_id);
        $this->db->where('DATE(FROM_UNIXTIME(start_time))', $date);
        $query = $this->db->get();
        return $query->result_array();
    }
    

    public function get_late_joiners($date) {
        $this->db->select('tbl_staff_time_entries.staff_id, tblstaff.firstname');
        $this->db->distinct();
        $this->db->from(db_prefix() . '_staff_time_entries');
        $this->db->join(db_prefix() . 'staff', 'tblstaff.staffid = tbl_staff_time_entries.staff_id');
        $this->db->join(db_prefix() . '_staff_leaves', 'tbl_staff_leaves.staff_id = tbl_staff_time_entries.staff_id', 'left');
        $this->db->where('DATE(tbl_staff_time_entries.clock_in)', $date);
        $this->db->where_not_in('tbl_staff_time_entries.staff_id', "SELECT staff_id FROM " . db_prefix() . "_staff_leaves WHERE start_date <= '$date' AND end_date >= '$date'", false);
    
        $this->db->where("(
            tbl_staff_time_entries.clock_in > (
                SELECT DATE_ADD(CONCAT_WS('-', YEAR('$date'), month, day, ' ', shift_start_time), INTERVAL 5 MINUTE) FROM " . db_prefix() . "_staff_shifts
                WHERE staff_id = tbl_staff_time_entries.staff_id
                AND month = MONTH('$date')
                AND day = DAY('$date')
                AND shift_number = 1
                LIMIT 1
            )
            OR tbl_staff_time_entries.clock_in > (
                SELECT DATE_ADD(CONCAT_WS('-', YEAR('$date'), month, day, ' ', shift_start_time), INTERVAL 5 MINUTE) FROM " . db_prefix() . "_staff_shifts
                WHERE staff_id = tbl_staff_time_entries.staff_id
                AND month = MONTH('$date')
                AND day = DAY('$date')
                AND shift_number = 2
                LIMIT 1
            )
        )", NULL, false);
    
        $query = $this->db->get();
        return $query->result_array();
    }
    
    
    
    
    
    
    
    
    
    public function get_on_timers($date) {
        $this->db->select('tbl_staff_shifts.staff_id, tblstaff.firstname, COUNT(tbl_staff_shifts.staff_id) as on_time_shifts');
        $this->db->from(db_prefix() . '_staff_time_entries');
        $this->db->join(db_prefix() . '_staff_shifts', 'tbl_staff_shifts.staff_id = tbl_staff_time_entries.staff_id');
        $this->db->join(db_prefix() . 'staff', 'tblstaff.staffid = tbl_staff_time_entries.staff_id');
        $this->db->where('DATE(tbl_staff_time_entries.clock_in)', $date);
        $this->db->where('tbl_staff_shifts.shift_start_time BETWEEN tbl_staff_time_entries.clock_in AND tbl_staff_time_entries.clock_out');
        $this->db->group_by('tbl_staff_shifts.staff_id, tblstaff.firstname');
        //$this->db->having('COUNT(tbl_staff_shifts.staff_id)', 2);
        $query = $this->db->get();
        return $query->result_array();
    }    
    
    public function get_staff_on_leave($date) {
        $this->db->select('tbl_staff_leaves.staff_id, tbl_staff_leaves.shift,tblstaff.firstname');
        $this->db->distinct();
        $this->db->from(db_prefix() . '_staff_leaves');
        $this->db->join(db_prefix() . 'staff', 'tblstaff.staffid = tbl_staff_leaves.staff_id');
        $this->db->where('start_date <=', $date);
        $this->db->where('end_date >=', $date);
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function get_most_clocked_in_staff_member($date) {
        $this->db->select('staff_id, tblstaff.firstname, SUM(TIMESTAMPDIFF(SECOND, clock_in, clock_out)) as total_time');
        $this->db->from(db_prefix() . '_staff_time_entries');
        $this->db->join(db_prefix() . 'staff', 'tblstaff.staffid = tbl_staff_time_entries.staff_id');
        $this->db->where('DATE(clock_in)', $date);
        $this->db->group_by('staff_id');
        $this->db->order_by('total_time', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_day_summary($date) {
        $this->db->where('date', $date);
        $query = $this->db->get(db_prefix() . '_day_summaries');
        return $query->row();
    }

    public function save_day_summary($date, $summary) {
        $day_summary = $this->get_day_summary($date);
        
        if ($day_summary) {
            $this->db->where('date', $date);
            return $this->db->update(db_prefix() . '_day_summaries', ['summary' => $summary, 'updated_at' => date('Y-m-d')]);
        } else {
            return $this->db->insert(db_prefix() . '_day_summaries', ['date' => $date, 'summary' => $summary, 'created_at' => date('Y-m-d')]);
        }
    }

    public function get_staff_time ($staff_id) {
        $this->db->where('staff_id', $staff_id);
        $this->db->from(db_prefix() . '_staff_time_entries');
        $this->db->order_by('id', 'desc');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function update_staff_time_entry($entry_id, $in_time, $out_time) {
        $data = [
            'clock_in' => $in_time,
            'clock_out' => $out_time
        ];

        $this->db->where('id', $entry_id);
        return $this->db->update('tbl_staff_time_entries', $data);
    }

    public function delete_staff_time_entry($entry_id) {
        $this->db->where('id', $entry_id);
        return $this->db->delete('tbl_staff_time_entries');
    }

    public function get_projects() {
        $this->db->select('tblprojects.*, COUNT(tbltasks.id) AS total_tasks, SUM(CASE WHEN tbltasks.status = 5 THEN 1 ELSE 0 END) AS completed_tasks');
        $this->db->from(db_prefix() . 'projects');
        $this->db->join(db_prefix() . 'tasks', 'tblprojects.id = tbltasks.rel_id AND tbltasks.rel_type = "project"', 'left');
        $this->db->where("tblprojects.status", "2");
        $this->db->group_by('tblprojects.id');
        $this->db->order_by("tblprojects.id", "DESC");
        $query = $this->db->get();
    
        return $query->result_array();
    }
    

    public function get_project_tasks($project_id) {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'tasks');
        $this->db->where('rel_id', $project_id);
        $this->db->where('rel_type', 'project');
        $this->db->order_by('datefinished', 'ASC');
        $this->db->order_by('priority', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_unassigned_staff($task_id) {
        $this->db->select('tblstaff.staffid, tblstaff.firstname, tblstaff.lastname');
        $this->db->from('tblstaff');
        $this->db->join('tbltask_assigned', 'tblstaff.staffid = tbltask_assigned.staffid AND tbltask_assigned.taskid = ' . $this->db->escape($task_id), 'left');
        $this->db->where('tbltask_assigned.taskid IS NULL', null, false);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function assign_staff_to_task($task_id, $staff_id, $assigned_from) {
        $data = [
            'taskid' => $task_id,
            'staffid' => $staff_id,
            'assigned_from' => $assigned_from
        ];
    
        $this->db->insert(db_prefix() . 'task_assigned', $data);
        return $this->db->affected_rows() > 0;
    }

    public function get_projects_with_task_counts() {
        $this->db->select('tblprojects.*, COUNT(tbltasks.id) AS total_tasks, SUM(CASE WHEN tbltasks.status = 5 THEN 1 ELSE 0 END) AS completed_tasks');
        $this->db->from(db_prefix() . 'tblprojects');
        $this->db->join(db_prefix() . 'tbltasks', 'tblprojects.id = tbltasks.rel_id AND tbltasks.rel_type = "project"', 'left');
        $this->db->group_by('tblprojects.id');
        $query = $this->db->get();
    
        return $query->result_array();
    } 

    public function add_dummy_task($project_id, $task_name)
    {
        $data = [
            'project_id' => $project_id,
            'name' => $task_name,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert(db_prefix() . '_dummy_tasks', $data);

        return $this->db->insert_id();
    }

    public function get_dummy_tasks_by_project($project_id)
    {
        $this->db->where('project_id', $project_id);
        $query = $this->db->get(db_prefix() . '_dummy_tasks');
        return $query->result_array();
    }


    public function get_tasks_by_staff_member($staff_id)
    {
        $this->db->select('tbltasks.*, GROUP_CONCAT(tbltask_assigned.staffid) as assignees');
        $this->db->from('tbltasks');
        $this->db->join('tbltask_assigned', 'tbltasks.id = tbltask_assigned.taskid');
        $this->db->where('tbltask_assigned.staffid', $staff_id);
        $this->db->group_by('tbltasks.id');
        $query = $this->db->get();

        return $query->result();
    }


    public function assign_task_to_dummy_task($taskId, $dummyTaskId)
    {
        $this->db->set('task_id', $taskId);
        $this->db->where('id', $dummyTaskId);
        return $this->db->update(db_prefix() . '_dummy_tasks');
    }

    public function fetch_task_details($taskId) {
        $this->db->select('tbltasks.name as task_name, tblstaff.staffid as assigned_user, tbltasks.id as task_id, tbltasks.status as status')
            ->from('tbltasks')
            ->join('tbltask_assigned', 'tbltask_assigned.taskid = tbltasks.id', 'left')
            ->join('tblstaff', 'tblstaff.staffid = tbltask_assigned.staffid', 'left')
            ->where('tbltasks.id', $taskId)
            ->limit(1);
    
        $query = $this->db->get();
        return $query->row_array();
    }    

    public function delete_dummy_task($dummy_task_id)
    {
        $this->db->where('id', $dummy_task_id);
        return $this->db->delete('tbl_dummy_tasks');
    }

    public function get_staff_summary($staff_id) {
        $today = date('Y-m-d');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('date', $today);
        $query = $this->db->get('tbl_staff_summaries');
      
        return $query->row();
    }
      
    public function save_staff_summary($staff_id, $summary) {
        $today = date('Y-m-d');
        $this->db->where('staff_id', $staff_id);
        $this->db->where('date', $today);
        $query = $this->db->get('tbl_staff_summaries');
      
        if ($query->num_rows() > 0) {
          // Update the summary
          $data = array('summary' => $summary);
          $this->db->where('staff_id', $staff_id);
          $this->db->where('date', $today);
          $this->db->update('tbl_staff_summaries', $data);
        } else {
          // Insert the summary
          $data = array(
            'staff_id' => $staff_id,
            'summary' => $summary,
            'date' => $today
          );
          $this->db->insert('tbl_staff_summaries', $data);
        }
      }
      

    public function get_staff_summaries($date) {
        // Fetch summaries from the database
        $this->db->select('tbl_staff_summaries.staff_id, tbl_staff_summaries.summary, tblstaff.firstname, tblstaff.lastname');
        $this->db->from('tbl_staff_summaries');
        $this->db->join('tblstaff', 'tblstaff.staffid = tbl_staff_summaries.staff_id', 'left');
        $this->db->where('tbl_staff_summaries.date', $date);
        $query = $this->db->get();
    
        // Convert the result set to an associative array
        $summaries = array();
        foreach ($query->result() as $row) {
            $summaries[$row->staff_id] = array(
                'staffid' => $row->staff_id,
                'summary' => $row->summary,
                'staff_name' => $row->firstname . ' ' . $row->lastname
            );
        }
    
        return $summaries;
    }

    public function get_all_staff_google_chat() {
        $this->db->select('tbl_staff_google_chat.staff_id, tbl_staff_google_chat.google_chat_user_id, tblstaff.firstname, tblstaff.lastname, tblstaff.staffid');
        $this->db->from('tblstaff');
        $this->db->join('tbl_staff_google_chat', 'tblstaff.staffid = tbl_staff_google_chat.staff_id', 'left');
        $staff = $this->db->get()->result_array();
        return $staff;
    }
    
    public function update_or_insert_google_chat_id($staff_id, $google_chat_user_id) {
        $data = array(
           'staff_id' => $staff_id,
           'google_chat_user_id' => $google_chat_user_id
        );
    
        $this->db->where('staff_id', $staff_id);
        $query = $this->db->get('tbl_staff_google_chat');
    
        if ($query->num_rows() > 0) {
            // A record does exist, so update it.
            $this->db->where('staff_id', $staff_id);
            $this->db->update('tbl_staff_google_chat', $data);
        } else {
            // No record exists, so insert a new one.
            $this->db->insert('tbl_staff_google_chat', $data);
        }
    }

    public function get_today_shift_timings() {
        $this->db->where('day', date('j')); // today's day
        $this->db->where('month', date('n')); // current month
        $shifts = $this->db->get('tbl_staff_shifts')->result_array();
        return $shifts;
    }
    
    
    

        
    
}
