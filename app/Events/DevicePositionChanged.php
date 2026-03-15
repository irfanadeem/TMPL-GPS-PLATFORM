<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Tobuli\Entities\Device;


use Illuminate\Contracts\Broadcasting\ShouldBroadcast;


class DevicePositionChanged extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $device;

    public function __construct(Device $device) {
        $this->device = $device;
    }

    public function broadcastOn()
    {
        // Using md5(device_id) to match the pattern seen in NewMessage.php for security/obfuscation
        return [md5('device_' . $this->device->id)];
    }

    public function broadcastAs()
    {
        return 'DevicePositionChanged';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->device->id,
            'name' => $this->device->name,
            'lat' => $this->device->traccar->lastValidLatitude,
            'lng' => $this->device->traccar->lastValidLongitude,
            'speed' => $this->device->traccar->speed,
            'course' => $this->device->traccar->course,
            'altitude' => $this->device->traccar->altitude,
            'time' => $this->device->traccar->time,
            'timestamp' => strtotime($this->device->traccar->time),
            'status' => $this->device->status, // Accessor
            'status_color' => $this->device->status_color, // Accessor
            'engine_status' => $this->device->engine_status, // Accessor
            'protocol' => $this->device->traccar->protocol,
            'sensors' => $this->device->sensors_values // Assuming this is set on the device instance during processing
        ];
    }
}

