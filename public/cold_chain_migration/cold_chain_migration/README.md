# Cold Chain Module & Dashboard Fixes Migration

This package contains everything needed to migrate the Cold Chain development and Dashboard improvements to another server.

## Contents
- `files/`: New library, view, and report files.
- `install.php`: Automation script to copy files and patch existing files (`web.php`, `ObjectsController.php`, `header.blade.php`, `ReportManager.php`).

## Instructions
1. Copy the `cold_chain_migration/` folder to your target server's root directory (where `artisan` is located).
2. Open your terminal and navigate to the folder.
3. Run the installer using PHP:
   ```bash
   php install.php
   ```
4. Verify the installation by:
   - Logging into the system and checking the "Cold Chain" tab.
   - Checking Reports for "Temperature Hourly Report" (Type 104).
   - Verifying the Dashboard defaults to Today's date.

## Manual Verification
After running the script, the following changes should have been made:
- New Controller: `app/Http/Controllers/Frontend/ColdChainController.php`
- New Report: `Tobuli/Reports/Reports/TemperatureHourlyReport.php`
- New Views: `Tobuli/Views/Frontend/Objects/tabs/coldchain.blade.php` and `type_104` report views.
- Updated Dashboard Blocks (WcBoardForm, FuelStatistics, etc.) to default to Today.
- Routes added to `routes/web.php`
- Logic added to `ObjectsController@index`
- Menu link added to `header.blade.php`
- Report type 104 registered in `ReportManager.php`

