<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); 

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

function formatShift($shiftNumer)
{
    if($shiftNumer == "1")
        return "1st Shift";
    else if ($shiftNumer == "2")
        return "2nd Shift";
    else
        return "All Day";
}


?>

<div id="wrapper" class="wrapper">

<div class="container mx-auto px-4 py-6">
    <div class="flex flex-row justify-between mb-6">
        <h1 class="text-3xl font-semibold">Daily Reports</h1>
        <div class="max-w-sm flex flex-row gap-2">
            <input type="date" id="date-input" class="block appearance-none w-full bg-white  border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
            <button onclick="changeReport();" class="bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded shadow">Search</button>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">
        <!-- Total Loggable Hours -->
        <div class="bg-gradient-to-br from-red-600 to-orange-600 shadow rounded p-4 text-white">
            <h2 class="text-xl font-semibold mb-2">Total Loggable Hours</h2>
            <p class="text-2xl"><?= convertSecondsToRoundedTime($report_data['total_loggable_hours']) ?></p>
        </div>

        <!-- Actual Total Logged in Time -->
        <div class="bg-gradient-to-br from-pink-600 to-purple-600 shadow rounded p-4 text-white">
            <h2 class="text-xl font-semibold mb-2">Actual Logged in Time</h2>
            <p class="text-2xl"><?= convertSecondsToRoundedTime($report_data['actual_total_logged_in_time']) ?> </p>
        </div>

        <!-- Total Present Staff -->
        <div class="bg-gradient-to-br from-blue-600 to-teal-600 shadow rounded p-4 text-white">
            <h2 class="text-xl font-semibold mb-2">Total Present Staff :: <?= $report_data['total_present_staff'] ?></h2>
            <div class="flex flex-row gap-2 overflow-x-auto">
                <?php if (!empty($report_data['present_staff_list'])): ?>
                    <?php foreach ($report_data['present_staff_list'] as $staff): ?>
                        <div title="<?= $staff['firstname'] ?>" data-toggle="tooltip" data-placement="top">
                            <?= staff_profile_image($staff['staff_id'], ['border-2 border-solid object-cover w-12 h-12 staff-profile-image-thumb'], 'thumb'); ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No staff member found</p>
                <?php endif; ?>
            </div>

        </div>
        
        <!-- Task Completion Rate -->
        <div class="bg-gradient-to-br from-teal-600 to-green-500 shadow rounded p-4 text-white">

            <h2 class="text-xl font-semibold mb-2">Task Rates</h2>
            <div class="text-2xl">

            <?= $report_data['total_completed_tasks'] ?> / <?= $report_data['total_all_tasks'] ?> (<?= $report_data['total_tasks_rate'] ?>%)


            </div>
        </div>

        <!-- On Timers -->
        <div class="bg-gradient-to-br from-yellow-500 to-orange-500 shadow rounded p-4 text-white">

            <h2 class="text-xl font-semibold mb-2">On Timers</h2>
            <div class="flex flex-wrap gap-2">

            <?php if (!empty($report_data['on_timers'])): ?>
                <?php foreach ($report_data['on_timers'] as $comer) : ?>
                    <div title="<?= $comer['firstname'] ?>" data-toggle="tooltip" data-placement="top">
                        <?= staff_profile_image($comer['staff_id'], ['border-2 border-solid object-cover w-12 h-12 staff-profile-image-thumb'], 'thumb'); ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No on-timers</p>
            <?php endif; ?>


            </div>
        </div>

        <!-- Late Joiners -->
        <div class="bg-gradient-to-br from-orange-600 to-red-500 shadow rounded p-4 text-white">
            <h2 class="text-xl font-semibold mb-2">Late Joiners</h2>
            <div class="flex flex-row gap-2 overflow-x-auto">

            <?php if (!empty($report_data['late_joiners'])): ?>
                <?php foreach ($report_data['late_joiners'] as $late_joiner) : ?>
                    <div title="<?= $late_joiner['firstname'] ?>" data-toggle="tooltip" data-placement="top">
                        <?= staff_profile_image($late_joiner['staff_id'], ['border-2 border-solid object-cover w-12 h-12 staff-profile-image-thumb'], 'thumb'); ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No late comers</p>
            <?php endif; ?>

            </div>
        </div>

        <!-- Total Absentees -->
        <div class="bg-gradient-to-br from-green-500 to-blue-600 shadow rounded p-4 text-white">
            <h2 class="text-xl font-semibold mb-2">Total Absentees :: <?= count($report_data['absentees']); ?></h2>
            <div class="flex flex-row gap-2 overflow-x-auto">
                <?php if (!empty($report_data['absentees'])): ?>
                    <?php foreach ($report_data['absentees'] as $absentee): ?>
                        <div title="<?= $absentee['firstname'] ?>" data-toggle="tooltip" data-placement="top">
                            <?= staff_profile_image($absentee['staffid'], ['border-2 border-solid object-cover w-12 h-12 staff-profile-image-thumb'], 'thumb'); ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No absentees</p>
                <?php endif; ?>
            </div>

        </div>
        

        <!-- On Leave -->
        <div class="bg-gradient-to-br from-indigo-600 to-purple-500 shadow rounded p-4 text-white">
            <h2 class="text-xl font-semibold mb-2">On Leave</h2>
            <div class="flex flex-wrap gap-2">
            <?php if (!empty($report_data['staff_on_leave'])): ?>
                <?php foreach ($report_data['staff_on_leave'] as $comer) :;?>
                    
                    <div title="<?= $comer['firstname'] ?> | <?= formatShift($comer['shift']) ?>" data-toggle="tooltip" data-placement="top">
                        <?= staff_profile_image($comer['staff_id'], ['border-2 border-solid object-cover w-12 h-12 staff-profile-image-thumb'], 'thumb'); ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No staff on leave</p>
            <?php endif; ?>

            </div>
        </div>

        

        <!-- Most Clocked In Staff Member -->
        <div class="bg-gradient-to-br from-amber-500 to-yellow-500 shadow rounded p-4 text-white">

            <h2 class="text-xl font-semibold mb-2">Most Clocked In Staff Member</h2>

            <?php if (!empty($report_data['most_clocked_in_staff_member'])): ?>

                <div class="text-2xl flex align-center justify-between">
                    <div class="my-auto"><?= $report_data['most_clocked_in_staff_member']['firstname'] ?></div>
                    <?= staff_profile_image($report_data['most_clocked_in_staff_member']['staff_id'], ['border-2 border-solid object-cover w-12 h-full staff-profile-image-thumb'], 'thumb'); ?>
                </div>

            <?php else: ?>
                <p>No staff member found</p>
            <?php endif; ?>

        </div>

        <!-- Most Efficient Staff Member -->
        <div class="bg-gradient-to-br from-pink-500 to-indigo-500 shadow rounded p-4 text-white">
            <h2 class="text-xl font-semibold mb-2">Most Efficient Staff Member</h2>
            <?php if (!empty($report_data['most_eff_staff_member'])): ?>

            <div class="text-2xl flex align-center justify-between">
                <div class="my-auto"><?= $report_data['most_eff_staff_member']->firstname ?></div>
                <?= staff_profile_image($report_data['most_eff_staff_member']->staffid, ['border-2 border-solid object-cover w-12 h-full staff-profile-image-thumb'], 'thumb'); ?>
            </div>

            <?php else: ?>
            <p>No staff member found</p>
            <?php endif; ?>
        </div>

        <!-- All Staff Members -->
        <div class="bg-white shadow rounded p-4 col-span-2">
            <h2 class="text-xl font-semibold mb-4">Staff Members</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-sm font-medium text-gray-700">
                            <th class="px-4 py-2 border-b-2 border-gray-200">Name</th>
                            <th class="px-4 py-2 border-b-2 border-gray-200">Shift</th>
                            <th class="px-4 py-2 border-b-2 border-gray-200">Work</th>
                            <th class="px-4 py-2 border-b-2 border-gray-200">Task Rate</th>
                            <th class="px-4 py-2 border-b-2 border-gray-200">Summary</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200 text-sm text-gray-600">
                    
                    <?php foreach ($report_data['all_staff'] as $staff): ?>
                        <tr class="border-solid border-b border-gray-200">
                            <td class="border px-4 py-2 flex flex-row gap-2 items-center">
                                <?= $staff['firstname'] . ' ' . $staff['lastname'] ?>
                                <?= staff_profile_image($staff['staffid'], ['h-8', 'w-8', 'rounded-full'], 'thumb') ?>
                            </td>
                            <td class="border px-4 py-2">
                                <?= convertSecondsToRoundedTime($staff['total_shift_timings']) ?>
                            </td>
                            <td class="border px-4 py-2">
                                <?= convertSecondsToRoundedTime($staff['total_logged_in_time']) ?>
                            </td>
                            <td class="border px-4 py-2">
                                <?=  $staff['task_rate'] ?>
                            </td>
                            <td class="border px-4 py-2">
                                <a href="#" class="text-blue-500" data-staffname="<?= $staff['firstname'] ?>" data-staffid="<?= $staff['staffid'] ?>" data-toggle="modal" data-target="#dailySummaryModal">Summary</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        </div>
 

        <!-- All Tasks Worked On -->
        <div class="bg-white shadow rounded p-4 col-span-2">
            <h2 class="text-xl font-semibold mb-4">All Tasks Worked On</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-sm font-medium text-gray-700">
                            <th class="px-4 py-2 border-b-2 border-gray-200">Name</th>
                            <th class="px-4 py-2 border-b-2 border-gray-200">Worked by:</th>
                            <th class="px-4 py-2 border-b-2 border-gray-200">Status</th>
                            <th class="px-4 py-2 border-b-2 border-gray-200">Total worked time:</th>
                            <th class="px-4 py-2 border-b-2 border-gray-200">Priority</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm text-gray-600">
                        <?php

                        foreach ($report_data['all_tasks_worked_on'] as $task): 
    
                        ?>
                            <tr class="border-solid border-b border-gray-200">

                            <td class="px-4 py-2 align-top" style="max-width:400px;">
                                <?php
                                $projectNameSpan = '';
                                if ($task['project_name'] !== null) {
                                    $projectNameSpan = '<a target="_blank" href="' . admin_url() . 'projects/view/' . $task['rel_id'] . '" style="font-size:12px;">' . $task['project_name'] . '</a>';
                                }
                                echo '<div class="flex flex-col"><a target="_blank" href="' . admin_url() . 'tasks/view/' . $task['id'] . '">' . $task['task_name'] . '</a>' . $projectNameSpan . '</div>';
                                ?>
                            </td>

                                <td class="px-4 py-2 flex flex-row gap-2">
                                <?php foreach ($task['staff'] as $staff): ?>
                                    <?= staff_profile_image($staff['staff_id'], ['w-10 h-full staff-profile-image-thumb'], 'thumb') ?>
                                <?php endforeach; ?>
                                </td>
                                <td class="px-4 py-2 align-top"><?= format_task_status($task['task_status']) ?></td>
                                <td class="px-4 py-2 align-top"><?= convertSecondsToRoundedTime($task['total_worked_time']) ?></td>
                                <td class="px-4 py-2 align-top"><?= task_priority($task['priority']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Day Summary -->
        <div class="bg-white shadow rounded p-4 col-span-2">
            <h2 class="text-xl font-semibold mb-2">Day Summary</h2>
            <div id="todaySummary"><?=htmlspecialchars_decode($day_summary)?></div>
        </div>
        
        <?php if (is_admin()) : ?>
        <div class="bg-white shadow rounded p-4 col-span-2">
            
            <!-- Day summary editor (hidden by default) -->
            <div id="day-summary-editor">
                <textarea id="summary-editor" name="summary"><?=htmlspecialchars($day_summary)?></textarea>
                <div class="flex flex-row gap-2">
                    <button id="generate-summary" class="font-semibold my-2 px-4 py-2 bg-gray-200 rounded" type="button">Generate</button>
                    <button id="save-summary-btn" class="font-semibold my-2 px-4 py-2 bg-gray-200 rounded" type="button">Save</button>
                </div>
            </div>

        </div>
        <?php endif; ?>
    </div>
</div>


</div>

<script>
  const summaries = <?php echo json_encode($summaries); ?>;
</script>

<!-- Daily Summary Modal -->
<div class="modal fade" id="dailySummaryModal" tabindex="-1" role="dialog" aria-labelledby="dailySummaryModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dailySummaryModalLabel">Daily Summary</h5>

      </div>
      <div class="modal-body" id="dailySummaryModalBody">
        <!-- Summary content will be updated here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<?php init_tail(); ?>

<script>
  $('#dailySummaryModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);
    const staffId = button.data('staffid');
    const staffName = button.data('staffname');

    let summary = 'No summary available';

    for (const staffSummary of Object.values(summaries)) {
        if (staffSummary.staffid == staffId) {
        summary = staffSummary.summary;

        break;
        }
    }

    $('#dailySummaryModalBody').html(summary);
    $('#dailySummaryModalLabel').html(staffName + "'s Summary");

  });

    $('#generate-summary').on('click', function () {
      alert_float("info", "Generating summary...");
      $.ajax({
        url: admin_url + 'team_management/generate_daily_summary/<?=$date?>',
        method: 'GET',
        success: function (response) {
          alert_float("success", "Generated!");
          // Set the value of the active TinyMCE editor to the fetched summary
          tinymce.activeEditor.setContent(response);
        },
        error: function (xhr, status, error) {
          console.error('Error fetching summary:', error);
        },
      });
    });


