<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\MultiImage;
use App\Models\Room;
use App\Models\RoomBookedDate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class FrontendRoomController extends Controller
{
    public function AllFrontendRoomList()
    {
        $rooms = Room::latest()->get();
        return view('frontend.room.all_rooms', compact('rooms'));
    }

    public function RoomDetailsPage($id)
    {
        $roomdetails = Room::find($id);
        $multiImage = MultiImage::where('rooms_id', $id)->get();
        $facility = Facility::where('rooms_id', $id)->get();
        $otherRooms = Room::where('id', '!=', $id)->orderBy('id', 'DESC')->limit(2)->get();
        $room_id = $id;
        return view('frontend.room.room_details', compact('roomdetails', 'multiImage', 'facility', 'otherRooms', 'room_id'));
    }

    public function BookingSeach(Request $request)
    {
        $request->flash();

        if ($request->check_in == $request->check_out) {

            $notification = array(
                'message' => 'Something want to worng. Checkin time same checkout time',
                'alert-type' => 'error'
            );

            return redirect()->back()->with($notification);
        }
        $checkInOut = explode(' - ', $request->check_in);
        $check_in = $checkInOut[0];
        $check_out = $checkInOut[1];

        $sdate = date('Y-m-d', strtotime($check_in));
        $edate = date('Y-m-d', strtotime($check_out));
        $alldate = Carbon::create($edate)->subDay();
        $d_period = CarbonPeriod::create($sdate, $alldate);
        $datetime_array = [];
        foreach ($d_period as $period) {
            array_push($datetime_array, date('Y-m-d', strtotime($period)));
        }

        // Cach 1: Not Use the tabel room_booked_dates => Đỡ tốn tài nguyên cho 1 table
        $bookings = Booking::withCount('assign_rooms')->get();
        /**
        if (!empty($bookings)) {
            foreach ($bookings as $key => $value) {
                $sdate = date('Y-m-d', strtotime($value->check_in));
                $edate = date('Y-m-d', strtotime($value->check_out));
                $alldate = Carbon::create($edate)->subDay();
                $d_period = CarbonPeriod::create($sdate, $alldate);
                $dt_array = [];
                foreach ($d_period as $period) {
                    array_push($dt_array, date('Y-m-d', strtotime($period)));
                }
                $value['check_times'] = $dt_array;
            }
        }
        $check_date_booking_ids= [];

        if (!empty($bookings)) {
            foreach ($bookings as $key => $value) {
                if (!empty($value['check_times'])) {
                    foreach ($value['check_times'] as $item) {
                        if( in_array($item, $datetime_array) ) {
                            $check_date_booking_ids[] = $value['id'];
                            break;
                        }
                    }
                }
            }
        }
        */
        
        // Cach 2: Use the tabel room_booked_dates
        $check_date_booking_ids = RoomBookedDate::whereIn('book_date', $datetime_array)->distinct()->pluck('booking_id')->toArray();
        

        $sqlQuery = "SELECT t1.*, COUNT(t2.rooms_id) as room_numbers_count FROM rooms t1
                    LEFT JOIN room_numbers t2 ON t1.id = t2.rooms_id AND t2.`status` = 'Active'
                    WHERE t1.status = 1
                    GROUP BY t1.id";

        $rooms = Room::withCount('room_numbers')->where('status', 1)->get();

        $bookings = Booking::withCount('assign_rooms')
                            ->whereIn('id', $check_date_booking_ids)
                            ->get()
                            ->toArray();

        return view('frontend.room.search_room', compact('rooms', 'check_date_booking_ids'));
    }

    public function SearchRoomDetails(Request $request, $id)
    {
        $request->flash();
        $roomdetails = Room::findOrFail($id);
        $multiImage = MultiImage::where('rooms_id', $id)->get();
        $facility = Facility::where('rooms_id', $id)->get();

        $otherRooms = Room::where('id', '!=', $id)->orderBy('id', 'DESC')->limit(2)->get();
        $room_id = $id;
        return view('frontend.room.search_room_details', compact('roomdetails', 'multiImage', 'facility', 'otherRooms', 'room_id'));
    }

    public function CheckRoomAvailability(Request $request)
    {

        $sdate = date('Y-m-d', strtotime($request->check_in));
        $edate = date('Y-m-d', strtotime($request->check_out));
        
        $alldate = Carbon::create($edate)->subDay();
        $d_period = CarbonPeriod::create($sdate, $alldate);
        $dt_array = [];
        foreach ($d_period as $period) {
            array_push($dt_array, date('Y-m-d', strtotime($period)));
        }

        $check_date_booking_ids = RoomBookedDate::whereIn('book_date', $dt_array)->distinct()->pluck('booking_id')->toArray();

        $room = Room::withCount('room_numbers')->find($request->room_id);

        $bookings = Booking::withCount('assign_rooms')->whereIn('id', $check_date_booking_ids)->where('rooms_id', $room->id)->get()->toArray();

        $total_room_booked = array_sum(array_column($bookings, 'assign_rooms_count'));

        $av_room = @$room->room_numbers_count - $total_room_booked;

        $toDate = Carbon::parse($request->check_in);
        $fromDate = Carbon::parse($request->check_out);
        $nights = $toDate->diffInDays($fromDate);

        return response()->json(['available_room' => $av_room, 'total_nights' => $nights]);
    }
}
