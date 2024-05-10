<?php

namespace App\Scheduler;

use App\Scheduler\Message\UpdateExpiredEventReservations;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule]
class UpdateExpiredEventReservationScheduler implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())->add(
            RecurringMessage::every('5 minutes', new UpdateExpiredEventReservations()),
        );

    }
}

