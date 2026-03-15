<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddChatbotPermissionToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add chatbot permission to settings
        $permission = settings('main_settings.user_permissions.chatbot');
        
        if (empty($permission)) {
            $userPermissions = settings('main_settings.user_permissions');
            $userPermissions['chatbot'] = [
                'view' => 1,
                'edit' => 0,
                'remove' => 0,
            ];
            settings('main_settings.user_permissions', $userPermissions);
        }

        // Add chatbot permission to all existing users
        DB::insert("INSERT INTO `user_permissions` ( `user_id`, `name`, `view`, `edit`, `remove` ) 
                    SELECT id, 'chatbot' AS `name`, 1 as `view`, 0 as `edit`, 0 as `remove` 
                    FROM users 
                    WHERE id NOT IN (SELECT user_id FROM user_permissions WHERE name = 'chatbot')");
        
        // Add chatbot permission to all billing plans
        DB::insert("INSERT INTO `billing_plan_permissions` ( `plan_id`, `name`, `view`, `edit`, `remove` ) 
                    SELECT id, 'chatbot' AS `name`, 1 as `view`, 0 as `edit`, 0 as `remove` 
                    FROM billing_plans 
                    WHERE id NOT IN (SELECT plan_id FROM billing_plan_permissions WHERE name = 'chatbot')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove chatbot permission from settings
        $userPermissions = settings('main_settings.user_permissions');
        if (isset($userPermissions['chatbot'])) {
            unset($userPermissions['chatbot']);
            settings('main_settings.user_permissions', $userPermissions);
        }

        // Remove chatbot permission from users
        DB::table('user_permissions')->where('name', 'chatbot')->delete();
        
        // Remove chatbot permission from billing plans
        DB::table('billing_plan_permissions')->where('name', 'chatbot')->delete();
    }
}
