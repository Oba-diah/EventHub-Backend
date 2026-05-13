<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function createBooking(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'event_id' => 'required|exists:events,id',
            'tickets_booked' => 'required|integer|min:1',
        ]);

        try {
            $event = Event::findOrFail($data['event_id']);

            if ($event->available_tickets < $data['tickets_booked']) {
                return response()->json([
                    'error' => 'Not enough tickets available'
                ], 400);
            }

            $totalPrice = $event->price * $data['tickets_booked'];

            $booking = Booking::create([
                'user_id' => $data['user_id'],
                'event_id' => $data['event_id'],
                'tickets_booked' => $data['tickets_booked'],
                'total_price' => $totalPrice,
            ]);

            $event->available_tickets -= $data['tickets_booked'];
            $event->save();

            return response()->json([
                'message' => 'Booking successful',
                'booking' => $booking->load(['user', 'event'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function readUserBookings(Request $request)
    {
        try {
            $bookings = Booking::with(['event'])
                ->where('user_id', Auth::id())  // ← filters by authenticated user
                ->latest()
                ->get();

            return response()->json($bookings, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve bookings'
            ], 500);
        }
    }

   
    public function readAllBookings()
    {
        try {
            $bookings = Booking::with(['user', 'event'])->latest()->get();

            return response()->json($bookings, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve bookings'
            ], 500);
        }
    }

    public function readBooking($id)
    {
        try {
            $booking = Booking::with(['user', 'event'])->findOrFail($id);

            return response()->json($booking, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Booking not found'
            ], 404);
        }
    }

    public function deleteBooking($id)
    {
        try {
            $booking = Booking::findOrFail($id);

            $event = Event::find($booking->event_id);
            if ($event) {
                $event->available_tickets += $booking->tickets_booked;
                $event->save();
            }

            $booking->delete();

            return response()->json([
                'message' => 'Booking cancelled successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete booking'
            ], 500);
        }
    }
}