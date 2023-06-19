document.addEventListener('DOMContentLoaded', function () {
    
    //setTimeout(function(){
    
    if (document.body.classList.contains('dashboard')) {

    
        
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = xhr.responseText;

                var widgetsContainer = document.querySelector('.content');

                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = response;

                widgetsContainer.insertBefore(tempDiv, widgetsContainer.firstChild);

                //Start of the view Scripting
                
                var timerInterval;
                var clockedIn = false;
                var startTime;

                const clockInBtn = document.getElementById('clock-in');
                const clockOutBtn = document.getElementById('clock-out');
                const statusSelect = document.getElementById('status');

                const liveTimer = document.getElementById('live-timer');
                const todayTimer = document.getElementById('today-timer');
                const yesterdayTimer = document.getElementById('yesterday-timer');
                const currentWeekTimer = document.getElementById('current-week-timer');
                const lastWeekTimer = document.getElementById('last-week-timer');

                function convertDateTimeZone(getDateObject) {

                    let timeZone = 'Asia/Kolkata';

                    var options = { timeZone: timeZone, hour: 'numeric', minute: 'numeric', second: 'numeric' };
                    var localTime = getDateObject.toLocaleString('en-US', options);
                    var localTimeArray = localTime.split(/[:\s]/);
                    var localDate = new Date(getDateObject.toLocaleDateString('en-US', { timeZone: timeZone }));
                    
                    //Convert the hours to 24-hour format if needed
                    if (localTimeArray[3] === 'PM') {
                        localTimeArray[0] = parseInt(localTimeArray[0], 10) + 12;
                    }
                    
                    localDate.setHours(localTimeArray[0], localTimeArray[1], localTimeArray[2]);
                    return localDate;
                }

                function getCurrentTimeInAsiaKolkata() {
                    const now = new Date();
                    const timeZone = 'Asia/Kolkata';
                    const localTimeString = now.toLocaleString('en-US', { timeZone });
                  
                    return new Date(localTimeString);
                }

                function updateLiveTimer() {
                    if (clockedIn) {

                        var currentTime = getCurrentTimeInAsiaKolkata();

                        var elapsedTime = Math.floor((currentTime - startTime) / 1000);

                        //console.log(currentTime);
                        //console.log(startTime);

                        document.getElementById('live-timer').textContent = formatTime(elapsedTime );
                    }
                }

                function formatTime(seconds) {
                    const hours = Math.floor(seconds / 3600);
                    seconds %= 3600;
                    const minutes = Math.floor(seconds / 60);
                    const remainingSeconds = seconds % 60;
                
                    return hours.toString().padStart(2, '0') + ':' +
                           minutes.toString().padStart(2, '0') + ':' +
                           remainingSeconds.toString().padStart(2, '0');
                }

                function fetchStats() {

                    var xhr = new XMLHttpRequest();
                    xhr.open('GET', admin_url + 'team_management/fetch_stats', true);

                    xhr.onload = function() {

                    if (this.status === 200) {

                        var stats = JSON.parse(this.responseText);

                        liveTimer.textContent = formatTime(stats.total_time);
                        todayTimer.textContent = formatTime(stats.todays_total_time);
                        yesterdayTimer.textContent = formatTime(stats.yesterdays_total_time);
                        currentWeekTimer.textContent = formatTime(stats.this_weeks_total_time);
                        lastWeekTimer.textContent = formatTime(stats.last_weeks_total_time);

                        if(stats.status == "Online"){
                            if (stats.clock_in_time) {
                                clockInBtn.disabled = true;
                                clockInBtn.style.opacity = 0.7;
                                clockOutBtn.disabled = false;
                                clockOutBtn.style.opacity = 1;
                                clockedIn = true;
                                console.log(stats.clock_in_time);
                                startTime = new Date(stats.clock_in_time);
                                timerInterval = setInterval(updateLiveTimer, 1000);
                            }else{
                                clockInBtn.disabled = false;
                                clockInBtn.style.opacity = 1;
                                clockOutBtn.disabled = true;
                                clockOutBtn.style.opacity = 0.7;
                                clearInterval(timerInterval);
                                clockedIn = false;
                            }

                        }else{
                            clockInBtn.disabled = true;
                            clockInBtn.style.opacity = 0.7;
                            clockOutBtn.disabled = true;
                            clockOutBtn.style.opacity = 0.7;
                            clearInterval(timerInterval);
                            clockedIn = false;
                            
                        }

                        if(stats.status == "Leave"){
                            document.getElementById('Online').disabled = true;
                            document.getElementById('AFK').disabled = true;
                            document.getElementById('Offline').disabled = true;
                            document.getElementById('Leave').disabled = false;
                        }else{
                            document.getElementById('Online').disabled = false;
                            document.getElementById('AFK').disabled = false;
                            document.getElementById('Offline').disabled = false;
                            document.getElementById('Leave').disabled = true;
                        }
                        

                        statusSelect.value = stats.status;
                        statusSelectColors(statusSelect);
                        
                    } 
                    else {
                        alert('Unable to fetch stats. Please try again.');
                    }
                    
                    }

                    xhr.send();
                }

                clockInBtn.addEventListener('click', () => {
                    
                    

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', admin_url + 'team_management/clock_in');
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;charset=UTF-8');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrf_token);
                    xhr.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            var response = JSON.parse(this.responseText);
                            if (response.success) {
                                clockedIn = true;
                                fetchStats();
                                clockInBtn.disabled = true;
                                timerInterval = setInterval(updateLiveTimer, 1000);
                            } else {
                                alert('Unable to clock in. Please try again.');
                            }
                        }
                    };
                    var requestData = csrf_token_name + '=' + encodeURIComponent(csrf_token);
                    xhr.send(requestData);

                });

                clockOutBtn.addEventListener('click', () => {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', admin_url + 'team_management/clock_out');
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;charset=UTF-8');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrf_token);
                    xhr.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            var response = JSON.parse(this.responseText);
                            if (response.success) {
                                fetchStats();
                            } else {
                                alert('Unable to clock out. Please try again.');
                            }
                        }
                    };
                
                    // Include the CSRF token in the request data
                    var requestData = csrf_token_name + '=' + encodeURIComponent(csrf_token);
                    xhr.send(requestData);
                });
                
                let previousValue = statusSelect.value;
                statusSelect.addEventListener('change', (event) => {
                    
                    var statusText = statusSelect.value;
                    
                    if (statusSelect != previousValue) {

                        //UI Timers
                        //if (statusText == 'Online') {
                        //    if (!clockedIn) {
                        //        clockInBtn.disabled = true;
                        //        clockInBtn.style.opacity = 0.7;
                        //        clockOutBtn.disabled = false;
                        //        clockOutBtn.style.opacity = 1;
                        //        clockedIn = true;
                        //        timerInterval = setInterval(updateLiveTimer, 1000);
                        //    }
                        //} else {
                        //    clockInBtn.disabled = true;
                        //    clockInBtn.style.opacity = 0.7;
                        //    clockOutBtn.disabled = true;
                        //    clockOutBtn.style.opacity = 0.7;
                        //    clearInterval(timerInterval);
                        //    clockedIn = false;
                        //}
                        
                        //Backend Timers
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', admin_url + 'team_management/update_status');
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;charset=UTF-8');
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrf_token);
                        xhr.onreadystatechange = function () {
                            if (this.readyState == 4 && this.status == 200) {
                                var response = JSON.parse(this.responseText);
                                if (!response.success) {
                                    alert('Unable to update status. Please try again.');
                                }
                                fetchStats();
                            }
                        };
                
                        // Include the CSRF token and status in the request data
                        var requestData = csrf_token_name + '=' + encodeURIComponent(csrf_token) + '&statusValue=' + encodeURIComponent(statusText);
                        xhr.send(requestData);
                    }
                });
                

                fetchStats();

                //Setting current shift status
                
                var xhrShift = new XMLHttpRequest();
                xhrShift.open("GET", admin_url + "team_management/get_shift_status", true);
                xhrShift.responseType = "json";
                xhrShift.onload = function () {
                    if (xhrShift.status === 200) {
                        
                        var response = xhrShift.response;
                        var shiftInfo = "";
                        if (response.status == 0) {
                            shiftInfo = "It's shift time. Time remaining: " + response.time_left;
                        } else if (response.status == 1) {
                            shiftInfo = "Upcoming shift in " + response.time_left;
                        } else {
                            shiftInfo = "No shift currently.";
                        }
                        document.getElementById("shiftInfo").textContent = shiftInfo;
                    } else {
                        console.error("Error retrieving shift information:", xhrShift.statusText);
                    }
                };
                xhrShift.onerror = function (error) {
                    console.error("Error retrieving shift information:", error);
                };
                xhrShift.send();
 


                const timezones = [
                    { name: 'IST', timeZone: 'Asia/Kolkata' },
                    { name: 'CST', timeZone: 'America/Chicago' },
                    { name: 'PST', timeZone: 'Asia/Karachi' },
                    { name: 'BST', timeZone: 'Asia/Dhaka' },
                ];

                function updateClocks() {
                    const clocksElement = document.getElementById('clocks');
                    clocksElement.innerHTML = '';
                
                    for (const timezone of timezones) {
                        const date = new Date();
                        const formatter = new Intl.DateTimeFormat('en-US', {
                            timeZone: timezone.timeZone,
                            dateStyle: 'full',
                            timeStyle: 'medium',
                        });
                
                        const formattedDate = formatter.format(date);
                        const clockElement = document.createElement('div');
                        clockElement.className = 'text-gray-700 font-semibold flex flex-row justify-between';
                        clockElement.innerHTML = `<div>${timezone.name}:</div> <div class="ml-4">${formattedDate}</div>`;
                        clocksElement.appendChild(clockElement);
                    }
                }
                
                updateClocks();
                setInterval(updateClocks, 1000);
                getOrSaveStaffSummary();

                initTimeline();
            }
        };
        xhr.open('GET', admin_url + 'team_management/widget', true);
        xhr.send();
    }

    //}, 3000);


});


