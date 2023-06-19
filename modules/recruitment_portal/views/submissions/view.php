<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="container mx-auto px-4 py-12">
            <h1 class="text-4xl font-semibold mb-6">Submission Details</h1>
            <div class="bg-white shadow-lg rounded-lg p-8">
                <div class="mb-4 text-xl">
                    <p class="font-semibold text-gray-700">Campaign: <span class="text-gray-600"><?= $submission->title ?></span></p>
                </div>
                <table class="min-w-full table-auto text-base">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-700 font-semibold">Field</th>
                            <th class="px-4 py-2 text-left text-gray-700 font-semibold">Value</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600">
                        <?php
                        $formData = json_decode($submission->form_data, true);
                        foreach ($formData as $key => $value) {
                            if ($key != 'campaign_id') {
                                echo '<tr class="hover:bg-gray-100 transition-all ease-in-out hover:scale-[1.04] hover:translate-x-4">';
                                echo '<td class="px-4 py-4 capitalize">' . $key . '</td>';

                                if (is_array($value)) {
                                    echo '<td class="px-4 py-4">';
                                    echo '<ul class="list-disc pl-5">';
                                    foreach ($value as $item) {
                                        echo '<li>' . $item . '</li>';
                                    }
                                    echo '</ul>';
                                    echo '</td>';
                                }else if($key == 'resume'){
                                    echo '<td class="px-4 py-2 border"><a target="_blank" href="'.admin_url('recruitment_portal/view_resume/'.$value).'">Resume</a></td>';
                                } 
                                else {
                                    echo '<td class="px-4 py-2 border">' . $value . '</td>';
                                }

                                echo '</tr>';
                                echo '<tr class="border-b border-solid border-gray-300"></tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-center">
                <button id="invite" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg mx-2">Invite for screening call</button>
                <button id="reject" class="bg-red-600 text-white font-semibold px-6 py-2 rounded-lg mx-2">Reject</button>
                <button id="on-hold" class="bg-yellow-600 text-white font-semibold px-6 py-2 rounded-lg mx-2">On-hold</button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

</body>
</html>