<?php

namespace App\Http\Controllers\Admin;

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

use App\Models\Billing;
use App\Models\Drug;
use App\Models\Admin;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Test;


class AdminController extends Controller
{
    //

    public function index(){

        return view('admin.dashboard');
    }

    // public function staff(){
    //     $staff = Staff::get();
    //     return view('admin.staff', [
    //         'staff' => $staff
    //     ]);
    // }

    public function staff(){

        $roles = Role::all();

        return view('admin.staff', [
            'roles' => $roles,
        ]);
    }
    
    public function test(){

        $tests = Test::all();

        return view('admin.test', [
            'tests' => $tests,
        ]);
    }

    public function prescription(){

        return view('admin.prescription');
    }

    public function patient(){

        return view('admin.patient');
    }

    public function drug(){
        
        $drugs = Drug::all();

        return view('admin.drug', [
            'drugs' => $drugs,
        ]);
    }

    public function billing(){

        return view('admin.billing');
    }

    public function addStaff(Request $request){
        $validator = Validator::make($request->all(), [
            'lastname' => 'required',
            'othernames' => 'required',
            'email' => 'required|unique:staff',
            'password' => 'required',
            'phone_number' => 'required',
            'address' => 'required',
            'confirm_password' => 'required',
            'role' => 'required',
            'image' => 'required',
            'bio' => 'required',
            'religion' => 'required',
            'dob' => 'required',
            'marital_status' => 'required',
            'gender' => 'required',

        ]);

        if($validator->fails()) {
            alert()->error('Error', $validator->messages()->all()[0])->persistent('Close');
            return redirect()->back();
        }

        if($request->password == $request->confirm_password){
            $password = bcrypt($request->password);
        }else{
            alert()->error('Oops!', 'Password mismatch')->persistent('Close');
            return redirect()->back();
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->lastname.'-'.$request->othernames)));
        $imageUrl = null;
        if($request->has('image')) {
            $imageUrl = 'uploads/staff/'.$slug.'.'.$request->file('image')->getClientOriginalExtension();
            $image = $request->file('image')->move('uploads/staff', $imageUrl);
        }
        $role = Role::all();

        $newStaff = ([
            'lastname' => $request->lastname,
            'othernames' => $request->othernames,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'role' => $request->role,
            'bio' => $request->bio,
            'slug' => $slug,
            'image' => $imageUrl,
            'marital_status' => $request->marital_status,
            'religion' => $request->religion,
            'gender' => $request->gender,
            'dob' => $request->dob,
        ]);

        if(Staff::create($newStaff)){
            alert()->success('Changes Saved', 'Staff added successfully')->persistent('Close');
            return redirect()->back();
        }

        alert()->error('Oops!', 'Something went wrong')->persistent('Close');
        return redirect()->back();
    }

    public function editStaff(Request $request)
    {
        if(!empty($request->staff_id) && !$staff = Staff::find($request->staff_id)){
            alert()->error('Oops', 'Invalid Staff Information')->persistent('Close');
            return redirect()->back();
        }


        $slug = $staff->slug;
        if(!empty($request->lastname) && $request->lastname != $staff->lastname){
            $staff->lastname = $request->lastname;
            $staff->othernames = $request->othernames;
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->lastname.'-', $request->othernames)));
            $staff->slug = $slug;
        }

        if(!empty($request->staffId) && $request->staffId != $staff->staffId){
            $staff->staffId = $request->staffId;
        }

        if(!empty($request->email) && $request->email != $staff->email){
            $staff->email = $request->email;
        }

        if(!empty($request->password) && $request->password != $staff->password){
            $staff->password = $request->password;
        }

        if(!empty($request->phone_number) && $request->phone_number != $staff->phone_number){
            $staff->phone_number = $request->phone_number;
        }

        if(!empty($request->address) && $request->address != $staff->address){
            $staff->address = $request->address;
        }

        if(!empty($request->role) && $request->role != $staff->role){
            $staff->role = $request->role;
        }

        if(!empty($request->bio) && $request->bio != $staff->bio){
            $staff->bio = $request->bio;
        }

        if(!empty($request->marital_status) && $request->marital_status != $staff->marital_status){
            $staff->marital_status = $request->marital_status;
        }

        if(!empty($request->religion) && $request->religion != $staff->religion){
            $staff->religion = $request->religion;
        }

        if(!empty($request->gender) && $request->gender != $staff->gender){
            $staff->gender = $request->gender;
        }

        if(!empty($request->dob) && $request->dob != $staff->dob){
            $staff->dob = $request->dob;
        }
       

        if($request->has('password') && !empty($request->password)){
            if($request->password == $request->confirm_password){
                $password = bcrypt($request->password);
            }else{
                alert()->error('Oops!', 'Password mismatch')->persistent('Close');
                return redirect()->back();
            }
            $staff->password = $password;
        }

        if ($request->hasFile('image')) {
            $imageUrl = 'uploads/staff/' . $slug . '.' . $request->file('image')->getClientOriginalExtension();
            $image = $request->file('image')->move('uploads/staff', $imageUrl);
            $staff->image = $imageUrl; 
        }

        if ($staff->save()) {
            alert()->success('Changes Saved', 'Changes saved successfully')->persistent('Close');
            return redirect()->back();
        }
    }

    


    public function allStaff() { 
        $staffs = Staff::all();
        $roles = Role::all();
        return view('admin.allStaff', [
            'staffs' => $staffs,
            'roles' => $roles
        ]);
    }

    

    public function addDrug(Request $request){
        $validator = Validator::make($request->all(), [
            'trade_name' => 'required',
            'generic_name' => 'required',
            'note' => 'required',
        ]);

        if ($validator->fails()) {
            alert()->error('Error', $validator->messages()->all()[0])->persistent('Close');
            return redirect()->back();
        }

        $newDrug = [
            'trade_name' => $request->trade_name,
            'generic_name' => $request->generic_name,
            'note' => $request->note,
        ];

        if (Drug::create($newDrug)) {
            alert()->success('Success', 'Drug added successfully')->persistent('Close');
            return redirect()->back();
        }

        alert()->error('Error', 'Failed to add drug')->persistent('Close');
        return redirect()->back();
    }

    public function editDrug(Request $request){
        $validator = Validator::make($request->all(), [
            'drug_id' => 'required',
        ]);
    
        if($validator->fails()) {
            alert()->error('Error', $validator->messages()->all()[0])->persistent('Close');
            return redirect()->back();
        }
    
        if(!$drug = Drug::find($request->drug_id)){
            alert()->error('Oops', 'Invalid Drug')->persistent('Close');
            return redirect()->back();
        }
    
        if(!empty($request->trade_name) && $request->trade_name != $drug->trade_name){
            $drug->trade_name = $request->trade_name;
        }
    
        if(!empty($request->generic_name) && $request->generic_name != $drug->generic_name){
            $drug->generic_name = $request->generic_name;
        }
    
        if(!empty($request->note) && $request->note != $drug->note){
            $drug->note = $request->note;
        }
    
        if($drug->save()){
            alert()->success('Changes Saved', 'Drug changes saved successfully')->persistent('Close');
            return redirect()->back();
        }
    
        alert()->error('Oops!', 'Something went wrong')->persistent('Close');
        return redirect()->back();
    }

    public function deleteDrug(Request $request){
        $validator = Validator::make($request->all(), [
            'drug_id' => 'required',
        ]);
    
        if($validator->fails()) {
            alert()->error('Error', $validator->messages()->all()[0])->persistent('Close');
            return redirect()->back();
        }
    
        if(!$drug = Drug::find($request->drug_id)){
            alert()->error('Oops', 'Invalid Drug')->persistent('Close');
            return redirect()->back();
        }
    
        if($drug->delete()) {
            alert()->success('Deleted', 'Drug successfully deleted');
            return redirect()->back();
        }
    
        alert()->error('Oops!', 'Something went wrong')->persistent('Close');
        return redirect()->back();
    }


    public function addTest(Request $request){
        $validator = Validator::make($request->all(), [
            'test_name' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            alert()->error('Error', $validator->messages()->all()[0])->persistent('Close');
            return redirect()->back();
        }

        $newTest = [
            'test_name' => $request->test_name,
            'description' => $request->description,
        ];

        if (Test::create($newTest)) {
            alert()->success('Success', 'Test added successfully')->persistent('Close');
            return redirect()->back();
        }

        alert()->error('Error', 'Failed to add test')->persistent('Close');
        return redirect()->back();
    }

    public function editTest(Request $request){
        $validator = Validator::make($request->all(), [
            'test_id' => 'required',
        ]);

        if($validator->fails()) {
            alert()->error('Error', $validator->messages()->all()[0])->persistent('Close');
            return redirect()->back();
        }

        if(!$test = Test::find($request->test_id)){
            alert()->error('Oops', 'Invalid Test')->persistent('Close');
            return redirect()->back();
        }

        if(!empty($request->test_name) && $request->test_name != $test->test_name){
            $test->test_name = $request->test_name;
        }

        if(!empty($request->description) && $request->description != $test->description){
            $test->description = $request->description;
        }

        if($test->save()){
            alert()->success('Changes Saved', 'Test changes saved successfully')->persistent('Close');
            return redirect()->back();
        }

        alert()->error('Oops!', 'Something went wrong')->persistent('Close');
        return redirect()->back();
    }

    public function deleteTest(Request $request){
        $validator = Validator::make($request->all(), [
            'test_id' => 'required',
        ]);

        if($validator->fails()) {
            alert()->error('Error', $validator->messages()->all()[0])->persistent('Close');
            return redirect()->back();
        }

        if(!$test = Test::find($request->test_id)){
            alert()->error('Oops', 'Invalid Test')->persistent('Close');
            return redirect()->back();
        }

        if($test->delete()) {
            alert()->success('Deleted', 'Test successfully deleted');
            return redirect()->back();
        }

        alert()->error('Oops!', 'Something went wrong')->persistent('Close');
        return redirect()->back();
    }


}
