<?php

namespace App\WebApi\Endpoints\Hours;

use App\Config;
use App\Models\Hours;
use App\WebApi\Auth;
use App\WebApi\Interfaces\WebApiEndpoint;
use App\WebApi\Models\ApiRequest;
use App\WebApi\Models\ApiResponse;
use Carbon\Carbon;

final class HoursEndpoint implements WebApiEndpoint
{

    private $response;
    private $request;
    private $gateway;
    private $auth;

    public function __construct(ApiRequest $request)
    {
        $this->request = $request;
        $this->auth = new Auth($request);
        $this->gateway = new HoursGateway;
    }

    public function process(): void
    {
        if(!$this->auth->isAuthenticated()){
            $this->response = new ApiResponse(401, "Must be logged in to use this endpoint");
            return;
        }

        $method = $this->request->getRequestMethod();
        $response;
        switch ($method) {
            case "GET":
                $response = $this->get();
                break;
            case "POST":
                $response = $this->post();
                break;
            default:
                $response = new ApiResponse(501, "Not implemented");
                break;
        }

        $this->response = $response;
    }

    public function getResponse(): ApiResponse
    {
        if (empty($this->response)) {
            $msg = "Internal error";
            if (Config::get()->getDevMode()) {
                $msg += ": No response from" . self::class;
            }
            return new ApiResponse(500, $msg);
        }

        return $this->response;
    }

    #region GET
    private function get(): ApiResponse
    {
        $user = $this->auth->getUser();
        
        $qArr = $this->request->getQueryArray();

        if(empty($qArr[0])){
            return new ApiResponse(400, "Must specify dates in url (../2000-01-01to2000-01-31)");
        }
        
        $dates = explode("to", $qArr[0]);

        if(empty($dates[1])){
            return new ApiResponse(400, "Invalid dates format (../2000-01-01to2000-01-31)");
        }

        $from = Carbon::parse($dates[0]);

        $to = Carbon::parse($dates[1]);

        if(empty($from) || empty($to)){
            return new ApiResponse(400, "Invalid dates format (../2000-01-01to2000-01-31)");
        }
        

        $hours = $this->gateway->getHoursForUser($user, $from, $to);

        $result = [];

        foreach($hours as $hour){
            $result[] = [
                "date_day" => $hour->date_day,
                "hours" => $hour->hours,
                "description" => $hour->description
            ];
        }

        return new ApiResponse(200, $result);
    }
    #endregion

    #region POST
    private function post(): ApiResponse
    {
        $user = $this->auth->getUser();

        $body = $this->request->getRequestBody();

        if(empty($body->date_day)){
            return new ApiResponse(400, "Day is required");
        }

        if(empty($body->hours)){
            return new ApiResponse(400, "Hours is required");
        }

        if(!is_numeric($body->hours) || (int)$body->hours < 1 || (int)$body->hours > 24){
            return new ApiResponse(400, "Hours must be a numeric value greater than 0 and less than 25");
        }

        $hours = (int)$body->hours;

        try{
            $day = Carbon::parse($body->date_day);
        }catch(\Throwable $e){
            return new ApiResponse(400, "Day must be a valid date string (2022-02-04)");
        }

        if($day < Carbon::now()){
            return new ApiResponse(403, "Reserving hours for a past date is not allowed");
        }

        if(
            $day->dayOfWeek == Carbon::SATURDAY
            ||
            $day->dayOfWeek == Carbon::SUNDAY
        ){
            return new ApiResponse(403, "Reserving hours for weekends is not allowed");
        }

        $description = NULL;
        if(!empty($body->description)){
            $description = (string)$body->description;
        }

        $usersHours = Hours::where([
            ['user_id', '=', $user->id],
            ['date_day', '=', $day]
        ])->get();

        $userHoursForSelectedDay = $hours;
        foreach($usersHours as $uh){
            $userHoursForSelectedDay += $uh->hours;
        }


        if($userHoursForSelectedDay > 24){
            return new ApiResponse(400, "Hours cannot exceed 24 for a particular day, you already have " . ($userHoursForSelectedDay - $hours) . " hours on day " . $day->toDateString() . " and trying to add $hours more hours");
        }

        $object = $this->gateway->createHours($user, $day, $hours, strip_tags($description));
        
        return new ApiResponse(200, [
            "date_day" => $object->date_day,
            "hours" => $object->hours,
            "description" => $object->description
        ]);
    }
    #endregion

}