function statusSelectColors(element){
    element.classList.remove('text-lime-500');
    element.classList.remove('text-blue-500');
    element.classList.remove('text-pink-500');
    element.classList.add(element.options.namedItem(element.value).classList.item(0));
}


function updateTaskValues(staffId, newTask, isCompleted) {
    const totalTasksElem = document.querySelector('#user-' + staffId + '-rate-a');
    const completedTasksElem = document.querySelector('#user-' + staffId + '-rate-c');
    const percentageElem = document.querySelector('#user-' + staffId + '-rate-p');

    const totalTasks = parseInt(totalTasksElem.textContent) + (newTask ? 1 : 0);
    const completedTasks = parseInt(completedTasksElem.textContent) + (isCompleted ? 1 : 0);
    const percentage = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;

    totalTasksElem.textContent = totalTasks;
    completedTasksElem.textContent = completedTasks;
    percentageElem.textContent = percentage;
}

function getOrSaveStaffSummary(summary = null) {
    $.ajax({
      url: 'team_management/staff_summary',
      type: 'POST',
      data: { summary: summary, csrf_token_name : csrf_token },
      success: function(response) {
        if (!summary) {
          // If you're fetching the summary, set the textarea value with the response
          document.querySelector('#summary-textarea').value = response;
        }else{
            alert_float("success", "Summary Saved!");
        }
      },
      error: function() {
        alert('Error fetching or saving the summary.');
      }
    });
}

