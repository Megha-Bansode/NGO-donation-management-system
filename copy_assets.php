<?php
$brainDir = 'C:/Users/Admin/.gemini/antigravity-ide/brain/b1b8e0cc-a9c4-43ce-8746-4176d32f7e65';
$targetDirs = [
    __DIR__ . '/assets/images'
];

foreach ($targetDirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Map uploaded media files specifically
$mediaMapping = [
    'hero_ngo_community.jpg'   => $brainDir . '/media__1784605386692.png',
    'hero_ngo_community.png'   => $brainDir . '/media__1784605386692.png',
    'media_children.png'       => $brainDir . '/media__1784605386692.png',
    
    'about_ngo_mission.jpg'    => $brainDir . '/media__1784605407885.png',
    'about_ngo_mission.png'    => $brainDir . '/media__1784605407885.png',
    'media_volunteers.png'     => $brainDir . '/media__1784605407885.png',
    
    'service_healthcare.jpg'   => $brainDir . '/media__1784605477488.png',
    'service_healthcare.png'   => $brainDir . '/media__1784605477488.png',
    'media_doctor.png'         => $brainDir . '/media__1784605477488.png',
];

$results = [];
foreach ($mediaMapping as $filename => $sourcePath) {
    if (file_exists($sourcePath)) {
        foreach ($targetDirs as $dir) {
            copy($sourcePath, $dir . '/' . $filename);
        }
        $results[$filename] = 'COPIED_SUCCESSFULLY';
    } else {
        $results[$filename] = 'SOURCE_NOT_FOUND';
    }
}

echo json_encode($results, JSON_PRETTY_PRINT);
