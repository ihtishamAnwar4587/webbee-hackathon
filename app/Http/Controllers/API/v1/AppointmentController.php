<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShowCalenderRequest;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Day;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Helpers;

class AppointmentController extends Controller
{

    public function show_calender(ShowCalenderRequest $request)
    {
        try {

            $data = show_calender_data($request->validated());
            return response()->json(['message' => "List of time slots, appointments and services", 'data' => $data], 200);

        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], $exception->getCode());
        }
    }

    public function book_appointment(StoreAppointmentRequest $request): ?\Illuminate\Http\JsonResponse
    {
        try {

            /*$service = Service::with('holidays', 'breaks')->find($request->service_id);

            if ($request->number_of_clients > $service->max_client_per_appointment) {
                return response()->json(['success' => false, 'message' => "You cannot book appointment for more than {$service->max_client_per_appointment} client per appointment"], 422);
            }

            $date = $request->date;
            $start_time = $request->start_time;
            $end_time = $request->end_time;

            $carbonStartTime = Carbon::parse($start_time)->format('H:i:s');
            $carbonEntTime = Carbon::parse($end_time)->format('H:i:s');

            $time_slots = $this->get_time_slots($request->date, $service->id);


//            check if the given start_time and end_time matches any time slot
            $timeIsBetweenSlots = false;
            foreach ( $time_slots[$date] as $dateCheck ){

                $dateStartTime = Carbon::parse($dateCheck['start_time']);
                $dateEndTime = Carbon::parse($dateCheck['end_time']);

                if ( $dateStartTime->eq($carbonStartTime) && $dateEndTime->eq($carbonEntTime)){
                    $timeIsBetweenSlots = true;
                    break;
                }
            }

            if ( !$timeIsBetweenSlots ){
                return response()->json(['success' => false, 'message' => 'No matching slot found !'], 404);
            }

//            check if the user is booking the appointment on off day

            if (count($service->holidays)) {
                foreach ($service->holidays as $holiday) {
                    if (Carbon::parse($date)->eq(Carbon::parse($holiday->date))) {
                        return response()->json(['success' => false, 'message' => 'You cannot book appointment on a holiday']);
                    }
                }
            }


//            Check if the current time is greater than the time we are getting in the api
            $current_time = now();

            $dateAndTimeGet = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' ' . $start_time);
            if ($current_time->gt($dateAndTimeGet)) {
                return response()->json(['success' => false, 'message' => 'You cannot book appointment in past time'], 422);
            }

            $appointments = Appointment::where(function ($query) use ($date, $start_time, $end_time) {
                $query->whereDate('date', $date);
                $query->whereBetween('start_time', [$start_time, $end_time]);
            })->orWhere(function ($query) use ($date, $start_time, $end_time) {
                $query->whereDate('date', $date);
                $query->whereBetween('end_time', [$start_time, $end_time]);
            })->get();

            if (count($appointments)) {
                return response()->json(['status' => false, 'message' => 'Appointment is already booked'], 422);
            }

            $details = $request->details;

            if (is_array($request->details)) {

                for ($i = 0; $i < $request->number_of_clients; $i++) {
                    $appointment = Appointment::create([
                        'first_name' => $details[$i]['first_name'] ?? $details[0]['first_name'],
                        'last_name' => $details[$i]['last_name'] ?? $details[0]['last_name'],
                        'email' => $details[$i]['email'] ?? $details[0]['email'],
                        'service_id' => $service->id,
                        'date' => $request->date,
                        'start_time' => $request->start_time,
                        'end_time' => $request->end_time,
                    ]);
                }

            }*/

            return book_appointment_for_user($request->validated());

//            return response()->json(['success' => true, 'message' => 'Appointment has been created'], 201);


        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], $exception->getCode());
        }

    }
}
