<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers</title>
    <!-- Include Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-12">
            <!-- Replace 'your_logo.png' with your company's logo file -->
            <img src="https://i.ibb.co/RhSFc27/Zikra-Infotec-for-web-png.png" alt="Company Logo" class="mx-auto mb-4 w-64">
            <h1 class="text-4xl font-semibold">Careers</h1>
        </div>
        
        <div class="grid grid-cols-1 gap-8">
            <?php foreach ($activeCampaigns as $campaign): ?>
                <div class="bg-white shadow-md rounded-lg p-6 flex">
                    <div class="flex-grow">
                        <h2 class="text-xl font-semibold mb-4"><?php echo $campaign->title; ?></h2>
                        <p class="text-gray-600 mb-4"><?php echo $campaign->position; ?></p>
                        <p class="text-gray-600 mb-4">Start Date: <?php echo $campaign->start_date; ?></p>
                        <p class="text-gray-600 mb-4">End Date: <?php echo $campaign->end_date; ?></p>
                        <?php if (isset($campaign->salary)): ?>
                            <p class="text-gray-600 mb-4">Salary: <?php echo $campaign->salary; ?></p>
                        <?php endif; ?>
                        <p class="text-gray-600 mb-6"><?php echo $campaign->description; ?></p>
                    </div>
                    <a href="<?php echo site_url('career/apply/'.$campaign->id); ?>" class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg self-center">Apply</a>
                </div>
            <?php endforeach; ?>
        </div>


    </div>
</body>
</html>
