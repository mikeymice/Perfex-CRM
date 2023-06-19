<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); 
//function secondsToReadableTime($seconds) {
//    $hours = floor($seconds / 3600);
//    $minutes = floor(($seconds % 3600) / 60);
//
//    return "{$hours}h {$minutes}m";
//}
?>

<div id="wrapper" class="wrapper">
    <div class="content flex flex-col">

        <div class="flex flex-col gap-4 rounded">
        
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex flex-row justify-between">
                    <h2 class="text-2xl font-semibold mb-4">Stats of <?= date("F", mktime(0, 0, 0, $month_this, 1)); ?> of <?= $staff_name_this ?></h2>

                    <div class="">
                        <select onchange="location = this.value;" class="block appearance-none w-full bg-white  border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                            <option value="#">Select Month</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/1">January</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/2">February</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/3">March</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/4">April</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/5">May</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/6">June</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/7">July</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/8">August</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/9">September</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/10">October</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/11">November</option>
                            <option value="<?= admin_url().'team_management/staff_stats/'.$staff_id_this;?>/12">December</option>
                        </select>
                    </div>
                </div>

                


                <div class="flex flex-col space-y-4">
                    <!-- Summed up stats -->
                    <div class="grid grid-cols-3 gap-6 mb-6">
                        <div class="bg-blue-100 rounded-lg p-4 shadow">
                            <h3 class="text-lg font-semibold mb-2">Total Clocked In Time</h3>
                            <p class="text-xl font-bold"><?= secondsToReadableTime($monthly_total_clocked_time) ?></p>
                        </div>
                        <div class="bg-green-100 rounded-lg p-4 shadow">
                            <h3 class="text-lg font-semibold mb-2">Total Shift Durations</h3>
                            <p class="text-xl font-bold"><?= secondsToReadableTime($monthly_shift_duration) ?></p>
                        </div>
                        <div class="bg-yellow-100 rounded-lg p-4 shadow">
                            <h3 class="text-lg font-semibold mb-2">Punctuality Rate</h3>
                            <p class="text-xl font-bold"><?= $punctuality_rate; ?></p>
                        </div>
                    </div>

                    <!-- Monthly table -->
                    <div class="overflow-auto">
                    <table class="min-w-full divide-y divide-gray-200 shadow-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift Timings</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Times Clocked In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Time Clocked In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Shift Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Time on Tasks</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($monthly_stats as $stat): ?>
                                <tr class="hover:bg-gray-100 transition-all">
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $stat['day_date'] ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $stat['shift_timings'] ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $stat['clock_times'] ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $stat['total_clock_in_time'] ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $stat['total_shift_duration'] ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $stat['total_task_time'] ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded" onclick="fetchDailyInfo(<?= $stat['day'] ?>)">Fetch Info</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mb-8" id="stats-per-day">
                <div class="flex flex-col space-y-4">
                    
                    <!-- Stats per day section -->
                    <div class="bg-white p-6 rounded-lg">
                    <h2 class="text-2xl font-semibold mb-8">Stats Per Day <span id="stats_daily_title">:: none selected!</span></h2>

                    <!-- Stats cards -->
                    <div class="grid grid-cols-3 gap-6 mb-10">
                        <div class="bg-blue-100 rounded-lg p-4 shadow">
                        <h3 class="text-lg font-semibold mb-2">Total Clocked In Time</h3>
                        <p class="text-xl font-bold" id="total_clock_in_time_day"><!-- Total clocked in time value --></p>
                        </div>
                        <div class="bg-green-100 rounded-lg p-4 shadow">
                        <h3 class="text-lg font-semibold mb-2">Total Shift Durations</h3>
                        <p class="text-xl font-bold" id="total_shift_duration"><!-- Total shift durations value --></p>
                        </div>
                        <div class="bg-yellow-100 rounded-lg p-4 shadow">
                        <h3 class="text-lg font-semibold mb-2">Total Time on Tasks</h3>
                        <p class="text-xl font-bold" id="total_task_time"><!-- Total time on tasks value --></p>
                        </div>
                    </div>

                    <!-- Additional stats -->

                    <div class="mb-10">
                        <h3 class="text-lg font-semibold mb-2">Task Timer Activity</h3>
                        <!-- Task timer activity table -->
                        <table class="min-w-full divide-y divide-gray-200 shadow-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Task</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Start Time</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">End Time</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="tbl_tasks_activity">
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mb-6">

                        <div>

                        <h3 class="text-lg font-semibold mb-2">AFK Time</h3>
                        <p class="text-xl font-bold"><!-- Total AFK time value --></p>
                        <!-- AFK time table -->
                        
                        <table class="min-w-full divide-y divide-gray-200 shadow-sm mb-6">
                            <thead class="bg-gray-50">
                                <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Start Time</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">End Time</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                </tr>
                            </thead>
                            <tbody id="afk_time_table" class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>

                        </div>
                        
                        <div>

                        <h3 class="text-lg font-semibold mb-2">Offline Time</h3>
                        <p class="text-xl font-bold"><!-- Total offline time value --></p>
                        <!-- Offline time table -->
                        <!-- Add offline time table here -->
                        
                        <table class="min-w-full divide-y divide-gray-200 shadow-sm mb-6">
                            <thead class="bg-gray-50">
                                <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Start Time</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">End Time</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                </tr>
                            </thead>
                            <tbody id="offline_time_table" class="bg-white divide-y divide-gray-200">
                                <!-- Add offline time entries here -->
                            </tbody>
                        </table>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Leave Status</h3>
                        <p class="text-xl font-bold" id="on_leave"><!-- Leave status value --></p>
                    </div>
                    </div>

                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col space-y-4">
                    <div class="bg-white p-6 rounded-lg">
                        <h2 class="text-2xl font-semibold mb-8">Leave Counts</span></h2>

                        <!-- Stats cards -->
                        <div class="grid grid-cols-3 gap-6 mb-10">

                            <div class="bg-blue-100 rounded-lg p-4 shadow">

                                <h3 class="text-xl font-bold mb-2">Paid Leaves:</h3>
                                <div class="text-lg font-semibold flex xl:flex-row flex-col justify-between"><div>Pending: <?= $pen_paid_no ?></div><div>Approved: <?= $app_paid_no ?></div><div>Disapproved: <?= $dis_paid_no ?></div></div>

                            </div>
                            <div class="bg-green-100 rounded-lg p-4 shadow">
                                <h3 class="text-xl font-bold mb-2">Unpaid Leaves:</h3>
                                <div class="text-lg font-semibold flex xl:flex-row flex-col justify-between"><div>Pending: <?= $pen_unpaid_no ?></div><div>Approved: <?= $app_unpaid_no ?></div><div>Disapproved: <?= $dis_unpaid_no ?></div></div>
                            </div>
                            <div class="bg-yellow-100 rounded-lg p-4 shadow">
                                <h3 class="text-xl font-bold mb-2">Gazetted Leaves:</h3>
                                <div class="text-lg font-semibold flex xl:flex-row flex-col justify-between"><div>Pending: <?= $pen_gaz_no ?></div><div>Approved: <?= $app_gaz_no ?></div><div>Disapproved: <?= $dis_gaz_no ?></div></div>
                            </div>
                        </div>
                        
                        <div class="overflow-auto">
                        <table class="min-w-full divide-y divide-gray-200 shadow-lg">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($all_applications as $stat): ?>
                                
                                <tr class="hover:bg-gray-100 transition-all">
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $stat->id ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $stat->application_type ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $stat->status ?></td>
                                    <td class="text-center px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $stat->start_date.'<br> to <br>'.$stat->end_date ?></td>
                                    <td class="max-w-[35%]  px-4 py-2 whitespace text-sm text-gray-500"><?= $stat->reason ?></td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $stat->created_at ?></td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<?php init_tail(); ?>
