<?php

use App\Models\Appointment;
use App\Models\Day;
use App\Models\Service;
use Carbon\Carbon;

function get_time_slots($current_date, $service_id): \Illuminate\Http\JsonResponse|array|string
{
    try {

        $date = Carbon::parse($current_date);
        $dayOfWeek = $date->format('l');

        $day = Day::where('name', $dayOfWeek)->first();

        if (!$day || empty($day)) {
            return "Incorrect Day";
        }

        $service = Service::with('breaks', 'holidays', 'appointments')->find($service_id);


        if (!$service) {
            return "Service Not Found";
        }

        $minutes = $service->duration_in_mins_after_appointment + $service->duration_in_mins;
        $duration_in_minutes_after_appointment = $service->duration_in_mins_after_appointment;

        // Define the start and end dates

        $start_date = $date->setMinutes(ceil((int)now()->format('i') / $duration_in_minutes_after_appointment) * $duration_in_minutes_after_appointment);
//            $start_date = Carbon::parse('2023-05-08');
        $end_date = now()->addDays($service->slots_for_next_days);

        // Define the time interval for each time slot

        $time_interval = $service->duration_in_mins; // In minutes

//            dd( $time_interval );

        // Define an array to store the time slots
        $time_slots = [];

        // Loop through each day and generate the time slots
        for ($date = $start_date; $date <= $end_date; $date->addDay()) {

            // Skip Sundays
            if ($date->isSunday()) {
                $time_slots[$date->format('Y-m-d')] = [];
//                    continue;
            }

            // Get the start and end times for the day

            $dayOfWeek = Day::where('name', $date->format('l'))->first();

            $date = $date->copy()->setHour(Carbon::parse($dayOfWeek->start_time)->format('H'))->setMinutes(Carbon::parse($dayOfWeek->start_time)->format('i'))->setSeconds(0);

            $start_time = Carbon::parse($dayOfWeek->start_time);
            $end_time = Carbon::parse($dayOfWeek->end_time);

            // If the current day is the start date, set the start time to the next available time slot
            if ($date->isSameDay($start_date)) {
                $start_time = $start_time->isBefore(now()) ? now()->copy()->addMinutes($time_interval - (now()->minute % $time_interval)) : $start_time;
            }

//                dd( $start_time->isBefore(now()) );

            // Loop through each time slot and generate the start and end times
            $slot_number = 0;
            for ($time = $start_time; $time <= $end_time; $time->addMinutes($time_interval)) {

                // If the current time slot is in the past, skip it
                if ($date->isSameDay(now()) && $time->isBefore(now())) {
                    continue;
                }
                $end_slot_time = $time->copy()->addMinutes($time_interval);
                if ($end_slot_time <= $end_time) {
                    foreach ($service->breaks as $break) {

                        $breakStartTime = Carbon::parse($break->start_time);
                        $breakEndTime = Carbon::parse($break->end_time);

                        if ($time->between($breakStartTime, $breakEndTime) || $end_slot_time->between($breakStartTime, $breakEndTime)) {
                            $time = $breakEndTime;
                            $end_slot_time = $time->copy()->addMinutes($time_interval);
                        }

                    }

//                        if service has appointments, then skip the time

                    $time_between_appointment = false;
                    if ( count( $service->appointments)){
                        foreach ($service->appointments as $appointment) {
                            $appointmentStartTime = Carbon::parse($appointment->start_time);
                            $appointmentEndTime = Carbon::parse($appointment->end_time);

                            if (($time->between($appointmentStartTime, $appointmentEndTime) || $end_slot_time->between($appointmentStartTime, $appointmentEndTime)) && Carbon::parse($appointment->date)->eq($date->format('Y-m-d'))) {
                                $time_between_appointment = true;
                                $time->addMinutes($duration_in_minutes_after_appointment);
                            }
                        }
                    }

                    if ($time_between_appointment) {
                        continue;
                    }

                    $time_slots[$date->format('Y-m-d')][$slot_number]['start_time'] = $time->setSeconds(0)->format('H:i:s');
                    $time_slots[$date->format('Y-m-d')][$slot_number]['end_time'] = $end_slot_time->setSeconds(0)->format('H:i:s');

                }
                $time->addMinutes($service->duration_in_mins_after_appointment);
                $slot_number++;
            }
        }

//            if there is a holiday, set the key to empty array

        if (count( $service->holidays)) {
            foreach ($service->holidays as $holiday) {
                $holiday_date = Carbon::parse($holiday->date)->format('Y-m-d');
                if (array_key_exists($holiday_date, $time_slots)) {
                    $time_slots[$holiday_date] = [];
                }
            }
        }

        return $time_slots;

    } catch (\Exception $exception) {
        return response()->json(['success' => false, 'message' => $exception->getMessage()], $exception->getCode());
    }

}

function show_calender_data($data): \Illuminate\Http\JsonResponse|array
{

    try {

        $current_date = $data['current_date'];
        $service_id = $data['service_id'];

        $time_slots = get_time_slots($current_date, $service_id);
        $all_data['time_slots'] = $time_slots;
        $services = Service::all();
        $all_data['services'] = $services->toArray();
        $all_data['appointments'] = Appointment::where('service_id', $service_id)->get();

        return $all_data;

    } catch (\Exception $exception) {
        return response()->json(['success' => false, 'message' => $exception->getMessage()], $exception->getCode());
    }
}

function book_appointment_for_user($data){
    try {

//        dd( $data );

        $service_id = $data['service_id'];
        $date = $data['date'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $number_of_clients = $data['number_of_clients'];
        $details = $data['details'];

        $service = Service::with('holidays', 'breaks')->find($service_id);

        if ($number_of_clients > $service->max_client_per_appointment) {
            return response()->json(['success' => false, 'message' => "You cannot book appointment for more than {$service->max_client_per_appointment} client per appointment"], 422);
        }

        $carbonStartTime = Carbon::parse($start_time)->format('H:i:s');
        $carbonEntTime = Carbon::parse($end_time)->format('H:i:s');

        $time_slots = get_time_slots($date, $service->id);


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
            return response()->json(['status' => false, 'message' => 'Slot is already booked'], 422);
        }

        if (is_array($details)) {

            for ($i = 0; $i < $number_of_clients; $i++) {
                $appointment = Appointment::create([
                    'first_name' => $details[$i]['first_name'] ?? $details[0]['first_name'],
                    'last_name' => $details[$i]['last_name'] ?? $details[0]['last_name'],
                    'email' => $details[$i]['email'] ?? $details[0]['email'],
                    'service_id' => $service->id,
                    'date' => $date,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                ]);
            }

        }

        return response()->json(['success' => true, 'message' => 'Appointment has been created'], 201);
    } catch ( Exception $exception ){
        return response()->json(['success' => false, 'message' => $exception->getMessage()], $exception->getCode());
    }
}
