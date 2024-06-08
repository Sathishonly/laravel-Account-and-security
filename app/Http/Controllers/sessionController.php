<?php

namespace App\Http\Controllers;

use App\Models\sessions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\TokenRepository;

class sessionController extends Controller
{
    protected $sessions;
    protected $validator;

    public function __construct(sessions $sessions, Validator $validator)
    {
        $this->sessions = $sessions;
        $this->validator = $validator;
    }
    public function sessionstore(Request $request)
    {
        $input = $request->all();
        $validation = $this->validator::make($input, [
            'userId' => 'required',
            'tokenId' => 'required',
            'token' => 'required',
            'device' => 'required',
            'location' => 'required',
            'date' => 'required',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors()->first();
            return response()->json(['status_code' => 400, 'error_code' => $errors]);
        } else {
            $tokenId = $request->tokenId;
            $session = $this->sessions::where('tokenId', $tokenId)->first();
            $session->token = $request->token;
            $session->device = $request->device;
            $session->location = $request->location;
            $session->date = $request->date;
            $session->save();
            return response()->json(['status_code' => 200, 'message' => "session stored successfully"]);
        }
    }


    public function getsession(Request $request)
    {
        $input = $request->all();
        $validation = $this->validator::make($input, [
            'userId' => 'required',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors()->first();
            return response()->json(['status_code' => 400, 'error_code' => $errors]);
        } else {
            $session = $this->sessions::where('userId', $request->userId)->get();
            if ($session) {
                return response()->json(['status_code' => 200, 'data' => $session]);
            } else {
                return response()->json(['status_code' => 400, 'message' => "No sessions found"]);
            }
        }
    }


    public function endsession(Request $request)
    {
        $input = $request->all();
        $validation = $this->validator::make($input, [
            'sessionid' => 'required',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors()->first();
            return response()->json(['status_code' => 400, 'error_code' => $errors]);
        } else {
            try {
                $sessionid = $request->sessionid;
                $session = $this->sessions::find($sessionid);
                if (!$session) {
                    return response()->json([
                        'message' => 'Session not found',
                        'status_code' => 404
                    ]);
                }
                $tokenId = $session->tokenId;
                if (!$tokenId) {
                    return response()->json([
                        'message' => 'Token not found in the session',
                        'status_code' => 404
                    ], 404);
                }

                $tokenRepository = app(TokenRepository::class);
                $tokenRepository->revokeAccessToken($tokenId);
                return response()->json([
                    'message' => 'Session ended successfully',
                    'status_code' => 200
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Failed to revoke token',
                    'error_message' => $e->getMessage(),
                ], 500);
            }
        }
    }

    public function signoutalldevice(Request $request)
    {
        $input = $request->all();
        $validation = $this->validator::make($input, [
            'userId' => 'required',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors()->first();
            return response()->json(['status_code' => 400, 'error_code' => $errors]);
        } else {
            $userId = $request->userId;
            $sessions = $this->sessions::where('userId', $userId)->get();

            if ($sessions->isempty()) {
                return response()->json([
                    'message' => 'Token not found in the session',
                    'status_code' => 404
                ], 404);
            } else {
                foreach ($sessions as $session) {
                    $tokenId = $session->tokenId;
                    $tokenRepository = app(TokenRepository::class);
                    $tokenRepository->revokeAccessToken($tokenId);
                    $session->delete();
                }
                return response()->json([
                    'message' => 'signout all device successfully',
                    'status_code' => 200
                ], 200);
            }
        }
    }
}
