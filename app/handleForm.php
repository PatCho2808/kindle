<?php

require __DIR__ . '/vendor/autoload.php';

use \CloudConvert\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


if(!isset($_FILES['ebook_file']) || !isset($_POST['user_email']))
{
    header('Location: index.php');
}

function convertFile()
{
    $cloudconvert = new CloudConvert([
        'api_key' => file_get_contents('secrets/API_KEY.txt'),
        'sandbox' => false
    ]);

    $job = getJob();
    return convert($cloudconvert, $job);
}

/**
 * @param CloudConvert $cloudconvert
 * @param Job $job
 */
function convert(CloudConvert $cloudconvert, Job $job)
{
    try {
        $cloudconvert->jobs()->create($job);
        $uploadTask = $job->getTasks()->name('import_epub')[0];
        move_uploaded_file($_FILES['ebook_file']['tmp_name'], '/tmp/' . $_FILES['ebook_file']['name']);
        $cloudconvert->tasks()->upload($uploadTask, fopen('/tmp/' . $_FILES['ebook_file']['name'], 'r'));
        $cloudconvert->jobs()->wait($job); // Wait for job completion

        foreach ($job->getExportUrls() as $file) {

            $source = $cloudconvert->getHttpTransport()->download($file->url)->detach();
            $dest = fopen('/tmp/' . $file->filename, 'w');

            stream_copy_to_stream($source, $dest);

            return '/tmp/' . $file->filename;

        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

/**
 * @return Job
 */
function getJob()
{
    $job = (new Job())
        ->addTask(
            (new Task('import/upload', 'import_epub'))
        )
        ->addTask(
            (new Task('convert', 'convert_epub_to_mobi'))
                ->set('input_format', 'epub')
                ->set('output_format', 'mobi')
                ->set('engine', 'calibre')
                ->set('input', ["import_epub"])
        )
        ->addTask(
            (new Task('export/url', 'export_mobi'))
                ->set('input', ["convert_epub_to_mobi"])
        );
    return $job;
}

function sendMail($path_to_file)
{
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'smtp.sendgrid.net';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'apikey';                     // SMTP username
        $mail->Password   = file_get_contents('secrets/SMTP_KEY.txt');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom('patcho2808@gmail.com');
        $mail->addAddress('ciastek2808@kindle.com');     // Add a recipient
        // Attachments
        $mail->addAttachment($path_to_file);         // Add attachments

        // Content
        $mail->Subject = 'empty';
        $mail->Body    = 'empty';

        $mail->send();
        header('Location: index.php');
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}


$path_to_converted_file = convertFile();
sendMail($path_to_converted_file);


