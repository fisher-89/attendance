<?php

namespace App\Listeners;

use App\Events\LeaveUpdating;

class UpdateClockWithLeaveRequest
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LeaveUpdated  $event
     * @return void
     */
    public function handle(LeaveUpdating $event)
    {
        $model           = $event->model;
        $statusOriginal  = $model->getOriginal('status');
        $statusAttribute = $model->getAttribute('status');
        switch ($statusOriginal . $statusAttribute) {
            case "01":
                break;
            case "0-2":
                break;
        }
    }
}
