<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use App\User;
use App\Patient;
use App\Appointment;
use App\Timeslot;
class AppointmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->role == 'Doctor'){
            $appointments = Auth::user()->appointments()->paginate(10);
        }
        else
        {
        $appointments = Appointment::where('clinic_id', Auth::user()->clinic_id)->paginate(10);
        }

        return view('appointments.index')->with(['appointments'=>$appointments]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $doctors = User::where('role', 'Doctor')->where('status', 'active')
                    ->where('clinic_id', Auth::user()->clinic_id)->get();
        $patients = Patient::where('clinic_id', Auth::user()->clinic_id)->get();
        return view('appointments.create')->with(['doctors'=>$doctors, 'patients'=>$patients]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($data = $request->all(), Appointment::$rules);

        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $data['time'] = Timeslot::findOrFail($data['timeslot_id'])->slot;
        $data['clinic_id'] = Auth::user()->clinic_id;
        Appointment::create($data);

        return redirect->route('appointments.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $appointment = Appointment::findOrFail($id);

        return view('appointments.show')->with(['appointment'=>$appointment]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $doctors = User::where('role', 'Doctor')->where('status', 'active')->get();
        $patients = Patient::where('clinic_id', Auth::user()->clinic_id)->get();
        $appointment = Appointment::find($id);
        $timeslot = $appointment->timeslot->first()->where('dutyday_id', $appointment->timeslot->dutyday_id)->lists('slot','id');

        return view('appointments.edit')
        ->with(['timeslot'=>$timeslot,'appointment'=>$appointment, 'doctors'=>$doctors, 'patients'=>$patients]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $validator = Validator::make($data = $request->all(), 
            [
            'employee_id' => 'required',
            'timeslot_id' => 'required',
            'patient_id' => 'required',
            'status' => 'required'
            ]);

        if ($validator->fails())
        {
            return redirect->back()->withErrors($validator)->withInput();
        }
        $data['time'] = Timeslot::findOrFail($data['timeslot_id'])->slot;

        if($request->input('status') == '3' || $request->input('status') == '4' || $request->input('status') == '5'){
            $data['time'] = null;
        }

        $appointment->update($data);

        return redirect()->route('appointments.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Appointment::destroy($id);

        return redirect()->route('appointments.index');
    }
}