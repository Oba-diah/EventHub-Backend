<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
   public function createEvent(Request $request){
    $validateData = $request->validate([
        'title' => 'required',
        'description' => 'required',
        'date' => 'required',
        'time' => 'required',
        'location' => 'required',
        'image' => 'required',
        'price' => 'required',
        'available_tickets' => 'required',
    ]);
    $event=new Event();
    $event->title=$validateData['title'];
    $event->description=$validateData['description'];
    $event->date=$validateData['date'];
    $event->time=$validateData['time'];
    $event->location=$validateData['location'];
    $event->image=$validateData['image']; 
    $event->price=$validateData['price'];
    $event->available_tickets=$validateData['available_tickets'];
    $event->save();  
   return response()->json([
    'message' => 'Event created successfully',
    'event' => $event
   ], 201);
   }

    public function readAllEvents(){
    try {
        $events = Event::all();
        return response()->json($events);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to retrieve events'
        ], 500);
    }
  }
  public function readEvent($id){
    try {
        $event = Event::findOrFail($id);
        return response()->json($event);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Event not found'
        ], 404);
    }
  }
  public function updateEvent(Request $request, $id){
    try {
        $event = Event::findOrFail($id);
        $validateData = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'date' => 'required',
            'time' => 'required',
            'location' => 'required',
            'image' => 'required',
            'price' => 'required',
            'available_tickets' => 'required',
        ]);
        $event->update($validateData);
        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to update event'
        ], 500);
    }
  }
  public function deleteEvent($id){
    try {
        $event = Event::findOrFail($id);
        $event->delete();
        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to delete event'
        ], 500);
    }
  }

}