</script>

<script>
$(document).ready(function() {
    // Initialize Summernote
    tinymce.init({
        selector: '#summary-editor',
        // Add any additional TinyMCE options you may need
    });

    // Save day summary on button click
    $('#save-summary-btn').click(function() {
        var summary = tinyMCE.activeEditor.getContent();
        alert_float("info", "Mailing summary...");
        $.post("<?=admin_url('team_management/save_day_summary')?>", {date: "<?=$date?>", summary: summary}, function() {
            $('#todaySummary').html(summary);
            alert_float("success", "Success!");
        });
    });

    const dateInput = document.getElementById("date-input");
    const today = new Date();
    
    const month = ("0" + (today.getMonth() + 1)).slice(-2);
    const day = ("0" + today.getDate()).slice(-2);
    const formattedDate = `${today.getFullYear()}-${month}-${day}`;
    
    dateInput.value = formattedDate;

});

function changeReport() {
    var date = document.getElementById("date-input").value;

    const selectedDate = new Date(date);
    const selectedMonth = ("0" + (selectedDate.getMonth() + 1)).slice(-2);
    const selectedDay = ("0" + selectedDate.getDate()).slice(-2);

    window.location.href = "<?=admin_url('team_management/daily_reports')?>/" + selectedMonth + "/" + selectedDay;

}
</script>
</body>
</html>