<script>
function fetchDailyInfo(day) {
    const staff_id = <?= $staff_id_this; ?> // Replace with the actual staff ID
    const currentDate = new Date();
    const month = <?= $month_this; ?>;
    const year = currentDate.getFullYear();
    $.ajax({
        url: admin_url + 'team_management/fetch_daily_info/',
        type: 'POST',
        data: {
            staff_id: staff_id,
            day: day,
            month: month,
            year: year,
            [csrfData.token_name]: csrfData.hash,
        },
        dataType: 'json',
        success: function (data) {
            console.log(data);

            $("#stats_daily_title").html(" :: " + day + "/" + month + "/" + <?= date('Y') ?>);

            $('#total_clock_in_time_day').html(data.total_clocked_in_time);
            $('#total_shift_duration').html(data.total_shift_duration);
            $('#total_task_time').html(data.total_task_time);
            $('#on_leave').html(data.on_leave ? 'Yes' : 'No');

            const afk_entries = data.afk_and_offline.filter(entry => entry.status === 'AFK');
            const offline_entries = data.afk_and_offline.filter(entry => entry.status === 'Offline');

            const afk_rows = generateStatusRows(afk_entries);
            const offline_rows = generateStatusRows(offline_entries);
            const tasks_rows = generateTasksRows(data.task_timers);

            (afk_rows != "") ? $('#afk_time_table').html(afk_rows) : $('#afk_time_table').html("<tr><td colspan='3' class='px-4 py-2'>No Data</td></tr>");
            
            (offline_rows != "") ? $('#offline_time_table').html(offline_rows) : $('#offline_time_table').html("<tr><td colspan='3' class='px-4 py-2'>No Data</td></tr>");

            (tasks_rows != "") ? $('#tbl_tasks_activity').html(tasks_rows) : $('#tbl_tasks_activity').html("<tr><td colspan='4' class='px-4 py-2'>No Data</td></tr>");

            
            var targetDiv = $('#stats-per-day'); // Replace 'your-target-div-id' with the actual div id
            $('html, body').animate({
                scrollTop: targetDiv.offset().top
            }, 1000);

        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error fetching daily stats:', textStatus, errorThrown);
        }
    });
}