function processShiftData(staffShifts) {
    //const timeLabels = new Set();
    const dotData = {};
  
    staffShifts.forEach((shift) => {
      const startTime = new Date(shift.shift_start_time);
      const endTime = new Date(shift.shift_end_time);
  
      const startHour = startTime.getHours();
      const endHour = endTime.getHours();
  
      
  
      if (!dotData[startHour]) {
        dotData[startHour] = [];
      }
      dotData[startHour].push({ name: shift.staff_name, type: 'Start' });
  
      if (!dotData[endHour]) {
        dotData[endHour] = [];
      }
      dotData[endHour].push({ name: shift.staff_name, type: 'End' });
    });

    const timeLabels = Array.from({ length: 13 }, (_, i) => i * 2);
  
    return {
      timeLabels: Array.from(timeLabels).sort((a, b) => a - b),
      dotData,
    };
}
  
function createTimeLabelElement(hour) {
    const div = document.createElement('div');
    div.classList.add('text-sm');
    div.textContent = hour >= 12 ? `${hour - 12 || 12} PM` : `${hour} AM`;
    return div;
}
  
function createDotElement(hour, data, timeLabels) {
    const dotContainer = document.createElement('div');
    dotContainer.classList.add(
      'dot-container',
      'absolute',
      'top-0',
      'transform',
      '-translate-x-1/2',
      '-translate-y-[6px]'
    );
    
    dotContainer.style.left = `${((hour - timeLabels[0]) / (timeLabels[timeLabels.length - 1] - timeLabels[0])) * 100}%`;
  
    const dot = document.createElement('div');
    dot.classList.add('dot', 'w-4', 'h-4', 'bg-blue-500', 'rounded-full', 'hover:bg-blue-700', 'cursor-pointer');
    dotContainer.appendChild(dot);
  
    const info = document.createElement('div');
    info.classList.add('info', 'hidden', 'mt-4', 'bg-white', 'text-sm', 'text-gray-700', 'p-4', 'rounded-lg', 'shadow-lg', 'border', 'border-gray-200', 'transition', 'duration-200', 'ease-in-out');
    data.forEach((text) => {
        const p = document.createElement('p');
        p.classList.add('mb-2', 'flex', 'items-center', 'space-x-1');
    
        // Create an icon element (using Font Awesome icons)
        const icon = document.createElement('i');
        icon.classList.add('text-blue-500', text.type === 'Start' ? 'fas' : 'far', 'fa-clock');
        p.appendChild(icon);
    
        const name = document.createElement('span');
        name.textContent = text.name;
        name.classList.add('font-semibold');
        p.appendChild(name);
    
        const type = document.createElement('span');
        type.textContent = text.type;
        type.classList.add('font-light', 'text-gray-600');
        p.appendChild(type);
    
        info.appendChild(p);
    });
    dotContainer.appendChild(info);

    const arrow = document.createElement('div');
    arrow.classList.add('absolute', 'w-3', 'h-3', 'bg-white', 'border', 'border-gray-200', 'shadow', 'transform', 'rotate-45', 'bottom-full', 'left-1/2', 'translate-x-[-50%]');
    info.appendChild(arrow);
  
    dotContainer.addEventListener('mouseenter', () => {
      info.classList.remove('hidden');
    });
    dotContainer.addEventListener('mouseleave', () => {
      info.classList.add('hidden');
    });
  
    return dotContainer;
}
    
function initTimeline() {

    const { timeLabels, dotData } = processShiftData(staffShifts);

    const timelineContainer = document.createElement('div');
    timelineContainer.classList.add('relative', 'w-full', 'h-1', 'bg-gray-200', 'rounded');

    const timeLabelsContainer = document.createElement('div');
    timeLabelsContainer.classList.add('flex', 'justify-between', 'mt-4');

    timeLabels.forEach((hour) => {

        //console.log(hour + " to " + (hour+2));
        
        for (let index = 0; index < 24; index++) {

            if( ( index > hour && index < (hour + 2) ) || (index == hour)){
                if(dotData[index]){
                    const dot = createDotElement(index, dotData[index], timeLabels); // Update this line
                    timelineContainer.appendChild(dot);
                }
                
            }
            
        }
        
        

        const label = createTimeLabelElement(hour);
        timeLabelsContainer.appendChild(label);
      
    });

    const wrapper = document.getElementById('timeline-wrapper');
    wrapper.appendChild(timelineContainer);
    wrapper.appendChild(timeLabelsContainer);
}