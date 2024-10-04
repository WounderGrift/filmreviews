<?php

namespace App\Http\Helpers;

use Illuminate\Http\JsonResponse;
use PHPMailer\PHPMailer\PHPMailer;

class MailHelper
{
    public static function compose($template, string $email, string $subject): JsonResponse
    {
        require base_path("vendor/autoload.php");
        try {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = config('mail.mailers.smtp.host');
            $mail->SMTPAuth = true;
            $mail->Username = config('mail.mailers.smtp.username');
            $mail->Password = config('mail.mailers.smtp.password');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = config('mail.mailers.smtp.port');
            $mail->setFrom(config('mail.mailers.smtp.username'), config('app.app_name'));
            $mail->addAddress($email);
            $mail->isHTML();
            $mail->Subject = $subject;
            $mail->Body = $template;

            if ($mail->send())
                return response()->json(['success' => true]);
            else
                return response()->json(['success' => false, 'message' => 'Unknown Error']);

        } catch (\Exception $error) {
            return response()->json(['success' => false, 'message' => $error->getMessage()], 403);
        }
    }
}