function generateStatusRows(entries) {
    let rows = '';
    entries.forEach(entry => {
        rows += `
        <tr>
            <td class="px-4 py-2">${entry.start_time}</td>
            <td class="px-4 py-2">${entry.end_time}</td>
            <td class="px-4 py-2">${(entry.duration)}</td>
        </tr>`;
    });
    return rows;
}

function generateTasksRows(entries) {
    let rows = '';
    entries.forEach(entry => {
        if(entry.project_id != null){
            rows += `
            <tr>
                <td class="px-4 py-2 flex flex-col">
                    <a target="_blank" href="<?= admin_url(); ?>tasks/${entry.task_id}" class="text-sm">${entry.task_name}</a>
                    <a target="_blank" href="<?= admin_url(); ?>projects/${entry.project_id}" class="text-xs">${entry.project_name}</a>
                </td>
                <td class="px-4 py-2">${entry.start_time}</td>
                <td class="px-4 py-2">${entry.end_time}</td>
                <td class="px-4 py-2">${(entry.duration)}</td>
            </tr>
            `;
        }else{
            rows += `
            <tr>
                <td class="px-4 py-2 flex flex-col">
                    <a target="_blank" href="<?= admin_url(); ?>tasks/${entry.task_id}" class="text-sm">${entry.task_name}</a>
                </td>
                <td class="px-4 py-2">${entry.start_time}</td>
                <td class="px-4 py-2">${entry.end_time}</td>
                <td class="px-4 py-2">${(entry.duration)}</td>
            </tr>
            `;
        }
        
    });
    return rows;
}

</script>
</body>
</html>