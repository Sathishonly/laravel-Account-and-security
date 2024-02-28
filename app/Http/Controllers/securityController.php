<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use App\Models\otp;
use Carbon\Carbon;

class securityController extends Controller
{

    public function twostepverification(Request $request)
    {
        $input = $request->all();
        $validation = Validator::make($input, [
            'userId' => 'required',
            'status' => 'required',         //true or false
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors(), 'status_code' => 400], 400);
        } else {
            $userId = $request->userId;
            $user = User::find($userId);
            if($user){
                $user->status = $request->status;
                $user->save();
                return response()->json(['status_code' => 200, 'message' => 'Twostep verification status updated']);
            }else{
                return response()->json(['status_code' => 404, 'message' => 'User not found'], 404);
            }

        }
    }


    public function sendotp(Request $request)
    {
        require base_path("vendor/autoload.php");
        $mail = new PHPMailer(true);

        $input = $request->all();
        $validation = Validator::make($input, [
            'email' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors(), 'status_code' => 400], 400);
        } else {

            $email = $request->input('email');

            $userexist = User::where('email', $email)->first();
            if (!$userexist) {
                return response()->json(['status_code' => 400, 'message' => 'Email does not Exist']);
            } else {
                $otp = mt_rand(100000, 999999);

                try {
                    $existingOTP = otp::where('email', $email)->first();
                    if ($existingOTP) {
                        $existingOTP->delete();
                    }

                    $mail->SMTPDebug = 0;
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; //  smtp host
                    $mail->SMTPAuth = true;
                    //    $mail->Username = 'contact@simpencil.com';   //  sender username
                    //    $mail->Password = 'D7#^$UBYE';       // sender password

                    $mail->Username = 'test@gmail.com'; //  sender username
                    $mail->Password = '*****'; // sender password

                    $mail->SMTPSecure = 'tls'; // encryption - ssl/tls
                    $mail->Port = 587; // port - 587/465

                    $mail->setFrom('test@gmail.com', 'Email verification OTP');

                    //$mail->addCC($request->emailCc);
                    //$mail->addBCC($request->emailBcc);

                    //   $mail->addReplyTo('sender-reply-email', 'sender-reply-name');

                    $mail->isHTML(true); // Set email content format to HTML

                    $mail->Subject = 'Email verification OTP';
                    $mail->Body = '<html>
                <head>
                <style>
                @import url("https://fonts.googleapis.com/css2?family=Assistant:wght@300;400;500;600;700&display=swap");
              </style>
            </head>
             <body>
            <div style="width:65%; padding: 10px;">
            <div>
                <section  style="background-color: white;">
                    <p style="font-size:12pt; margin: 15px 0px 0px 0px; font-family: \'Assistant\', sans-serif;" class="mt-5">Your OTP is ' . $otp . ' to complete the verification process.</p>

                    <p style="font-size:12pt; margin: 15px 0px 0px 0px; font-family: \'Assistant\', sans-serif;" class="mt-5">Enter the OTP in the designated field to proceed with your application.</p>

					<p style="font-size:12pt;margin: 15px 0px 0px 0px;  font-family: \'Assistant\', sans-serif;" class="mt-3">Best regards,</p>
					<p style="font-size:12pt;font-size:12pt;margin:0px; padding-top: 0px; font-family: \'Assistant\', sans-serif;">Management team</p>


				</section>

                <footer">
                <span style="font-size:12pt;margin: 0px; font-family: \'Assistant\', sans-serif;"><a style="color:blue;" target="_blank" href="https://www.google.com">www.google.com</a> </span>
            </footer>
            </div>
           </div>
            </body>

            </html>';
                    $mail->AddAddress($email);
                    $mail->send();


                    $otpEntry = otp::create([
                        'email' => $email,
                        'otp' => $otp,
                    ]);

                    return response()->json([
                        'status_code' => 200,
                        'message' => 'OTP sent successfully, please check your inbox.',
                    ]);

                } catch (\Exception $e) {
                    return response()->json([
                        'error' => 'Failed to send OTP',
                        'error_message' => $e->getMessage(),
                    ], 500);
                }
            }
        }
    }


    public function verifyotp(Request $request)
    {
        $input = $request->all();
        $validation = Validator::make($input, [
            'email' => 'required',
            'otp' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors(), 'status_code' => 400], 400);
        } else {
            $email = $request->input('email');
            $otp = $request->input('otp');

            $emaildetails = otp::where('email', $email)->first();

            if (!$emaildetails) {
                return response()->json([
                    'status_code' => 200,
                    'error' => 'Data not found',
                ]);
            }

            if ($otp === strval($emaildetails->otp)) {
                $createdexpiryTime = Carbon::parse($emaildetails->created_at)->addMinutes(5);

                if (Carbon::now()->greaterThan($createdexpiryTime)) {
                    $emaildetails->delete();
                    return response()->json([
                        'status_code' => 401,
                        'error' => 'OTP has expired',
                    ]);
                } else {
                    return response()->json([
                        'status_code' => 200,
                        'message' => 'OTP verified',
                    ]);
                }
            } else {
                return response()->json([
                    'status_code' => 400,
                    'error' => 'Invalid OTP',
                ]);
            }
        }
    }
}
