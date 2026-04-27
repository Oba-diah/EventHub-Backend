<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function createBooking(Request $request)
    {
        $validateData = $request->validate([

            'user_id' => 'required|exists:users,id',
            'event_id' => 'required|exists:events,id',
            'tickets_booked' => 'required|integer|min:1',
        ]);

        try {
            $event = Event::findOrFail($validateData['event_id']);

            if ($event->available_tickets < $validateData['tickets_booked']) {
                return response()->json([
                    'error' => 'Not enough tickets available'
                ], 400);
            }

            $totalPrice = $event->price * $validateData['tickets_booked'];

            $booking = new Booking();
            $booking->user_id = $validateData['user_id'];
            $booking->event_id = $validateData['event_id'];
            $booking->tickets_booked = $validateData['tickets_booked'];
            $booking->total_price = $totalPrice;
            $booking->save();

            $event->available_tickets -= $validateData['tickets_booked'];
            $event->save();

            return response()->json([
                'message' => 'Booking successful',
                'booking' => $booking->load(['user', 'event'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create booking'
            ], 500);
        }
    }

    public function readAllBookings()
    {
        try {
            $bookings = Booking::with(['user', 'event'])->get();
            return response()->json($bookings);
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
            return response()->json($booking);
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
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete booking'
            ], 500);
        }
    }
}