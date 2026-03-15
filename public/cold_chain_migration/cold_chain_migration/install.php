<?php
/**
 * Cold Chain Module Migration Installer
 * This script automates the migration of the Cold Chain module, including Reports and Dashboard updates.
 */

$basePath = __DIR__;
// Traverse up until we find 'artisan' to identify project root
while (!file_exists($basePath . '/artisan') && $basePath != '/' && $basePath != '.') {
    $basePath = dirname($basePath);
}

if (!file_exists($basePath . '/artisan')) {
    die("[ERROR] Could not find Laravel root (artisan file). Please run this script from inside the Laravel project structure.\n");
}

log_msg("Detailed Path Info:");
log_msg("  Script Dir: " . __DIR__);
log_msg("  Detected Root: " . $basePath);
log_msg("------------------------------------------------");

$filesPath = __DIR__ . '/files';

function log_msg($msg) {
    echo $msg . PHP_EOL;
}

// 1. Copy New Files
log_msg("Copying new files...");
$filesToCopy = [
    // Controllers
    'app/Http/Controllers/Frontend/ColdChainController.php',
    'app/Http/Controllers/Frontend/DashboardController.php',
    
    // Views
    'Tobuli/Views/Frontend/Objects/tabs/coldchain.blade.php',
    'Tobuli/Views/Frontend/Reports/partials/type_104.blade.php',
    'Tobuli/Views/Frontend/Reports/partials/type_104_excel.blade.php',
    'Tobuli/Views/Frontend/Dashboard/Blocks/wc_board_form/content.blade.php',

    // Reports
    'Tobuli/Reports/Reports/TemperatureHourlyReport.php',

    // Dashboard Blocks
    'Tobuli/Helpers/Dashboard/Blocks/WcBoardFormBlock.php',
    'Tobuli/Helpers/Dashboard/Blocks/FuelStatisticsBlock.php',
    'Tobuli/Helpers/Dashboard/Blocks/FuelAverageChartBlock.php',
    'Tobuli/Helpers/Dashboard/Blocks/FuelFenceBlock.php',
    'Tobuli/Helpers/Dashboard/Blocks/Block.php',
];

foreach ($filesToCopy as $file) {
    if (!file_exists($filesPath . '/' . $file)) {
        log_msg("  [WARNING] Source file not found: $file");
        continue;
    }

    $dest = $basePath . '/' . $file;
    $dir = dirname($dest);
    
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            log_msg("  [ERROR] Failed to create directory $dir");
            continue;
        }
    }
    
    if (file_exists($dest) && !is_writable($dest)) {
        log_msg("  [INFO] Target file exists and is not writable. Attempting chmod...");
        if (!chmod($dest, 0666)) {
             log_msg("  [WARNING] chmod failed. You may need to run this script with sudo.");
        }
    }
    
    if (copy($filesPath . '/' . $file, $dest)) {
        log_msg("  [OK] Copied $file");
        // Restore permissions if needed, generally 644 is good
        chmod($dest, 0644);
    } else {
        $error = error_get_last();
        log_msg("  [ERROR] Failed to copy $file - " . ($error['message'] ?? 'Unknown error'));
        log_msg("  [TIP] Try running: sudo php install.php");
    }
}

// 2. Modify routes/web.php
log_msg("Modifying routes/web.php...");
$webPath = $basePath . '/routes/web.php';
$webContent = file_get_contents($webPath);
if (strpos($webContent, 'cold_chain') === false) {
    $search = "    Route::resource('forwards', 'ForwardsController', ['except' => ['destroy']]);\n});";
    $replacement = "    Route::resource('forwards', 'ForwardsController', ['except' => ['destroy']]);\n\n    Route::get('cold_chain', ['as' => 'cold_chain.index', 'uses' => 'ColdChainController@index']);\n    Route::get('cold_chain/history', ['as' => 'cold_chain.history', 'uses' => 'ColdChainController@history']);\n});";
    $webContent = str_replace($search, $replacement, $webContent);
    file_put_contents($webPath, $webContent);
    log_msg("  [OK] Routes added.");
} else {
    log_msg("  [SKIP] Routes already exist.");
}

// 3. Modify ObjectsController.php
log_msg("Modifying ObjectsController.php...");
$controllerPath = $basePath . '/app/Http/Controllers/Frontend/ObjectsController.php';
$controllerContent = file_get_contents($controllerPath);

if (strpos($controllerContent, 'coldChainDevices') === false) {
    // Regex strategy to find return statement flexibly
    $pattern = "/return\s+view\s*\(\s*['\"]front::Objects\.index['\"]\s*\)/";
    
    $logic = '
        $coldChainDevices = $this->user->devices()
            ->with([
                \'traccar\',
                \'sensors\' => function($query) {
                    $query->where(\'type\', \'temperature\');
                }
            ])
            ->whereHas(\'sensors\', function($query) {
                $query->where(\'type\', \'temperature\');
            })
            ->get();

        ';

    if (preg_match($pattern, $controllerContent, $matches)) {
        $match = $matches[0];
        // Insert logic before return
        $controllerContent = str_replace($match, $logic . $match, $controllerContent);
        
        // Update compact(). Regex to find compact and insert variable
        $compactPattern = "/compact\s*\((.*?)\)/s";
        if (preg_match($compactPattern, $controllerContent, $matches)) {
            $compactArgs = $matches[1];
            if (strpos($compactArgs, 'coldChainDevices') === false) {
                $newCompactArgs = "'coldChainDevices', " . $compactArgs;
                $newCompact = "compact(" . $newCompactArgs . ")";
                $controllerContent = str_replace($matches[0], $newCompact, $controllerContent);
            }
        }
        
        file_put_contents($controllerPath, $controllerContent);
        log_msg("  [OK] ObjectsController logic updated.");
    } else {
        log_msg("  [ERROR] Could not find return statement (regex failed) in ObjectsController.php. Manual intervention required.");
    }
} else {
    log_msg("  [SKIP] ObjectsController already updated.");
}

