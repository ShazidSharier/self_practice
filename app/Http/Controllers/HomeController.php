<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    public function getData(){

        $students = DB::table('student_details')
        ->join('student_addresses', 'student_details.id', '=', 'student_addresses.student_details_id')
        ->join('address_types', 'student_addresses.address_types_id', '=', 'address_types.id' )
        ->select(
            'student_details.id',
            'student_details.name',
            'student_details.code',
            'student_details.email',
            'student_details.mobile',
            'student_details.dob',
            'student_details.created_at',
            DB::raw('GROUP_CONCAT(CONCAT(address_types.type, ":", student_addresses.address_details) SEPARATOR "</br>") as address_details')
        )
        ->whereIn('student_details.id',function($query){
            $query->select(DB::raw('MAX(student_details.id)'))
            ->from('student_details')
            ->groupBy('student_details.name');
        })
        ->groupBy(
            'student_details.id',
            'student_details.name',
            'student_details.code',
            'student_details.email',
            'student_details.mobile',
            'student_details.dob',
            'student_details.created_at',
        )->get();
        return response()->json([
            'data' => $students
        ]);
    }
    public function create(){

        $data['form_type'] = "Create";
        $data['address_types'] = DB::table('address_types')->get();
        return view ('create',$data);
    }
    public function edit(string $id){
        $data['form_type'] = 'Edit';
        $data['address_types'] = DB::table('address_types')->get();
        $data['student_details'] = DB::table('student_details')->where('id',$id)->first();
        $data['student_addresses'] = DB::table('student_addresses')
        ->join('address_types', 'student_addresses.address_types_id', '=', 'address_types.id')
        ->select(
            'student_addresses.id',
            'student_addresses.address_details',
            'student_addresses.address_types_id',
            'address_types.type',
        )
        ->where('student_addresses.student_details_id',$id)->get();
        return view('create', $data);
    }
    public function store(Request $request){
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255|unique:student_details,name',
            'email' => 'required|email',
            'mobile' => 'required|string|max:11|min:11',
            'dob' => 'required|date',
            'address_types_id' => 'required|array',
            'address_details' => 'required|array',
        ]);
        $latestCode= DB::table('student_details')->orderBy('id','desc')->value('code');
        $newSerial = $latestCode ? intval($latestCode) +1 :1;
        $studentCode = str_pad($newSerial,6,'0',STR_PAD_LEFT);
        while (DB::table('student_details')->where('code', $studentCode)->exists()) {
            $newSerial++;
            $studentCode = str_pad($newSerial, 6, '0', STR_PAD_LEFT);
        }
        DB::beginTransaction();
        try {
            $student = DB::table('student_details')->insertGetId([
                'name' => $request->name,
                'code' =>$studentCode,
                'email'=> $request->email,
                'mobile' =>$request->mobile,
                'dob'=>$request->dob,
                'users_id' => Auth::id(),
                'created_at'=> now(),
            ]);
            foreach($request->address_types_id as $key => $value)
            DB::table('student_addresses')->insert([
                'users_id' => Auth::id(),
                'student_details_id' => $student,
                'address_types_id' => $value,
                'address_details' => $request->address_details[$key],
                'created_at' => now(),
            ]);
            DB::commit();
            return redirect()->route('dashboard')->with('message', 'Student Details Create Successfully');
        } catch (\Exception $e) {
            return $e;
            DB::rollBack();

        }
    }
    public function update(Request $request, String $id)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            DB::table('student_details')->where('id',$id)->update([
                'name'=>$request->input('name'),
                'email' =>$request->input('email'),
                'mobile' =>$request->input('mobile'),
                'dob' =>$request->input('dob'),
            ]);
            DB::table('student_addresses')->where('student_details_id',$id)->delete();
            foreach($request->address_types_id as $key => $value){
                DB::table('student_addresses')->insert([
                    'users_id' => Auth::id(),
                    'student_details_id' => $id,
                    'address_types_id' => $value,
                    'address_details' => $request->address_details[$key],
                    'created_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('dashboard')->with('message','Student Details Update Successfully');
        } catch (\Exception $e) {
            return $e;
            DB::rollBack();
        }
    }
    public function destroy(String $id)
    {
        DB::beginTransaction();
        try {
            $student = DB::table('student_details')->where('id', $id)->first();
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid student ID",
                ]);
            }
            DB::table('student_marks')->where('student_details_id', $id)->delete();
            DB::table('student_addresses')->where('student_details_id', $id)->delete();
            DB::table('student_details')->where('id', $id)->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Student details deleted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting record: ' . $e->getMessage(),
            ]);
        }
    }

}
