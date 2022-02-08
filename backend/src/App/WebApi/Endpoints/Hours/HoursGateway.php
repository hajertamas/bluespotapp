<?php

namespace App\WebApi\Endpoints\Hours;

use App\Models\Hours;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

final class HoursGateway
{

    public function __construct()
    {
    }


    public function createHours(User $user, Carbon $date, int $hours, string $description = null): Hours
    {
        $hours = Hours::create([
            'user_id' => $user->id,
            'date_day' => $date,
            'hours' => $hours,
            'description' => $description
        ]);

        return $hours;
    }

    public function getHoursForUser(User $user, Carbon $from, Carbon $to)
    {
        //$result = $user->hours()->whereDate('dates_day', '>=', "'{$from->toDateString()}'")->whereDate('date_day', '<=', "'{$to->toDateString()}'")->get();
        $result = $user->hours()->whereDate('date_day', '>=', $from)->whereDate('date_day', '<=', $to)->get();

        return $result;
    }
}
