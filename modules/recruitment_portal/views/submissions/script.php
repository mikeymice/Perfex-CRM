<script>
    
document.addEventListener('DOMContentLoaded', function() {

    //Datatable init code
    var submissionsTable = $('#submissions_table').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '<?= admin_url("recruitment_portal/get_submissions"); ?>',
            type: 'GET',
            dataType: 'json',
            dataSrc: 'submissions'
        },
        columns: [
            { 
                data: null,
                "render": function ( data, type, row ) {
                    return `
                        <a target="_blank" href="`+admin_url +`recruitment_portal/view_submission/`+ row.sub + `" class="text-blue-500 hover:text-yellow-900">`+row.name+`</a>
                    `;
                }
            },
            { data: 'campaign' },
            { 
                data: 'submission_date',
            },
            { 
                data: null,
                "render": function ( data, type, row ) {
                    console.log(row);
                    return `
                    <div class="flex w-full gap-4">
                        <a target="_blank" href="`+admin_url +`recruitment_portal/view_submission/`+ row.sub + `" class="text-cyan-600 hover:text-yellow-900">View</a>
                        <a target="_blank" href="`+admin_url +`recruitment_portal/view_resume/`+ row.resume + `" class="text-lime-600 hover:text-yellow-900">Resume</a>
                    </div>
                    `;
                }
            },
            { 
                data: null,
                "render": function ( data, type, row ) {
                    console.log(row);
                    return `
                    <div class="flex w-full gap-4">
                        <a target="_blank" href="`+admin_url +`recruitment_portal/view_submission/`+ row.sub + `" class="text-rose-600 hover:text-yellow-900">Reject</a>
                        <a target="_blank" href="`+admin_url +`recruitment_portal/view_resume/`+ row.resume + `" class="text-lime-600 hover:text-yellow-900">Invite</a>
                    </div>
                    `;
                }
            }
        ],
        initComplete: function() {
            $('#submissions_table_wrapper').removeClass('table-loading');
        },
        order: [[0, 'desc']]
    });

});

</script>