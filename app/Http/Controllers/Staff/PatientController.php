<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests;
use Illuminate\Support\Facades\Validator;

use SweetAlert;
use Alert;
use Carbon\Carbon;

use App\Models\Billing;
use App\Models\Drug;
use App\Models\Admin;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Test;
use App\Models\Genotype;
use App\Models\Bloodgroup;
use App\Models\Vital;
use App\Models\Session;
use App\Models\TestResult;

class PatientController extends Controller
{
    //

    //STAFF TO PATIENT VIEW LOGIC
    public function viewPatient($slug){
        $patient = Patient::with('sessions')->where('slug', $slug)->firstOrFail();
        $tests = Test::all();
        $vitals = $patient->vitals;
        $testResults = TestResult::all();
        $drugs = Drug::all();
        return view('staff.viewPatient',[
            'patient' => $patient,
            'vitals' => $vitals,
            'tests' => $tests,
            'testResults' => $testResults,
            'drugs' => $drugs,
        ]);
    }

    //ADD VITALS 
    public function addVitals(Request $request){

        $validator = Validator::make($request->all(), [
            'body_temperature' => 'required',
            'pulse_rate' => 'required',
            'respiration_rate' => 'required',
            'blood_pressure_systolic' => 'required',
            'blood_pressure_diastolic' => 'required',
            'notes' => 'required',
        ]);
        

        if($validator->fails()) {
            alert()->error('Error', $validator->messages()->all()[0])->persistent('Close');
            return redirect()->back();
        }

        $newVital = ([
            'patient_id' => $request->patient_id,
            'session_id' => $request->session_id,
            'body_temperature' => $request->body_temperature,
            'pulse_rate' => $request->pulse_rate,
            'respiration_rate' => $request->respiration_rate,
            'blood_pressure_systolic' => $request->blood_pressure_systolic,
            'blood_pressure_diastolic' => $request->blood_pressure_diastolic,
            'notes' => $request->notes,
        ]);

        if(Vital::create($newVital)){
            alert()->success('Changes Saved', 'Vitals added successfully')->persistent('Close');
            return redirect()->back();
        }

        alert()->error('Oops!', 'Something went wrong')->persistent('Close');
        return redirect()->back();
    }



    //ADD SESSION 
    public function createSession(Request $request){
        $patient = Patient::where('id', $request->patient_id)->firstOrFail();
        $uuid = $patient->lastname.' '.$patient->othernames.' '.Carbon::now();
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $uuid)));

        $newSession = ([
            'patient_id' => $request->patient_id,
            'staff_id' => $request->staff_id,
            'slug' => $slug,
            'symptoms' => $request->symptoms,
            'status' => 'Under Treatment',
        ]);

        if(Session::create($newSession)){
            alert()->success('Changes Saved', 'Session added successfully')->persistent('Close');
            return redirect()->back();
        }

        alert()->error('Oops!', 'Something went wrong')->persistent('Close');
        return redirect()->back();
    }

    //FETCH ALL SESSION
    public function fetchPatientSessions($patient_id) {
        $sessions = Session::where('patient_id', $patient_id)->orderBy('created_at', 'desc')->get();
    
        return view('staff.viewPatient', [
            'sessions' => $sessions
        ]);
    }

    // UPDATE SESSION 
    public function updateSession(Request $request){
        if (!empty($request->session_id) && !$session = Session::find($request->session_id)) {
            alert()->error('Oops', 'Invalid Session Information')->persistent('Close');
            return redirect()->back();
        }

        if (!empty($request->staff_id)) {
            $session->staff_id = $request->staff_id;
        }
        if (!empty($request->patient_id)) {
            $session->patient_id = $request->patient_id;
        }
        if (!empty($request->symptoms)) {
            $session->symptoms = $request->symptoms;
        }

        if ($session->save()) {
            alert()->success('Changes Saved', 'Session details updated successfully')->persistent('Close');
            return redirect()->back();
        } else {
            alert()->error('Oops!', 'Failed to update session details')->persistent('Close');
            return redirect()->back();
        }
    }

    //DELETE SESSION
    public function deleteSession(Request $request){
        $validator = Validator::make($request->all(), [
            'session_id' => 'required',
        ]);

        if ($validator->fails()) {
            alert()->error('Error', $validator->messages()->all()[0])->persistent('Close');
            return redirect()->back();
        }

        if(!$session = Session::find($request->session_id)){
            alert()->error('Oops', 'Invalid Session')->persistent('Close');
            return redirect()->back();
        }

        if($session->delete()) {
            alert()->success('Deleted', 'Session successfully deleted');
            return redirect()->back();
        }

        alert()->error('Oops!', 'Something went wrong')->persistent('Close');
        return redirect()->back();
    }

    //UPDATE SESSION STATUS
    public function updateSessionStatus(Request $request){
        $validator = Validator::make($request->all(), [
            'session_id' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            alert()->error('Error', $validator->messages()->first())->persistent('Close');
            return redirect()->back();
        }

        $session = Session::find($request->session_id);
        if (!$session) {
            alert()->error('Error', 'Session not found')->persistent('Close');
            return redirect()->back();
        }

        $session->status = $request->status;
        if ($session->save()) {
            alert()->success('Success', 'Session status updated successfully')->persistent('Close');
        } else {
            alert()->error('Error', 'Failed to update session status')->persistent('Close');
        }
        return redirect()->back();
    }

    
    public function testResult(Request $request){
        $validator = Validator::make($request->all(), [
            'session_id' => 'required',
            'patient_id' => 'required',
            'test_name' => 'required',
            'image' => 'required',
            'summary' => 'required',
        ]);


        if($validator->fails()) {
            alert()->error('Error', $validator->messages()->all()[0])->persistent('Close');
            return redirect()->back();
        }

        $patient_id = $request->patient_id;

        $patient = Patient::findOrFail($patient_id);
        $patient_name = $patient->lastname . '-' . $patient->othernames;

        $test_name = $request->test_name;

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $patient_name . '-' . $test_name)));
        $fileUrl = null;
        if($request->has('image')) {
            $imageUrl = 'uploads/staff/'.$slug.'.'.$request->file('image')->getClientOriginalExtension();
            $image = $request->file('image')->move('uploads/staff', $imageUrl);
        }else {
            $imageUrl = null;
        }

        $newTestResult = ([
            'patient_id' => $request->patient_id,
            'session_id' => $request->session_id,
            'test_name' => $request->test_name,
            'slug' => $slug,
            'image' => $imageUrl,
            'summary' => $request->summary,
        ]);

        if(TestResult::create($newTestResult)){
            alert()->success('Changes Saved', 'Test Result added successfully')->persistent('Close');
            return redirect()->back();
        }

        alert()->error('Oops!', 'Something went wrong')->persistent('Close');
        return redirect()->back();
    }

}
