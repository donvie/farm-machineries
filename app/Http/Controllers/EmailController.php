<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationEmail; // Create this Mailable

class EmailController extends Controller
{
    public function sendEmail(Request $request)
    {
        try {
            Mail::to($request->email)->send(new NotificationEmail($request->subject, $request->message));

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}