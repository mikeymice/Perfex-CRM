<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="w-full mx-auto">
            <div class="bg-white shadow-md rounded-md p-6">
                <div class="mb-10 w-full flex justify-between">
                    <h4 class="text-2xl font-semibold mb-4">Submissions</h4>
                </div>
                <table id="submissions_table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>
                                Name
                            </th>
                            <th>
                                Campaign
                            </th>
                            <th>
                                Date
                            </th>
                            <th>
                                View
                            </th>
                            <th>
                                Quick
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<?php include('script.php'); ?>

</body>
</html>