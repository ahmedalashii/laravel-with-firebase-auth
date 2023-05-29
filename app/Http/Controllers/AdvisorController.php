<?php

namespace App\Http\Controllers;

use App\Models\Advisor;
use App\Models\Trainee;
use App\Models\Discipline;
use Illuminate\Http\Request;
use App\Mail\AdvisorTraineeMail;
use App\Http\Traits\EmailProcessing;
use Illuminate\Support\Facades\Session;
use App\Notifications\TraineeNotification;
use App\Http\Traits\FirebaseStorageFileProcessing;

class AdvisorController extends Controller
{
    use FirebaseStorageFileProcessing, EmailProcessing;

    public function index()
    {
        $recent_trainees = auth_advisor()->recent_enrolled_trainees()->take(5)->get();
        return view('advisor.index', compact('recent_trainees'));
    }


    public function edit()
    {
        $disciplines = Discipline::withoutTrashed()->select('id', 'name')->get();
        return view('advisor.edit', compact('disciplines'));
    }

    public function read_notifications(Request $request)
    {
        $advisor = auth_advisor();
        $notifications = $advisor->notifications();
        $notifications->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }


    public function send_email_form(Trainee $trainee)
    {
        $advisor = auth_advisor();
        $trainee = $advisor->trainees()->where('trainee_id', $trainee->id)->first();
        if (!$trainee) {
            return redirect()->back()->with(['fail' => 'You are not allowed to access this trainee!', 'type' => 'error']);
        }
        return view('advisor.send_email', compact('trainee'));
    }

    public function send_email(Trainee $trainee)
    {
        $advisor = auth_advisor();
        $trainee = $advisor->trainees()->where('trainee_id', $trainee->id)->first();
        if (!$trainee) {
            return redirect()->back()->with(['fail' => 'You are not allowed to access this trainee!', 'type' => 'error']);
        }
        $data = request()->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:255',
        ]);
        $mailable = new AdvisorTraineeMail($advisor, $trainee, $data['subject'], $data['message']);
        $this->sendEmail($trainee->email, $mailable);
        $trainee->notify(new TraineeNotification(null, $advisor, 'You have a new email from your advisor. Check your email inbox.'));
        // Store the email in the database
        $advisor->sent_emails()->create([
            'trainee_id' => $trainee->id,
            'advisor_id' => $advisor->id,
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);
        return redirect()->back()->with(['success' => 'Email sent successfully 😎!', 'type' => 'success']);
    }


    public function trainee_details(Trainee $trainee)
    {
        $advisor = auth_advisor();
        $trainee = $advisor->trainees()->where('trainee_id', $trainee->id)->first()->load('disciplines');
        if (!$trainee) {
            return redirect()->back()->with(['fail' => 'You are not allowed to access this trainee!', 'type' => 'error']);
        }
        return view('advisor.trainee_details', compact('trainee'));
    }

    public function trainees_list()
    {
        $advisor = auth_advisor();
        $paginate = 3;
        $trainees = $advisor->trainees()->paginate($paginate);
        return view('advisor.trainees_list', compact('trainees'));
    }

    public function update(Advisor $advisor)
    {
        $data = request()->validate([
            'displayName' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:advisors,email,' . $advisor->id,
            'phone' => 'required|string|max:255|unique:advisors,phone,' . $advisor->id,
            'address' => 'required|string|max:255',
            'disciplines' => 'required|array|min:1',
            'avatar-image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'cv-file' => 'nullable|mimes:pdf,doc,docx,txt,rtf,odt,ods,odp,odg,odc,odb,xls,xlsx,ppt,pptx',
        ]);

        // Check if the user uploaded a new avatar image
        if (request()->hasFile('avatar-image')) {
            // Delete the old avatar image from firebase storage
            if ($advisor->avatar) {
                $reference = app('firebase.storage')->getBucket()->object($advisor->avatar);
                if ($reference->exists()) {
                    $reference->delete();
                }
                $advisor->avatar_file->delete();
            }
            $avatarImage = request()->file('avatar-image');
            // Using firebase storage to upload files and make a record for File in the database linked with the advisor
            $avatar_file_name = $advisor->firebase_uid . '_advisor_avatar_image.' . $avatarImage->getClientOriginalExtension();
            $avatar_file_path = 'Advisor/Images/' . $avatar_file_name;
            $this->uploadFirebaseStorageFile($avatarImage, $avatar_file_path);

            // Create a file record in the database
            $file = new \App\Models\File;
            $file->name = $avatar_file_name;
            $file->firebase_file_path = $avatar_file_path;
            $file->extension = $avatarImage->getClientOriginalExtension();
            $file->advisor_id = $advisor->id;
            $file->description = 'Advisor Avatar Image';
            $size = $avatarImage->getSize();
            $file->size = $size ? $size : 0;
        }

        // cv-file
        // Check if the user uploaded a new cv file
        if (request()->hasFile('cv-file')) {
            // Delete the old cv file from firebase storage
            if ($advisor->cv) {
                info('Advisor has a cv file');
                $reference = app('firebase.storage')->getBucket()->object($advisor->cv);
                if ($reference->exists()) {
                    $reference->delete();
                }
                $advisor->cv_file->delete();
            }
            $cvFile = request()->file('cv-file');
            // Using firebase storage to upload files and make a record for File in the database linked with the advisor
            $cv_file_name = $advisor->firebase_uid . '_advisor_cv_file.' . $cvFile->getClientOriginalExtension();
            $cv_file_path = 'Advisor/CVs/' . $cv_file_name;
            $this->uploadFirebaseStorageFile($cvFile, $cv_file_path);
            // Create a file record in the database
            $file = new \App\Models\File;
            $file->name = $cv_file_name;
            $file->firebase_file_path = $cv_file_path;
            $file->extension = $cvFile->getClientOriginalExtension();
            $file->advisor_id = $advisor->id;
            $file->description = 'Advisor CV File';
            $size = $cvFile->getSize();
            $file->size = $size ? $size : 0;
            $file->save();
        }

        // Check the disciplines selected by the user and remove the old disciplines and add the new ones
        $disciplines = $data['disciplines'];
        $advisor->disciplines()->detach();
        foreach ($disciplines as $discipline) {
            $advisor->disciplines()->attach($discipline);
        }

        // Check before update firebase user if the email or phone number is used before and not by the same user
        $auth = app('firebase.auth');
        try {
            $firebaseUser = $auth->getUserByEmail($data['email']);
            if ($firebaseUser->uid != $advisor->firebase_uid) {
                return redirect()->back()->with(['fail' => 'The email address is already in use by another account!', 'type' => 'error']);
            }
            // Update firebase user
            $user = $auth->getUser($advisor->firebase_uid);
            $auth->updateUser($user->uid, [
                'email' => $data['email'],
                'displayName' => $data['displayName'],
            ]);
            $status = $advisor->update($data);
            return redirect()->back()->with([$status ? 'success' : 'fail' => $status ? 'Your Information has been updated successfully!' : 'Something is wrong!', 'type' => $status ? 'success' : 'error']);
        } catch (\Throwable $th) { // If the email is not used before then the getUserByEmail will throw an exception
            // Do nothing
            return redirect()->back()->with(['fail' => $th->getMessage(), 'type' => 'error']);
        }
    }


    public function trainees_requests()
    {
        return view('advisor.trainees_requests');
    }

    public function meetings_schedule()
    {
        return view('advisor.meetings_schedule');
    }

    public function notifications()
    {
        return view('advisor.notifications_and_emails');
    }

    public function trainees()
    {
        return view('advisor.trainees');
    }
}
