<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ChatbotController extends BaseController
{
    protected $chatbotService;

    public function __construct(\App\Services\ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    public function send(Request $request)
    {
        $message = $request->input('message');
        $historyJson = $request->input('history');
        $apiKey = config('services.openai.key');

        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'OpenAI API key not configured.'
            ]);
        }

        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must be logged in to use this feature.'
            ]);
        }

        // Check chatbot permission
        if (!$user->perm('chatbot', 'view')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to use the chatbot.'
            ]);
        }

        // Check user quota
        $quotaCheck = $this->chatbotService->checkUserQuota($user);
        if (!$quotaCheck['allowed']) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have reached your chatbot usage limit. Daily: ' . $quotaCheck['daily_remaining'] . ' remaining, Monthly: ' . $quotaCheck['monthly_remaining'] . ' remaining.'
            ]);
        }

        $systemPrompt = $this->chatbotService->getSystemPrompt($user);
        $deviceContext = $this->chatbotService->getDeviceContext($user);
        
        // Build messages array with conversation history
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt . "\n\n" . $deviceContext]
        ];
        
        // Add conversation history if provided
        if ($historyJson) {
            $history = json_decode($historyJson, true);
            if (is_array($history)) {
                // Limit history to last 10 messages
                $recentHistory = array_slice($history, -10);
                
                foreach ($recentHistory as $msg) {
                    if (isset($msg['role']) && isset($msg['content'])) {
                        if (in_array($msg['role'], ['user', 'assistant'])) {
                            $messages[] = [
                                'role' => $msg['role'],
                                'content' => $msg['content']
                            ];
                        }
                    }
                }
            }
        }
        
        // Add current user message
        $messages[] = ['role' => 'user', 'content' => $message];

        // Call OpenAI with function calling
        $response = $this->callOpenAIWithFunctions($apiKey, $messages, $user);
        
        $toolCalled = $response['tool_called'] ?? null;
        $tokensUsed = $response['tokens_used'] ?? 0;
        $deviceName = $response['device_name'] ?? null;
        $botMessage = $response['content'];

        // Log the interaction
        $this->chatbotService->logChatInteraction(
            $user,
            $message,
            $botMessage,
            $toolCalled,
            $tokensUsed,
            $deviceName
        );

        // Increment quota usage
        $this->chatbotService->incrementQuotaUsage($user);

        return response()->json([
            'status' => 'success',
            'message' => $botMessage
        ]);
    }

    private function callOpenAIWithFunctions($apiKey, $messages, $user)
    {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        // Define available functions
        $functions = $this->getFunctionDefinitions();
        
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'functions' => $functions,
            'function_call' => 'auto',
            'temperature' => 0.7,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return [
                'status' => 'error',
                'content' => 'API Error: ' . $httpCode,
                'tokens_used' => 0
            ];
        }

        $responseData = json_decode($response, true);
        $tokensUsed = $responseData['usage']['total_tokens'] ?? 0;
        $message = $responseData['choices'][0]['message'] ?? null;

        if (!$message) {
            return [
                'status' => 'error',
                'content' => 'Invalid response from OpenAI',
                'tokens_used' => $tokensUsed
            ];
        }

        // Check if OpenAI wants to call a function
        if (isset($message['function_call'])) {
            $functionName = $message['function_call']['name'];
            $functionArgs = json_decode($message['function_call']['arguments'], true);
            
            // Execute the function
            $functionResult = $this->executeFunction($functionName, $functionArgs, $user);
            
            // Add function call and result to messages
            $messages[] = $message;
            $messages[] = [
                'role' => 'function',
                'name' => $functionName,
                'content' => $functionResult
            ];
            
            // Call OpenAI again to get final response
            $data['messages'] = $messages;
            unset($data['functions']); // Don't need functions for final response
            unset($data['function_call']);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ]);

            $finalResponse = curl_exec($ch);
            curl_close($ch);
            
            $finalData = json_decode($finalResponse, true);
            $finalMessage = $finalData['choices'][0]['message']['content'] ?? $functionResult;
            $tokensUsed += $finalData['usage']['total_tokens'] ?? 0;
            
            return [
                'status' => 'success',
                'content' => $finalMessage,
                'tokens_used' => $tokensUsed,
                'tool_called' => $functionName,
                'device_name' => $functionArgs['device_name'] ?? null
            ];
        }

        // No function call, return direct response
        return [
            'status' => 'success',
            'content' => $message['content'] ?? 'No response',
            'tokens_used' => $tokensUsed
        ];
    }

    private function getFunctionDefinitions()
    {
        return [
            [
                'name' => 'get_device_stats',
                'description' => 'Get current status, location, speed, and sensor data for a specific device by name',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'device_name' => [
                            'type' => 'string',
                            'description' => 'The name of the device (e.g., "AMV:790", "LZZ:1077 AA")'
                        ]
                    ],
                    'required' => ['device_name']
                ]
            ],
            [
                'name' => 'get_devices_list',
                'description' => 'Get a list of devices filtered by status',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'status' => [
                            'type' => 'string',
                            'enum' => ['online', 'offline', 'moving', 'stopped', 'never_connected'],
                            'description' => 'Filter devices by status'
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Maximum number of devices to return',
                            'default' => 50
                        ]
                    ],
                    'required' => ['status']
                ]
            ],
            [
                'name' => 'get_history_stats',
                'description' => 'Get historical statistics for a device including distance, fuel consumed, engine hours, etc.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'device_name' => [
                            'type' => 'string',
                            'description' => 'The name of the device'
                        ],
                        'date_from' => [
                            'type' => 'string',
                            'description' => 'Start date/time in YYYY-MM-DD HH:mm:ss format'
                        ],
                        'date_to' => [
                            'type' => 'string',
                            'description' => 'End date/time in YYYY-MM-DD HH:mm:ss format'
                        ]
                    ],
                    'required' => ['device_name', 'date_from', 'date_to']
                ]
            ],
            [
                'name' => 'get_fuel_data',
                'description' => 'Get fuel consumption, fills, thefts, and efficiency data for a device',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'device_name' => [
                            'type' => 'string',
                            'description' => 'The name of the device'
                        ],
                        'date_from' => [
                            'type' => 'string',
                            'description' => 'Start date/time in YYYY-MM-DD HH:mm:ss format'
                        ],
                        'date_to' => [
                            'type' => 'string',
                            'description' => 'End date/time in YYYY-MM-DD HH:mm:ss format'
                        ]
                    ],
                    'required' => ['device_name', 'date_from', 'date_to']
                ]
            ],
            [
                'name' => 'get_device_events',
                'description' => 'Get events for a specific device',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'device_name' => [
                            'type' => 'string',
                            'description' => 'The name of the device'
                        ],
                        'event_type' => [
                            'type' => 'string',
                            'description' => 'Type of event to filter (optional)'
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Maximum number of events to return',
                            'default' => 50
                        ]
                    ],
                    'required' => ['device_name']
                ]
            ]
        ];
    }

    private function executeFunction($functionName, $args, $user)
    {
        try {
            switch ($functionName) {
                case 'get_device_stats':
                    return $this->chatbotService->getDeviceStats($user, $args['device_name']);
                
                case 'get_devices_list':
                    return $this->chatbotService->getDevicesList(
                        $user,
                        $args['status'] ?? null,
                        $args['limit'] ?? 50
                    );
                
                case 'get_history_stats':
                    return $this->chatbotService->getHistoryStats(
                        $user,
                        $args['device_name'],
                        $args['date_from'],
                        $args['date_to']
                    );
                
                case 'get_fuel_data':
                    return $this->chatbotService->getFuelData(
                        $user,
                        $args['device_name'],
                        $args['date_from'],
                        $args['date_to']
                    );
                
                case 'get_device_events':
                    return $this->chatbotService->getDeviceEvents(
                        $user,
                        $args['device_name'],
                        $args['event_type'] ?? null,
                        $args['limit'] ?? 50
                    );
                
                default:
                    return "Unknown function: {$functionName}";
            }
        } catch (\Exception $e) {
            return "Error executing {$functionName}: " . $e->getMessage();
        }
    }
}
