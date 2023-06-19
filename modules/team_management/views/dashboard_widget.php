<?php 
function formatTime($seconds) {
    $hours = floor($seconds / 3600);
    $seconds %= 3600;
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;

    return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' .
           str_pad($minutes, 2, '0', STR_PAD_LEFT) . ':' .
           str_pad($remainingSeconds, 2, '0', STR_PAD_LEFT);
}

?>

<style>
.dot-container {
  transition: all 0.2s ease;
}

.dot {
  transition: all 0.2s ease;
}

.dot:hover + .info {
  display: block;
}

.info {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  white-space: nowrap;
}

</style>

<div class="bg-white shadow rounded-lg p-4 my-4 flex md:flex-row flex-col justify-between">

<div class="md:w-3/5 w-full">

    <div class="flex items-center md:flex-row flex-col">
        <?php echo staff_profile_image($GLOBALS['current_user']->staffid, ['h-full', 'w-32' , 'object-cover', 'md:mr-4' , 'md:ml-0 mx-auto self-start' , 'staff-profile-image-thumb'], 'thumb') ?>
        <div class="flex flex-col gap-1 md:items-start items-center">

            <div class="text-xl font-semibold flex flex-row justify-between">

                <div class="flex items-center">Hi, <?php echo $GLOBALS['current_user']->firstname; ?>! 👋</div>                    

            </div>
            <p class="text-lg">Welcome to your dashboard.</p>

            <div class="w-fit flex flex-row justify-between border border-slate-300 border-double pl-2 text-lg rounded shadow-md transition-all hover:shadow-none">

                <div class="pr-2">Status: </div>

                <select class="px-2 bg-transparent text-lime-500 appearance-none cursor-pointer " onchange="statusSelectColors(this);" id="status">
                    <option id="Online" value="Online" class="text-lime-500">Online</option>
                    <option id="AFK" value="AFK" class="text-blue-500">AFK</option>
                    <option id="Offline" value="Offline" class="text-pink-500">Offline</option>
                    <option id="Leave" value="Leave" class="text-amber-600">Leave</option>
                </select>


            </div>

            <div class="my-2" id="shiftInfo">Upcoming Shift: </div>

            <div class="flex flex-row gap-2">
                <button class="px-2 py-1 text-base bg-blue-600 rounded text-white transition-all shadow-lg hover:shadow-none" id="clock-in">Clock in</button>
                <button class="px-2 py-1 text-base bg-blue-600 rounded text-white transition-all shadow-lg hover:shadow-none" id="clock-out">Clock Out</button>
            </div>


        </div>
    </div>
</div>

<div class="md:w-2/5 w-full flex flex-col md:text-right text-center md:mt-0 mt-5 justify-between">

    <div>
        <h2 class="text-xl font-semibold" id="live-timer">
                <?php //echo formatTime($stats->total_time); ?>
        </h2>
    </div>

    <div class="flex flex-col gap-2">
        <h3 class="text-md">Today: <span id="today-timer"></span></h3>
        <h3 class="text-md">Yesterday: <span id="yesterday-timer"></span></h3>
        <h3 class="text-md">This Week: <span id="current-week-timer"></span></h3>
        <h3 class="text-md">Last Week: <span id="last-week-timer"></span></h3>
    </div>


</div>

</div>

<div class="flex lg:flex-row flex-col justify-between w-full bg-white shadow rounded-lg">
    <div class="lg:w-1/2 w-full transition-all hover:shadow-sm rounded overflow-hidden p-8 xl:pr-20 md:pl-10">
        <div class="uppercase tracking-wide text-xl text-center text-gray-700 font-bold">Today Tasks</div>
        <div class="flex flex-col h-full">
            <div class="p-1 flex flex-col mt-4 gap-5 h-full">
                
                <div class="text-gray-500 text-center flex flex-col justify-center gap-2" id="user-<?= $GLOBALS['current_user']->staffid?>-task-list">
                <?php
                    $today = date('Y-m-d');
                    $tasks = $this->team_management_model->get_tasks_by_staff_member($GLOBALS['current_user']->staffid);
                    $total_tasks = 0;
                    $completed_tasks = 0;                     
                    foreach ($tasks as $task) {


                        if (
                            (date('Y-m-d', strtotime($task->startdate)) == $today) 
                            || 
                            ($task->status != 5)
                            )
                        
                        {
                            
                            $total_tasks++;
                            if ($task->status == 5) {
                                $completed_tasks++;
                            }

                            if($task->status == 5){
                                $taskBG = 'bg-green-100';
                                $compDisabled = 'disabled';
                            }else{
                                ((date('Y-m-d', strtotime($task->dateadded)) != $today) && $task->status != 5) ? $taskBG = 'bg-rose-100' : $taskBG= 'bg-gray-100';
                                $compDisabled = '';
                            }

                            $assignees = explode(',', $task->assignees);
                            echo '
                            <div class="flex flex-row justify-between items-center px-4 py-2 '.$taskBG.' rounded-lg">
                                <a onclick="init_task_modal(' . $task->id . '); return false" href="#" class="text-sm text-left">' . $task->id . ' :: ' . $task->name . '</a>';
                
                                if (in_array($this->session->userdata('staff_user_id'), $assignees)) {
                                        echo '
                                        <button '.$compDisabled.' onclick="mark_complete(' . $task->id . '); this.parentElement.style.background= `#dcf7db`; this.disabled = true;updateTaskValues(`' . $GLOBALS['current_user']->staffid . '`,false,true); return false;" class="bg-green-500 text-white rounded-full px-[8px] py-1"><i class="fas fa-check"></i></button>';
                                }
                    
                            echo '</div>';
                        }
                    }
                    $percentage = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
                ?>
                
                </div>
                <div class="mt-auto uppercase tracking-wide text-xl text-gray-700 font-bold flex flex-row justify-between items-center">
                    <div class="mt-2">Rate:</div>
                    <div>
                        <span id="user-<?= $GLOBALS['current_user']->staffid?>-rate-p"><?= $percentage ?></span>%
                        (<span id="user-<?= $GLOBALS['current_user']->staffid?>-rate-c"><?= $completed_tasks ?></span>/<span id="user-<?= $GLOBALS['current_user']->staffid?>-rate-a"><?= $total_tasks ?></span>)
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="lg:w-1/2 w-full bg-white border-l border-gray-200 flex flex-col p-8 xl:pl-20 md:pl-10">
        <div class="uppercase tracking-wide text-xl text-center text-gray-700 font-bold">Summary</div>
        <div class="px-4 py-6 flex-grow">
            <textarea id="summary-textarea" class="w-full h-full p-3 bg-gray-100/20 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none" placeholder="Write your message here..."></textarea>
        </div>
        <div class="px-4 pb-4">
            <button onclick="getOrSaveStaffSummary(document.getElementById('summary-textarea').value)" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">Add/Edit</button>
        </div>
    </div>
</div>

<div class="bg-white shadow rounded-lg p-4 my-4 flex md:flex-row flex-col justify-between">

    <div id="timeline-wrapper" class="relative w-full">
        <!-- The timeline will be attached to this div -->
    </div>

</div>

<div class="bg-white shadow rounded-lg p-4 my-4 flex md:flex-row flex-col justify-between">
    <div id="clocks" class="p-4 rounded-lg space-y-2 text-lg flex flex-col gap-2"></div>
</div>