// 4. Modify header.blade.php
log_msg("Modifying header.blade.php...");
$headerPath = $basePath . '/Tobuli/Views/Frontend/Layouts/partials/header.blade.php';
$headerContent = file_get_contents($headerPath);

if (strpos($headerContent, 'coldchain_tab') === false) {
    // Regex for expenses types check
    $pattern = "/@if\s*\(\s*Auth::User\(\)->perm\('device_expenses',\s*'view'\)\s*&&\s*expensesTypesExist\(\)\s*\)/";
    
    $menuItem = "                            <li>
                                <a href=\"javascript:\" onclick=\"app.openTab('coldchain_tab');\">
                                    <span class=\"icon icon-fa fa-snowflake-o\"></span>
                                    <span class=\"text\">Cold Chain</span>
                                </a>
                            </li>
                            ";
                            
    if (preg_match($pattern, $headerContent, $matches)) {
        $match = $matches[0];
        // Insert menu item before the check
        $headerContent = str_replace($match, $menuItem . $match, $headerContent);
        file_put_contents($headerPath, $headerContent);
        log_msg("  [OK] Header updated.");
    } else {
        log_msg("  [WARNING] Could not match header menu anchor exactly with regex. Header NOT updated.");
    }
} else {
    log_msg("  [SKIP] Header already has Cold Chain link.");
}

// 5. Modify ReportManager.php (Register Type 104)
log_msg("Modifying ReportManager.php...");
$reportManagerPath = $basePath . '/Tobuli/Reports/ReportManager.php';
$reportManagerContent = file_get_contents($reportManagerPath);

if (strpos($reportManagerContent, 'Reports\TemperatureHourlyReport::class') === false) {
    // Regex for types array start
    $startPattern = "/public\s+static\s+\\\$types\s*=\s*\[/";
    
    if (preg_match($startPattern, $reportManagerContent, $matches, PREG_OFFSET_CAPTURE)) {
        $startPos = $matches[0][1];
        // Find closing bracket ];
        $closePos = strpos($reportManagerContent, '];', $startPos);
        
        if ($closePos !== false) {
             $insert = "\n        104 => Reports\TemperatureHourlyReport::class,";
             $reportManagerContent = substr_replace($reportManagerContent, $insert, $closePos, 0);
             file_put_contents($reportManagerPath, $reportManagerContent);
             log_msg("  [OK] Report type 104 registered in ReportManager.");
        } else {
            log_msg("  [WARNING] Could not find closing bracket for types array.");
        }
    } else {
        log_msg("  [WARNING] Could not find types array definition with regex.");
    }
} else {
    log_msg("  [SKIP] Report type 104 already registered.");
}

// 6. Modify Tobuli/Views/Frontend/Objects/index.blade.php (Add logic tab container)
log_msg("Modifying Tobuli/Views/Frontend/Objects/index.blade.php...");
$indexPath = $basePath . '/Tobuli/Views/Frontend/Objects/index.blade.php';
if (file_exists($indexPath)) {
    $indexContent = file_get_contents($indexPath);
    
    if (strpos($indexContent, 'id="coldchain_tab"') === false) {
        // Strategy: Insert after alerts_tab block
        $anchor = '<div class="tab-pane" id="alerts_tab">';
        // We look for the closing div of this block. It's risky to guess indentation.
        // Instead, let's look for the END of the tab-content container?
        // Or just insert after the alerts include line.
        
        // Better: look for `@include('Frontend.Objects.tabs.alerts')`
        // Then find the next `</div>`.
        // Then insert after that.
        
        $searchInclude = "@include('Frontend.Objects.tabs.alerts')";
        $pos = strpos($indexContent, $searchInclude);
        
        if ($pos !== false) {
            $closingDivPos = strpos($indexContent, '</div>', $pos);
            if ($closingDivPos !== false) {
                $insertPos = $closingDivPos + 6; // After </div>
                $insertion = "\n    <div class=\"tab-pane\" id=\"coldchain_tab\">\n        @include('Frontend.Objects.tabs.coldchain')\n    </div>";
                
                $indexContent = substr_replace($indexContent, $insertion, $insertPos, 0);
                file_put_contents($indexPath, $indexContent);
                log_msg("  [OK] Cold Chain tab container added to index.blade.php.");
            } else {
                 log_msg("  [WARNING] Could not find closing div for alerts tab in index.blade.php.");
            }
        } else {
             // Fallback: Try to append before the last closing div of .tab-content?
             // Risky. Let's try searching for another tab like history
             log_msg("  [WARNING] Could not find alerts tab include in index.blade.php.");
        }
    } else {
        log_msg("  [SKIP] Cold Chain tab already exists in index.blade.php.");
    }
} else {
     log_msg("  [ERROR] Tobuli/Views/Frontend/Objects/index.blade.php not found.");
}

// 7. Clear Cache
log_msg("Clearing cache...");
exec("php $basePath/artisan route:clear");
exec("php $basePath/artisan view:clear");
log_msg("  [OK] Cache cleared.");

log_msg("Migration completed successfully!");
